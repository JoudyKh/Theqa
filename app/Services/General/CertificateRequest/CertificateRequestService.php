<?php

namespace App\Services\General\CertificateRequest;
use App\Models\CertificateRequest;
use App\Enums\CertificateRequestStatusEnum;
use App\Http\Resources\CertificateRequestResource;
use App\Http\Requests\Api\General\CertificateRequest\GetAllCertificateRequest;
use App\Http\Requests\Api\App\CertificateRequest\StoreCertificateRequestRequest;

class CertificateRequestService
{
    public function getAll(GetAllCertificateRequest &$request)
    {
        $certificate_requests = CertificateRequest::with(['course', 'student.images'])->orderByDesc('created_at');

        if ($request->has('student_id')) {
            $certificate_requests->where('student_id', $request->validated('student_id'));
        }
        if ($request->has('course_id')) {
            $certificate_requests->where('course_id', $request->validated('course_id'));
        }
        if ($request->has('status')) {
            $certificate_requests->where('status', $request->validated('status'));
        }
        if ($request->has('accepted')) {
            $certificate_requests->whereNotNull('accepted');
        }
        if ($request->has('rejected')) {
            $certificate_requests->whereNotNull('rejected');
        }

        $certificate_requests = $request->boolean('get') ? $certificate_requests->get() : $certificate_requests->paginate(config('app.pagination_limit'));

        return CertificateRequestResource::collection($certificate_requests);
    }

    public function store(StoreCertificateRequestRequest &$request): CertificateRequest
    {
        $data = [
            'student_id' => auth('sanctum')->id(),
            'course_id' => $request->validated('course_id'),
            'status' => CertificateRequestStatusEnum::PENDING->value,
            'created_at' => now()->toDateString(),
        ];
        return CertificateRequest::create($data);
    }
}
