<?php


namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class UserModel extends Model {
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'admin_user';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
//        'start_day'
        'username',
        'password',
        'salt',
        'api_token',
    ];
}