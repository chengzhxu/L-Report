<?php


namespace App\Http\Service;


use App\Http\Model\RegionCodeModel;
use Illuminate\Support\Facades\DB;

class RegionCodeService {
    private $_model;

    public function __construct(){
        $this->_model = app()->make(RegionCodeModel::class);
    }

    /**
     * 根据regioncode获取地域信息
    */
    public function getRegionByCode($region_code = ''){
        $result = [];

        if($region_code){
            $result = $this->_model->where(['regioncode' => $region_code])->first()->toArray();
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
}