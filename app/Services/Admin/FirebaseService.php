<?php

namespace App\Services\Admin;

use App\Jobs\BroadcastFirebaseMessagesJob;
use App\Models\User;
use App\Jobs\WhatsAppMessageJob;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\Admin\Firebase\BroadcastFirebaseRequest;
use App\Constants\Constants;

class FirebaseService
{
    public function broadcast(BroadcastFirebaseRequest &$request)
    {
        $users = User
        ::whereHas('roles' , function($role){
            $role->where('name' , Constants::STUDENT_ROLE);
        })
        ->with(['fcmTokens']);

        if ($request->has('users') and !empty($request->validated('users'))) {
            $users->whereIn('id', $request->validated('users'));
        }

        $users->chunk(100 , function($usersChuck)use(&$request){
            dispatch(new BroadcastFirebaseMessagesJob(
                $usersChuck ,
                $request->validated('title') ,
                $request->validated('body')
            ));
        });

        return success();
    }
}
