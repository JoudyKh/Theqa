<?php
namespace App\Http\Controllers\Api\App\ContactMessage;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactMessageResource;
use App\Http\Requests\Api\Admin\ContactMessage\StoreContactMessageRequest;
use App\Services\App\ContactMessage\ContactMessageService as AppContactMessageService;

class ContactMessageController extends Controller
{
    public function __construct(protected AppContactMessageService $contactMessagesService)
    {
    }

    /**
     * @OA\Post(
     *     path="/contact-messages",
     *     summary="Create a new contact message",
     *     tags={"App" , "App - ContactMessage"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="first_name",
     *                 type="string",
     *                 description="The name of the person sending the message",
     *                 example="John Doe"
     *             ),
     *             @OA\Property(
     *                 property="last_name",
     *                 type="string",
     *                 description="The name of the person sending the message",
     *                 example="John Doe"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 description="The email of the person sending the message",
     *                 example="johndoe@example.com"
     *             ),
     *             @OA\Property(
     *                 property="phone",
     *                 type="string",
     *                 description="The phone number of the person sending the message",
     *                 example="12345678"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="The content of the message",
     *                 example="Hello, I would like to know more about your services."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contact message created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ContactMessageResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function store(StoreContactMessageRequest $request)
    {
        $message = $this->contactMessagesService->store($request->validated());

        return success(ContactMessageResource::make($message));
    }

}
