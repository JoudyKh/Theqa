<?php

namespace App\Http\Controllers\Api\Admin\Whatsapp;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\WhatsappService;
use App\Http\Requests\Api\Admin\Whatsapp\BroadcastWhatsappRequest;

class WhatsappController extends Controller
{
    public function __construct(protected WhatsappService $whatsappService)
    {
    }
    /**
     * @OA\Post(
     *     path="/admin/whatsapp/broadcast",
     *     summary="Broadcast a WhatsApp message to users",
     *     description="Sends a WhatsApp message to users based on the provided filters and parameters.",
     *     tags={"Admin - Whatsapp" , "Whatsapp"},
     *     security={{"bearerAuth": {} ,"lmsAuth": {}}},
     *     
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/BroadcastWhatsappRequest")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Message broadcast successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Broadcast sent successfully."),
     *             @OA\Property(property="data", type="object", description="Additional response data.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object", description="Detailed validation errors.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred."),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function broadcast(BroadcastWhatsappRequest $request)
    {        
        try {
            return $this->whatsappService->broadcast($request);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
}