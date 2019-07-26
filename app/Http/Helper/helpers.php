<?php

/**
 * @title 给对象赋值 用于排除non-of-object错误
 */
if (! function_exists('Q')) {
    function Q(...$args)
    {
        $count = count($args);
        if($count > 1){
            $obj = null;
            if(isset($args[0]->{$args[1]})){
                $obj = $args[0]->{$args[1]};
            }elseif(isset($args[0][$args[1]])){
                $obj = $args[0][$args[1]];
            }
            if($count > 2){
                for($i = 2; $i < $count; $i++){
                    if(!isset($obj->{$args[$i]})){
                        if(isset($obj[$args[$i]])){
                            $obj = $obj[$args[$i]];
                        }else{
                            return null;
                        }
                    }else{
                        $obj = $obj->{$args[$i]};
                    }
                }
            }
            return $obj;
        }
        return $args[0] ?? null;
    }
}


if(!function_exists('array_combine_v')){
    function array_combine_v($arr1, $arr2){
        $new_arr = [];
        foreach ($arr1 as $k => $v){

            if(array_key_exists($v, $new_arr)){
                $xv = $new_arr[$v];
                if(is_array($xv)){
                    array_push($xv, $arr2[$k]);
                    $new_arr[$v] = $xv;
                    $arr2[$k] = $xv;
                }else{
                    $nv = [];
                    array_push($nv, $xv);
                    array_push($nv, $arr2[$k]);
                    $new_arr[$v] = $nv;
                    $arr2[$k] = $nv;
                }
            }else{
                $nv = [
                    $v => $arr2[$k]
                ];
                if($new_arr){
                    $new_arr = array_merge($new_arr, $nv);
                }else{
                    $new_arr = $nv;
                }

            }
        }
        $result = array_combine($arr1, $arr2);
        return $result;
    }
}


if(!function_exists('file_type')){
    function file_type($file = ''){
        if($file){
            try{
                $ch = curl_init();
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt ($ch, CURLOPT_URL, $file);
                curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
                curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_NOBODY, true);

                $content = curl_exec ($ch);

                curl_close ($ch);

                return $content;
            }catch (\mysql_xdevapi\Exception $e){
                return false;
            }

        }else{
            return false;
        }
    }
}


/**
 * 导出excel(csv)
 * @data 导出数据
 * @headlist 第一行,列名
 * @fileName 输出Excel文件名
 */
if(!function_exists('exportCsv')){
    function exportCsv($data = array(), $headlist = array(), $fileName){
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$fileName.".csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        //打开PHP文件句柄,php://output 表示直接输出到浏览器
        $fp = fopen('php://output', 'w');

        //输出Excel列名信息
        foreach ($headlist as $key => $value) {
            //CSV的Excel支持GBK编码，一定要转换，否则乱码
            $headlist[$key] = iconv('utf-8', 'gbk', $value);
        }

        //将数据通过fputcsv写到文件句柄
        fputcsv($fp, $headlist);

        //计数器
        $num = 0;

        //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 100000;

        //逐行取出数据，不浪费内存
        $count = count($data);
        for ($i = 0; $i < $count; $i++) {

            $num++;

            //刷新一下输出buffer，防止由于数据过多造成问题
            if ($limit == $num) {
                ob_flush();
                flush();
                $num = 0;
            }

            $row = $data[$i];
            foreach ($row as $key => $value) {
                $row[$key] = iconv('utf-8', 'gbk', $value);
            }

            fputcsv($fp, $row);
        }
    }
}


/**
 * @param string $file_name excel表的表名
 * @param array $data 要导出excel表的数据，接受一个二维数组
 * @param array $head excel表的表头，接受一个一维数组
 * @param array $user_style 列样式
 * @param string $sheet_name sheet名字
 * @throws \PhpOffice\PhpSpreadsheet\Exception
 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
 */
if(!function_exists('exportExcel')){
    function exportExcel($file_name = '', $data = [], $head = [],$format = "xlsx", $user_style = [], $sheet_name = ''){
        set_time_limit(0);
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        if($sheet_name){
            $sheet->setTitle($sheet_name);
        }

//        $sheet->setTitle('表名');
        $letter = 'A';
        foreach($head as $values){
            $sheet->setCellValue($letter.'1', $values);
            ++$letter;
        }
        if($user_style){
            foreach ($user_style as $key => $val){
                if(Q($val, 'column') && Q($val, 'value')){
                    $sheet->getColumnDimension(Q($val, 'column'))->setWidth(Q($val, 'value'));
                }
            }
        }
        if(is_array($data)){
            foreach($data as $k=>$v){
                $letter = 'A';
                $k = $k+2;
                reset($head);
                foreach($head as $key=>$value){
                    $testKey = explode('.',$key);
                    if(count($testKey)>1){
                        $val = $v[$testKey[0]][$testKey[1]];
                    }else{
                        $v = array_values($v);
                        $val = $v[$key];
                    }
                    $sheet->setCellValue($letter.$k, $val);
                    ++$letter;
                }
            }
        }
        ob_end_clean();
        if ($format == 'xls') {
            //输出Excel03版本
            header('Content-Type:application/vnd.ms-excel');
            $class = "\PhpOffice\PhpSpreadsheet\Writer\Xls";
        } elseif ($format == 'xlsx') {
            //输出07Excel版本
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $class = "\PhpOffice\PhpSpreadsheet\Writer\Xlsx";
        }
        //输出名称
        header('Content-Disposition:attachment;filename="'.mb_convert_encoding($file_name,"GB2312", "utf-8").'.'.$format.'"');
        //禁止缓存
        header('Cache-Control: max-age=0');
        $writer = new $class($spreadsheet);
//        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

        $writer->save('php://output');

//        $filePath = env('runtime_path')."temp/".time().microtime(true).".tmp";
//        $writer->save($filePath);
//        readfile($filePath);
//        unlink($filePath);

//        header('Content-Type: application/vnd.ms-excel');
//        header('Content-Disposition: attachment;filename="' . $name . '.xlsx"');
//        header('Cache-Control: max-age=0');
//        $writer = new Xlsx($spreadsheet);
//        $writer->save('php://output');
//
//        //删除清空：
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        exit;
    }
}


if(!function_exists('UnicodeEncode')){
    function UnicodeEncode($str){
        //split word
        preg_match_all('/./u',$str,$matches);

        $unicodeStr = "";
        foreach($matches[0] as $m){
            //拼接
            $unicodeStr .= "&#".base_convert(bin2hex(iconv('UTF-8',"UCS-4",$m)),16,10);
        }
        return $unicodeStr;
    }

}


if(!function_exists('unicodeDecode')){
    function unicodeDecode($unicode_str){
        $json = '{"str":"'.$unicode_str.'"}';
        $arr = json_decode($json,true);
        if(empty($arr)) return '';
        return $arr['str'];

    }

}


if(!function_exists('sanitize')){
    function sanitize($string){
        $string = preg_replace("/\r|\n/", "", $string);

        return trim($string);
    }
}


if(!function_exists('get_cpanel_info_test')){
    function get_cpanel_info_test($url, $data){
        $data  = json_encode($data);

        $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        return json_decode($output,true);
    }
}