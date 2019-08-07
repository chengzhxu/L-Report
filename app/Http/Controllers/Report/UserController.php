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
            $this->guard()->setUser($user);
            return $this->toJson(200, $user);
        }else{
            return $this->toJson(3001, []);
        }
    }

    public function logout(Request $request){
        $token = $request['api_token'];

        if(app()->make(UserService::class)->logOut($token)){
            return $this->toJson(200, []);
        }else{
            return $this->toJson(3002, []);
        }
    }
}