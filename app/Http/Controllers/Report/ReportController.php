<?php


namespace App\Http\Controllers\Report;

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
            return $this->toJson(310, [], 'appid获取失败');
        }
    }

    /**
     * 获取当日数据快报 - 实时数据
    */
    public function getRealTimeData(){
        $result = $this->_service->getRealTimeData($this->_appid);

        return $this->toJson(200, $result, '操作成功');
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

        $result = $this->_service->getRegionUv($this->_appid, $region_code, $time_start, $time_end, $page_size);

        return $this->toJson(200, $result, '操作成功');
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


    /**
     * 获取指定日期相关uv数据
    */
    public function getUvByDay(){
        $time_start = Q($this->request, 'time_start');
        $time_end = Q($this->request, 'time_end');

        $result = $this->_service->getUvByDay($this->_appid, $time_start, $time_end);
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
                return $this->toJson(401, [], '请选择开始时间');
                break;
            case -2:
                return $this->toJson(401, [], '请选择结束时间');
                break;
            case -3:
                return $this->toJson(401, [], '结束时间必须大于开始时间');
                break;
            case -4:
                return $this->toJson(401, [], '日期间隔不能超过三个月');
                break;
        }

        return $this->toJson(200, $result, '操作成功');
    }



}