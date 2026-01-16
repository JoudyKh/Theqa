<?php

namespace App\Http\Controllers\Api\General\Book;

use App\Models\Book;
use App\Models\Section;
use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\Request;
use App\Services\General\Book\BookService;

class BookController extends Controller
{
    public function __construct(protected BookService $bookService){}

     /**
     * @OA\Get(
     *     path="/admin/sections/{parentSection}/books",
     *     summary="Get a list of all books",
     *     tags={"Admin" , "Admin - Books"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id ,",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of books",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/BookResource")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *     )
     * )
     * 
     *  * @OA\Get(
     *     path="/sections/{parentSection}/books",
     *     summary="Get a list of all books",
     *     tags={"App" , "App - Books"},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of books",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/BookResource")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *     )
     * )
     */
    public function index($parentSection , Request $request)
    {
        return success($this->bookService->getAll($parentSection)) ;
    }

    /**
     * @OA\Get(
     *     path="/sections/{parentSection}/books/{id}",
     *     summary="Get a specific book",
     *     tags={"App" , "App - Books"},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id , dont pass it if its super section or book section  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the book to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details of the requested book",
     *         @OA\JsonContent(ref="#/components/schemas/BookResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *     )
     * )
     * 
     * @OA\Get(
     *     path="/admin/sections/{parentSection}/books/{id}",
     *     summary="Get a specific book",
     *     tags={"Admin" , "Admin - Books"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id , dont pass it if its super section or book section  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the book to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details of the requested book",
     *         @OA\JsonContent(ref="#/components/schemas/BookResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *     )
     * )
     */
    public function show(Section $parentSection , Book $book)
    {
        try {
            if($parentSection->type != Constants::SECTION_TYPE_BOOK_SUB_SECTION){
                throw new \Exception('type error') ;
            }
            
            return success(BookResource::make($book)) ;
        } catch (\Throwable $th) {
            return error($th->getMessage() , [$th->getMessage()] , $th->getCode()) ;
        }
    }

    /**
     * @OA\Get(
     *     path="/sections/{parentSection}/books/{id}/download",
     *     summary="Download a specific book",
     *     tags={"App" , "App - Books"},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id , dont pass it if its super section or book section  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the book to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details of the requested book",
     *         @OA\JsonContent(ref="#/components/schemas/BookResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *     )
     * )
     * 
     * @OA\Get(
     *     path="/admin/sections/{parentSection}/books/{id}/download",
     *     summary="dDownload a specific book",
     *     tags={"Admin" , "Admin - Books"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id , dont pass it if its super section or book section  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the book to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details of the requested book",
     *         @OA\JsonContent(ref="#/components/schemas/BookResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *     )
     * )
     */
    public function download($parentSection , Book $book)
    {
        $pdf = 'storage/' . $book->file ;

        $filePath = public_path($pdf) ;

        if (!file_exists($filePath)) {
            return error('File not found.',['File not found.'], 404);
        }

        return response()->download($filePath , $book->name);
    }
}
