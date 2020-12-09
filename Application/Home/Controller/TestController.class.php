<?php


namespace Home\Controller;

class TestController extends \Think\Controller{
    
    public function index(){
        $seatch = I('seatch','','trim,htmlspecialchars');
        var_dump($seatch);exit;
    }
    
}
