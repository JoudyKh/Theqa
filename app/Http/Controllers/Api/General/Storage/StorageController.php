<?php

namespace App\Http\Controllers\Api\General\Storage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\General\Storage\StorageService;
use App\Http\Requests\Api\General\Storage\DownloadRequest;

class StorageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/storage/download",
     *     summary="Download a specific file",
     *     tags={"App" , "App - Info" , "App - Storage"},
     *     @OA\Parameter(
     *          name="file_path",
     *          in="query",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Parameter(
     *         name="file_name",
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *     )
     * )
     * 
     * @OA\Get(
     *     path="/admin/storage/download",
     *     tags={"Admin" , "Admin - Info" , "Admin - Storage"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *          name="file_path",
     *          in="query",
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Parameter(
     *         name="file_name",
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *     )
     * )
     */
    public function download(DownloadRequest $request)
    {
        return response()->download(
            public_path('/storage/' . $request->validated('file_path'))
            ,
            $request->validated('file_name')
        );
    }
}
