<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Org\My;

/**
 * 公共日志
 *
 * @author xiao.yingjie
 */
class MyLog {
    
    //写入日志
    static public function write_log($run_log='',$file='all',$dir='',$mode='a') {
        $log_dir = C('LOG_DIR');
        if($dir) $log_dir = $log_dir.trim($dir, '/').'/';
        if(is_dir($log_dir) || mkdir($log_dir)) {
            if($run_log) {
                $run_log_path = $file;
                $run_log_file = fopen($log_dir.$run_log_path,$mode); //打开执行日志文件
                fwrite($run_log_file,$run_log); //写入执行日志
                fclose($run_log_file);
            }
        }
        return null;
    }
    
   //读取日志
   static public function read_log($node='',$file='all',$dir='') {
       $log_dir = C('LOG_DIR');
       if($dir) $log_dir = $log_dir.trim($dir, '/').'/';
        $data = array();
        $fa_log_path = $log_dir.$file;
        if(is_file($fa_log_path)) $data = json_decode(file_get_contents($fa_log_path),true);
        return $node ? $data[$node] : $data;
   }
}
