<?php


namespace App\Http\Service;


use App\Http\Model\RegionCodeModel;
use Illuminate\Support\Facades\DB;

class RegionCodeService {
    private $_model;

    public function __construct(){
        $this->_model = app()->make(RegionCodeModel::class);
    }

    public function getRegionByCode($region_code = ''){
        $result = [];

        if($region_code){
            $result = $this->_model->where(['regioncode' => $region_code])->first()->toArray();
        }

        return $result;
    }
}