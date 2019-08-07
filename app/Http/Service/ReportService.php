<?php


namespace App\Http\Service;


use App\Http\Model\AppRegionPvModel;
use App\Http\Model\AppRegionUvModel;
use Illuminate\Support\Facades\DB;

class ReportService {
    /**
     * 获取当日实时数据快报
    */
    public function getRealTimeData($app_id = 0){
        $t_day = date('Ymd',time());
        $y_day = date('Ymd',strtotime("-1 day"));

        $sql = 'select * from day_app_pv_stat where appid = '.$app_id.' and day >= ' . $y_day;
        $res = DB::select($sql);

        $t_count = 0;
        $y_count = 0;
        if(!empty($res)){
            foreach ($res as $key => $val){
                if(!is_array($val)){
                    $val = (array)$val;
                }
                if(Q($val, 'day') == $t_day){
                    $t_count = Q($val, 'times');
                }else if(Q($val, 'day') == $y_day){
                    $y_count = Q($val, 'times');
                }
            }
        }
        if($t_count == 0 || $y_count == 0){
            $t_day = substr($t_day, 2);
            $y_day = substr($y_day, 2);
            $sql = 'select dw_date, sum(num) as num from stat_app_ad where appid = ' .$app_id. ' and dw_date >= ' . $y_day . ' group by dw_date';
            $t_res = DB::connection('online_v2_cpanel')->select($sql);
            if(!empty($t_res)){
                foreach ($t_res as $key => $val){
                    if(!is_array($val)){
                        $val = (array)$val;
                    }
                    if(Q($val, 'dw_date') == $t_day){
                        $t_count = Q($val, 'num');
                    }else if(Q($val, 'dw_date') == $y_day){
                        $y_count = Q($val, 'num');
                    }
                }
            }
        }

        return ['yesterday_pv_count' => $y_count, 'today_pv_count' => $t_count];
    }

    /**
     * 获取指定日期相关pv数据
     */
    public function getRegionPvByDay($app_id = 0, $region_code = '', $time_start = '', $time_end = ''){
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
     * 获取指定日期相关uv数据
     */
    public function getUvByDay($app_id = 0, $time_start = '', $time_end = ''){
        $day_count = 7;

        $sql = ' select day, uv AS num from day_app_uv_stat where appid = ' . $app_id;
        if(!$time_start && !$time_end){
            $start_day = date('Ymd',time());
            $end_day = date('Ymd',strtotime("-7 day"));
            $sql .= ' and day between ' . $start_day . ' and ' . $end_day;
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

        $sql .= ' group by day order by day desc ';
        $res = DB::select($sql);

        $total_pv = 0;
        if(!empty($res))
        {
            foreach ($res as $val)
            {
                if(!is_array($val)){
                    $val = (array)$val;
                }
                $total_pv += intval($val['num']);
            }
        }
        $result = [
            'total_uv' => $total_pv,
            'uv_data' => $res,
            'day_uv' => round($total_pv / $day_count, 2)
        ];

        return $result;
    }

    /**
     * 获取pv历史数据
     */
    public function getHistoryPv($app_id = 0, $region_code = '', $time_start = '', $time_end = ''){
        $day_count = 7;
        $where = [
            'appid' => $app_id
        ];
        if(!$time_start && !$time_end){
            $start_day = date('Ymd',time());
            $end_day = date('Ymd',strtotime("-7 day"));
        }else{
            if($time_start && $time_end){
                $second_start = strtotime($time_start);
                $second_end = strtotime($time_end);

                if($second_end < $second_start){
                    return -3;
                }

                $diff = diffDate(date('Y-m-d',$second_start), date('Y-m-d',$second_end));
                if(Q($diff, 'year') > 0 || (Q($diff, 'month') > 2 && Q($diff, 'day') > 0)){
                    return -4;
                }

                $start_day = date('Ymd',$second_start);
                $end_day = date('Ymd',$second_end);

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

        $regionService = app()->make(RegionCodeService::class);
        $region_name = '全国';
        if($region_code){
            $where['regioncode'] = $region_code;
            $region = $regionService->getRegionByCode($region_code);
            $region_name = Q($region, 'region_name');
        }

        $res = DB::table('day_group_app_region_pv_stat')->where($where)->whereBetween('day', [$start_day, $end_day])->groupBy('day')->orderBy('day', 'desc')->get(['day', DB::raw('SUM(times) as num')])->toArray();
        $total_pv = DB::table('day_group_app_region_pv_stat')->where($where)->whereBetween('day', [$start_day, $end_day])->sum('times');
//        if(!empty($res))
//        {
//            foreach ($res as $key => &$val){
//                if(!is_array($val)){
//                    $val = (array)$val;
//                }
////                $total_pv += intval($val['num']);
//                $region = $regionService->getRegionByCode(Q($val, 'regioncode'));
//                $val['region_name'] = Q($region, 'region_name');
//            }
//        }
        $result = [
            'total_pv' => $total_pv,
            'day_pv' => round($total_pv / $day_count, 2),
            'region_name' => $region_name,
            'pv_data' => $res,
        ];

        return $result;
    }

    /**
     * 获取uv历史数据
     */
    public function getHistoryUv($app_id = 0, $region_code = '', $time_start = '', $time_end = ''){
        $day_count = 7;
        $where = [
            'appid' => $app_id
        ];
        if(!$time_start && !$time_end){
            $start_day = date('Ymd',time());
            $end_day = date('Ymd',strtotime("-7 day"));
        }else{
            if($time_start && $time_end){
                $second_start = strtotime($time_start);
                $second_end = strtotime($time_end);

                if($second_end < $second_start){
                    return -3;
                }

                $diff = diffDate(date('Y-m-d',$second_start), date('Y-m-d',$second_end));
                if(Q($diff, 'year') > 0 || (Q($diff, 'month') > 2 && Q($diff, 'day') > 0)){
                    return -4;
                }

                $start_day = date('Ymd',$second_start);
                $end_day = date('Ymd',$second_end);

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

        $regionService = app()->make(RegionCodeService::class);
        $region_name = '全国';
        if($region_code){
            $where['regioncode'] = $region_code;
            $region = $regionService->getRegionByCode($region_code);
            $region_name = Q($region, 'region_name');
        }

        $res = DB::table('day_group_app_region_uv_stat')->where($where)->whereBetween('day', [$start_day, $end_day])->groupBy('day')->orderBy('day', 'desc')->get(['day', DB::raw('SUM(uv) as num')])->toArray();
        $total_uv = DB::table('day_group_app_region_uv_stat')->where($where)->whereBetween('day', [$start_day, $end_day])->sum('uv');
        $result = [
            'total_uv' => $total_uv,
            'day_uv' => round($total_uv / $day_count, 2),
            'region_name' => $region_name,
            'uv_data' => $res,
        ];

        return $result;
    }

    /**
     * 获取地域pv数据
    */
    public function getRegionPv($app_id = 0, $region_code = '', $time_start = '', $time_end = '', $page_size = 10){
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
     * 获取地域uv数据
    */
    public function getRegionUv($app_id = 0, $region_code = '', $time_start = '', $time_end = '', $page_size = 10){
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