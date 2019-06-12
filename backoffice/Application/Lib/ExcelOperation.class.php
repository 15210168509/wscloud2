<?php
/**
 * PHPExcel
 * Created by dbn
 * Date: 2016/12/15
 */

namespace Lib;

class ExcelOperation
{

    public function __construct()
    {
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.IOFactory');
        vendor('PHPExcel.PHPExcel.Writer.Excel5');
        vendor('PHPExcel.PHPExcel.Writer.Excel2007');
    }

    /**
     * 数据导出
     * @param string $fileName  文件名
     * @param array $headArr    表头数据（一维）
     * @param array $data       列表数据（二维）
     * @param int   $width      列宽
     * @return bool
     */
    public function push($fileName="", $headArr=array(), $data=array(), $width=20)
    {

        if (empty($headArr) && !is_array($headArr) && empty($data) && !is_array($data)) {
            return false;
        }

        $date = date("YmdHis",time());
        $fileName .= "_{$date}.xls";

        $objPHPExcel = new \PHPExcel();

        //设置表头
        $tem_key = "A";
        foreach($headArr as $v){
            if (strlen($tem_key) > 1) {
                $arr_key = str_split($tem_key);
                $colum = '';
                foreach ($arr_key as $ke=>$va) {
                    $colum .= chr(ord($va));
                }
            } else {
                $key = ord($tem_key);
                $colum = chr($key);
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setWidth($width); // 列宽
            $objPHPExcel->getActiveSheet()->getStyle($colum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // 垂直居中
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getFont()->setBold(true); // 字体加粗
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $tem_key++;
        }

        $objActSheet = $objPHPExcel->getActiveSheet();

        $border_end = 'A1'; // 边框结束位置初始化

        // 写入内容
        $column = 2;
        foreach($data as $key => $rows){ //获取一行数据
            $tem_span = "A";
            foreach($rows as $keyName=>$value){// 写入一行数据
                if (strlen($tem_span) > 1) {
                    $arr_span = str_split($tem_span);
                    $j = '';
                    foreach ($arr_span as $ke=>$va) {
                        $j .= chr(ord($va));
                    }
                } else {
                    $span = ord($tem_span);
                    $j = chr($span);
                }
                $objActSheet->setCellValue($j.$column, $value);
                $border_end = $j.$column;
                $tem_span++;
            }
            $column++;
        }

        $objActSheet->getStyle("A1:".$border_end)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN); // 设置边框


        $fileName = iconv("utf-8", "gb2312", $fileName);

        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');

        //设置活动单指数到第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }


    /**
     * 业务统计数据导出
     * @param string $fileName  文件名
     * @param array $headArr    表头数据（一维）
     * @param array $data       列表数据（二维）
     * @param int   $width      列宽
     * @return bool
     */
    public function stats_push($fileName="", $headArr=array(), $data=array(), $width=20)
    {

        if (empty($headArr) && !is_array($headArr) && empty($data) && !is_array($data)) {
            return false;
        }

        $date = date("YmdHis",time());
        $fileName .= "_{$date}.xls";

        $objPHPExcel = new \PHPExcel();

        //设置表头
        $key = ord("A");
        foreach($headArr as $v){
            $colum = chr($key);
            $objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setWidth($width); // 列宽
            $objPHPExcel->getActiveSheet()->getStyle($colum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // 垂直居中
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getFont()->setBold(true); // 字体加粗
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }

        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();

        $border_end = 'A1'; // 边框结束位置初始化
        // 写入内容
        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);

                $objActSheet->setCellValue($j.$column, $value);
                $objActSheet->getStyle( $j.$column)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

                if ('A'!=chr($span-count($rows)) && "A"!=chr($span-count($rows)+1)){
                    if (isset($rows['fee_list'])&&$rows['fee_list']){
                        $objActSheet->mergeCells($j.$column.':'.$j."".($column+count($rows['fee_list'])-1));
                    }
                    $span++;

                }else{
                    foreach ($rows['fee_list'] as $k=>$v){
                        $objActSheet->setCellValue(chr($span).$column, $v['type_name']);
                        $objActSheet->setCellValue(chr($span+1).$column, $v['total_fee']);
                        $column++;
                    }

                }
            }
            $border_end = chr(($span+1)).($column-1);

        }
        $objActSheet->getStyle("A1:".$border_end)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN); // 设置边框

        $fileName = iconv("utf-8", "gb2312", $fileName);

        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');

        //设置活动单指数到第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }

    /**
     * 导出订单信息
     * @param string $fileName
     * @param array $headArr
     * @param array $data
     * @param int $width
     * @return bool
     */
    public function orderPush($fileName="", $headArr=array(), $data=array(), $width=20)
    {

        if (empty($headArr) && !is_array($headArr) && empty($data) && !is_array($data)) {
            return false;
        }

        $date = date("YmdHis",time());
        $fileName .= "_{$date}.xls";

        $objPHPExcel = new \PHPExcel();

        //设置表头
        $tem_key = "A";
        foreach($headArr as $v){
            if (strlen($tem_key) > 1) {
                $arr_key = str_split($tem_key);
                $colum = '';
                foreach ($arr_key as $ke=>$va) {
                    $colum .= chr(ord($va));
                }
            } else {
                $key = ord($tem_key);
                $colum = chr($key);
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setWidth($width); // 列宽
            $objPHPExcel->getActiveSheet()->getStyle($colum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // 垂直居中
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getFont()->setBold(true); // 字体加粗
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $tem_key++;
        }

        $objActSheet = $objPHPExcel->getActiveSheet();

        $border_end = 'A1'; // 边框结束位置初始化

        // 写入内容
        $column = 2;

        foreach($data as $key => $rows){ //获取一行数据
            $tem_span = ord('A');
            foreach($rows as $keyName=>$value){// 写入一行数据
                $tem_char = chr($tem_span);

                $objActSheet->setCellValue($tem_char.$column, $value);
                $objActSheet->getStyle( $tem_char.$column)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $cellRow = count($rows['load_address']) >= count($rows['unload_address']) ? count($rows['load_address']) :count($rows['unload_address']);
                if ($cellRow>1 && $keyName !='load_address' && $keyName!= 'unload_address' && $keyName!='load_name'&& $keyName!='unload_name'&& $keyName!='load_phone'&& $keyName!='unload_phone') {
                    $objActSheet->mergeCells($tem_char.$column.':'.$tem_char.($column+$cellRow-1));
                }
                if ($keyName == 'load_address') {
                    $load_column = $column;
                    foreach ($rows['load_address'] as $k=>$v) {
                        $objActSheet->setCellValue($tem_char.$load_column, $v['load_address'].' ');
                        $load_column++;
                    }
                }
                if ($keyName == 'unload_address') {
                    $load_column = $column;
                    foreach ($rows['unload_address'] as $k=>$v) {
                        $objActSheet->setCellValue($tem_char.$load_column, $v['unload_address']);

                        $load_column++;
                    }
                }
                if ($keyName == 'load_name') {
                    $load_column = $column;
                    foreach ($rows['load_name'] as $k=>$v) {
                        $objActSheet->setCellValue($tem_char.$load_column, $v['load_name']);

                        $load_column++;
                    }
                }
                if ($keyName == 'load_phone') {
                    $load_column = $column;
                    foreach ($rows['load_phone'] as $k=>$v) {
                        $objActSheet->setCellValue($tem_char.$load_column, $v['load_phone']);
                        $load_column++;
                    }
                }
                if ($keyName == 'unload_name') {
                    $load_column = $column;
                    foreach ($rows['unload_name'] as $k=>$v) {
                        $objActSheet->setCellValue($tem_char.$load_column, $v['unload_name']);
                        $load_column++;
                    }
                }
                if ($keyName == 'unload_phone') {
                    $load_column = $column;
                    foreach ($rows['unload_phone'] as $k=>$v) {
                        $objActSheet->setCellValue($tem_char.$load_column, $v['unload_phone']);
                        $load_column++;
                    }
                }

                $border_end = $tem_char.$column;
                $tem_span++;
            }
            $column+= $cellRow?$cellRow:1;
        }
        $objActSheet->getStyle("A1:".$border_end)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN); // 设置边框


        $fileName = iconv("utf-8", "gb2312", $fileName);

        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');

        //设置活动单指数到第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }

    /**
     * 导出作业
     * @param string $fileName
     * @param array $headArr
     * @param array $data
     * @param int $width
     * @return bool
     */
    public function cyclePush ($fileName="", $headArr=array(), $data=array(), $width=20)
    {
        if (empty($headArr) && !is_array($headArr) && empty($data) && !is_array($data)) {
            return false;
        }

        $date = date("YmdHis",time());
        $fileName .= "_{$date}.xls";

        $objPHPExcel = new \PHPExcel();

        //设置表头
        $key = ord("A");
        foreach($headArr as $v){
            $colum = chr($key);
            $objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setWidth($width); // 列宽
            $objPHPExcel->getActiveSheet()->getStyle($colum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // 垂直居中
            $objPHPExcel->getActiveSheet()->getStyle($colum.'1')->getFont()->setBold(true); // 字体加粗
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }

        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();

        $border_end = 'A1'; // 边框结束位置初始化
        foreach($data as $key => $rows){ //获取一行数据
            $tem_span = ord('A');
            //填充色
            $objPHPExcel->getActiveSheet()->getStyle( 'A'.$column.":H".$column)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle( 'A'.$column.":H".$column)->getFill()->getStartColor()->setARGB('FF808080');
            foreach($rows as $keyName=>$value){// 写入一行数据
                $tem_char = chr($tem_span);
                $objActSheet->setCellValue($tem_char.$column, $value);
                $objActSheet->getStyle( $tem_char.$column)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $border_end = $tem_char.$column;
                $tem_span++;
                if ($keyName == 'transport') {
                    $counNum = count($rows['transport']);
                    //运输单表头
                    if (!empty($rows['transport'])) {
                        $column = $column+1;
                        $objActSheet->setCellValue('A'.($column), '运输单号');
                        $objActSheet->setCellValue('B'.($column), '司机');
                        $objActSheet->setCellValue('C'.($column), '装货货地址');
                        $objActSheet->setCellValue('D'.($column), '装货人');
                        $objActSheet->setCellValue('E'.($column), '装货人电话');
                        $objActSheet->setCellValue('F'.($column), '卸货地址');
                        $objActSheet->setCellValue('G'.($column), '卸货人');
                        $objActSheet->setCellValue('H'.($column), '卸货人电话');
                    }


                    $column=$column + 1;

                    //运输单
                    foreach ($rows['transport'] as $keyName=>$value) {
                        $tem_span_tr = ord('A');
                        foreach ($value as $k=>$v) {
                            $tem_span_tr_chr = chr($tem_span_tr);
                            $num = count($value['loadAddress']) >= count($value['unloadAddress']) ? count($value['loadAddress']):count($value['unloadAddress']);
                            if ($num>1 && ($k=='transport_code' || $k=='driver')) {
                                $objActSheet->mergeCells($tem_span_tr_chr.$column.':'.$tem_span_tr_chr.($column+$num-1));

                            }
                            if ($k =='transport_code' || $k == 'driver') {
                                $objActSheet->setCellValue($tem_span_tr_chr.$column, $v);
                                $objActSheet->getStyle( $tem_span_tr_chr.$column)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            } else {
                                $flg = $column;
                                foreach ($v as $key=>$val) {
                                    $objActSheet->setCellValue($tem_span_tr_chr.$flg, $val.' ');
                                    $flg++;
                                }

                            }

                            $tem_span_tr++;
                        }
                        $column=$column+$num;
                    }$column--;
                }
            }
            $column++;


            $border_end = $tem_span_tr_chr.$column;

        }

        $objActSheet->getStyle("A1:".$border_end)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN); // 设置边框


        $fileName = iconv("utf-8", "gb2312", $fileName);

        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');

        //设置活动单指数到第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }

}