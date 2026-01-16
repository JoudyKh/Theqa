<?php

namespace App\Services\App\User;


use App\Models\UserFcmToken;


class UserService 
{
    public function handleFcmToken($user, $fcmToken)
    {
        //check if the fcm token stored in guest mode to link it with the user .
        $existingFcmToken = UserFcmToken::where('fcm_token', $fcmToken)->first();
        if ($existingFcmToken) {
            return $existingFcmToken->update([
                'token_id' => $user->tokens()->orderBy('id', 'DESC')->first()->id,
                'user_id' => $user->id,
            ]);
        }
        return $user->fcmTokens()->firstOrCreate(
            [
                'fcm_token' => $fcmToken,
                'token_id' => $user->tokens()->orderBy('id', 'DESC')->first()->id
            ]
        );
    }
}
