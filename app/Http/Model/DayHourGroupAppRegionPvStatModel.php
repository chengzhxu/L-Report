<?php


namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class DayHourGroupAppRegionPvStatModel extends Model {
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'day_hour_group_app_region_pv_stat';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'day',
        'hour',
        'groupid',
        'appid',
        'regioncode',
        'times',
    ];


    public function regionCode()
    {
        return $this->belongsTo(RegionCodeModel::class, 'regioncode', 'regioncode');
    }
}