<?php


namespace App\Http\Controllers\Report;

use App\Http\Model\AppRegionNuvStatModel;
use App\Http\Service\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends ReportAbstract {


    public function test(){
//        $data = DB::select();
//        $data = DB::select('select * from app_region_nuv_stat where appid = 124541520 and regioncode = 320800');
        $where = [
            'appid' => 124541520,
            'regioncode' => 420500
        ];
//        $data = AppRegionNuvStatModel::where($where)->get();
        $data = DB::table('app_region_nuv_stat')->where($where)->get();
//        echo 111;exit;

        return $this->toJson(200, $data, 'success');
    }



}