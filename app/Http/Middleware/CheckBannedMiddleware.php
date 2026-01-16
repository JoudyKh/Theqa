<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Info;
use Illuminate\Http\Request;
use App\Services\General\Info\InfoService;
use Symfony\Component\HttpFoundation\Response;

class CheckBannedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(request()->user() and request()->user()->is_banned)
        {
            $infoService = new InfoService() ;
            
            return response()->json([
                'message' => __('messages.you_are_banned') , 
                'info' => $infoService->getAll(),
            ] , 403) ;
        }
        
        return $next($request);
    }
}
