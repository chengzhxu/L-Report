<?php


namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class AppCategoryPriceModel extends Model {
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $connection = 'in_ssp';

    protected $table = 'app_category_price';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $fillable = [
        'id',
        'appid',
        'category_id',
        'price',
        'created_at',
        'updated_at',
    ];

    public function category()
    {
        return $this->belongsTo(RegionCategoryModel::class, 'category_id', 'id');
    }
}