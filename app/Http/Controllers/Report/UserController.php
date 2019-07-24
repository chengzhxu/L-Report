<?php


namespace App\Http\Controllers\Report;


use App\Http\Controllers\Controller;
use App\Http\Service\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ReportAbstract {

    public function login(Request $request){

        $data = [
            'username' => Q($request, 'username'),
            'password' => Q($request, 'password'),
        ];

        $user = app()->make(UserService::class)->checkLogin($data);
        if($user){
            return $this->toJson(200, $user, '登录成功');
        }else{
            return $this->toJson(301, [], '登录失败，用户名和密码不匹配');
        }
    }

    public function logout(Request $request){
        $token = $request['api_token'];

        if(app()->make(UserService::class)->logOut($token)){
            return $this->toJson(200, [], '登出成功');
        }else{
            return $this->toJson(302, [], '登出失败');
        }
    }
}