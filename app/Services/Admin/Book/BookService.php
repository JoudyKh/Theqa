<?php

namespace App\Services\Admin\Book;
use App\Models\Book;
use App\Models\Section;
use App\Constants\Constants;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Api\Admin\Book\StoreBookRequest;
use App\Http\Requests\Api\Admin\Book\UpdateBookRequest;

class BookService
{
    public function store(Section &$parentSection , array $data):Book
    {
        if($parentSection->type != Constants::SECTION_TYPE_BOOK_SUB_SECTION){
            throw new \Exception('type error') ;
        }
        
        try {
            $data['image'] = $image = request()->file('image')->storePublicly('books/images' , 'public') ;
            $data['file'] = $file = request()->file('file')->storePublicly('books/files' , 'public') ;

            $book = Book::create($data) ;

            $book->sections()->attach($parentSection) ;
            
            return $book ;
        } catch (\Throwable $th) {
            if(Storage::disk('public')->exists($image)){
                Storage::disk('public')->delete($image) ;
            }
            if(Storage::disk('public')->exists($file)){
                Storage::disk('public')->delete($file) ;
            }
            throw $th;
        }
    }
    public function storeTransaction(Section &$parentSection , StoreBookRequest &$request):Book
    {
        return DB::transaction(function()use(&$parentSection , &$request){
            return $this->store($parentSection , $request->validated()) ;
        }) ;
    }
    public function update(Section &$parentSection , Book &$book , array $data):?bool
    {
        if($parentSection->type != Constants::SECTION_TYPE_BOOK_SUB_SECTION){
            throw new \Exception('type error') ;
        }
        
        try {
            $image = $file = null ;
            
            if(request()->has('image'))
                $data['image'] = $image = request()->file('image')->storePublicly('books/images' , 'public') ;
            if(request()->has('file'))
                $data['file'] = $file = request()->file('file')->storePublicly('books/files' , 'public') ;

            return $book->update($data) ;

        } catch (\Throwable $th) {
            if($image != null and Storage::disk('public')->exists($image)){
                Storage::disk('public')->delete($image) ;
            }
            if($file != null and Storage::disk('public')->exists($file)){
                Storage::disk('public')->delete($file) ;
            }
            throw $th;
        }
    }
    public function updateTransaction(Section &$parentSection , Book &$book , UpdateBookRequest &$request):?bool
    {
        return DB::transaction(function()use(&$parentSection , &$book , &$request){
            return $this->update($parentSection , $book , $request->validated()) ;
        }) ;
    }

    public function delete(Book $book , $force = false):?bool
    {
        if ($force) {
            return $book->forceDelete();
        }
        return $book->deleteOrFail();
    }
}
