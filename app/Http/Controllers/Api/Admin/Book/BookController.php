<?php

namespace App\Http\Controllers\Api\Admin\Book;

use App\Models\Book;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Services\Admin\Book\BookService;
use App\Http\Requests\Api\Admin\Book\StoreBookRequest;
use App\Http\Requests\Api\Admin\Book\UpdateBookRequest;

class BookController extends Controller
{
    public function __construct(protected BookService $bookService){}
    
    /**
     * @OA\Post(
     *     path="/admin/sections/{parentSection}/books",
     *     tags={"Admin" , "Admin - Books"},
     *     summary="Store a new book",
     *     description="Create a new book entry.",
     *     operationId="storeBook",
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id , dont pass it if its super section or book section  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreBookRequest")
     *         )
     *     ),
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(ref="#/components/schemas/BookResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    public function store(Section $parentSection , StoreBookRequest $request)
    {
        try {
            $book = $this->bookService->storeTransaction($parentSection,$request) ;
            return success(BookResource::make($book)) ;
        } catch (\Throwable $th) {
            return error($th->getMessage() , [$th->getMessage()] , 400) ;
        }
    }

     /**
     * @OA\Post(
     *     path="/admin/sections/{parentSection}/books/{book}",
     *     tags={"Admin" , "Admin - Books"},
     *     summary="Update an existing book",
     *     description="Update the details of an existing book.",
     *     operationId="updateBook",
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
     *         name="_method",
     *         in="query",
     *         required=true,
     *         description="Override HTTP method",
     *         @OA\Schema(type="string", example="PUT")
     *     ),
     *     @OA\Parameter(
     *         name="book",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         ),
     *         description="ID of the book to update"
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateBookRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(ref="#/components/schemas/BookResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    public function update(Section $parentSection , Book $book , UpdateBookRequest $request)
    {
        try {
            $this->bookService->updateTransaction($parentSection,$book,$request) ;
            return success(BookResource::make($book)) ;
        } catch (\Throwable $th) {
            return error($th->getMessage() , [$th->getMessage()] , 400) ;
        }
    }

     /**
     * @OA\Delete(
     *     path="/admin/sections/{parentSection}/books/{book}",
     *     tags={"Admin" , "Admin - Books"},
     *     summary="Delete a book",
     *     description="Delete a book entry. Optionally force delete it.",
     *     operationId="deleteBook",
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
     *         name="book",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         ),
     *         description="ID of the book to delete"
     *     ),
     *     @OA\Parameter(
     *         name="force",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="boolean",
     *             example=false
     *         ),
     *         description="Whether to force delete the book (bypassing soft delete)"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    public function delete($parentSection , Book $book)
    {
        try {
            $this->bookService->delete($book, request()->boolean('force'));
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    
}
