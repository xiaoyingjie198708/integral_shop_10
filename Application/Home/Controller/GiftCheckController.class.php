<?php


namespace Home\Controller;

class GiftCheckController extends BaseController{
    
    //赠品审核列表
    public function index(){
        $mod = D('PromotionActivityCheck');
        $where = $this->get_where();
        $page = new \Org\My\Page($mod->where($where)->count(),10);
        $list = $mod->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $activity_ids = getFieldsByKey($list, 'activity_id');
        foreach($list as $k=>$info){
            $bundles_goods = M('promotion_bundles_goods_info_check')->where(array('activity_id'=>$info['activity_id']))->select();
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
    
    //审核通过
    public function check_success(){
        $activity_ids = I('activity_ids','','trim,htmlspecialchars');
        $activity_ids = explode(',', trim($activity_ids,','));
        $check_mod = D('PromotionActivityCheck');
        $mod = D('PromotionActivity');
        $where['activity_id'] = array('in',$activity_ids);
        $check_activity_list = $check_mod->where($where)->select();
        $mod->startTrans();
        foreach($check_activity_list as $k=>$check_activity_info){
            if($check_activity_info['status'] != 20) {
                $errors[] = '赠品活动：'.$check_activity_info['activity_name'].',不是待审核状态，不能被审核通过';
                break;
            }
            $formal_activity_id = $check_activity_info['formal_activity_id'];
            $exist_activity = $mod->where(array('activity_id'=>$formal_activity_id))->find();
            $activity_base_info = $check_activity_info;
            //没有的项
            unset($activity_base_info['formal_activity_id']);
            unset($activity_base_info['edit_activity_id']);
            unset($activity_base_info['admin_id']);
            unset($activity_base_info['check_time']);
            unset($activity_base_info['refuse_reason']);
            
            $activity_base_info['activity_id'] = $formal_activity_id;
            //审核通过
            $activity_base_info['status'] = 21;
            $activity_base_info['update_time'] = date('Y-m-d H:i:s');
            if($exist_activity){
                $activity_bool = $mod->where(array('activity_id'=>$formal_activity_id))->data($activity_base_info)->save();
            }else{
                $activity_bool = $mod->data($activity_base_info)->add();
            }
            //赠品关联主品，赠品，优惠券
            M('promotion_bundles_goods_info')->where(array('activity_id'=>$formal_activity_id))->delete();
            $check_bundles_list = M('promotion_bundles_goods_info_check')->where(array('activity_id'=>$check_activity_info['activity_id']))->select();
            $bundles_list = array();
            foreach($check_bundles_list as $j=>$check_bundles_info){
                unset($check_bundles_info['id']);
                $check_bundles_info['activity_id'] = $formal_activity_id;
                $bundles_list[] = $check_bundles_info;
            }
            $bundles_bool = M('promotion_bundles_goods_info')->addAll($bundles_list);
            $update_check_activity_data = array(
                'status'=>21,
                'admin_id'=>  session('admin_uid'),
                'update_time'=>  date('Y-m-d H:i:s'),
                'check_time'=>  date('Y-m-d H:i:s'),
            );
            $check_activity_bool = $check_mod->where(array('activity_id'=>$check_activity_info['activity_id']))->data($update_check_activity_data)->save();
            if(!($activity_bool && $bundles_bool && $check_activity_bool)) $errors[] = '赠品活动：'.$check_activity_info['activity_name'].',审核失败';
        }
        if(empty($errors)){
            $mod->commit();
            $this->oldAjaxReturn(0, '审核成功', 1);
        }else{
            $mod->rollback();
            $this->oldAjaxReturn(0, implode('; ', $errors), 0);
        }
    }
    
    //查看详情
    public function info(){
        $mod = D('PromotionActivityCheck');
        $activity_id = I('activity_id','','trim,htmlspecialchars');
        $activity_info = $mod->where(array('activity_id'=>$activity_id))->find();
        $bundles_goods_list = M('promotion_bundles_goods_info_check')->where(array('activity_id'=>$activity_id))->select();
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
    
    public function get_check_fail(){
        $activity_ids = I('activity_ids','','trim,htmlspecialchars');
        $this->assign('activity_ids', $activity_ids);
        $content = $this->fetch('fail_info');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    //审核拒绝
    public function check_fail(){
        $activity_ids = I('activity_ids','','trim,htmlspecialchars');
        $activity_ids = explode(',', trim($activity_ids,','));
        if(!$activity_ids) $this->oldAjaxReturn (0, '参数错误', 0);
        $refuse_reason = I('refuse_reason','','trim,htmlspecialchars');
        if(!$refuse_reason) $this->oldAjaxReturn ('refuse_reason', '请输入拒绝原因', 0);
        $mod = D('PromotionActivityCheck');
        $where['activity_id'] = array('in',$activity_ids);
        $check_activity_list = $mod->where($where)->select();
        foreach($check_activity_list as $k=>$check_activity_info){
            if($check_activity_info['status'] != 20) {
                $errors[] = '赠品活动：'.$check_activity_info['activity_name'].',不是待审核状态，不能被审核通过';
                break;
            }
        }
        $mod->startTrans();
        $check_activity_data['admin_id'] = session('admin_uid');
        $check_activity_data['refuse_reason'] = $refuse_reason;
        $check_activity_data['status'] = 22;
        $check_activity_data['update_time'] = date('Y-m-d H:i:s');
        $check_activity_data['check_time'] = date('Y-m-d H:i:s');
        $check_activity_bool = $mod->where($where)->data($check_activity_data)->save();
        $edit_activity_data['refuse_reason'] = $refuse_reason;
        $edit_activity_data['update_time'] = date('Y-m-d H:i:s');
        $edit_activity_data['status'] = 10;
        $activity_ids = getFieldsByKey($check_activity_list, 'edit_activity_id');
        $where['activity_id'] = array('in',$activity_ids);
        $edit_activity_bool = D('PromotionActivityEdit')->where($where)->data($edit_activity_data)->save();
        if(empty($errors) && $check_activity_bool && $edit_activity_bool){
            $mod->commit();
            $this->oldAjaxReturn(0, '提交成功', 1);
        }else{
            $mod->rollback();
            $this->oldAjaxReturn(0, implode('; ', $errors), 0);
        }
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('GiftCheck/index'),
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
        $where['status'] = array('in',array(20,21,22));
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['activity_name'] = array('like','%'.$seatch.'%');
        $main_goods_code = I('main_goods_code','','trim,htmlspecialchars');
        if($main_goods_code){
            $activity_ids = M('promotion_bundles_goods_info_check')->where(array('goods_code'=>array('like','%'.$main_goods_code.'%'),'type'=>1))->getField('activity_id',true);
            $where['activity_id'] = array('in',$activity_ids);
        }
        return $where;
    }
}
