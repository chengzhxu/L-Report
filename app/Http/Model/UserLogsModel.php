<?php


namespace App\Http\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserLogsModel extends Authenticatable {
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $connection = 'in_ssp';

    protected $table = 'user_logs';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'appid',
        'route',
        'param',
        'ctime',
    ];
}