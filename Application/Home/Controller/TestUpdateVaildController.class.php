<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

/**
 * Description of TestUpdateVaildController
 *
 * @author admin
 */
class TestUpdateVaildController extends BaseController{
    
    public function index(){
        $this->display();
    }
    
    public function update(){
        $use_code = I('use_code','','trim');
        $valid_time = I('valid_time','','trim');
        $data['valid_time'] = strtotime($valid_time);
        $data['update_time'] = date('Y-m-d H:i:s');
        $up_bool = M('member_use_code')->where(array('use_code'=>$use_code))->data($data)->save();
        if($up_bool) $this->oldAjaxReturn (0, '修改成功', 1);
        else $this->oldAjaxReturn (0, '修改失败', 0);
    }
}
