<?php

namespace Api\Controller;

/**
 *优惠券使用类
 * @author xiao.yingjie
 */
class CouponController extends BaseController{
    
    public function query_count(){
        $member_id = I('memberId','','trim,htmlspecialchars');
        if(!$member_id) exit(json_encode(array('code'=>0,'count'=>0,'msg'=>'memberId is null')));
        $order_where = array('member_id'=>$member_id);
        $order_where['create_time'] = array('gt', date('Y').'-01-01 00:00:00');
        $order_ids = M('order_base_info')->where($order_where)->getField('order_id',true);
        $where = array(
            'b.coupon_status'=>20,//已使用
            'a.order_id'=>array('in',$order_ids)
        );
        $count = M('order_pay_discount_info as a,tm_coupon_code_info as b')->where('a.discount_code = b.coupon_code')->where($where)->count();
        echo json_encode(array('code'=>1,'count'=>$count,'msg'=>'success'));
    }
}
