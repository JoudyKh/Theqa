<?php

namespace App\Http\Controllers\Api\Admin\PurchaseCode;

use App\Models\PurchaseCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseCodeResource;
use App\Http\Requests\Api\Admin\PurchaseCode\StorePurchaseCodeRequest;
use App\Http\Requests\Api\Admin\PurchaseCode\UpdatePurchaseCodeRequest;
use App\Services\Admin\PurchaseCode\PurchaseCodeService as AdminPurchaseCodeService;


class PurchaseCodeController extends Controller
{
    public function __construct(protected AdminPurchaseCodeService $purchaseCodeService){}

    /**
     * @OA\Get(
     *     path="/admin/purchase-codes",
     *     tags={"Admin", "Admin - PurchaseCode"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     summary="Retrieve a list of purchase codes",
     *     @OA\Response(
     *         response=200,
     *         description="A list of purchase codes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/PurchaseCodeResource")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return $this->purchaseCodeService->getAll() ;
    }
     /**
     * @OA\Get(
     *     path="/admin/purchase-codes/{purchaseCode}",
     *     tags={"Admin", "Admin - PurchaseCode"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     summary="Retrieve a specific purchase code",
     *     @OA\Parameter(
     *         name="purchaseCode",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details of a specific purchase code",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseCodeResource")
     *     )
     * )
     */
    public function show(PurchaseCode $purchaseCode)
    {
        return PurchaseCodeResource::make($purchaseCode->load('sections')) ;
    }
    /**
     * @OA\Post(
     *     path="/admin/purchase-codes",
     *     tags={"Admin", "Admin - PurchaseCode"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     summary="Create a new purchase code",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StorePurchaseCodeRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="The created purchase code",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseCodeResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function store(StorePurchaseCodeRequest $request)
    {
        try {
            $code = $this->purchaseCodeService->store($request->validated()) ; 
            return success(PurchaseCodeResource::make($code->load('sections')) , 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     * @OA\Post(
     *     path="/admin/purchase-codes/{purchaseCode}",
     *     tags={"Admin", "Admin - PurchaseCode"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     summary="Update an existing purchase code",
     *     @OA\Parameter(
     *         name="purchaseCode",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdatePurchaseCodeRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The updated purchase code",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseCodeResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function update(PurchaseCode $purchaseCode , UpdatePurchaseCodeRequest $request)
    {
        try {
            $this->purchaseCodeService->update($purchaseCode,$request) ;          
            return success(PurchaseCodeResource::make($purchaseCode->load('sections')));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
     /**
     * @OA\Delete(
     *     path="/admin/purchase-codes/{purchaseCode}",
     *     tags={"Admin", "Admin - PurchaseCode"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     summary="Delete a specific purchase code",
     *     @OA\Parameter(
     *         name="purchaseCode",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function delete(PurchaseCode $purchaseCode)
    {
        $purchaseCode->delete() ;
        
        return success() ;
    }
}
