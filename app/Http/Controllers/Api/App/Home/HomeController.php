<?php

namespace App\Http\Controllers\Api\App\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\App\Home\HomeService;

class HomeController extends Controller
{
    public function __construct(protected HomeService $appHomeService){}

    /**
     * @OA\Get(
     *     path="/home/mobile",
     *     tags={"App" , "App - Info"},
     *     summary="Get all mobile home info",
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
             $data = $this->appHomeService->getMobileHome();
             return success($data);
         } catch (\Throwable $th) {
            return error($th->getMessage()) ;
         }
         
    }
}
