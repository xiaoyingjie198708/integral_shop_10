<?php

namespace Home\Controller;

/**
 *赠品编辑管理
 * @author xiao.yingjie
 */
class GiftEditController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('PromotionActivityEdit')->where($where)->count(),10);
        $list = D('PromotionActivityEdit')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $activity_ids = getFieldsByKey($list, 'activity_id');
        foreach($list as $k=>$info){
            $bundles_goods = M('promotion_bundles_goods_info_edit')->where(array('activity_id'=>$info['activity_id']))->select();
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
    
    public function add(){
        if(IS_AJAX){
            $edit_pro_activity = D('PromotionActivityEdit');
            if(!$edit_pro_activity->create()) {
                $error = each($edit_pro_activity->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $activity_id = (new \Org\Util\ThinkString())->uuid();
                $edit_pro_activity->activity_id = $activity_id;
                $edit_pro_activity->formal_activity_id = $activity_id;
                $activity_name = $edit_pro_activity->activity_name;
                $exist_activity = $edit_pro_activity->where(array('promotion_type'=>1,'activity_name'=>$activity_name,'status'=>array('neq',0)))->find();
                if($exist_activity) $this->oldAjaxReturn ('activity_name', '活动已经存在,请重新输入活动名称', 0);
                if(strtotime($edit_pro_activity->start_time) >= strtotime($edit_pro_activity->end_time)) $this->oldAjaxReturn ('end_time', '有效期结束时间必须大于开始时间', 0);
                $edit_pro_activity->promotion_type = 1;
                $edit_pro_activity->status = 10;
                $edit_pro_activity->create_time = date('Y-m-d H:i:s');
                $edit_pro_activity->update_time = date('Y-m-d H:i:s');
                $edit_pro_activity->startTrans();
                $other_bool = $this->promotion_bundles_goods($activity_id);
                $pro_activity_bool = $edit_pro_activity->add();
                if($other_bool && $pro_activity_bool){
                    $edit_pro_activity->commit();
                    $this->oldAjaxReturn($activity_name, '添加成功', 1);
                }else{
                    $edit_pro_activity->rollback();
                    $this->oldAjaxReturn(0, '添加失败', 0);
                }
            }
        }
        $this->display();
    }
    
    public function update(){
        $activity_id = I('activity_id','','trim,htmlspecialchars');
        if(IS_AJAX){
            $edit_pro_activity = D('PromotionActivityEdit');
            if(!$edit_pro_activity->create()) {
                $error = each($edit_pro_activity->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $activity_name = $edit_pro_activity->activity_name;
                $exist_activity = $edit_pro_activity->where(array('promotion_type'=>1,'activity_name'=>$activity_name,'status'=>array('neq',0),'activity_id'=>array('neq',$activity_id)))->find();
                if($exist_activity) $this->oldAjaxReturn ('activity_name', '活动已经存在,请重新输入活动名称', 0);
                if(strtotime($edit_pro_activity->start_time) >= strtotime($edit_pro_activity->end_time)) $this->oldAjaxReturn ('end_time', '有效期结束时间必须大于开始时间', 0);
                $edit_pro_activity->promotion_type = 1;
                $edit_pro_activity->status = 10;
                $edit_pro_activity->is_wap = I('is_wap',0,'intval');
                $edit_pro_activity->is_pc = I('is_pc',0,'intval');
                $edit_pro_activity->update_time = date('Y-m-d H:i:s');
                $edit_pro_activity->startTrans();
                M('promotion_bundles_goods_info_edit')->where(array('activity_id'=>$activity_id))->delete();
                $other_bool = $this->promotion_bundles_goods($activity_id);
                $pro_activity_bool = $edit_pro_activity->where(array('activity_id'=>$activity_id))->save();
                if($other_bool && $pro_activity_bool){
                    $edit_pro_activity->commit();
                    $this->oldAjaxReturn($activity_name, '修改成功', 1);
                }else{
                    $edit_pro_activity->rollback();
                    $this->oldAjaxReturn(0, '修改失败', 0);
                }
            }
        }
        $activity_info = M('promotion_bundles_activity_info_edit')->where(array('activity_id'=>$activity_id))->find();
        $bundles_goods_list = M('promotion_bundles_goods_info_edit')->where(array('activity_id'=>$activity_id))->select();
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
        $this->display();
    }
    //查看详情
    public function info(){
        $activity_id = I('activity_id','','trim,htmlspecialchars');
        $activity_info = M('promotion_bundles_activity_info_edit')->where(array('activity_id'=>$activity_id))->find();
        $bundles_goods_list = M('promotion_bundles_goods_info_edit')->where(array('activity_id'=>$activity_id))->select();
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
        $this->display();
    }
    
    //提交申请
    public function submit_check(){
        $activity_ids = I('activity_ids','','trim,htmlspecialchars');
        $activity_ids = explode(',', trim($activity_ids,','));
        $mod = D('PromotionActivityEdit');
        //活动信息
        $where['activity_id'] = array('in',$activity_ids);
        $where['status'] = array('in','10,22');
        $activity_list = $mod->where($where)->select();
        if(!$activity_list) $this->oldAjaxReturn (0, '请选择要提交审核的赠品活动', 0);
        
        foreach($activity_list as $k=>$activity_info){
            $activity_check = $activity_info;
            $new_activity_id = (new \Org\Util\ThinkString())->uuid();
            $activity_check['activity_id'] = $new_activity_id;
            $activity_check['edit_activity_id'] = $activity_info['activity_id'];
            $activity_check['status'] = 20;
            $activity_check['update_time'] = date("Y-m-d H:i:s");
            unset($activity_check['refuse_reason']);
            $activity_check_data[] = $activity_check;
            $bundles_goods_list = M('promotion_bundles_goods_info_edit')->where(array('activity_id'=>$activity_info['activity_id']))->select();
            foreach($bundles_goods_list as $kk=>$bundles_goods){
                $bundles_goods_check = $bundles_goods;
                unset($bundles_goods_check['id']);
                $bundles_goods_check['activity_id'] = $new_activity_id;
                $bundles_goods_check_data[] = $bundles_goods_check;
            }
        }
        $mod->startTrans();
        $update_bool = $mod->where($where)->data(array('status'=>20,'update_time'=>  date('Y-m-d H:i:s')))->save();
        $check_bool = D('PromotionActivityCheck')->addAll($activity_check_data);
        $check_bundles_bool = M('promotion_bundles_goods_info_check')->addAll($bundles_goods_check_data);
        if($update_bool && $check_bool && $check_bundles_bool){
            $mod->commit();
            $this->oldAjaxReturn(0, '提交成功', 1);
        }else{
            $mod->rollback();
            $this->oldAjaxReturn(0, '提交失败', 0);
        }
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('GiftEdit/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'活动名称',
                    'width'=>250,
                    'tip'=>'请输入活动名称'
                )
            ),
        );
    }
    
    private function get_where(){
        $where = array();
        //编辑和拒绝状态
        $where['status'] = array('in',array(10,22));
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['activity_name'] = array('like','%'.$seatch.'%');
        return $where;
    }
    
    //添加促品
    private function promotion_bundles_goods($activity_id){
        $main_goods = I('goods_buy_num','','trim,htmlspecialchars');
        $promote_goods = I('goods_promote_num','','trim,htmlspecialchars');
        $coupon_codes = I('coupon_codes','','trim,htmlspecialchars');
        foreach($main_goods as $k=>$goods_str){
            $goods_str = explode(',', trim($goods_str,','));
            $goods_info = D('GoodsBase')->where(array('goods_code'=>$goods_str[0]))->find();
            $count = $goods_str[1];
            if(intval($count) < 1) $this->oldAjaxReturn (0, $goods_info['goods_name'].'请输入正确的购买数量', 0);
            //主商品只能参加一个促销活动
            $bundles_where = array('goods_id'=>$goods_info['goods_id'],'type'=>1);
            $exist_activity = M('promotion_bundles_goods_info_edit')->where($bundles_where)->find();
            if($exist_activity){
                $activity_info = M('promotion_bundles_activity_info_edit')->where(array('activity_id'=>$exist_activity['activity_id']))->find();
                $this->oldAjaxReturn(0, '商品['.$goods_info['goods_name'].']已经参加了活动['.$activity_info['activity_name'].'],主商品只能参加一个赠品促销活动', 0);
            }
            //去重复
            $goods_ids = getFieldsByKey($main_bundles_data, 'goods_id');
            if(in_array($goods_info['goods_id'], $goods_ids)){
                continue;
            }
            $main_bundles_data[] = array(
                'activity_id'=>$activity_id,
                'goods_id'=>$goods_info['goods_id'],
                'goods_code'=>$goods_info['goods_code'],
                'type'=>1,
                'count'=>$count,
                'create_time'=>  date('Y-m-d H:i:s'),
                'update_time'=> date('Y-m-d H:i:s')
            );
        }
        foreach($promote_goods as $k=>$goods_str){
            $goods_str = explode(',', trim($goods_str,','));
            $goods_info = D('GoodsBase')->where(array('goods_code'=>$goods_str[0]))->find();
            $count = $goods_str[1];
            if(intval($count) < 1) $this->oldAjaxReturn (0, $goods_info['goods_name'].'请输入正确的赠送数量', 0);
             //去重复
            $goods_ids = getFieldsByKey($promote_bundles_data, 'goods_id');
            if(in_array($goods_info['goods_id'], $goods_ids)){
                continue;
            }
            $promote_bundles_data[] = array(
                'activity_id'=>$activity_id,
                'goods_id'=>$goods_info['goods_id'],
                'goods_code'=>$goods_info['goods_code'],
                'type'=>2,
                'count'=>$count,
                'create_time'=>  date('Y-m-d H:i:s'),
                'update_time'=> date('Y-m-d H:i:s')
            );
        }
        //去重复
        $coupon_codes = array_unique($coupon_codes);
        foreach($coupon_codes as $k=>$coupon_code){
            $coupon_info = D('Coupon')->where(array('coupon_code'=>$coupon_code))->find();
            $count = 1;
            $promote_bundles_data[] = array(
                'activity_id'=>$activity_id,
                'goods_id'=>$coupon_info['coupon_id'],
                'goods_code'=>$coupon_info['coupon_code'],
                'type'=>3,
                'count'=>$count,
                'create_time'=>  date('Y-m-d H:i:s'),
                'update_time'=> date('Y-m-d H:i:s')
            );
        }
       $main_bundles_bool = M('promotion_bundles_goods_info_edit')->addAll($main_bundles_data);
       $promote_bundles_bool =  M('promotion_bundles_goods_info_edit')->addAll($promote_bundles_data);
       return $main_bundles_bool && $promote_bundles_bool;
    }
}
