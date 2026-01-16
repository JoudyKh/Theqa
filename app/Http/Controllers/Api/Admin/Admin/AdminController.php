<?php

namespace App\Http\Controllers\Api\Admin\Admin;
use App\Models\User;
use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminResource;
use App\Services\Admin\Admin\AdminService;
use Illuminate\Pagination\AbstractPaginator;
use App\Http\Requests\Api\Admin\Admin\StoreAdminRequest;
use App\Http\Requests\Api\Admin\Admin\UpdateAdminRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class AdminController extends Controller
{
    public function __construct(protected AdminService $adminService)
    {
    }
    /**
     * @OA\Get(
     *     path="/admin/admins",
     *     tags={"Admin", "Admin - Admins"},
     *     summary="Retrieve a list of admins",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="trash",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of admins",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/AdminResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request parameters"
     *     ),
     *     @OA\Header(
     *         header="accept",
     *         description="return json",
     *         @OA\Schema(
     *             type="string",
     *             example="application/json"
     *         )
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection|AbstractPaginator
    {
        return $this->adminService->getAll(Constants::ADMIN_ROLE, $request->trash, true);
    }

    /**
     * @OA\Get(
     *     path="/admin/admins/{user}",
     *     tags={"Admin", "Admin - Admins"},
     *     summary="Retrieve a specific admin",
     *     description="Retrieve a specific admin by user ID. Returns an error if the user is not a admin.",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the admin to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found or not a admin",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The user is not a admin")
     *         )
     *     )
     * )
     */
    public function show(User $admin): JsonResponse|adminResource
    {
        try {
            return success(AdminResource::make($admin->loadMissing(['images'])));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/admins",
     *     tags={"Admin", "Admin - Admins"},
     *     summary="Store a new admin",
     *     description="Create a new admin record. Returns the created admin resource.",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreAdminRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function store(StoreAdminRequest $request): JsonResponse
    {
        try {
            $user = $this->adminService->store($request, Constants::ADMIN_ROLE);
            return success(AdminResource::make($user));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/admins/{user}",
     *     tags={"Admin", "Admin - Admins"},
     *     summary="Update an existing admin",
     *     description="Update details of an existing admin. Returns the updated admin resource.",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="_method",
     *         in="query",
     *         required=true,
     *         description="Override HTTP method",
     *         @OA\Schema(type="string", example="PUT")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the admin to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateAdminRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AdminResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Admin not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function update(User $admin, UpdateAdminRequest $request)
    {
        try {
            $this->adminService->update($admin, $request->validated());
            return success(AdminResource::make($admin->load(['images' , 'roles'])));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/admins/{admin}",
     *     summary="Delete a admin",
     *     description="Deletes a admin. If `force` is specified, the admin will be permanently deleted; otherwise, it will be soft deleted.",
     *     operationId="delete admin",
     *     tags={"Admin", "Admin - Admins"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         required=true,
     *         description="ID of the admin to delete",
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
     *         response="200",
     *         description="Successfully deleted admin",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request, possibly due to invalid parameters",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid request")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="admin not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="admin not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Server error")
     *         )
     *     )
     * )
     */
    public function delete(User $admin, $force = null): JsonResponse
    {
        try {
            return $this->adminService->delete($admin, request()->boolean('force'));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    public function restore()
    {
        //
    }
}
