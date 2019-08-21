<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;

class ValidateAdministrator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('report')->guest()) {
            return response()->json(['code' => 3000,'message' => '无效的token信息']);
        }

        $user = Auth::guard('report')->user();
        if($user['user_type'] != 1){
            return response()->json(['code' => 3003,'message' => '您无权访问该接口']);
        }

        return $next($request);
    }
}
