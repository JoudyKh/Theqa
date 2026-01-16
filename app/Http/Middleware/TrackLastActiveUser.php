<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use App\Models\Device;
use Illuminate\Http\Request;

class TrackLastActiveUser
{

    public function handle(Request $request, Closure $next)
    {
        if (request()->user()) {
            $request->user()->update(['last_active_at' => Carbon::now()]);

            if($request->hasHeader('fingerprint'))
            {
                Device::updateOrCreate([
                    'user_id' => auth('sanctum')->id() ,
                    'fingerprint' => $request->header('fingerprint') ,
                ] , [
                    'agent' => $request->userAgent() ,
                ]) ;
            }
        }


        return $next($request);
    }
}
