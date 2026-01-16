<?php

namespace App\Http\Controllers\Api\Admin\Option;

use App\Models\Option;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OptionResource;
use App\Services\Admin\Option\OptionService;
use App\Http\Requests\Api\Admin\Option\UpdateOptionRequest;

class OptionController extends Controller
{
    public function __construct(protected OptionService $optionService) {}
    
    public function index()
    {
        
    }
    
     /**
     * @OA\Post(
     *     path="/admin/exam/question/options/{option}",
     *     summary="Update an existing option",
     *     description="Update an existing option.",
     *     tags={"Admin" , "Admin - Options"},
     *     @OA\Parameter(
     *         name="option",
     *         in="path",
     *         required=true,
     *         description="The ID of the option to update",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="_method",
     *         in="query",
     *         required=false,
     *         description="Use this query parameter to spoof the HTTP method to PUT",
     *         @OA\Schema(type="string", enum={"PUT"})
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", description="The name of the option"),
     *                 @OA\Property(property="value", type="string", description="The value of the option")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Option successfully updated",
     *         @OA\JsonContent(ref="#/components/schemas/OptionResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Option not found"
     *     )
     * )
     */
    public function update(Option $option , UpdateOptionRequest $request)
    {
        try {
            $this->optionService->update($option , $request->validated()) ;
            return success(OptionResource::make($option)) ;
        } catch (\Throwable $th) {
            
        }
    }

    /**
     * @OA\Delete(
     *     path="/exam/question/options/{option}",
     *     summary="Delete an existing option",
     *     description="Delete an existing option .",
     *     tags={"Admin" , "Admin - Options"},
     *     @OA\Parameter(
     *         name="option",
     *         in="path",
     *         required=true,
     *         description="The ID of the option to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="force",
     *         in="query",
     *         required=true,
     *         description="Whether to force delete the option",
     *         @OA\Schema(
     *             type="integer",
     *             enum={0,1} ,
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Option successfully deleted"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Option not found"
     *     )
     * )
     */
    public function delete(Option $option , $force=null)
    {        
        try {
            $this->optionService->delete($option , request()->boolean('force')) ;
            return success() ;
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

       /**
     * @OA\Patch(
     *     path="/exam/question/options/{option}/restore",
     *     summary="Restore a deleted option",
     *     description="Restore a previously deleted option.",
     *     tags={"Admin" , "Admin - Options"},
     *     @OA\Parameter(
     *         name="option",
     *         in="path",
     *         required=true,
     *         description="The ID of the option to restore",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Option successfully restored",
     *         @OA\JsonContent(ref="#/components/schemas/OptionResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Option not deleted"
     *     )
     * )
     */
    public function restore(Option $option)
    {
        if (!$option->trashed()) {
            return error('not deleted', 'not deleted', 422);
        }
        $option->restore();
        return success(OptionResource::make($option));
    }

    
}
