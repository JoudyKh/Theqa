<?php

namespace App\Services\Admin\CertificateRequest;
use App\Models\User;
use App\Models\CertificateRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Events\CertificateAcceptedEvent;
use App\Events\CertificateRejectedEvent;
use App\Enums\CertificateRequestStatusEnum;
use App\Http\Resources\CertificateRequestResource;
use App\Notifications\CertificateAcceptedNotification;
use App\Notifications\CertificateRejectedNotification;
use App\Http\Requests\Api\App\CertificateRequest\StoreCertificateRequestRequest;
use App\Http\Requests\Api\Admin\CertificateRequest\UpdateCertificateRequestRequest;

class CertificateRequestService
{
    public function __construct(){}
    public function getAll($trashOnly)
    {
        $certificate_requests = CertificateRequest::orderByDesc($trashOnly ? 'deleted_at' : 'created_at');

        if ($trashOnly) {
            $certificate_requests->onlyTrashed();
        }
        $certificate_requests = $certificate_requests->paginate(config('app.pagination_limit'));
        return CertificateRequestResource::collection($certificate_requests);
    }

    public function update(CertificateRequest &$certificateRequest ,  UpdateCertificateRequestRequest &$request)
    {
        $data = $request->validated() ;
        try {
            return DB::transaction(function () use (&$certificateRequest,&$request,&$data) {

                $old_file = $certificateRequest->file;

                if ($request->has('file')) {
                    if ($request->hasFile('file'))
                        $data['file'] = $request->file('file')->storePublicly('certificates/files', 'public');
                    else
                        $data['file'] = null;
                }

                if($request->has('status'))
                {
                    $changes = [
                        CertificateRequestStatusEnum::PENDING->value => [
                            'file' => null ,
                            'accepted_at' => null ,
                            'rejected_at' => null ,
                        ] ,
                        CertificateRequestStatusEnum::ACCEPTED->value => [
                            'file' => $data['file'] ?? null ,
                            'accepted_at' => now()->toDateString() ,
                            'rejected_at' => null ,
                        ] ,
                        CertificateRequestStatusEnum::REJECTED->value => [
                            'file' => null ,
                            'accepted_at' => null ,
                            'rejected_at' => now()->toDateString()  ,
                        ] ,
                    ] ;
                    $data = array_merge($data , $changes[$request->input('status')]) ;
                }

                $certificateRequest->update($data);
                $certificateRequest->refresh();

                DB::afterCommit(function () use ($old_file,&$request,&$data,&$certificateRequest) {
                                        
                    if($certificateRequest->student_id)
                    {
                        $notificationData = [
                            'clickable' => true,
                            'params' => [
                                'certificate_request' => $certificateRequest ,
                                'course' => $certificateRequest->course
                            ],
                        ] ;

                        $student = User::where('id' , $certificateRequest->student_id)->firstOrFail() ;

                        if($request->get('status') == CertificateRequestStatusEnum::ACCEPTED->value){
                            $notificationData['state'] = CertificateAcceptedNotification::STATE;
                            event(new CertificateAcceptedEvent($student , $notificationData));
                        }
                        
                        if($request->get('status') == CertificateRequestStatusEnum::REJECTED->value){
                            $notificationData['state'] = CertificateRejectedNotification::STATE;
                            event(new CertificateRejectedEvent($student, $notificationData));
                        }
                    }
                    
                    
                    if ($request->has('file') and $old_file and Storage::disk('public')->exists($old_file)) {
                        Storage::disk('public')->delete($old_file);
                    }
                    
                    if (isset($data['file_to_delete']) and $data['file_to_delete'] and Storage::disk('public')->exists($data['file_to_delete'])) {
                        Storage::disk('public')->delete($data['file_to_delete']);
                    }
                    
                });

                return $certificateRequest;
            });
        } catch (\Throwable $th) {
            if (isset($data['file']) and $data['file'] and Storage::disk('public')->exists($data['file'])) {
                Storage::disk('public')->delete($data['file']);
            }
            throw $th;
        }
    }
    public function delete(CertificateRequest $certificate_request , $force):?bool
    {
        if($force){
            return $certificate_request->forceDelete() ;
        }
        return $certificate_request->deleteOrFail() ;
    }
}