<?php


namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class RegionAppCategoryModel extends Model {
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'region_app_category';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = [
        'id',
        'region_code',
        'appid',
        'category_id',
        'created_at',
        'updated_at',
    ];


    public function region()
    {
        return $this->belongsTo(RegionCodeModel::class, 'region_code', 'regioncode');
    }

    public function category()
    {
        return $this->belongsTo(RegionCategoryModel::class, 'category_id', 'id  ');
    }
}