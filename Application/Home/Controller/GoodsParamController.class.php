<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

/**
 * 商品配置参数管理
 *
 * @author xiao.yingjie
 */
class GoodsParamController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('ParamCategory')->where($where)->count(),10);
        $list = D('ParamCategory')->where($where)->limit($page->firstRow.','.$page->listRows)->order('category_sort asc,create_time desc')->select();
        //模拟树
        $new_list = array();
        foreach($list as $k=>$category){
            $category['parint_id'] = 0;
            $category['show_id'] = $category['param_category_id'];
            $category['level'] = 1;
            $category['show_name'] = $category['category_name'];
            $category['show_sort'] = $category['category_sort'];
            $category['path'] = '0';
            $new_list[] = $category;
            $param_name_list = D('ParamName')->where(array('param_category_id'=>$category['param_category_id']))->order('param_sort asc,create_time desc')->select();
            foreach($param_name_list as $kk=>$param_name){
                $param_name['parint_id'] = $category['param_category_id'];
                $param_name['show_id'] = $param_name['param_id'];
                $param_name['level'] = 2;
                $param_name['show_name'] = $param_name['param_name'];
                $param_name['show_sort'] = $param_name['param_sort'];
                $param_name['path'] = '0_'.$category['param_category_id'];
                $new_list[] = $param_name;
                $param_value_list = D('ParamValue')->where(array('param_id'=>$param_name['param_id']))->order('create_time asc')->select();
                foreach($param_value_list as $kkk=>$param_value){
                    $param_value['parint_id'] = $param_name['param_id'];
                    $param_value['show_id'] = $param_value['param_value_id'];
                    $param_value['level'] = 3;
                    $param_value['show_name'] = $param_value['param_value'];
                    $param_value['path'] = '0_'.$category['param_category_id'].'_'.$param_name['param_id'];
                    $new_list[] = $param_value;
                }
            }
        }
        $this->assign('page', $page->show());
        $this->assign('list', $new_list);
        $this->display();
    }
    
    public function get_add_page(){
        $content = $this->fetch('add');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    public function add(){
        $param_category = D('ParamCategory');
        if(!$param_category->create()) {
            $error = each($param_category->getError());
            $this->oldAjaxReturn($error['key'],$error['value'],0);
        }else{
            $param_category->param_category_id = (new \Org\Util\ThinkString())->uuid();
            $count = $param_category->where(array('category_name'=>$param_category->category_name,'category_type'=>$param_category->category_type,'status'=>array('neq',0)))->count();
            if($count) $this->oldAjaxReturn ('category_name', '分类名称已经存在，请重新输入', 0);
            //状态默认是有效
            $param_category->status = I('label_status',1,'intval');
            $param_category->create_time = date('Y-m-d H:i:s');
            $param_category->update_time = date('Y-m-d H:i:s');
            $add_bool = $param_category->add();
            if(!$add_bool) $this->oldAjaxReturn(0,'添加失败',0);
            $this->oldAjaxReturn(0,'添加成功',1);
        }
    }
    
    public function get_update_page(){
        $info = D('ParamCategory')->where($this->get_where())->find();
        $this->assign('info', $info);
        $content = $this->fetch('update');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    public function update(){
        $param_category_id = I('param_category_id','','trim,htmlspecialchars');
        $param_category = D('ParamCategory');
        if(!$param_category->create()) {
            $error = each($param_category->getError());
            $this->oldAjaxReturn($error['key'],$error['value'],0);
        }else{
            $count = $param_category->where(array('category_name'=>$param_category->category_name,'category_type'=>$param_category->category_type,'status'=>array('neq',0),'param_category_id'=>array('neq',$param_category_id)))->count();
            if($count) $this->oldAjaxReturn ('category_name', '分类名称已经存在，请重新输入', 0);
            //状态默认是有效
            $param_category->status = I('label_status',1,'intval');
            $param_category->update_time = date('Y-m-d H:i:s');
            //修改对应的值
            $param_value = D('ParamValue');
            $value_data['category_name'] = $param_category->category_name;
            $value_data['category_sort'] = $param_category->category_sort;
            $value_data['update_time'] = date('Y-m-d H:i:s');
            $param_category->startTrans();
            $update_value_bool = $param_value->where(array('param_category_id'=>$param_category_id,'status'=>array('neq',0)))->data($value_data)->save();
            $update_bool = $param_category->where($this->get_where())->save();
            if($update_bool === FALSE || $update_value_bool === FALSE) {
                $param_category->rollback();
                $this->oldAjaxReturn(0,'修改失败',0);
            }else{
                $param_category->commit();
                $this->oldAjaxReturn(0,'修改成功',1);
            }
        }
    }
    
    public function delete(){
        $param_category_id = I('param_category_id','','trim,htmlspecialchars');
        $name_count = D('ParamName')->where(array('param_category_id'=>$param_category_id,'status'=>array('neq',0)))->count();
        if($name_count) $this->oldAjaxReturn (0, '请删除分类下的参数', 0);
        $del_bool = D('ParamCategory')->where($this->get_where())->data(array('status'=>0,'update_time'=>date('Y-m-d H:i:s')))->save();
        if(!$del_bool) $this->oldAjaxReturn (0, '删除失败', 0);
        $this->oldAjaxReturn(0, '删除成功', 1);
    }
    
    /*------------------------------------------------商品属性名称---------------------------------------------*/
    public function get_add_param_name_page(){
        $parint_id = I('parint_id','','trim,htmlspecialchars');
        $category_info = D('ParamCategory')->where(array('param_category_id'=>$parint_id))->find();
        $this->assign('category_info', $category_info);
        $content = $this->fetch('add_param_name');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    public function add_param_name(){
        $param_name = D('ParamName');
        if(!$param_name->create()) {
            $error = each($param_name->getError());
            $this->oldAjaxReturn($error['key'],$error['value'],0);
        }else{
            if(!$param_name->param_category_id) $this->oldAjaxReturn (0, '参数错误', 0);
            $param_name->param_id = (new \Org\Util\ThinkString())->uuid();
            $count = $param_name->where(array('param_name'=>$param_name->param_name,'param_category_id'=>$param_name->param_category_id,'status'=>array('neq',0)))->count();
            if($count) $this->oldAjaxReturn ('param_name', '配置参数名称已经存在，请重新输入', 0);
            //状态默认是有效
            $param_name->status = I('status',1,'intval');
            $param_name->create_time = date('Y-m-d H:i:s');
            $param_name->update_time = date('Y-m-d H:i:s');
            $add_bool = $param_name->add();
            if(!$add_bool) $this->oldAjaxReturn(0,'添加失败',0);
            $this->oldAjaxReturn(0,'添加成功',1);
        }
    }
    
    public function get_update_param_name_page(){
        $param_id = I('param_id','','trim,htmlspecialchars');
        $info = D('ParamName')->where(array('param_id'=>$param_id))->find();
        $category_info = D('ParamCategory')->where(array('param_category_id'=>$info['param_category_id']))->find();
        $this->assign('category_info', $category_info);
        $this->assign('info', $info);
        $content = $this->fetch('update_param_name');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    public function update_param_name(){
        $param_name = D('ParamName');
        if(!$param_name->create()) {
            $error = each($param_name->getError());
            $this->oldAjaxReturn($error['key'],$error['value'],0);
        }else{
            if(!$param_name->param_category_id) $this->oldAjaxReturn (0, '参数错误', 0);
            $count = $param_name->where(array('param_name'=>$param_name->param_name,'param_category_id'=>$param_name->param_category_id,'status'=>array('neq',0),'param_id'=>array('neq',$param_name->param_id)))->count();
            if($count) $this->oldAjaxReturn ('param_name', '配置参数名称已经存在，请重新输入', 0);
            //状态默认是有效
            $param_name->status = I('status',1,'intval');
            $param_name->update_time = date('Y-m-d H:i:s');
            $param_name->startTrans();
            //修改对应的值
            $param_value = D('ParamValue');
            $value_data['param_name'] = $param_name->param_name;
            $value_data['param_sort'] = $param_name->param_sort;
            $value_data['update_time'] = date('Y-m-d H:i:s');
            $update_value_bool = $param_value->where(array('param_id'=>$this->param_id,'status'=>array('neq',0)))->data($value_data)->save();
            $update_bool = $param_name->where(array('param_id'=>$this->param_id))->save();
            if($update_bool === FALSE || $update_value_bool === FALSE) {
                $param_name->rollback();
                $this->oldAjaxReturn(0,'修改失败',0);
            }else{
                $param_name->commit();
                $this->oldAjaxReturn(0,'修改成功',1);
            }
        }
    }
    
    public function delete_param_name(){
        $param_id = I('param_id','','trim,htmlspecialchars');
        $value_count = D('ParamValue')->where(array('param_id'=>$param_id,'status'=>array('neq',0)))->count();
        if($value_count) $this->oldAjaxReturn (0, '请先删除参数下的参数值', 0);
        $del_bool = D('ParamName')->where(array('param_id'=>$param_id))->data(array('status'=>0,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($del_bool) $this->oldAjaxReturn (0, '删除成功', 1);
        else $this->oldAjaxReturn (0, '删除失败', 0);
    }
    /*-------------------------------------------------商品属性值----------------------------------------------*/
    public function get_add_param_value_page(){
        $parint_id = I('parint_id','','trim,htmlspecialchars');
        $name_info = D('ParamName')->where(array('param_id'=>$parint_id))->find();
        $category_info = D('ParamCategory')->where(array('param_category_id'=>$name_info['param_category_id']))->find();
        $this->assign('category_info', $category_info);
        $this->assign('name_info', $name_info);
        $content = $this->fetch('add_param_value');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    public function add_param_value(){
        $param_value = D('ParamValue');
        if(!$param_value->create()) {
            $error = each($param_value->getError());
            $this->oldAjaxReturn($error['key'],$error['value'],0);
        }else{
            if(!$param_value->param_category_id || !$param_value->param_id) $this->oldAjaxReturn (0, '参数错误', 0);
            $param_value->param_value_id = (new \Org\Util\ThinkString())->uuid();
            $count = $param_value->where(array('param_value'=>$param_value->param_value,'param_id'=>$param_value->param_id,'status'=>array('neq',0)))->count();
            if($count) $this->oldAjaxReturn ('param_value', '配置参数值已经存在，请重新输入', 0);
            $category_info = D('ParamCategory')->where(array('param_category_id'=>$param_value->param_category_id))->find();
            $name_info = D('ParamName')->where(array('param_id'=>$param_value->param_id))->find();
            $param_value->category_name = $category_info['category_name'];
            $param_value->category_sort = $category_info['category_sort'];
            $param_value->param_name = $name_info['param_name'];
            $param_value->param_sort = $name_info['param_sort'];
            //状态默认是有效
            $param_value->status = I('status',1,'intval');
            $param_value->create_time = date('Y-m-d H:i:s');
            $param_value->update_time = date('Y-m-d H:i:s');
            $add_bool = $param_value->add();
            if(!$add_bool) $this->oldAjaxReturn(0,'添加失败',0);
            $this->oldAjaxReturn(0,'添加成功',1);
        }
    }
    
    public function get_update_param_value_page(){
        $param_value_id = I('param_value_id','','trim,htmlspecialchars');
        $info = D('ParamValue')->where(array('param_value_id'=>$param_value_id))->find();
        $this->assign('info', $info);
        $content = $this->fetch('update_param_value');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    public function update_param_value(){
        $param_value_id = I('param_value_id','','trim,htmlspecialchars');
        $param_id = I('param_id','','trim,htmlspecialchars');
        $data['param_value'] = I('param_value','','trim,htmlspecialchars');
        if(!$data['param_value']) $this->oldAjaxReturn ('param_value', '请输入配置参数值', 0);
        $param_value = D('ParamValue');
        $count = $param_value->where(array('param_value'=>$data['param_value'],'param_id'=>$param_id,'status'=>array('neq',0)))->count();
        if($count) $this->oldAjaxReturn ('param_name', '配置参数值已经存在，请重新输入', 0);
        $data['update_time'] = date('Y-m-d H:i:s');
        $update_bool = $param_value->where(array('param_value_id'=>$param_value_id))->data($data)->save();
        if(!$update_bool) $this->oldAjaxReturn(0, '修改失败', 0);
        else $this->oldAjaxReturn (0, '修改成功', 1);
    }
    
    public function delete_param_value(){
        $param_value_id = I('param_value_id','','trim,htmlspecialchars');
        //TODO 先不判断商品
        $del_bool = D('ParamValue')->where(array('param_value_id'=>$param_value_id))->data(array('status'=>0,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($del_bool) $this->oldAjaxReturn (0, '删除成功', 1);
        else $this->oldAjaxReturn (0, '删除失败', 0);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_where(){
        $where = array();
        $where['status'] = array('neq',0);
        $param_category_id = I('param_category_id','','trim,htmlspecialchars');
        if($param_category_id) $where['param_category_id'] = $param_category_id;
        return $where;
    }
}
