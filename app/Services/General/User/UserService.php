<?php

namespace App\Services\General\User;

use App\Http\Resources\GovernorateResource;
use Exception;
use App\Models\User;
use App\Models\Governorate;
use App\Constants\Constants;
use App\Models\UserFcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\AdminResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\TeacherResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\AbstractPaginator;
use App\Services\App\User\UserService as AppUserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
class UserService
{
    public function __construct(protected AppUserService $appUserService)
    {
    }
    public function getAll($role, $trash = false, $paginate = false, int $limit = null, $inRandomOrder = false)
    {
        $filters = request()->get('filters');
        $sort_by = request()->get('sort_by');

        $currUserId = auth('sanctum')->id();

        $extraData = [];

        if (!in_array($role, Constants::ROLES_ARRAY)) {
            throw new \InvalidArgumentException("Invalid role: $role", 500);
        }

        $isAdmin = Auth::user()?->hasRole(Constants::ADMIN_ROLE);

        $topStudents = null;

        $roleRelationsMap = [
            Constants::ADMIN_ROLE => ['images',],
            Constants::TEACHER_ROLE => ['images',],
            Constants::STUDENT_ROLE => ['images', 'city.governorate',],
        ];


        $users = User::whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        })->with($roleRelationsMap[$role] ?? []);


        if ($filters) {

            if (isset($filters['name'])) {
                $users
                    ->where('last_name', 'LIKE', '%' . $filters['name'] . '%')
                    ->orWhere('first_name', 'LIKE', '%' . $filters['name'] . '%')
                    ->orWhere('full_name', 'LIKE', '%' . $filters['name'] . '%');
            }
            if (isset($filters['phone'])) {
                $users->where('phone_number', 'LIKE', '%' . $filters['phone'] . '%');
            }
            if (isset($filters['city_id']) or isset($filters['governorate_id'])) {
                $users->when(isset($filters['city_id']), function ($userQuery) use ($filters) {
                    $userQuery->where('users.city_id', $filters['city_id']);
                })->when(isset($filters['governorate_id']), function ($query) use ($filters) {
                    $query->whereHas('city', function ($cityQuery) use ($filters) {
                        $cityQuery->where('cities.governorate_id', $filters['governorate_id']);
                    });
                });
            }

            if (isset($filters['is_banned'])) {
                $users->where('is_banned', $filters['is_banned']);
            }

            if (isset($filters['is_active'])) {
                $users->where('is_active', $filters['is_active']);
            }
        }

        if ($limit) {
            $users->limit($limit);
        }
        if ($inRandomOrder) {
            $users->inRandomOrder();
        }
        if ($trash && $isAdmin) {
            $users->onlyTrashed();
        }

        if ($role == Constants::STUDENT_ROLE) {

            $topStudentsSortArray = request()->boolean('top_students') ? [
                'solved_exams_count' => 'desc',
                'total_avg' => 'desc',
                'attempts_count_sum' => 'asc',
            ] : [];

            if ($sort_by and isset($sort_by['total_avg']) and in_array(strtolower($sort_by['total_avg']), ['asc', 'desc'])) {
                $topStudentsSortArray['solved_exams_count'] = $topStudentsSortArray['solved_exams_count'] ?? 'desc';
                $topStudentsSortArray['total_avg'] = $sort_by['total_avg'] ?? 'desc';
            }

            $users->withTopStudentsQuery(
                $topStudentsSortArray,
                'ASC',
                false
            );

            $extraData['curr_student'] = null;

            if (request()->boolean('top_students') and !request()->is('*admin*')) {

                $topStudents = User::fromSub($users->whereNull('users.deleted_at'), 'top_students')
                    ->with($roleRelationsMap[$role] ?? [])
                    ->withTrashed()
                    ->limit(3)
                    ->get();

                if (
                    !$topStudents->contains(function ($user) use ($currUserId) {
                        return $user->id === $currUserId;
                    })
                ) {
                    $currUser = $currTopStudent = User::fromSub($users->whereNull('users.deleted_at'), 'top_students')
                        ->with($roleRelationsMap[$role] ?? [])
                        ->where('id', $currUserId)
                        ->withTrashed()
                        ->first();

                    $extraData['curr_student'] = $currUser ? StudentResource::make($currUser) : null;
                }
            }
        }


        $users->withCount('devices');

        if (request()->has('order_by_devices_count')) {
            $users->orderBy('devices_count', request()->boolean('order_by_devices_count') ? 'desc' : 'asc');
        }
        if (isset($filters['devices_count'])) {
            $users->having('devices_count', '=', $filters['devices_count']);
        }


        $users->orderByDesc('users.created_at');

        if (!($users instanceof Collection)) {
            $users = ($paginate) ? $users->paginate(config('app.pagination_limit')) : $users->get();
        }

        if (request()->is('*admin*')) {
            $governorates = Governorate::with('cities')->get();
            $extraData['governorates'] = GovernorateResource::collection($governorates);
        }

        $roleResourceMap = [
            Constants::TEACHER_ROLE => fn($T) => TeacherResource::collection($T),
            Constants::ADMIN_ROLE => fn($T) => AdminResource::collection($T),
            Constants::STUDENT_ROLE => fn($T) => StudentResource::collection($T),
        ];

        $response = $roleResourceMap[$role]($topStudents ?? $users);

        return success($response, 200, $extraData);
    }

    /**
     * @throws Exception
     */
    public function validateRole(User $user, $role): User
    {
        if (!$user->hasRole($role)) {
            throw new Exception(__('messages.user_not_student'), 404);
        }
        return $user;
    }

    public function createUser(FormRequest $request, $role, $isSignUp = false, $overrideData = []): User
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create(array_merge($data, $overrideData));
        $user->assignRole($role);
        $this->handleUserImage($user, $request);
        if ($isSignUp) {
            $user['token'] = $this->generateUserToken($user);
            if ($request->fcm_token) {
                $this->appUserService->handleFcmToken($user, $request->fcm_token);
            }
        }
        return $user;
    }

    public function handleUserImage(?User $user, FormRequest $request): void
    {
        if ($request->has('trash_images_ids')) {
            $user->images()->whereIn('id', $request->input('trash_images_ids', []))->delete();
        }
        if ($request->hasFile('image')) {
            $user->images()->updateOrCreate(
                ['user_id' => $user->id], // Search criteria
                ['image' => $request->file('image')->storePublicly('users/images', 'public')] // Values to update or create
            );
        } elseif ($request->has('image') && $request->image === null) {
            $image = $user->images()->first();
            if ($image && Storage::exists('public/' . $image->image)) {
                Storage::delete('public/' . $image->image);
            }
            $user->images()->delete();
        }
    }

    protected function generateUserToken(User $user): string
    {
        return $user->createToken('auth')->plainTextToken;
    }

}
