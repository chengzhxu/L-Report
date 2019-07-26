<?php


namespace App\Http\Controllers\Report;

use App\Http\Model\AppRegionNuvStatModel;
use App\Http\Service\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends ReportAbstract {

    /**
     * 获取地区pv数据
    */
    public function getRegionPv(){
        $region_code = Q($this->request, 'region_code');
        $time_start = Q($this->request, 'time_start');
        $time_end = Q($this->request, 'time_end');
        $page_size = Q($this->request, 'page_size') ? Q($this->request, 'page_size') : 15;

        $result = app()->make(ReportService::class)->getRegionPv($region_code, $time_start, $time_end, $page_size);

        return $this->toJson(200, $result, '操作成功');
    }

    /**
     * 获取地区pv数据
     */
    public function getRegionUv(){
        $region_code = Q($this->request, 'region_code');
        $time_start = Q($this->request, 'time_start');
        $time_end = Q($this->request, 'time_end');
        $page_size = Q($this->request, 'page_size') ? Q($this->request, 'page_size') : 15;

        $result = app()->make(ReportService::class)->getRegionUv($region_code, $time_start, $time_end, $page_size);

        return $this->toJson(200, $result, '操作成功');
    }

    /**
     * 获取指定日期相关pv数据
     */
    public function getRegionPvByDay(){
        $region_code = Q($this->request, 'region_code');
        $time_start = Q($this->request, 'time_start');
        $time_end = Q($this->request, 'time_end');

        $result = app()->make(ReportService::class)->getRegionPvByDay($region_code, $time_start, $time_end);
        switch ($result){
            case -1:
                return $this->toJson(401, [], '请选择开始时间');
                break;
            case -2:
                return $this->toJson(401, [], '请选择结束时间');
                break;
            case -3:
                return $this->toJson(401, [], '结束时间必须大于开始时间');
                break;
        }

        return $this->toJson(200, $result, '操作成功');
    }








}