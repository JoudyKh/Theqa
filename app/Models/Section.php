<?php

namespace App\Models;

use Auth;
use App\Constants\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Scopes\WithTeachersIfCourses;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'type',
        'name',
        'image',
        'description',
        'is_free',
        'price',
        'discount',
        'is_special',
        'intro_video',
    ];
    protected $hidden = [];
    protected $appends = ['total_price'];

    protected static function boot()
    {
        parent::boot();
        static::retrieved(function ($model) {
            if (!$model?->type || !in_array($model->type, array_keys(Constants::SECTIONS_TYPES))) {
                return;
            }
            $model->hidden = $model->getHiddenAttributes();
        });

        static::saving(function ($model) {
            if (!$model?->type || !in_array($model->type, array_keys(Constants::SECTIONS_TYPES))) {
                return;
            }
            $model->hidden = $model->getHiddenAttributes();
        });
    }

    public function getTotalPriceAttribute()
    {
        return ($this->price ?? 0) - (($this->discount ?? 0) * ($this->price ?? 0)) / 100;
    }

    public static function mergeSubscribed(?User $user = null, $mergeWithRequest = true)
    {
        if (!$user) {
            $user = User::with(['roles', 'studentSectionsPivotTable'])
                ->where('id', auth('sanctum')->id())
                ->first();
        }

        if (!$user or !$user->hasRole(Constants::STUDENT_ROLE))
            return null;

        $student_courses_ids = $user->studentSectionsPivotTable->pluck('section_id')->toArray();

        if ($mergeWithRequest and !app()->bound('student_courses_ids')) {
            app()->instance(
                'student_courses_ids',
                $student_courses_ids
            );
        }

        return $student_courses_ids;
    }


    public function getHiddenAttributes(): array
    {

        $sectionAttributes = Constants::SECTIONS_TYPES[$this->attributes['type']]['attributes'];
        $sectionAttributes[] = 'id';
        $sectionAttributes[] = 'parent_id';
        $sectionAttributes[] = 'type';
        $allAttributes = Schema::getColumnListing($this->getTable());

        //only courses has a price .
        if ($this->type != 'courses') {
            $allAttributes[] = 'total_price';
        }

        return array_diff($allAttributes, $sectionAttributes);
    }

    public function getTeacherAttribute()
    {
        $firstTeacher = $this->teachers()->first();
        return $firstTeacher ?? null;
    }

    public function subSections(): HasMany
    {
        return $this->hasMany(Section::class, 'parent_id');
    }
    public function parentSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'parent_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'section_student', 'section_id', 'student_id');
    }
    public function sectionStudents(): HasMany
    {
        return $this->hasMany(SectionStudent::class, 'section_id');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_teacher', 'course_id', 'teacher_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('lesson_order');
    }

    public function units()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('type', Constants::SECTION_TYPE_COURSE_SECTIONS);
    }

    public function freeLessons(): HasMany
    {
        return $this->hasMany(Lesson::class)
            ->where('is_free', 1)->orderBy('lesson_order');
    }

    public function paidLessons(): HasMany
    {
        return $this->hasMany(Lesson::class)
            ->where('is_free', 0)->orderBy('lesson_order');
    }

    public function scopeWithSubSectionLessonTimes(Builder $query, $section_id = null)
    {
        if ($section_id) {
            $query->where('sections.id', $section_id);
        }
    }

    public function getSubscribedAttribute()
    {
        $student = app('student');

        if (!$student) {
            return null;
        }

        $section_student = $this->relationLoaded('sectionStudents') ? $this->getRelation('sectionStudents') : null;

        if (!$section_student) {
            return null;
        }

        return $section_student->contains('student_id', $student->id);
    }

    public static function getStudentSubscribed(Section &$section): bool
    {
        $student = app('student');
        return $section->subscribed = $student->sections()->where('sections.id', $section->id)->exists();
    }

    public static function getStudentSubscribedQI(&$sections)
    {
        return $sections->with('students');
    }

    public static function eagerLoadRelations(Section &$section)
    {
        // if (request()->boolean('subSections')) {
        //     $section->load('subSections');
        // }

        $user = User::with(['roles'])->where('id', auth('sanctum')->id())->first();

        $sectionStudent = null;

        if ($user and $user->hasRole(Constants::STUDENT_ROLE)) {
            // $user->loadMissing('studentLessonsPivotTable');

            // $nextLessonId = Lesson::loadLessonStudentArray($user, false);

            // if ($nextLessonId and ($nextLessonId == -1 or $user->studentLessonsPivotTable->where('lesson_id', $nextLessonId)->count() > 0)) {
            //     request()->merge(['next_lesson_id' => $nextLessonId]);
            // }
            //means subscribed
            $sectionStudent = SectionStudent::where([
                'section_id' => $section->id,
                'student_id' => auth('sanctum')->id(),
            ]);
        }

        $relations = [
                //todo cancel
            Constants::SECTION_TYPE_COURSES => array_merge(
                ['teachers'],
            ),
            Constants::SECTION_TYPE_COURSE_SECTIONS => ['lessons'],
            Constants::SECTION_TYPE_BOOK_SUB_SECTION => ['books'],
        ];
        if (isset($relations[$section->type]))
            $section = $section->with($relations[$section->type])->withSubSectionLessonTimes()->find($section->id);
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }

    public function exams()
    {
        return $this->morphMany(Exam::class, 'model');
    }

    public function children()
    {
        return $this->hasMany(Section::class, 'parent_id');
    }


    public static function getAllSubSectionsLessonsTimes(Section &$section)
    {
        $totalSeconds = 0;
        $lessons = $section->lessons->pluck('time');
        foreach ($lessons as $time) {
            $totalSeconds += convertTimeToSeconds($time);
        }
        foreach ($section->children as $child) {
            $totalSeconds += self::getAllSubSectionsLessonsTimes($child);
        }
        $section->total_lessons_time = $totalSeconds;
        return $totalSeconds;
    }

    public static function formatAllSubSectionsLessonsTimes(Section &$section)
    {
        $section->total_lessons_time = convertSecondsToTime($section->total_lessons_time);
    }

    public static function getFirstExam($sectionId, int $depth = null)
    {
        if ($depth === null) {
            $depth = 0;
        }

        if ($sectionId instanceof Section)
            $sectionId = $sectionId->id;

        return Section::where(function ($sec) use ($depth, $sectionId) {
            if ($depth > 0) {
                $depth--;
                $sec->where('sections.parent_id', $sectionId);
            } else
                $sec->where('sections.id', $sectionId);
        })
            ->when($depth > 0, function ($query) use ($depth) {
                for ($i = 0; $i < $depth; $i++) {
                    $query->join(
                        'sections as sub_sections' . ($i + 1),
                        'sub_sections' . ($i + 1) . '.parent_id',
                        '=',
                        $i === 0 ? 'sections.id' : 'sub_sections' . $i . '.id'
                    )
                        ->whereNull(['sub_sections' . ($i + 1) . '.deleted_at']);
                }
                $query->join('exams', 'exams.model_id', '=', 'sub_sections' . $depth . '.id');
            }, function ($query) {
                $query->join('exams', 'exams.model_id', '=', 'sections.id');
            })
            ->whereNull(['exams.deleted_at'])
            ->orderBy('exam_order')
            ->select('exams.*')
            ->first()
        ;
    }


    public static function getFirstLesson(Section &$section)
    {
        return $section
            ->orderBy('lessons.lesson_order')
            ->select('*')
            ->first();
    }

    public static function isSubscribed(string|int $sectionId, $userId = null)
    {


        $ok = SectionStudent::where([
            'section_id' => $sectionId,
            'student_id' => $userId ?? auth('sanctum')->id(),
        ])->exists();

        return $ok;
    }

    public static function getNextSection(Section &$section)
    {
        if (!$section)
            return null;

        return Section::where('parent_id', $section->parent_id)
            ->where('sections.id', '>', $section->id)
            ->first();
    }
}
