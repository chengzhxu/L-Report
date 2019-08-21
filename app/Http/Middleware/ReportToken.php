<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;

class ReportToken
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
        if((!isset($user['appid']) || !$user['appid']) && $user['user_type'] != 1){
            return response()->json(['code' => 3010,'message' => '渠道信息获取失败']);
        }

        return $next($request);
    }
}
