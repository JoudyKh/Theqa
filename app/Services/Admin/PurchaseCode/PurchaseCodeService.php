<?php

namespace App\Services\Admin\PurchaseCode;
use Exception;
use App\Models\Section;
use App\Models\PurchaseCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Resources\PurchaseCodeResource;
use Illuminate\Contracts\Queue\EntityNotFoundException;
use App\Http\Requests\Api\Admin\PurchaseCode\UpdatePurchaseCodeRequest;

class PurchaseCodeService
{
    public function getAll($courseId = null)
    {
        $codes = PurchaseCode::query() ;

        if($courseId != null)
        {
            $codes->whereHas('sections' , function($query)use($courseId){
                $query->where('section_id' , $courseId) ;
            }) ;
        }
        
        $codes->orderByDesc('created_at');

        return PurchaseCodeResource::collection($codes->paginate(config('app.pagination_limit')));
    }
    
    public function getById($id):PurchaseCode|null
    {
        return PurchaseCode::where('id' , $id)->with('sections')->first() ;
    }   
    
    public function store(array $data):PurchaseCode
    {
        return DB::transaction(function() use ($data){
            
            $code = PurchaseCode::create($data) ;

            $code->sections()->attach($data['courses']) ;

            return $code ; 
        });
    }
    
    public function update(PurchaseCode &$purchaseCode , UpdatePurchaseCodeRequest &$request)
    {
        DB::transaction(function()use(&$purchaseCode , &$request){
            
            $purchaseCode->update($request->validated()) ;

            if($request->has('courses')){
                $purchaseCode->sections()->sync($request->validated('courses')) ;
            }
        });
        
        return true ;
    }
}