<?php


namespace App\Http\Service;


use App\Http\Model\UserModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserService {
    private $_model;

    public function __construct(){
        $this->_model = app()->make(UserModel::class);
    }

    public function checkLogin($data = []){
        if(Q($data, 'username') && Q($data, 'password')){
            $where = [
                'username' => Q($data, 'username')
            ];
            $user = $this->_model->where($where)->first();
            $pwd = md5(Q($data, 'password').Q($user, 'salt'));
            if($user && $pwd == Q($user, 'password')){
                if(!Q($user, 'api_token')){
                    $token = strtoupper(md5(Q($user, 'username').time()));
                    $this->_model->where($where)->update(['api_token' => $token]);
                }
                $user = $this->_model->where($where)->first(['username', 'fullname', 'api_token', 'appid', 'user_type']);

                $log = [
                    'appid' => Q($user, 'appid'),
                    'route' => \Request::getRequestUri(),
                    'param' => json_encode(\Request::all()),
                    'ctime' => date('Y-m-d H:i:s', time())
                ];
                insert_user_log($log);

                return $user;
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