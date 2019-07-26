<?php


namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class RegionCodeModel extends Model {
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 't_region_code';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'regioncode',
        'region_name',
    ];
}