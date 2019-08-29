<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;

class ValidateUserOperation
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
        $user = Auth::guard('report')->user();
        if(Q($user, 'appid')){
            $diff_time = get_user_last_operation_diff_time($user['appid']);
            if($diff_time > 1800){
                return response()->json(['code' => 3000,'message' => '长时间未操作，请重新登录']);
            }
        }

        $log = [
            'appid' => Q($user, 'appid') ? Q($user, 'appid') : 0,
            'route' => $request->getRequestUri(),
            'param' => json_encode($request->all()),
            'ctime' => date('Y-m-d H:i:s', time())
        ];
        insert_user_log($log);

        return $next($request);
    }
}
