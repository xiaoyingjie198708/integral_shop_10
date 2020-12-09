<?php
    namespace Org\My;
    class Excel {

        private $objPHPExcel;
        public $export_type = 1; //导出类型 1:直接输出文件  2:保存文件到本地
        public $savepath = 'php://output';  //导出的目录
        private $column_type = array();//字段的类型
        public function __construct() {
             include('excel/PHPExcel.php');
             include('excel/PHPExcel/IOFactory.php');
             include('excel/PHPExcel/Reader/Excel5.php');
             include('excel/PHPExcel/Reader/Excel2007.php');
             include('excel/PHPExcel/Reader/CSV.php');
             include('excel/PHPExcel/Style/Border.php');
            $this->objPHPExcel = new \PHPExcel();
            //parent::__construct();
        }

        //重命名sheet页名称
        public function sheetTitle($titleName = ''){
            $this->objPHPExcel->getActiveSheet()->setTitle($titleName);
        }

        //设置字段的类型
        public function set_column_type($field_types){
            $this->column_type = $field_types;
        }

        //设置exel文件属性
        public function fileAttribute(){
            $this->objPHPExcel->getProperties()->setCreator("twomi.cn")
                                 ->setLastModifiedBy("twomi.cn")
                                 ->setTitle("Data of twomi.cn")
                                 ->setSubject("Data of twomi.cn")
                                 ->setDescription("Data of twomi.cn")
                                 ->setKeywords("Data of twomi.cn")
                                 ->setCategory("Data of twomi.cn");
        }
        /* @功 能：导出数据到exel文件
         * @参 数：1,$data:				- 二维数组
         * 		   2,$fileName			- 下载后的文件名称。
         *		   3,$fields			- 导出数据对应的数组中的下标的字段。
         * 		   4,columnNameArr		- 多列首列的名称
         *		   5,columnAvgWidth		- 各列的宽度
         * 		   6,$columnBgColor		- 首列的默认背景色
         * @返 回：无（下载到exel文件）
         * @说 明：当前最多是A-Z列（26列），在数组$columnLetter按Exel列格式添加即可扩展。
         * @示 例：PublicModel::downMoreColumnDateToExel($userInfo,'活动参与者信息',array('id','real_name','pmi','email'),array('用户ID号','真实姓名','用户PMI号','用户邮箱'),150);
         */
        public function exportexcel($data,$fileName,$fields,$columnNameArr,$columnAvgWidth='',$columnBgColor='#88aa22',$headerName=''){
            if (PHP_SAPI == 'cli')
                die('This example should only be run from a Web Browser');
            //设置exel列（真接在后面即可扩展）
            $columnLetter=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
            //边框样式
            $styleArray = array(  
                'borders' => array(  
                    'allborders' => array(  
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,//细边框  
                        //'color' => array('argb' => 'FFFF0000'),  
                    ),  
                ),  
            );  
            $temp = array();
            $j = 0;
            $k = 0;
            for($i=0;$i<count($fields);$i++) {
                if($i < count($columnLetter)) {
                    $temp[] = $columnLetter[$j];
                    if($j == (count($columnLetter)-1)) $j=0;
                    else $j++;
                }else{
                    $temp[] = $columnLetter[$k].$columnLetter[$j];
                    if($j == (count($columnLetter)-1)) {
                        $j=0;
                        $k++;
                    }else{
                        $j++;
                    }
                }
            }
            $init_column = 2;
            if($headerName){
                $this->objPHPExcel->getActiveSheet()->mergeCells('A1:'.$columnLetter[count($fields) -1].'1')->setCellValue('A1', $headerName);
                $this->objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
                $init_column = 3;
            }
            $columnLetter = $temp;
            $Acount=count($columnNameArr);
            for($i=0;$i<$Acount;$i++){
                $columnWord=$columnLetter[$i];
                $columnName=$columnNameArr[$i];
                $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnWord.($init_column - 1), $columnName);

                //设置指定列的宽度
                if(!empty($columnAvgWidth)){
                    $this->objPHPExcel->getActiveSheet()->getColumnDimension($columnWord.($init_column - 1))->setWidth($columnAvgWidth);			//列为设定宽度。
                }else{
                    $this->objPHPExcel->getActiveSheet()->getColumnDimension($columnWord.($init_column - 1))->setAutoSize(true); 				//列自动
                }
                //设置列的背景色
                $this->objPHPExcel->getActiveSheet()->getStyle($columnWord.($init_column - 1))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $this->objPHPExcel->getActiveSheet()->getStyle($columnWord.($init_column - 1))->getFill()->getStartColor()->setARGB($columnBgColor);
                $this->objPHPExcel->getActiveSheet()->getStyle($columnWord.($init_column - 1))->applyFromArray($styleArray);
            }

            // 写入exel数据。
            $fcount=count($fields);
            foreach($data as $key=>$val){
                for($i=0;$i<$fcount;$i++){
                    $word=$columnLetter[$i];
                    if($this->column_type){
                        $obj=$this->objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit(($word.($key+$init_column)), $data[$key][$fields[$i]],$this->column_type[$i]);
                    }else{
                        $obj=$this->objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit(($word.($key+$init_column)), $data[$key][$fields[$i]],\PHPExcel_Cell_DataType::TYPE_STRING);
                    }
                    if($val['color']){
                        $this->objPHPExcel->setActiveSheetIndex(0)->getStyle($word.($key+$init_column))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                        $this->objPHPExcel->setActiveSheetIndex(0)->getStyle($word.($key+$init_column))->getFill()->getStartColor()->setARGB($val['color']);
                    }
                    $this->objPHPExcel->setActiveSheetIndex(0)->getStyle($word.($key+$init_column))->applyFromArray($styleArray);
                }
                if($val['merage']){
                    $this->objPHPExcel->getActiveSheet()->mergeCells($val['merage']);
                    $this->objPHPExcel->getActiveSheet()->getStyle('A')->applyFromArray(array('alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
                }
                if($val['vertical_center']) $this->objPHPExcel->getActiveSheet()->getStyle($val['vertical_center'])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            }
            //设置打开exel后第一个显示的文件。
            $this->objPHPExcel->setActiveSheetIndex(0);
            if($this->export_type == 1) {
                //设置发送头信息
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
                header('Cache-Control: max-age=0');
            }else{
                $this->savepath .= $fileName.'.xls';
            }
            $objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
            $objWriter->save($this->savepath);
            return true;
        }

        /**
         *@功能 导入excel数据
         *
         *
         */
        public function importExcel($excelFile,$fields){
            $pathinfo = pathinfo($excelFile);
            if($pathinfo['extension'] == 'xlsx'){
                $objReader = \PHPExcel_IOFactory::createReader('Excel2007');//2007
            }elseif($pathinfo['extension'] == 'csv'){
				$objReader = \PHPExcel_IOFactory::createReader('CSV');//CSV
			}else{
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');//2003
            }
            $objPHPExcel = $objReader->load($excelFile);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $highestRow = $objWorksheet->getHighestRow();
            $highestColumn = $objWorksheet->getHighestColumn();
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
            for($row=2;$row<=$highestRow;$row++) {
                for($col=0;$col<$highestColumnIndex;$col++) {
                    $row_data[$fields[$col]] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                }
                $data[] = $row_data;
            }
            return $data;
        }
    }
?>