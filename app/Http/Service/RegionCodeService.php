<?php


namespace App\Http\Service;


use App\Http\Model\RegionAppCategoryModel;
use App\Http\Model\RegionCategoryModel;
use App\Http\Model\RegionCodeModel;
use App\Http\Model\UserModel;
use Illuminate\Support\Facades\DB;

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
            $res['category_name'] = Q($cate, 'name');
            $region_code = RegionAppCategoryModel::where($where)->pluck('region_code');
            $region_list = RegionCodeModel::whereIn('regioncode', $region_code)->pluck('region_name')->toArray();
            $regions = $region_list ? implode(',', $region_list) : '';

            if(Q($cate, 'id') == 99){    //B类城市
                $regions = '其他';
            }
            $res['region_list'] = $regions ? $regions : '无';

            $result[] = $res;
        }

        return $result;
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