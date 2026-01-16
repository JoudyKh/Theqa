<?php

namespace App\Services\Admin\Option;
use Exception;
use App\Models\Option;
use App\Models\StudentAnswer;
use Illuminate\Support\Facades\DB;

class OptionService
{
   public function update(Option &$option , array $data):?bool
   {
        return DB::transaction(function()use($option , $data){
            return $option->update($data) ;
        }) ;
   }
   public function delete(Option &$option , bool $force = false):?bool
   {
        $studentAnswer = StudentAnswer::where('option_id' , $option->id) ;
        if($studentAnswer->exists()){
            throw new Exception(__('messages.option_has_been_chosen') , 403) ;
        }
        
        if($force){
            return $option->forceDelete() ;
        }
        return $option->deleteOrFail() ;
   }
}
