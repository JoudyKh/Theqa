<?php

namespace App\Http\Controllers\Api\Admin\CertificateRequest;

use Illuminate\Http\Request;
use App\Models\CertificateRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateRequestResource;
use App\Http\Requests\Api\Admin\CertificateRequest\UpdateCertificateRequestRequest;
use App\Services\Admin\CertificateRequest\CertificateRequestService as AdminCertificateRequestService;

class CertificateRequestController extends Controller
{
    public function __construct(protected AdminCertificateRequestService $certificateRequestService)
    {
    }

    /**
     * @OA\Post(
     *     path="/admin/certificate-requests/{id}",
     *     tags={"Admin", "Admin - CertificateRequest"},
     *     summary="Update an existing certificate_request (simulated PUT request)",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="_method",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", enum={"PUT"}, default="PUT")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateCertificateRequestRequest") 
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CertificateRequestResource")
     *     )
     * )
     */
    public function update(CertificateRequest $certificate_request, UpdateCertificateRequestRequest $request)
    {
        try {
            $this->certificateRequestService->update($certificate_request, $request);
            return success(CertificateRequestResource::make($certificate_request));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/certificate-requests/{id}",
     *     tags={"Admin", "Admin - CertificateRequest"},
     *     summary="Delete an certificate_request",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the certificate_request to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="force",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0,1} ,
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function delete(CertificateRequest $certificate_request, $force = null)
    {
        try {
            $this->certificateRequestService->delete($certificate_request, request()->boolean('force'));
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     * @OA\Patch(
     *     path="/admin/certificate-requests/{id}/restore",
     *     tags={"Admin", "Admin - CertificateRequest"},
     *     summary="Restore a soft-deleted certificate_request",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="force",
     *         in="query",
     *         description="If true, performs a force delete; if not provided, performs a soft delete",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0,1} ,
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */

    public function restore(CertificateRequest $certificate)
    {
        if (!$certificate->trashed()) {
            return error('not deleted', 'not deleted', 422);
        }
        $certificate->restore();
        return success(CertificateRequestResource::make($certificate));
    }
}