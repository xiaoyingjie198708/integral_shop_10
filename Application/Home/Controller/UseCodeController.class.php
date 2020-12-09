<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

class UseCodeController extends BaseController{
    
   public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(M('member_use_code')->where($where)->count(),10);
        $list = M('member_use_code')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $order_ids = getFieldsByKey($list, 'order_id');
        $order_cur_ids = D('Order')->where(array('order_id'=>array('in',$order_ids)))->getField('order_id,order_cur_id,member_id,shops_code',true);
        $order_goods_ids = getFieldsByKey($list, 'order_goods_id');
        $order_goods_names = M('order_goods_info')->where(array('order_goods_id'=>array('in',$order_goods_ids)))->getField('order_goods_id,goods_name,goods_price,integral,max_integral',true);
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_lists = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_name,goods_price_pc,integral,max_integral,shops_code',true);
        $member_ids = getFieldsByKey($list, 'member_id');
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        $shops_codes = getFieldsByKey($goods_lists, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        foreach($list as $k=>$code_info){
            if($code_info['order_goods_id']){
                $order_info = $order_cur_ids[$code_info['order_id']];
                $order_goods = $order_goods_names[$code_info['order_goods_id']];
            }else{
                 $order_goods = $goods_lists[$code_info['goods_code']];
                 $order_goods['goods_price'] = $order_goods['goods_price_pc'];
                 $order_info['order_cur_id'] = $code_info['order_id'];
            }
            $shops_code = $goods_lists[$code_info['goods_code']]['shops_code'];
            if($order_goods['integral']){
                if($order_goods['goods_price'] && ($order_goods['max_integral'] >0))  $goods_price = '￥'.$order_goods['goods_price'] .' + ' .$order_goods['max_integral'].'积分';
                if(!$order_goods['goods_price'] && $order_goods['max_integral']) $goods_price = $order_goods['max_integral'].'积分';
                if(($order_goods['goods_price']>0) && !$order_goods['max_integral']) $goods_price = '￥'.$order_goods['goods_price'];
            }else{
                $goods_price = '￥'.$order_goods['goods_price'];
            }
            $list[$k]['order_cur_id'] = $order_info['order_cur_id'];
            $list[$k]['goods_name'] = $goods_lists[$code_info['goods_code']]['goods_name'];
            $list[$k]['goods_price'] = $goods_price;
            $list[$k]['shops_name'] = $shops_names[$shops_code];
            $list[$k]['member_account'] = $member_accounts[$code_info['member_id']];
        }
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    //回访备注页面
    public function get_visit_comment(){
        $this->assign('use_id', I('use_id','','trim,htmlspecialchars'));
        $content = $this->fetch('comment');
        $this->oldAjaxReturn('', $content, 1);
    }
    
    //添加回访记录
    public function add_comment(){
        $visit_data['use_id'] = I('use_id','','trim,htmlspecialchars');
        $visit_data['content'] = I('visit_comment','','trim,htmlspecialchars');
        if(!$visit_data['content']) $this->oldAjaxReturn ('visit_comment', '请输入备注内容', 0);
        $visit_data['admin_uid'] = session('admin_uid');
        $visit_data['create_time'] = date('Y-m-d H:i:s');
        $visit_data['update_time'] = date('Y-m-d H:i:s');
        $add_bool = M('use_code_comment')->data($visit_data)->add();
        M('member_use_code')->where(array('use_id'=>$visit_data['use_id']))->data(array('is_visit'=>2,'update_time'=> date('Y-m-d H:i:s')))->save();
        if($add_bool) $this->oldAjaxReturn (0, '添加成功', 1);
        else $this->oldAjaxReturn (0, '添加失败', 0);
    }
    
    //回访备注页面
    public function get_comment_list(){
        $use_id = I('use_id','','trim,htmlspecialchars');
        $use_list = M('use_code_comment')->where(array('use_id'=>$use_id))->select();
        $admin_uids = getFieldsByKey($use_list, 'admin_uid');
        $user_arr = M('admin_user')->where(array('id'=>array('in',$admin_uids)))->getField('id,realname',true);
        foreach($use_list as $k=>$info){
            $info['realname'] = $user_arr[$info['admin_uid']];
            $use_list[$k] = $info;
        }
        $this->assign('use_list', $use_list);
        $this->assign('count', count($use_list));
        $content = $this->fetch('comment_list');
        $this->oldAjaxReturn('', $content, 1);
    }
    
    
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('UseCode/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'消费码',
                    'width'=>250,
                    'tip'=>'请输入消费码'
                ),
                array(
                    'name'=>'seatch_goods_code',
                    'show_name'=>'商品编码',
                    'width'=>250,
                    'tip'=>'请输入商品编码'
                ),
                array(
                    'name'=>'member_account',
                    'show_name'=>'会员账号',
                    'width'=>250,
                    'tip'=>'请输入会员账号'
                ),
                array(
                    'name' => 'search_status',
                    'show_name' => '使用状态',
                    'tip' => '请选择使用状态',
                    'select' => array(1=>'无效',10=>'未使用',20=>'已使用',30=>'冻结',40=>'已过期'),
                    'type' => 'select'
                ),
                array(
                    'name' => 'search_warn',
                    'type' => 'hidden'
                ),
            ),
            'other'=>array(
                array(
                    'name' => 'search_visit',
                    'show_name' => '回访状态',
                    'tip' => '请选择回访状态',
                    'select' => C('is_visit'),
                    'type' => 'select'
                ),
            )
        );
    }
    
    private function get_where(){
        $where = array();
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['use_code'] = $seatch;
        $search_status = I('search_status',0,'intval');
        if($search_status && $search_status != 1) $where['status'] = $search_status;
        if($search_status == 1) $where['status'] = 0;
        $member_account = I('member_account','','trim,htmlspecialchars');
        if($member_account){
            $member_ids = M('user_member_base_info')->where(array('member_account'=>array('like','%'.$member_account.'%')))->getField('member_id',true);
            $member_ids = $member_ids ? $member_ids : array();
            $where['member_id'] = array('in',$member_ids);
        }
        $seatch_goods_code = I('seatch_goods_code','','trim,htmlspecialchars');
        if($seatch_goods_code) $where['goods_code'] = $seatch_goods_code;
        $search_warn = I('search_warn',0,'intval');
        if($search_warn) $where['is_warn'] = $search_warn;
        $search_visit = I('search_visit',0,'intval');
        if($search_visit) $where['is_visit'] = $search_visit;
        //检查权限
        $shops_codes = $this->check_access_for_shops();
        if($shops_codes) $where['shops_code'] = array('in',$shops_codes);
        return $where;
    }
    
    //验证管理员操作商品的其他权限
    private function check_access_for_shops() {
        if(check_admin_access('show_use_code_all',1,'other')){ //验证是否可以查看所有
            return null;
        }elseif(check_admin_access('show_use_code_shops',1,'other')){ //验证是否可以查看本商家
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
}
