<?php


namespace App\Http\Service;


use App\Http\Model\AppRegionPvModel;
use App\Http\Model\AppRegionUvModel;
use App\Http\Model\RegionAppCategoryModel;
use App\Http\Model\RegionCategoryModel;
use Illuminate\Support\Facades\DB;

class ReportService {

    protected $end_day = '';
    protected $start_day = '';
    protected $day_count = 7;

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
            $t_res = DB::connection('v2_cpanel')->select($sql);
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

        return ['yesterday_pv_count' => number_format($y_count), 'today_pv_count' => number_format($t_count)];
    }

    /**
     * 获取渠道列表
    */
    public function getAppList($words = ''){
        $sql = "select appid, title from cpanel_app ";
        if($words){
            $sql .= " where title like '%" . $words . "%'";
        }
        $t_res = DB::connection('v2_cpanel')->select($sql);

        return $t_res ? $t_res : [];
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
            $start_day = date('Ymd',strtotime("-7 day"));
            $end_day = date('Ymd',time());
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
    public function getHistoryPv($app_id = 0, $time_start = '', $time_end = ''){
        $where = [
            'appid' => $app_id
        ];

        $res = $this->validateSearchDate($time_start, $time_end);
        if($res !== true){
            return $res;
        }

        $date_list = $this->transeDateList($this->start_day, $this->end_day);

        $res = DB::table('day_group_app_region_pv_stat')->where($where)->whereBetween('day', [$this->start_day, $this->end_day])->groupBy('day')->orderBy('day', 'desc')->get(['day', DB::raw('IFNULL(SUM(times), 0) as num')])->toArray();
        $total_pv = DB::table('day_group_app_region_pv_stat')->where($where)->whereBetween('day', [$this->start_day, $this->end_day])->sum('times');

        $pv_data = [];
        foreach ($date_list as $d){
            $ie = 0;
            foreach ($res as $r){
                if(Q($r, 'day') == $d){
                    $ie = 1;
                    $r->num = number_format(Q($r, 'num'));
                    $r->ask_num = '暂不支持';
                    $r->send_num = '暂不支持';
                    array_push($pv_data, $r);
                    break;
                }
            }
            if($ie == 0){
                $uv = ['day' => $d, 'num' => 0, 'ask_num' => '暂不支持', 'send_num' => '暂不支持'];
                array_push($pv_data, $uv);
            }
        }

        $cate_list = RegionCategoryModel::all();
        $chart_list = [
            'total_chart' => []
        ];

        $title_list = ['日期','请求量','下发量', '曝光量'];
        foreach ($cate_list as $cate){
            array_push($title_list, $cate['name']);
            $chart_list[substr($cate['name'],0, 1) . '_chart'] = [];
        }

        foreach ($pv_data as $key=> $val){
            if(!is_array($val)){
                $val = (array)$val;
            }
            array_push($chart_list['total_chart'], str_replace(',','',Q($val, 'num')));
            $res = $this->getCategoryHistoryData($val, 'day_group_app_region_pv_stat', 'times', $where, $app_id, Q($val, 'day'), $cate_list, $chart_list);
            $pv_data[$key] = Q($res, 'data');
            $chart_list = Q($res, 'chart');
        }

        $result = [
            'total_pv' => number_format($total_pv),
            'day_pv' => number_format(round($total_pv / $this->day_count, 2)),
            'pv_data' => $pv_data,
            'title_list' => $title_list,
            'chart_list' => $chart_list
        ];

        return $result;
    }

    /**
     * 获取uv历史数据
     */
    public function getHistoryUv($app_id = 0, $time_start = '', $time_end = ''){
        $where = [
            'appid' => $app_id
        ];

        $res = $this->validateSearchDate($time_start, $time_end);
        if($res !== true){
            return $res;
        }

        $date_list = $this->transeDateList($this->start_day, $this->end_day);

        $res = DB::table('day_group_app_region_uv_stat')->where($where)->whereBetween('day', [$this->start_day, $this->end_day])->groupBy('day')->orderBy('day', 'desc')->get(['day', DB::raw('IFNULL(SUM(uv),0) as num')])->toArray();
        $total_uv = DB::table('day_group_app_region_uv_stat')->where($where)->whereBetween('day', [$this->start_day, $this->end_day])->sum('uv');
        $uv_data = [];
        $chart_list = [
            'total_chart' => []
        ];
        foreach ($date_list as $d){
            $ie = 0;
            foreach ($res as $r){
                if(Q($r, 'day') == $d){
                    $ie = 1;
                    $r->num = number_format(intval(Q($r, 'num')));
                    array_push($uv_data, $r);
                    break;
                }
            }
            if($ie == 0){
                $uv = ['day' => $d, 'num' => 0];
                array_push($uv_data, $uv);
            }
        }

        $cate_list = RegionCategoryModel::all();


        $title_list = ['日期','去重设备数'];
        foreach ($cate_list as $cate){
            array_push($title_list, $cate['name']);
            $chart_list[substr($cate['name'],0, 1) . '_chart'] = [];
        }

        foreach ($uv_data as $key=> $val){
            if(!is_array($val)){
                $val = (array)$val;
            }
            array_push($chart_list['total_chart'], str_replace(',','',Q($val, 'num')));
            $res = $this->getCategoryHistoryData($val, 'day_group_app_region_uv_stat', 'uv', $where, $app_id, Q($val, 'day'), $cate_list, $chart_list);
            $uv_data[$key] = Q($res, 'data');
            $chart_list = Q($res, 'chart');
        }

        $result = [
            'total_uv' => number_format($total_uv),
            'day_uv' => number_format(round($total_uv / $this->day_count, 2)),
            'uv_data' => $uv_data,
            'title_list' => $title_list,
            'chart_list' => $chart_list
        ];

        return $result;
    }

    /**
     * 验证搜索日期
    */
    private function validateSearchDate($time_start, $time_end){
        if(!$time_start && !$time_end){
            $this->start_day = $time_start = date('Ymd',strtotime("-7 day"));
            $this->end_day = $time_end = date('Ymd',time());
        }
        if($time_start && $time_end){
            $second_start = strtotime($time_start);
            $second_end = strtotime($time_end);
            if($second_end < $second_start || $second_end == $second_start){
                return -3;
            }
            if($second_end > time()){
                return -4;
            }
            $diff = diffDate(date('Y-m-d',$second_start), date('Y-m-d',$second_end));
            if(Q($diff, 'year') > 0 || (Q($diff, 'month') > 2 && Q($diff, 'day') > 0)){
                return -5;
            }
            $this->start_day = date('Ymd',$second_start);
            $this->end_day = date('Ymd',$second_end);

            $this->day_count = ($second_end - $second_start) / 86400;
        }else{
            if(!$time_start){
                return -1;
            }
            if(!$time_end){
                return -2;
            }
        }

        return true;
    }


    /**
     * 获取指定条件不同城市等级下的相关数据
    */
    private function getCategoryHistoryData($data, $table, $col = '', $where, $app_id, $day, $cate_list, $chart_list = []){
        $exist_region = [];
        $where['day'] = $day;
        foreach ($cate_list as $cate){
            $res = [];
            $regioncodes  = RegionAppCategoryModel::where(['category_id' => Q($cate, 'id'), 'appid' => $app_id])->pluck('region_code')->toArray();

            if($regioncodes){
                $exist_region = array_merge($exist_region, $regioncodes);
                $res = DB::table($table)->where($where)->whereIn('regioncode', $regioncodes)->get([DB::raw("IFNULL(SUM($col), 0) as num")])->toArray();
            }

            if(Q($cate, 'id') == 99){    //B类城市  其他
                $res = DB::table($table)->where($where)->whereNotIn('regioncode', $exist_region)->get([DB::raw("IFNULL(SUM($col), 0) as num")])->toArray();
            }

            $c = substr($cate['name'],0, 1);
            $key = $c . '_num';
            $num = 0;
            if($res && $res[0]){
                $num = $res[0]->num;
            }
            $s_chart = $c . '_chart';
            array_push($chart_list[$s_chart], intval($num));

            $data[$key] = number_format($num);
        }

        return ['data' => $data, 'chart' => $chart_list];
    }



    /**
     * 获取地域pv数据
    */
    public function getRegionPv($app_id = 0, $category_id, $region_code = '', $time_start = '', $time_end = '', $page_size = 10){
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
    private function transeDateList($start_day = '', $end_day = ''){
        $date_list = [];
        if($start_day && $end_day){
            $start_time = strtotime($start_day);
            $end_time = strtotime($end_day);
            $current_time = $start_time;
            array_push($date_list, date('Ymd',$start_time));
            while ($current_time < $end_time){
                $current_time += 1*24*60*60;
                array_push($date_list, date('Ymd',$current_time));
            }
            if(!in_array(date('Ymd',$end_time), $date_list)){
                array_push($date_list, date('Ymd',$end_time));
            }
        }

        return $date_list;
    }
}