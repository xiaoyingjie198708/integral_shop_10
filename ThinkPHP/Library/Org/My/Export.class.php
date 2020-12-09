<?php
/*
* 批量导出类
* @package ORG
*/
namespace Org\My;
class Export extends \Think\Model {
    public $limit = 1000;
    public $title = '';
    public $execl_fields = array();
    public $table = '';
    protected  $count = 0;
    protected  $data = array();
    private $page;
    private $header_name = '';
    public function __construct($table='') {
        $table ? parent::__construct($table) : exit('Table name is empty!') ;
        $this->title = $this->title ? $this->title : $table;
        $this->table = $table;
    }
    //设置总数据量
    public function setCount($count=0) {
        if($count <= $this->limit && IS_AJAX) exit('export');
        return $this->count = $count;
    }
    //获取数据
    public function getExportData() {
        $this->page = new \Think\Page($this->count,$this->limit);
        $data = $this->limit($this->page->firstRow.','.$this->page->listRows)->select();
        $this->data = $data;
        return $data;
    }
    //设置数据
    public function setData($data) {
        return $this->data = $data;
    }
    //设置表头
    public function setHeaderName($headerName){
        return $this->header_name = $headerName;
    }
    //导出数据
    public function export($column_type = array()) {
        $excelModel = new \Org\My\Excel();
        if($column_type) $excelModel->set_column_type($column_type);
        $count_page = ceil($this->count/$this->limit);
        if($count_page > 1) {
            if(intval($_REQUEST['p']) > $count_page && IS_AJAX) exit('export');
            if(intval($_REQUEST['p']) > $count_page) $this->package();
            $excelModel->export_type = 2;
            session('export_path') ? session('export_path') : session('export_path',time());
            $excelModel->savepath = RUNTIME_PATH.'export_data_'.session('export_path').'/';
            $this->title = str_replace('tm_','',$this->table).'_'.$_REQUEST['p'];
        }
        if(!is_dir($excelModel->savepath)) mkdir($excelModel->savepath);
        if($this->header_name){
            $excelModel->exportexcel($this->data,iconv('utf-8','gbk',$this->title),array_keys($this->execl_fields),array_values($this->execl_fields),150,'#88aa22',$this->header_name);
        }else{
            $excelModel->exportexcel($this->data,iconv('utf-8','gbk',$this->title),array_keys($this->execl_fields),array_values($this->execl_fields),150);
        }
        return $count_page;
    }
    //打包下载
    public function package() {
        import('Org.My.PclZip');
        $zipurl = RUNTIME_PATH.'export_data_'.session('export_path').'.zip';
        $zip = new \PclZip($zipurl);
        $file_url = RUNTIME_PATH.'export_data_'.session('export_path');
        $zip->create($file_url,PCLZIP_OPT_REMOVE_ALL_PATH);
        $this->unlink($file_url);
        session('export_path',null);
        \Org\Net\Http::download($zipurl,$this->title.'.zip','',0,true);
    }
    //删除导出的文件
    public function unlink($dir) {
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) unlink($fullpath);
                else $this->unlink($fullpath);
            }
        }
        closedir($dh);
        rmdir($dir);
        return true;
    }
}
?>