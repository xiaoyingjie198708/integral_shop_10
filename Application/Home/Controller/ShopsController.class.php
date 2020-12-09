<?php

namespace Home\Controller;

/**
 * 商铺管理
 *
 * @author xiao.yingjie
 */
class ShopsController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(M('shops_base_info')->where($where)->count(),10);
        $list = M('shops_base_info')->where($where)->limit($page->firstRow.','.$page->listRows)->order('create_time desc')->select();
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display(); 
    }
    
    public function add(){
        if(IS_AJAX){
            $shops = D('Shops');
            if(!$shops->create()) {
                $error = each($shops->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $shops_id = (new \Org\Util\ThinkString())->uuid();
                $shops->shops_id = $shops_id;
                $shops_name = $shops->shops_name;
                $count = $shops->where(array('shops_name'=>$shops_name,'shops_status'=>array('neq',0)))->count();
                if($count) $this->oldAjaxReturn ('shops_name', '商家名称已经存在，请重新输入', 0);
                $shops_code = $shops->order('shops_code desc')->getField('shops_code');
                if($shops_code) $shops_code += 1;
                else $shops_code = 100;
                $shops->shops_code = $shops_code;
                $shops->shops_status = 1;
                $shops->create_time = date('Y-m-d H:i:s');
                $shops->update_time = date('Y-m-d H:i:s');
                $shops->startTrans();
                $add_bool = $shops->add();
                $add_contact_bool = $this->add_contacts($shops_id);
                if($add_bool && $add_contact_bool){
                    $shops->commit();
                   $this->oldAjaxReturn ($shops_name, '添加成功', 1); 
                }else{
                    $shops->rollback();
                    $this->oldAjaxReturn(0, '添加失败', 0); 
                }
            }
        }
        $this->assign('shops_label', array('基本信息','联系人信息'));
        $this->display();
    }
    
    public function update(){
        $shops_code = I('shops_code','','trim,htmlspecialchars');
        $shops = D('Shops');
        if(IS_AJAX){
            $shops_id = I('shops_id','','trim,htmlspecialchars');
            if(!$shops->create()) {
                $error = each($shops->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $shops_name = $shops->shops_name;
                $count = $shops->where(array('shops_name'=>$shops_name,'shops_status'=>array('neq',0),'shops_id'=>array('neq',$shops_id)))->count();
                if($count) $this->oldAjaxReturn ('shops_name', '商家名称已经存在，请重新输入', 0);
                $shops->update_time = date('Y-m-d H:i:s');
                $shops->startTrans();
                $update_bool = $shops->where(array('shops_id'=>$shops_id))->save();
                $del_contact_bool = M('shops_contact_info')->where(array('shops_id'=>$shops_id))->delete();
                $add_contact_bool = $this->add_contacts($shops_id);
                if($update_bool && $add_contact_bool){
                    $shops->commit();
                   $this->oldAjaxReturn ($shops_name, '更新成功', 1); 
                }else{
                    $shops->rollback();
                    $this->oldAjaxReturn(0, '更新失败', 0); 
                }
            } 
        }
        $info = $shops->where(array('shops_code'=>$shops_code))->find();
        $this->assign('info', $info);
        $this->assign('contact_list', M('shops_contact_info')->where(array('shops_id'=>$info['shops_id']))->select());
        $this->assign('shops_label', array('基本信息','联系人信息'));
        $this->display();
    }
    
    public function delete(){
        $shops_code = I('shops_code','','trim');
//        $goods_count = M('goods_base_info_edit')->where(array('shops_code'=>$shops_code,'goods_status'=>array('neq',0)))->count();
//        if($goods_count) $this->oldAjaxReturn (0, '请先解除与商品的关联', 0);
//        $goods_count = M('goods_base_info')->where(array('shops_code'=>$shops_code,'goods_status'=>array('neq',0)))->count();
//        if($goods_count) $this->oldAjaxReturn (0, '请先解除与商品的关联', 0);
        $del_bool = M('shops_base_info')->where(array('shops_code'=>$shops_code))->data(array('shops_status'=>0,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($del_bool === FALSE) $this->oldAjaxReturn (0, '删除失败', 0);
        $this->oldAjaxReturn(0, '删除成功', 1);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('Shops/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'商家名称',
                    'width'=>250,
                    'tip'=>'请输入商家名称'
                )
            ),
        );
    }
    
    private function get_where(){
        $where = array();
        $where['shops_status'] = array('neq',0);
        $shops_id = I('shops_id','','trim,htmlspecialchars');
        if($shops_id) $where['shops_id'] = $shops_id;
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['shops_name'] = array('like','%'.$seatch.'%');
        return $where;
    }
    
    //添加联系人
    private function add_contacts($shops_id){
        $shops_contacts = I('shops_contacts');
        $contact_save_data = array();
        $contact_bool = true;
        if($shops_contacts && is_array($shops_contacts)) {
            for($i=0;$i<count($shops_contacts);$i++) {
                $contact_data = explode('&',htmlspecialchars_decode($shops_contacts[$i]));
                for($j=0;$j<count($contact_data);$j++) {
                    $temp = explode('=',$contact_data[$j]);
                    $contact_save_data[$i][$temp[0]] = urldecode($temp[1]);
                }
                $contact_save_data[$i]['contact_id'] = (new \Org\Util\ThinkString())->uuid();
                $contact_save_data[$i]['create_time'] = date('Y-m-d H:i:s');
                $contact_save_data[$i]['update_time'] = date('Y-m-d H:i:s');
                $contact_save_data[$i]['shops_id'] = $shops_id;
            }
            $contact_bool = M('shops_contact_info')->addAll($contact_save_data);
        }
        return $contact_bool;
    }
}
