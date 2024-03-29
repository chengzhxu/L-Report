<?php


namespace App\Http\Controllers\Report;

use App\Http\Service\RegionCodeService;
use App\Http\Service\ReportService;
use Illuminate\Http\Request;

class ReportController extends ReportAbstract {

    protected $_service;
    protected $_appid;
    protected $_regionService;

    public function __construct(Request $request){
        parent::__construct($request);

        $this->_service = app()->make(ReportService::class);
        $this->_regionService = app()->make(RegionCodeService::class);
        $this->_appid = $this->getAppid() ? $this->getAppid() : 0;
    }

    /**
     * 获取当日数据快报 - 实时数据
    */
    public function getRealTimeData(){
        $result = $this->_service->getRealTimeData($this->_appid);

        return $this->toJson(200, $result);
    }

    /**
     * 获取渠道列表信息
    */
    public function getAppList(){
        $words = Q($this->request, 'words');
        $result = $this->_service->getAppList($words);

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
        $time_start = Q($this->request, 'time_start');
        $time_end = Q($this->request, 'time_end');

        $result = $this->_service->getHistoryPv($this->_appid, $time_start, $time_end);
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
                return $this->toJson(4004, []);
                break;
            case -5:
                return $this->toJson(4005, []);
                break;
        }

        return $this->toJson(200, $result);
    }

    /**
     * 获取uv历史数据
     */
    public function getHistoryUv(){
        $time_start = Q($this->request, 'time_start');
        $time_end = Q($this->request, 'time_end');

        $result = $this->_service->getHistoryUv($this->_appid, $time_start, $time_end);
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
                return $this->toJson(4004, []);
                break;
            case -5:
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

        switch ($region_type){
            case 1:      //城市list
                $region_list = $this->_regionService->getRegionList('region_name', $words);
                break;
            case 2:
                $province_list = $this->_regionService->getProvinceList('province_name', $words);
                break;
            default:
                $region_list = $this->_regionService->getRegionList('region_name', $words);
                $province_list = $this->_regionService->getProvinceList('province_name', $words);
        }
        $result = ['region_list' => $region_list, 'province_list' => $province_list];

        return $this->toJson(200, $result);
    }

    /**
     * 获取当前APP的城市信息
    */
    public function getCategoryRegionList(){
        $app_id = $this->_appid ? $this->_appid : Q($this->request, 'app_id');
        $result = $this->_regionService->getCategoryRegionList($app_id);

        return $this->toJson(200, $result);
    }

    /**
     * 新增等级城市信息
    */
    public function addCategoryRegion(){
        $category_id = Q($this->request, 'category_id');
        $region_code = Q($this->request, 'region_code');
        $price = Q($this->request, 'price');
        $app_id = $this->_appid ? $this->_appid : Q($this->request, 'app_id');

        $res_code = $this->validateCategory($this->request, $app_id);
        if($res_code !== true){
            return $this->toJson($res_code, []);
        }
        $category = $this->_regionService->getCategoryByAppRegion($app_id, $region_code);
        if($category){
            return $this->toJson(5007, $category);
        }

        $category = [
            'region_code' => $region_code,
            'appid' => $app_id,
            'category_id' => $category_id,
            'price' => $price
        ];
        $res = $this->_regionService->addCategoryRegion($category);
        if($res){
            return $this->toJson(200, ['id' => $res]);
        }else{
            return $this->toJson(5003, $res);
        }
    }

    /**
     * 获取指定城市信息详情
    */
    public function getCategoryRegionById(){
        $region_id = Q($this->request, 'region_id');
        if(!$region_id) {
            return $this->toJson(5004, []);
        }

        $result = $this->_regionService->getCategoryRegionInfo($region_id);
        if($result){
            return $this->toJson(200, $result);
        }else{
            return $this->toJson(5005, $result);
        }
    }

    /**
     * 更新指定城市信息
    */
    public function updateCategoryRegionById(){
        $region_id = Q($this->request, 'region_id');
        $category_id = Q($this->request, 'category_id');
        $region_code = Q($this->request, 'region_code');
        $app_id = $this->_appid ? $this->_appid : Q($this->request, 'app_id');
        if(!$region_id) {
            return $this->toJson(5004, []);
        }
        $res_code = $this->validateCategory($this->request, $app_id);
        if($res_code !== true){
            return $this->toJson($res_code, []);
        }
        $category = $this->_regionService->getCategoryRegionInfo($region_id);
        if($category){
            $appRegion = $this->_regionService->getCategoryByAppRegion($app_id, $region_code);
            if($appRegion && Q($appRegion, 'id') != $region_id){
                return $this->toJson(5007, $appRegion);
            }
            $new_category = [
                'region_code' => $region_code,
                'category_id' => $category_id
            ];
            if(!$this->_regionService->updateCategoryRegionInfo($region_id, $new_category)){
                return $this->toJson(5006, []);
            }
            $category = $this->_regionService->getCategoryRegionInfo($region_id);
            return $this->toJson(200, $category);
        }else{
            return $this->toJson(5005, []);
        }
    }

    /**
     * 删除城市等级信息
    */
    public function deleteCategoryRegionById(){
        $region_id = Q($this->request, 'region_id');
//        $app_id = $this->_appid ?  $this->_appid : Q($this->request, 'app_id');
        $region = $this->_regionService->getCategoryRegionInfo($region_id);
        if(!$region){
            return $this->toJson(5005, []);
        }
//        if(Q($region, 'appid') != $app_id){
//            return $this->toJson(5009, []);
//        }
        if($this->_regionService->delCategoryRegionInfo($region_id)){
            return $this->toJson(200, []);
        }else{
            return $this->toJson(5010, []);
        }
    }

    /**
     * 验证等级城市信息新增
    */
    private function validateCategory($data = [], $appid){
        if(!Q($data, 'category_id')){
            return 5001;
        }
        if(!Q($data, 'region_code')){
            return 5002;
        }
        if(!$appid){
            return 5011;
        }
        if(!$this->_regionService->getRegionByCode(Q($data, 'region_code'))){
            return 5008;
        }

        return true;
    }

    /**
     * 获取城市等级列表
    */
    public function getRegionCategoryList(){
        $result = $this->_regionService->getRegionCategoryList();

        return $this->toJson(200, $result);
    }


    /**
     * 根据等级获取相关城市
    */
    public function getRegionByCategory(){
        $category_id = Q($this->request, 'category_id');
        $app_id = $this->_appid ? $this->_appid : Q($this->request, 'app_id');
        $result = $this->_regionService->getRegionByCategory($app_id, $category_id);

        return $this->toJson(200, $result);
    }

    /**
     * 新增城市分类价格信息
    */
    public function addCategoryPrice(){
        $category_id = Q($this->request, 'category_id');
        $price = Q($this->request, 'price');
        $app_id = $this->_appid ? $this->_appid : Q($this->request, 'app_id');

        if(($price !== 0) && (!is_numeric($price) || $price < 0)){
            return $this->toJson(5012, []);
        }

        if(!$category_id){
            return $this->toJson(5001, []);
        }

        $category = $this->_regionService->getPriceByCategory($app_id, $category_id);
        if($category){
            return $this->toJson(5013, $category);
        }

        $category_price = [
            'appid' => $app_id,
            'category_id' => $category_id,
            'price' => $price
        ];
        $res = $this->_regionService->addCategoryPrice($category_price);
        if($res){
            return $this->toJson(200, ['id' => $res]);
        }else{
            return $this->toJson(5014, $res);
        }
    }

    /**
     * 获取指定城市分类价格信息
    */
    public function getCategoryPriceById(){
        $price_id = Q($this->request, 'price_id');
        if(!$price_id) {
            return $this->toJson(5015, []);
        }

        $result = $this->_regionService->getCategoryPriceInfo($price_id);
        if($result){
            return $this->toJson(200, $result);
        }else{
            return $this->toJson(5016, $result);
        }
    }

    /**
     * 更新指定城市分类价格信息
     */
    public function updateCategoryPriceById(){
        $price_id = Q($this->request, 'price_id');
        $category_id = Q($this->request, 'category_id');
        $price = Q($this->request, 'price');
        $app_id = $this->_appid ? $this->_appid : Q($this->request, 'app_id');
        if(!$price_id) {
            return $this->toJson(5015, []);
        }
        if(($price !== 0) && (!is_numeric($price) || $price < 0)){
            return $this->toJson(5012, []);
        }
        $category_price = $this->_regionService->getCategoryPriceInfo($price_id);
        if($category_price){
            $regionPrice = $this->_regionService->getPriceByCategory($app_id, $category_id);
            if($regionPrice && Q($regionPrice, 'id') != $price_id){
                return $this->toJson(5013, $regionPrice);
            }
            $new_price = [
                'price' => $price,
            ];
            if(!$this->_regionService->updateCategoryPriceInfo($price_id, $new_price)){
                return $this->toJson(5018, []);
            }
            $category = $this->_regionService->getCategoryPriceInfo($price_id);
            return $this->toJson(200, $category);
        }else{
            return $this->toJson(5016, []);
        }
    }

    /**
     * 删除指定城市分类价格信息
    */
    public function deleteCategoryPriceById(){
        $price_id = Q($this->request, 'price_id');
        $price_info = $this->_regionService->getCategoryPriceInfo($price_id);
        if(!$price_info){
            return $this->toJson(5016, []);
        }
        if($this->_regionService->delCategoryPriceInfo($price_id)){
            return $this->toJson(200, []);
        }else{
            return $this->toJson(5017, []);
        }
    }

}