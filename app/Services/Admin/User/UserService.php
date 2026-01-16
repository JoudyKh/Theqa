<?php

namespace App\Services\Admin\User;

use App\Models\User;
use App\Models\UserImage;
use App\Constants\Constants;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\General\User\UserService as GeneralUserService;

class UserService
{
    public function __construct(protected GeneralUserService $gUserService)
    {
    }

    public function store(FormRequest $request, $role): User
    {
        $overrideData = [] ;
        
        return $this->gUserService->createUser($request, $role , false ,$overrideData)->loadMissing('images');
    }

    public function update(User &$user, array $data)
    {
        return DB::transaction(function () use (&$user, $data) {
            
            $user->update($data);
            
            $oldImage = null ;
            
            if(request()->has('image'))
            {
                if(($oldImage = $user->images()->first()))
                {
                    $oldImage = $oldImage->pluck('image');
                    $user->images()->delete() ;
                }
            }
            if (request()->hasFile('image')) 
            {
                $newImagePath = request()->file('image')->storePublicly('teachers', 'public');

                $firstImage = $user->images()->first() ;

                if($firstImage)
                {
                    $firstImage->update(['image' => $newImagePath]);
                }else{
                    $user->images()->create(['image' => $newImagePath]) ;
                }
            }

            DB::afterCommit(function () use ($oldImage) {
                if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                    Storage::disk('public')->delete($oldImage);
                }
            });

            return true;
        });
    }

    public function delete(User $user, $force = false)
    {
        if($user->hasRole(Constants::SUPER_ADMIN_ROLE)){
            return error(__('messages.cant_delete_super_admin')) ;
        }
        
        if ($force) {
            return success(['deleted' => $user->forceDelete()]);
        }
        return success(['deleted' => $user->delete()]);
    }
}
