<?php

namespace App\Http\Controllers;

/**
 * 
 * @OA\Info(
 *     title="Theqa Apis ",
 *     version="1.0.0",
 *     @OA\Contact(
 *         name="Yosof Bayan",
 *         url="https://wa.me/+963967213544",
 *         email="yosofbayan75@gmail.com"
 *     ),
 * )
 * 
 * 
 * @OA\SecurityScheme(
 *     securityScheme="lmsAuth",
 *     type="apiKey",
 *     in="header",
 *     name="lms",
 *     description="LMS Header for API requests"
 * )
 * 
 * @OA\Server(
 *      url="http://127.0.0.1:8000/api/v1",
 *      description="local Base URL"
 *  )
 * @OA\Server(
 *     url="https://dev-back.theqa-team.com/api/v1",
 *     description="Develop Base URL"
 * )
 * @OA\Server(
 *     url="https://th-back.theqa-team.com/api/v1",
 *     description="Develop Base URL"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer"
 * )
 * @OA\Components(
 *         @OA\Header(
 *             header="Accept",
 *             description="Header indicating the expected response format. Should be set to 'application/json'.",
 *             required=true,
 *             @OA\Schema(type="string", default="application/json"),
 *         ),
 * ),
 */
//App Apis
//SectionController
/*
* @OA\Get(
*     path="/sections",
*     summary="Get super sections ",
*     operationId="app/super-sections",
*     summary="get sections data",
*     tags={"App", "App - Sections"},
*     @OA\Response(
*         response=200,
*         description="Successful response",
*     )
* )
*
*@OA\Get(
*     path="/admin/sections/{parentSection}",
*     operationId="app/sections",
*     summary="get sections data",
*     tags={"App", "App - Sections"},
*     @OA\Parameter(
*     name="parentSection",
*     in="path",
*     description="pass the parent section id ",
*     required=true,
*     @OA\Schema(
*         type="integer"
*     )
*      ),
*    security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "application/json" }},
*    @OA\Response(response=200, description="Successful operation"),
* )
*/


//Admin apis
abstract class Controller
{
    //
}
