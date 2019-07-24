<?php


namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class AppRegionNuvStatModel extends Model {
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'app_region_nuv_stat';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
//        'start_day'
    ];
}