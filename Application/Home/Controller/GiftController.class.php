<?php

namespace Home\Controller;

class GiftController extends BaseController{
    
     //赠品审核列表
    public function index(){
        $mod = D('PromotionActivity');
        $where = $this->get_where();
        $page = new \Org\My\Page($mod->where($where)->count(),10);
        $list = $mod->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        foreach($list as $k=>$info){
            $bundles_goods = M('promotion_bundles_goods_info')->where(array('activity_id'=>$info['activity_id']))->select();
            $main_goods_code = array();
            $promote_goods_code = array();
            foreach($bundles_goods as $goods_info){
                if($info['activity_id'] == $goods_info['activity_id']){
                    if($goods_info['type'] == 1){
                        $main_goods_code[] = $goods_info['goods_code'];
                    }elseif($goods_info['type'] == 2){
                        $promote_goods_code[] = $goods_info['goods_code'].'(商品)';
                    }else{
                        $promote_goods_code[] = $goods_info['goods_code'].'(券)';
                    }
                }
            }
            $list[$k]['main_goods_code'] = implode(',', $main_goods_code);
            $list[$k]['promote_goods_code'] = implode(',', $promote_goods_code);
            if($info['start_time']) $list[$k]['start_time'] = date('Y-m-d H:i:s',  strtotime($info['start_time']));
            if($info['end_time']) $list[$k]['end_time'] = date('Y-m-d H:i:s',  strtotime($info['end_time']));
        }
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    //查看详情
    public function info(){
        $mod = D('PromotionActivity');
        $activity_id = I('activity_id','','trim,htmlspecialchars');
        $activity_info = $mod->where(array('activity_id'=>$activity_id))->find();
        $bundles_goods_list = M('promotion_bundles_goods_info')->where(array('activity_id'=>$activity_id))->select();
        $main_goods_arr = array();
        foreach($bundles_goods_list as $k=>$bundles_goods){
            if($bundles_goods['type'] == 1){
                $goods_info = M('goods_base_info')->where(array('goods_id'=>$bundles_goods['goods_id']))->find();
                $goods_info['count'] = $bundles_goods['count'];
                $goods_info['shops_name'] = M('shops_base_info')->where(array('shops_code'=>$goods_info['shops_code']))->getField('shops_name');
                $main_goods_arr[] = $goods_info;
            }elseif($bundles_goods['type'] == 2){
                $goods_info = M('goods_base_info')->where(array('goods_id'=>$bundles_goods['goods_id']))->find();
                $goods_info['count'] = $bundles_goods['count'];
                $goods_info['shops_name'] = M('shops_base_info')->where(array('shops_code'=>$goods_info['shops_code']))->getField('shops_name');
                $promite_goods_arr[] = $goods_info;
            }else{
                $coupon_info = M('coupon_base_info')->where(array('coupon_id'=>$bundles_goods['goods_id']))->find();
                $coupon_info['count'] = $bundles_goods['count'];
                $coupon_arr[] = $coupon_info; 
            }
        }
        if($coupon_arr) $send_coupon = 1;
        else $send_coupon  = 0;
        $this->assign('info', $activity_info);
        $this->assign('mian_goods', $main_goods_arr);
        $this->assign('promote_goods', $promite_goods_arr);
        $this->assign('coupon_list', $coupon_arr);
        $this->assign('send_coupon', $send_coupon);
        $this->display('GiftEdit/info');
    }
    
    //修改状态
    public function change_status(){
      $mod = D('PromotionActivity');
      $activity_ids = I('activity_ids','','trim,htmlspecialchars');
      $activity_ids = explode(',', trim($activity_ids,','));
      $enable_status = I('enable_status',0,'intval');
      $update_bool = $mod->where(array('activity_id'=>array('in',$activity_ids)))->data(array('enable_status'=>$enable_status,'update_time'=>  date('Y-m-d H:i:s')))->save();
      if($update_bool) $this->oldAjaxReturn (0, '更新状态成功', 1);
      else $this->oldAjaxReturn (0, '更新状态失败', 0);
    }
    
    //修改
    public function edit(){
        $activity_id = I('activity_id','','trim,htmlspecialchars');
        $update_bool = D('PromotionActivityEdit')->where(array('formal_activity_id'=>$activity_id))->data(array('status'=>10,'update_time'=>date('Y-m-d H:i:s')))->save();
        if(!$update_bool) $this->oldAjaxReturn (0, '修改请求失败', 0);
        $this->oldAjaxReturn(0, '修改请求成功', 1);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('Gift/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'活动名称',
                    'width'=>250,
                    'tip'=>'请输入活动名称'
                ),
                array(
                    'name'=>'main_goods_code',
                    'show_name'=>'主商品编码',
                    'width'=>250,
                    'tip'=>'请输入主商品编码'
                )
            ),
        );
    }
    
    private function get_where(){
        $where = array();
        //编辑和拒绝状态
        $where['status'] = 21;
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['activity_name'] = array('like','%'.$seatch.'%');
        $main_goods_code = I('main_goods_code','','trim,htmlspecialchars');
        if($main_goods_code){
            $activity_ids = M('promotion_bundles_goods_info')->where(array('goods_code'=>array('like','%'.$main_goods_code.'%'),'type'=>1))->getField('activity_id',true);
            $where['activity_id'] = array('in',$activity_ids);
        }
        return $where;
    }
}
