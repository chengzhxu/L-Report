<?php


namespace App\Http\Service;


use App\Http\Model\AppRegionPvModel;
use App\Http\Model\AppRegionUvModel;
use App\Http\Model\DayHourGroupAppRegionPvStatModel;
use Illuminate\Support\Facades\DB;

class ReportService {

    /**
     * 获取地域pv数据
    */
    public function getRegionPv($region_code = '', $time_start = '', $time_end = '', $page_size = 10){
        $app_id = 8002;

        $query = AppRegionPvModel::with('regionCode')->where('appid', $app_id);
        if($region_code){
            $query = $query->where('regioncode', $region_code);
        }
        if($time_start){
            $start_day =  date('Ymd',strtotime($time_start));
            $query = $query->where('start_day', '>=', $start_day);
        }
        if($time_end){
            $end_day =  date('Ymd',strtotime($time_end));
            $query = $query->where('end_day', '<=', $end_day);
        }
        $result = $query->paginate($page_size);

        return $result;
    }

    /**
     * 获取指定日期相关pv数据
    */
    public function getRegionPvByDay($region_code = '', $time_start, $time_end = ''){
        $app_id = 8002;
        $day_count = 1;

        $sql = ' select hour, COUNT(times) AS num from day_hour_group_app_region_pv_stat where appid = ' . $app_id;
        if(!$time_start && !$time_end){
            $time_day = date('Ymd',time());
            $sql .= ' and day = ' . $time_day;
        }else{
            if($time_start && $time_end){
                $second_start = strtotime($time_start);
                $second_end = strtotime($time_end);

                if($second_end < $second_start){
                    return -3;
                }

                $start_day = date('Ymd',$second_start);
                $end_day = date('Ymd',$second_end);

                $sql .= ' and day between '.$start_day.' and ' .$end_day;

                $day_count = ($second_end - $second_start) / 86400;
            }else{
                if(!$time_start){
                    return -1;
                }
                if(!$time_end){
                    return -2;
                }
            }
        }

        if($region_code){
            $sql .= ' and regioncode = ' . $region_code;
        }
        $sql .= ' group by hour order by hour ';
        $res = DB::select($sql);

        $total_pv = 0;
        $pv_data = [];
        if(!empty($res))
        {
            foreach ($res as $val)
            {
                if(!is_array($val)){
                    $val = (array)$val;
                }
                $total_pv += intval($val['num']);
                $pv_data[] = [
                    'hour' => $val['hour'],
                    'pv' => $val['num']
                ];
            }
        }
        $result = [
            'total_pv' => $total_pv,
            'pv_data' => $pv_data,
            'day_pv' => round($total_pv / $day_count, 2)
        ];

        return $result;
    }

    /**
     * 获取地域uv数据
    */
    public function getRegionUv($region_code = '', $time_start = '', $time_end = '', $page_size = 10){
        $app_id = 8002;

        $query = AppRegionUvModel::with('regionCode')->where('appid', $app_id);
        if($region_code){
            $query = $query->where('regioncode', $region_code);
        }
        if($time_start){
            $start_day =  date('Ymd',strtotime($time_start));
            $query = $query->where('start_day', '>=', $start_day);
        }
        if($time_end){
            $end_day =  date('Ymd',strtotime($time_end));
            $query = $query->where('end_day', '<=', $end_day);
        }
        $result = $query->paginate($page_size);

        return $result;
    }

    /**
     *
    */
    public function getAppRegionNuv(){

    }
}