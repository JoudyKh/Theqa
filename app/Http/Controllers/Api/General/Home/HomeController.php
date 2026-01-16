<?php

namespace App\Http\Controllers\Api\General\Home;

use DB;
use App\Http\Controllers\Controller;
use App\Services\General\Home\HomeService;


class HomeController extends Controller
{
    public function __construct(protected HomeService $homeService)
    {
    }


    /**
     * @OA\Get(
     *     path="/home",
     *     tags={"App" , "App - Info"},
     *     summary="Get all info",
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *      security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      @OA\Parameter(
     *         name="locale",
     *         in="header",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"en", "ar"}
     *         ),
     *         description="The locale of the response"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */

    public function index()
    {
        try {
            $data = $this->homeService->getHome();
            return success($data);
        } catch (\Throwable $th) {
            return error($th->getMessage()) ;
        }
    }
}
