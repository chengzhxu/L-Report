<?php


namespace App\Http\Service;


use App\Http\Model\AppCategoryPriceModel;
use App\Http\Model\RegionAppCategoryModel;
use App\Http\Model\RegionCategoryModel;
use App\Http\Model\RegionCodeModel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegionCodeService {
    private $_model;
    private $_regionApp;

    public function __construct(){
        $this->_model = app()->make(RegionCodeModel::class);
        $this->_regionApp = app()->make(RegionAppCategoryModel::class);
    }

    /**
     * 根据regioncode获取地域信息
    */
    public function getRegionByCode($region_code = ''){
        $result = [];

        if($region_code){
            $result = $this->_model->where(['regioncode' => $region_code])->first();
        }

        return $result;
    }

    /**
     * 获取城市地域信息列表
    */
    public function getRegionList($col = 'region_name', $val = ''){
        $where = [
            ['regioncode', '>', 0]
        ];
        if($col && $val){
            array_push($where, [$col, 'like', '%'.$val.'%']);
        }
        $result = $this->_model->where($where)->get()->toArray();

        return $result;
    }

    /**
     * 获取省份地域信息列表
     */
    public function getProvinceList($col = 'province_name', $val = ''){
        $where = [
            ['provincecode', '>', 0]
        ];
        if($col && $val){
            array_push($where, [$col, 'like', '%'.$val.'%']);
        }
        $result = DB::table('t_province_code')->where($where)->get()->toArray();

        return $result;
    }

    /**
     * 获取当前APP用户下的分类城市信息
    */
    public function getCategoryRegionList($appid = 0){
        $result = [];
        $where['appid'] = $appid;

        $categoryList = RegionCategoryModel::all();
        foreach ($categoryList as $cate){
            $where['category_id'] = Q($cate, 'id');
            $res = [];
            $res['category_id'] = Q($cate, 'id');
            $res['category_name'] = Q($cate, 'name');
            $cate_region = RegionAppCategoryModel::where($where)->with(['region'])->get(['id', 'region_code', 'price'])->toArray();
            array_walk($cate_region, function (&$row) {
                $row['region_name'] = Q($row, 'region', 'region_name');
            });
            $region_code = array_column($cate_region, 'region_code');
            $region_list = RegionCodeModel::whereIn('regioncode', $region_code)->pluck('region_name')->toArray();
            $regions = $region_list ? implode(',', $region_list) : '';
            if(Q($cate, 'id') == 99){    //B类城市
                $regions = '其他';
            }
            $res['region_list'] = $regions ? $regions : '无';
            $res['category_region'] = $cate_region ? $cate_region : [];

            $result[] = $res;
        }

        $result = $this->getCategoryRegionPrice($result, $appid);

        return $result;
    }

    /**
     * 获取当前渠道下的分类城市价格
    */
    private function getCategoryRegionPrice($categoryList = [], $appid = 0){
        if(Schema::connection('in_ssp')->hasTable('app_category_price')){     //判断价格表是否存在
            if(!$categoryList){
                $categoryList = RegionCategoryModel::all();
            }
            array_walk($categoryList, function (&$cate) use ($appid) {
                $where = [
                    'category_id' => Q($cate, 'category_id') ? $cate['category_id'] : 0,
                    'appid' => $appid
                ];
                $catePrice = AppCategoryPriceModel::where($where)->first();
                $cate['price_id'] = $catePrice ? Q($catePrice, 'id') : 0;
                $cate['price'] = $catePrice ? Q($catePrice, 'price') : 0;
            });
        }

        return $categoryList;
    }

    /**
     * 新增渠道城市等级价格
    */
    public function addCategoryPrice($cate_price = []){
        if($cate_price){
            $entity = new AppCategoryPriceModel($cate_price);
            return $entity->save($cate_price) ? $this->getCategoryPriceInfo($entity->id) : [];
        }

        return false;
    }

    /**
     * 更新城市等级信息
     */
    public function updateCategoryPriceInfo($price_id = 0, $price = []){
        if($price_id && $price){
            unset($price['id']);

            return AppCategoryPriceModel::where('id', $price_id)->update($price);
        }

        return false;
    }

    /**
     * 删除城市等级价格信息
     */
    public function delCategoryPriceInfo($price_id = 0){
        if($price_id){
            return AppCategoryPriceModel::where('id', $price_id)->delete();
        }

        return false;
    }

    /**
     * 获取城市等级价格信息
     */
    public function getCategoryPriceInfo($id = 0){
        $where = ['id' => $id];
        $result = AppCategoryPriceModel::where($where)->with(['category'])->get();

        return $result;
    }

    /**
     * 判断分类城市价格信息是否存在
    */
    public function getPriceByCategory($appid = 0, $cate_id = 0){
        if($appid && $cate_id){
            $where = [
                'appid' => $appid,
                'category_id' => $cate_id
            ];

            return AppCategoryPriceModel::where($where)->first();
        }

        return [];
    }

    /**
     * 获取指定城市等级信息
     * @param appid
     * @param region_code
     * @return category
    */
    public function getCategoryByAppRegion($appid = 0, $region_code = ''){
        if($appid && $region_code){
            $where = [
                'appid' => $appid,
                'region_code' => $region_code
            ];
            return RegionAppCategoryModel::where($where)->first();
        }
    }

    /**
     * 新增城市等级信息
    */
    public function addCategoryRegion($category = []){
        $entity = new RegionAppCategoryModel($category);

        return $entity->save($category) ? $this->getCategoryRegionInfo($entity->id) : [];
    }

    /**
     * 获取城市等级信息
     */
    public function getCategoryRegionInfo($id = 0){
        $where = ['id' => $id];
//        $columns = ['c.*', 'r.region_name', 'l.name as category_name'];
//        $result = DB::connection('in_ssp')->table('region_app_category as c')
//            ->join('t_region_code as r', 'c.region_code', '=', 'r.regioncode')
//            ->join('region_category as l', 'c.category_id', '=', 'l.id')
//            ->where('c.id', $id)->first($columns);

        $result = RegionAppCategoryModel::where($where)->with(['region', 'category'])->get();

        return $result;
    }

    /**
     * 更新城市等级信息
    */
    public function updateCategoryRegionInfo($region_id = 0, $category = []){
        if($region_id && $category){
            unset($category['id']);

            return RegionAppCategoryModel::where('id', $region_id)->update($category);
        }
    }

    /**
     * 删除城市等级信息
    */
    public function delCategoryRegionInfo($region_id = 0){
        if($region_id){
            return RegionAppCategoryModel::where('id', $region_id)->delete();
        }
    }

    /**
     * 获取等级分类列表
    */
    public function getRegionCategoryList(){
        $result = RegionCategoryModel::all()->toArray();

        return $result ? $result : [];
    }

    /**
     * 根据城市等级和获取相关渠道的城市信息
     * @param appid
     * @param category_id
     * @return array
    */
    public function getRegionByCategory($appid = 0, $category_id = 0){
        $result = [];
        if($appid){
            $where['appid'] = $appid;
            if($category_id){
                $where['category_id'] = $category_id;
            }

            $result = RegionAppCategoryModel::where($where)->with(['region'])->get();
        }

        return $result;
    }
}