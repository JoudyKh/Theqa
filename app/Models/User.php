<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Constants\Constants;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

        'username',
        'email',
        'first_name',
        'last_name',

        'phone_number',
        'family_phone_number',

        'phone_number_country_code',
        'family_phone_number_country_code',

        'email_verified_at',
        'password',
        'last_active_at',

        'location',
        'full_name',
        'birth_date',

        //for teacher

        'description',
        'is_hidden',

        //new
        'city_id',
        'is_active',
        'is_banned',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pivot',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $appends = [
        'image',
        'full_name',
    ];

    public function getFullNameAttribute()
    {
        return trim(trim($this->attributes['full_name'] ?? '') ?: "{$this->first_name} {$this->last_name}");
    }

    public function getImageAttribute()
    {
        if (!$this->relationLoaded('images')) {
            return null;
        }
        $firstImage = $this->images()->first();
        return $firstImage ? $firstImage->image : null;
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function images()
    {
        return $this->hasMany(UserImage::class);
    }

    public function fcmTokens()
    {
        return $this->hasMany(UserFcmToken::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function isStudent()
    {
        return $this->hasRole(Constants::STUDENT_ROLE);
    }

    public function isTeacher()
    {
        return $this->hasRole(Constants::TEACHER_ROLE);
    }

    public function teacherCourses(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'course_teacher', 'teacher_id', 'course_id');
    }

    public function studentExams(): HasMany
    {
        return $this->hasMany(StudentExam::class, 'student_id');
    }
    public function studentSectionsPivotTable(): HasMany
    {
        return $this->hasMany(SectionStudent::class, 'student_id');
    }
    public function studentLessonsPivotTable(): HasMany
    {
        return $this->hasMany(LessonStudent::class, 'student_id');
    }

    public function studentCourses(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'section_student', 'student_id', 'section_id')
            ->withPivot(['created_at']);
    }

    //this didnt work with eager loading ->load('courses') ;
    public function courses(): BelongsToMany
    {
        if ($this->isTeacher()) {
            return $this->teacherCourses();
        } elseif ($this->isStudent()) {
            return $this->studentCourses();
        }

        throw new \Exception('User is neither a student nor a teacher.');
    }

    public function subscriptionRequests()
    {
        return $this->hasMany(SubscriptionRequest::class);
    }
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_student', 'student_id', 'section_id')->withTimestamps();
    }
    public function scopeWithTopStudentsQuery($builder, $orderBy = [], $studentExamOrder = 'ASC', $allAttemptsForTotalAvg = false)
    {
        $builder
            ->leftJoinSub(
                DB::table('student_exams as last_student_exams')
                    ->join('exams', 'last_student_exams.exam_id', '=', 'exams.id')
                    ->whereNotNull('last_student_exams.start_date')
                    ->whereNull('last_student_exams.deleted_at')
                    ->selectRaw("
                    last_student_exams.*,
                    ROW_NUMBER() OVER (
                        PARTITION BY last_student_exams.student_id, last_student_exams.exam_id
                        ORDER BY last_student_exams.created_at $studentExamOrder
                    ) AS row_num
                ")
                ->whereNull('exams.deleted_at'),
                'RankedStudentExams',
                function ($join) use ($allAttemptsForTotalAvg) {
                    $join->on('RankedStudentExams.student_id', '=', 'users.id');
                    if ( ! $allAttemptsForTotalAvg) {
                        $join->whereRaw('RankedStudentExams.row_num = 1');
                    }
                }
            )
            ->whereNull('users.deleted_at')
            ->selectRaw('
            users.id,
            users.is_banned,
            users.location,
            users.full_name,
            users.birth_date,
            users.username,
            users.email,
            users.first_name,
            users.last_name,
            users.phone_number,
            users.family_phone_number,
            users.email_verified_at,
            users.password,
            users.is_active,
            users.last_active_at,
            users.description,
            users.is_hidden,
            users.created_at,
            users.updated_at,
            users.deleted_at,

            -- Calculate the total average
            CASE
                WHEN COUNT(RankedStudentExams.id) = 0 THEN 0
                ELSE (
                    AVG(
                        CASE
                            WHEN ( RankedStudentExams.total_degree > 0 )
                                    THEN COALESCE( (RankedStudentExams.degree / RankedStudentExams.total_degree) , 0)
                            ELSE 0
                        END
                    ) * 100
                )
            END AS total_avg,

            -- Calculate solved exams count for first/last attempts only
            SUM(
                CASE
                    WHEN
                      RankedStudentExams.row_num = 1
                      -- AND RankedStudentExams.total_degree > 0
                      -- AND RankedStudentExams.degree IS NOT NULL
                      AND RankedStudentExams.start_date IS NOT NULL
                      -- AND RankedStudentExams.end_date IS NOT NULL
                      -- AND ((RankedStudentExams.degree * 100) / RankedStudentExams.total_degree) >= RankedStudentExams.exam_pass_percentage
                    THEN 1
                    ELSE 0
                END
            ) AS solved_exams_count,

            -- Calculate failed exams count for first/last attempts only
            SUM(
                CASE
                    WHEN RankedStudentExams.row_num = 1
                    AND RankedStudentExams.start_date IS NOT NULL
                    AND (
                        (RankedStudentExams.total_degree > 0 AND ((RankedStudentExams.degree * 100) / RankedStudentExams.total_degree) < RankedStudentExams.exam_pass_percentage)
                        OR RankedStudentExams.total_degree IS NULL
                    )
                    THEN 1
                    ELSE 0
                END
            ) AS failed_exams_count,


            -- Calculate the total number of attempts
            (
                SELECT COUNT(*) FROM student_exams t_student_exams
                WHERE t_student_exams.student_id = users.id
            ) AS attempts_count_sum,

            -- Assign ranks based on solved exams, total average, and attempts count
            ROW_NUMBER() OVER (
                ORDER BY
                    COUNT(
                        CASE
                              WHEN RankedStudentExams.row_num = 1
                              -- WHEN RankedStudentExams.total_degree > 0
                              -- AND RankedStudentExams.degree IS NOT NULL
                              AND RankedStudentExams.start_date IS NOT NULL
                              -- AND RankedStudentExams.end_date IS NOT NULL
                              -- AND ((RankedStudentExams.degree * 100) / RankedStudentExams.total_degree) >= RankedStudentExams.exam_pass_percentage
                            THEN 1
                            ELSE NULL
                        END
                    ) DESC,
                    CASE
                        WHEN COUNT(RankedStudentExams.id) = 0 THEN 0
                        ELSE (
                            AVG(
                                CASE
                                    WHEN RankedStudentExams.total_degree = 0 THEN 0
                                    ELSE (
                                        (RankedStudentExams.degree / RankedStudentExams.total_degree)
                                    )
                                END
                            )
                        )
                    END DESC,
                    (
                        SELECT COUNT(*) FROM student_exams last_student_exams
                        WHERE last_student_exams.student_id = users.id
                    ) ASC
            ) AS top_rank
        ')
            ->groupBy(
                'users.id',
                'users.location',
                'users.full_name',
                'users.birth_date',
                'users.username',
                'users.email',
                'users.first_name',
                'users.last_name',
                'users.phone_number',
                'users.family_phone_number',
                'users.email_verified_at',
                'users.password',
                'users.is_active',
                'users.last_active_at',
                'users.description',
                'users.is_hidden',
                'users.is_banned',
                'users.created_at',
                'users.updated_at',
                'users.deleted_at'
            );

        order_by_fields($builder, $orderBy);
    }
}
