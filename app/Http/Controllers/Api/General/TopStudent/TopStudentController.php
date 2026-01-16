<?php

namespace App\Http\Controllers\Api\General\TopStudent;

use App\Models\TopStudent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TopStudentResource;
use App\Services\General\TopStudent\TopStudentService as GeneralTopStudentService;

class TopStudentController extends Controller
{
    public function __construct(protected GeneralTopStudentService $topStudentService){} 

    /**
     * @OA\Get(
     *     path="/top-students",
     *     tags={"General", "General - TopStudent"},
     *     summary="Retrieve a list of top_students",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/TopStudentResource")
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
    public function index()
    {
        return $this->topStudentService->getAll() ;
    }
}
