<?php

namespace App\Services\General\Book;
use App\Models\Book;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\Request;

class BookService
{
    public function getAll($parentSection)
    {
        $query = Book::query() ;

        if(request()->boolean('trash'))
        {
            $query->onlyTrashed();
        }
        
        if($parentSection != 'all'){
            $query->whereHas('sections' , function($query)use($parentSection){
                $query->where('sections.id' , $parentSection) ;
            }) ;
        }

        return BookResource::collection($query->paginate(config('app.pagination_limit'))) ;
    }
}
