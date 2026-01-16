<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Jobs\WhatsAppMessageJob;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\Admin\Whatsapp\BroadcastWhatsappRequest;

class WhatsappService
{
    public function broadcast(BroadcastWhatsappRequest &$request)
    {
        $users = User::query();

        if ($request->has('city_id')) {
            $users->where('city_id', $request->validated('city_id'));
        }

        if ($request->has('role')) {
            $users->whereHas('roles', function ($role) use (&$request) {
                $role->where('name', $request->validated('role'));
            });
        }

        if ($request->has('governorate_id')) {
            $users->whereHas('city', function ($city) use (&$request) {
                $city->where('governorate_id', $request->validated('governorate_id'));
            });
        }

        if ($request->has('users') and !empty($request->validated('users'))) {
            $users->whereIn('id', $request->validated('users'));
        }

        $users->get()->each(function ($user) use (&$request) {

            if(in_array($request->validated('phone_type') , ['phone_number' , 'both']) and $user->phone_number){
                pushWhatsAppMessage(
                    $request->validated('message'),
                    $user->phone_number,
                    $user->id
                );
            }

            if(in_array($request->validated('phone_type') , ['family_phone_number' , 'both']) and $user->family_phone_number){
                pushWhatsAppMessage(
                    $request->validated('message'),
                    $user->family_phone_number,
                    $user->id
                );
            }
            
        });

        return success();
    }
}
