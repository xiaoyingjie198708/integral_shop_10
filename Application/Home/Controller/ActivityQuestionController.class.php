<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

class ActivityQuestionController extends BaseController{
    
    public function index(){
        $mod = D('ActivityQuestion');
        $where = $this->get_where();
        $page = new \Org\My\Page($mod->where($where)->count(), 10);
        $list = $mod->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('update_time desc')->select();
//        $this->search($this->get_search());
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->display();
    }
    
    public function add(){
        if(IS_AJAX){
            $mod = D('ActivityQuestion');
            if(!$mod->create()) {
                $error = each($mod->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $mod->create_time = date('Y-m-d H:i:s');
                $mod->update_time = date('Y-m-d H:i:s');
                $add_bool = $mod->add();
                if($add_bool) $this->oldAjaxReturn (0, '添加成功', 1);
                else $this->oldAjaxReturn (0, '添加失败', 0);
            }
        }
        $this->display();
    }
    
    public function update(){
        $id = I('id',0,'intval');
        $mod = D('ActivityQuestion');
        if(IS_AJAX){
            if(!$mod->create()) {
                $error = each($mod->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $mod->update_time = date('Y-m-d H:i:s');
                $update_bool = $mod->where(array('id'=>$id))->save();
                if($update_bool === FALSE) $this->oldAjaxReturn (0, '更新失败', 0);
                else $this->oldAjaxReturn (0, '更新成功', 1);
            }
        }
        $info = $mod->where(array('id'=>$id))->find();
        $this->assign('info', $info);
        $this->display();
    }
    
    public function change_status($id,$status){
        $mod = D('ActivityQuestion');
        $update_bool = $mod->where(array('id'=>$id))->data(array('status'=>$status,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($update_bool === FALSE) $this->oldAjaxReturn(0, '更新失败', 0);
        else $this->oldAjaxReturn(0, '更新成功', 1);
    }
    /*----------------------------------------------------受保护方法----------------------------------------------------------*/
    private function get_where(){
        $where = array();
        $where['status'] = array('gt',0);
        return $where;
    }
}
