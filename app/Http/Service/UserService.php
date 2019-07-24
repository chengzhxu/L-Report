<?php


namespace App\Http\Service;


use App\Http\Model\UserModel;
use Illuminate\Support\Facades\DB;

class UserService {
    private $_model;

    public function __construct(){
        $this->_model = app()->make(UserModel::class);
    }

    public function checkLogin($data = []){
        $code = 200;
        $msg = '登录成功';

        if(Q($data, 'username') && Q($data, 'password')){
            $where = [
                'username' => Q($data, 'username')
            ];
            $user = DB::table('admin_user')->where($where)->first();
            $pwd = md5(Q($data, 'password').Q($user, 'salt'));
            if($user && $pwd == Q($user, 'password')){
                $token = strtoupper(md5(Q($user, 'username').time()));
                if(DB::table('admin_user')->where($where)->update(['api_token' => $token])){
                    return DB::table('admin_user')->where($where)->first(['username', 'fullname', 'api_token']);
                }
                return false;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    public function logOut($token){
        if($token){
            $user = DB::table('admin_user')->where(['api_token' => $token])->first();
            if($user){
                return DB::table('admin_user')->where(['api_token' => $token])->update(['api_token' => '']);
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}