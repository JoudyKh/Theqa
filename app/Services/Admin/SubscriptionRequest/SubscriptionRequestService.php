<?php

namespace App\Services\Admin\SubscriptionRequest;

use App\Models\User;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Models\SectionStudent;
use App\Constants\Notifications;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionRequest;
use App\Enums\SectionStudentStatusEnum;
use App\Events\SubscriptionAcceptedEvent;
use App\Events\SubscriptionRejectedEvent;
use App\Services\App\StudentExam\StudentExamService;
use App\Notifications\SubscriptionAcceptedNotification;
use App\Notifications\SubscriptionRejectedNotification;
use App\Services\App\LessonStudent\LessonStudentService;
use App\Services\General\Notification\NotificationService;
use App\Http\Requests\Api\App\SubscriptionRequest\CreateSubscriptionReqRequest;
use App\Http\Requests\Api\Admin\SubscriptionRequest\UpdateSubscriptionReqRequest;

class SubscriptionRequestService
{

    public function __construct(
        protected LessonStudentService $lessonStudentService,
        protected StudentExamService $studentExamService,
        protected NotificationService $notificationService
    ) {
    }

    public function getRequests(Request $request)
    {
        $data = SubscriptionRequest::latest();
        if ($request->status)
            $data->where('status', $request->status);
        $data = $request->boolean('get') ? $data->get() : $data->paginate(config('app.pagination_limit'));
        return $data;
    }

    public function delete(SubscriptionRequest $subscriptionRequest, $force = false): ?bool
    {
        if ($force) {
            return $subscriptionRequest->forceDelete();
        }
        return $subscriptionRequest->delete();
    }

    public function manageStatus(SubscriptionRequest &$subscriptionRequest, UpdateSubscriptionReqRequest &$request)
    {
        return DB::transaction(function () use (&$subscriptionRequest, &$request) {
            
            $subscriptionRequest->update($request->validated());
            $subscriptionRequest->refresh();

            $user = User::where('id', $subscriptionRequest->user_id)->firstOrFail();
            $extraData = [];
            if ($request->input('status') == SectionStudentStatusEnum::ACCEPTED->value) {
                SectionStudent::create([
                    'section_id' => $subscriptionRequest->section_id,
                    'student_id' => $subscriptionRequest->user_id,
                ]);

                $this->lessonStudentService->openFirstLesson(
                    $subscriptionRequest->section_id,
                    $subscriptionRequest->user_id
                );
            }
            if (
                $request->input('status') == SectionStudentStatusEnum::REJECTED->value
                or
                $request->input('status') == SectionStudentStatusEnum::PENDING->value

            ) {
                $deleted = SectionStudent::where([
                    'section_id' => $subscriptionRequest->section_id,
                    'student_id' => $subscriptionRequest->user_id,
                ])->delete();
            }

            DB::afterCommit(function () use ($subscriptionRequest, &$user, $request) {

                $notificationData = [
                    'clickable' => true,
                    'params' => [
                        'subscription_request' => $subscriptionRequest,
                        'course' => $subscriptionRequest->section,
                    ],
                ];

                if ($request->get('status') == SectionStudentStatusEnum::ACCEPTED->value) {
                    $notificationData['state'] = SubscriptionAcceptedNotification::STATE;
                    event(new SubscriptionAcceptedEvent($user, $notificationData));
                }

                if ($request->get('status') == SectionStudentStatusEnum::REJECTED->value) {
                    $notificationData['state'] = SubscriptionRejectedNotification::STATE;
                    event(new SubscriptionRejectedEvent($user, $notificationData));
                }
            });
            return success($subscriptionRequest, 200);
        });
    }
}