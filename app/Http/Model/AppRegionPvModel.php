<?php


namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class AppRegionPvModel extends Model {
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'app_region_pv_stat';    //已废弃

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'start_day',
        'end_day',
        'appid',
        'regioncode',
        'times',
    ];


    public function regionCode()
    {
        return $this->belongsTo(RegionCodeModel::class, 'regioncode', 'regioncode');
    }
}