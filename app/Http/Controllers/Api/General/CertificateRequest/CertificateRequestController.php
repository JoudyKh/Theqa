<?php

namespace App\Http\Controllers\Api\General\CertificateRequest;

use Illuminate\Http\Request;
use App\Models\CertificateRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateRequestResource;
use App\Http\Requests\Api\General\Section\GetAllSectionRequest;
use App\Http\Requests\Api\General\CertificateRequest\GetAllCertificateRequest;
use App\Http\Requests\Api\App\CertificateRequest\StoreCertificateRequestRequest;
use App\Services\General\CertificateRequest\CertificateRequestService as GeneralCertificateRequestService;

class CertificateRequestController extends Controller
{
    public function __construct(protected GeneralCertificateRequestService $generalCertificateRequestService)
    {
    }


    /**
     * @OA\Get(
     *     path="/certificate-requests",
     *     tags={"General", "General - CertificateRequest" , "Admin", "Admin - CertificateRequest"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     summary="Retrieve a list of certificate requests",
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="ID of the student"
     *     ),
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="ID of the course"
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "accepted", "rejected"},nullable=true),
     *         description="Filter by status of the request"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/CertificateRequestResource")
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request parameters"
     *     )
     * )
     */

    public function index(GetAllCertificateRequest $request)
    {
        return $this->generalCertificateRequestService->getAll($request);
    }
    /**
     * @OA\Post(
     *     path="/certificate-requests",
     *     tags={"App" , "App - CertificateRequest"},
     *     summary="Create a new certificate_request",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreCertificateRequestRequest") ,
     *         )
     *      ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CertificateRequestResource")
     *     )
     * )
     */
    public function store(StoreCertificateRequestRequest $request)
    {
        try {
            $certificate_request = $this->generalCertificateRequestService->store($request);
            return success(CertificateRequestResource::make($certificate_request), 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
}
