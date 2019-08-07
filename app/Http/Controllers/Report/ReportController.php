<?php


namespace App\Http\Controllers\Report;

use App\Http\Service\RegionCodeService;
use App\Http\Service\ReportService;
use Illuminate\Http\Request;

class ReportController extends ReportAbstract {

    protected $_service;
    protected $request;
    protected $_appid;

    public function __construct(Request $request){
        $this->request = $request;

        $this->_service = app()->make(ReportService::class);
        $this->_appid = $this->getAppid();
        if(!$this->_appid){
            return $this->toJson(3010, []);
        }
    }

    /**
     * 获取当日数据快报 - 实时数据
    */
    public function getRealTimeData(){
        $result = $this->_service->getRealTimeData($this->_appid);

        return $this->toJson(200, $result);
    }

    /**
     * 获取地区pv数据
    */
    public function getRegionPv(){
        $region_code = Q($this->request, 'region_code');
        $time_start = Q($this->request, 'time_start');
        $time_end = Q($this->request, 'time_end');
        $page_size = Q($this->request, 'page_size') ? Q($this->request, 'page_size') : 15;

        $result = $this->_service->getRegionPv($this->_appid, $region_code, $time_start, $time_end, $page_size);

        return $this->toJson(200, $result);
    }

    /**
     * 获取地区pv数据
     */
    public function getRegionUv(){
        $region_code = Q($this->request, 'region_code');
        $time_start = Q($this->request, 'time_start');
        $time_end = Q($this->request, 'time_end');
        $page_size = Q($this->request, 'page_size') ? Q($this->request, 'page_size') : 15;

        $result = $this->_service->getRegionUv($this->_appid, $region_code, $time_start, $time_end, $page_size);

        return $this->toJson(200, $result);
    }

    /**
     * 获取指定日期相关pv数据
     */
    public function getRegionPvByDay(){
        $region_code = Q($this->request, 'region_code');
        $time_start = Q($this->request, 'time_start');
        $time_end = Q($this->request, 'time_end');

        $result = $this->_service->getRegionPvByDay($this->_appid, $region_code, $time_start, $time_end);
        switch ($result){
            case -1:
                return $this->toJson(4001, []);
                break;
            case -2:
                return $this->toJson(4002, []);
                break;
            case -3:
                return $this->toJson(4003, []);
                break;
        }

        return $this->toJson(200, $result);
    }


    /**
     * 获取指定日期相关uv数据
    */
    public function getUvByDay(){
        $time_start = Q($this->request, 'time_start');
        $time_end = Q($this->request, 'time_end');

        $result = $this->_service->getUvByDay($this->_appid, $time_start, $time_end);
        switch ($result){
            case -1:
                return $this->toJson(4001, []);
                break;
            case -2:
                return $this->toJson(4002, []);
                break;
            case -3:
                return $this->toJson(4003, []);
                break;
        }

        return $this->toJson(200, $result);
    }

    /**
     * 获取pv历史数据
     */
    public function getHistoryPv(){
        $region_code = Q($this->request, 'region_code');
        $time_start = Q($this->request, 'time_start');
        $time_end = Q($this->request, 'time_end');

        $result = $this->_service->getHistoryPv($this->_appid, $region_code, $time_start, $time_end);
        switch ($result){
            case -1:
                return $this->toJson(4001, []);
                break;
            case -2:
                return $this->toJson(4002, []);
                break;
            case -3:
                return $this->toJson(4003, []);
                break;
            case -4:
                return $this->toJson(4005, []);
                break;
        }

        return $this->toJson(200, $result);
    }

    /**
     * 获取uv历史数据
     */
    public function getHistoryUv(){
        $region_code = Q($this->request, 'region_code');
        $time_start = Q($this->request, 'time_start');
        $time_end = Q($this->request, 'time_end');

        $result = $this->_service->getHistoryUv($this->_appid, $region_code, $time_start, $time_end);
        switch ($result){
            case -1:
                return $this->toJson(4001, []);
                break;
            case -2:
                return $this->toJson(4002, []);
                break;
            case -3:
                return $this->toJson(4003, []);
                break;
            case -4:
                return $this->toJson(4005, []);
                break;
        }

        return $this->toJson(200, $result);
    }




    /**
     * 获取地域列表
    */
    public function getRegionList(){
        $region_type = Q($this->request, 'region_type');
        $words = Q($this->request, 'words');

        $region_list = [];
        $province_list = [];

        $regionService = app()->make(RegionCodeService::class);
        switch ($region_type){
            case 1:      //城市list
                $region_list = $regionService->getRegionList('region_name', $words);
                break;
            case 2:
                $province_list = $regionService->getProvinceList('province_name', $words);
                break;
            default:
                $region_list = $regionService->getRegionList('region_name', $words);
                $province_list = $regionService->getProvinceList('province_name', $words);
        }
        $result = ['region_list' => $region_list, 'province_list' => $province_list];

        return $this->toJson(200, $result);
    }

}