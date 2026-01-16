<?php

namespace App\Observers;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;

class BookObserver
{
    public $afterCommit = true;
    public function forceDeleted(Book $book): void
    {
        Storage::disk('public')->delete($book->image) ;
        Storage::disk('public')->delete($book->file) ;
    }
}
