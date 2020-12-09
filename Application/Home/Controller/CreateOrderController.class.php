<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

/**
 *临时测试订单
 * @author xiao.yingjie
 */
class CreateOrderController extends BaseController{
    
    public function index(){
        $this->display();
    }
    
    public function add(){
        $goods_code = I('goods_code','','trim,htmlspecialchars');
        $coupon_code = I('coupon_code','','trim,htmlspecialchars');
        $integral = I('integral','','trim,htmlspecialchars');
        $pay_status = I('pay_status',0,'intval');
        $goods_number = I('goods_number',1,'intval');
        $name = I('name','','trim,htmlspecialchars');
        $province = I('province','','trim,htmlspecialchars');
        $city = I('city','','trim,htmlspecialchars');
        $county = I('county','','trim,htmlspecialchars');
        $address = I('address','','trim,htmlspecialchars');
        $mobile = I('mobile','','trim,htmlspecialchars');
        $user_remark = I('user_remark','','trim,htmlspecialchars');
        $address = I('address','','trim,htmlspecialchars');
        $is_invoice = I('is_invoice',0,'intval');
        $invoice_title = I('invoice_title','','trim,htmlspecialchars');
        $pay_status = I('pay_status',0,'intval');
        $email = I('email','','trim,htmlspecialchars');
        $member_id = I('member_id','','trim,htmlspecialchars');
        $goods_info = D('GoodsBase')->where(array('goods_code'=>$goods_code))->find();
        $mod = D('Order');
        $order_id = (new \Org\Util\ThinkString())->uuid();
        $order_cur_id = createOrderId();
        $total_value = $goods_info['goods_price_pc'] * $goods_number;
        $order_data = array(
            'order_id'=>$order_id,
            'order_cur_id'=>$order_cur_id,
            'member_id'=>$member_id ? $member_id : 'N100001',
            'total_value'=>$total_value,
            'name'=>$name,
            'province'=>$province,
            'city'=>$city,
            'county'=>$county,
            'address'=>$address,
            'mobile'=>$mobile,
            'update_time'=>date('Y-m-d H:i:s'),
            'create_time'=>date('Y-m-d H:i:s'),
            'is_invoice'=>$is_invoice,
            'pay_status'=>$pay_status,
            'user_remark'=>$user_remark,
            'email'=>$email,
            'shops_code'=>$goods_info['shops_code']
        );
        $order_goods_id = (new \Org\Util\ThinkString())->uuid();
        $order_goods_data = array(
            'primary_order_goods_id'=>'',
            'order_goods_id'=>$order_goods_id,
            'order_id'=>$order_id,
            'goods_code'=>$goods_info['goods_code'],
            'goods_category_code'=>$goods_info['goods_category_code'],
            'goods_type_code'=>$goods_info['goods_type_code'],
            'goods_name'=>$goods_info['goods_name'],
            'goods_materiel_code'=>$goods_info['goods_materiel_code'],
            'goods_extern_code'=>$goods_info['ext_code'],
            'goods_price'=>$goods_info['goods_price_pc'],
            'promotion_content'=>$goods_info['promotion_content_pc'],
            'create_time'=>  date('Y-m-d H:i:s'),
            'goods_number'=>$goods_number,
            'shops_code'=>$goods_info['shops_code'],
            'update_time'=>  date('Y-m-d H:i:s')
        );
        $mod->startTrans();
        if($coupon_code){
            $coupon_mod = D('Coupon');
            $coupon_info = $coupon_mod->where(array('coupon_code'=>$coupon_code))->find();
            $discount_value = $coupon_info['coupon_value'];
            $pay_discount_data[] = array(
                'order_id'=>$order_id,
                'discount_type'=>3,
                'discount_code'=>$coupon_code,
                'discount_value'=>floatval($coupon_info['coupon_value']),
                'create_time'=>date('Y-m-d H:i:s')
            );
            $order_goods_discount_data[] = array(
                'order_id'=>$order_id,
                'order_goods_id'=>$order_goods_id,
                'discount_type'=>3,
                'discount_code'=>$coupon_code,
                'discount_value'=>floatval($coupon_info['coupon_value']),
                'create_time'=>date('Y-m-d H:i:s')
            );
            $goods_discount_list[] = array(
                'order_goods_id'=>$order_goods_id,
                'order_id'=>$order_id,
                'discount_type'=>2,
                'discount_code'=>$coupon_code,
                'discount_value'=>$discount_value,
                'create_time'=>date('Y-m-d H:i:s')
            );
            $payment_serial_number = (new \Org\Util\ThinkString())->uuid();
            $pay_list[] = array(
               'payment_serial_number'=>$payment_serial_number,
               'order_id'=>$order_id,
                'payment_type'=>'02010000',
                'payment_fee'=>$discount_value,
                'create_time'=>date('Y-m-d H:i:s')
            );
        }
        if($integral){
            $integral_value = $integral/100;
            $discount_value += $integral/100;
            $pay_discount_data[] = array(
                'order_id'=>$order_id,
                'discount_type'=>5,
                'discount_code'=>$integral,
                'discount_value'=>$integral_value,
                'create_time'=>date('Y-m-d H:i:s')
            );
            $order_goods_discount_data[] = array(
                'order_id'=>$order_id,
                'order_goods_id'=>$order_goods_id,
                'discount_type'=>5,
                'discount_code'=>$integral,
                'discount_value'=>$integral_value,
                'create_time'=>date('Y-m-d H:i:s')
            );
            $payment_serial_number = (new \Org\Util\ThinkString())->uuid();
            $pay_list[] = array(
               'payment_serial_number'=>$payment_serial_number,
               'order_id'=>$order_id,
                'payment_type'=>'02030000',
                'payment_fee'=>$integral_value,
                'create_time'=>date('Y-m-d H:i:s')
            );
        }
        $pay_value = $total_value - $discount_value;
        $order_data['pay_total_cash'] = $pay_value;
        if($pay_status == 12){
            $payment_serial_number = (new \Org\Util\ThinkString())->uuid();
            $payment_serial_data = array(
                'order_id'=>$order_id,
                'payment_serial_number'=>$payment_serial_number,
                'create_time'=>date('Y-m-d H:i:s')
            );
            $payment_fee = $total_value - $discount_value;
            $order_payment_data = array(
                'payment_serial_number'=>$payment_serial_number,
                'order_id'=>$order_id,
                'payment_trade_no'=>(new \Org\Util\ThinkString())->uuid(),
                'payment_notify_time'=>date('Y-m-d H:i:s'),
                'payment_fee'=>$payment_fee,
                'create_time'=>date('Y-m-d H:i:s')
            );
            $order_data['pay_value'] = $pay_value;
            $pay_list[] = array(
               'payment_serial_number'=>$payment_serial_number,
               'order_id'=>$order_id,
                'payment_type'=>'01010302',
                'payment_fee'=>$payment_fee,
                'create_time'=>date('Y-m-d H:i:s')
            );
        }
        if($is_invoice){
            $invoice_data = array(
                'order_id'=>$order_id,
                'invoice_title'=>$invoice_title,
                'create_time'=>date('Y-m-d H:i:s'),
                'update_time'=>date('Y-m-d H:i:s'),
                'open_time'=>'',
            );
        }
        $order_bool = $mod->data($order_data)->add();
        $order_goods_bool = M('order_goods_info')->data($order_goods_data)->add();
        $pay_dis_bool = true;
        if($pay_discount_data) $pay_dis_bool = M('order_pay_discount_info')->addAll($pay_discount_data);
        $order_goods_dis_bool = true;
        if($order_goods_discount_data) $order_goods_dis_bool = M('order_goods_discount_info')->addAll($order_goods_discount_data);
        $serial_bool = true;
        if($payment_serial_data) $serial_bool = M('order_payment_serial_number_info')->data($payment_serial_data)->add();
        $order_pay_bool = true;
        if($order_payment_data) $order_pay_bool = M('order_payment_info')->data($order_payment_data)->add();
        $invoice_bool = true;
        if($invoice_data) $invoice_bool = M('order_invoice_info')->data($invoice_data)->add();
        //订单基本信息同步
        $this->sync_crm_order_info($order_data);
//        //订单商品数据同步
        $this->sync_crm_order_goods(array($order_goods_data));
//        //同步订单支付信息
        if($pay_list) $this->sync_crm_order_pay ($pay_list);
//        //同步订单商品优惠信息
        if($goods_discount_list) $this->sync_order_goods_discount ($goods_discount_list);
        
        if($order_bool && $order_goods_bool && $pay_dis_bool && $order_goods_dis_bool && $serial_bool && $order_pay_bool && $invoice_bool){
            $mod->commit();
            $this->oldAjaxReturn(0, '成功', 1);
        }else{
            $mod->rollback();
            $this->oldAjaxReturn(0, '失败', 0);
        }
    }
    
    //同步订单到CRM
    private function sync_crm_order_info($base_order_info){
        $crm_order_data['order_id'] = $base_order_info['order_id'];
        $crm_order_data['order_cur_id'] = $base_order_info['order_cur_id'];
        $crm_order_data['member_id'] = $base_order_info['member_id'];
        $crm_order_data['order_status'] = 1;
        $crm_order_data['pay_status'] = $base_order_info['pay_status'];
        $crm_order_data['order_type'] = 1;
        $crm_order_data['order_way'] = 1;
        $crm_order_data['total_value'] = $base_order_info['total_value'];
        $crm_order_data['pay_value'] = $base_order_info['pay_value'];
        $crm_order_data['pay_total_cash'] = $base_order_info['pay_total_cash'];
        $crm_order_data['create_time'] = $base_order_info['create_time'];
        $crm_order_data['update_time'] = $base_order_info['update_time'];
        $add_bool =  M('crm_order_base_info')->data($crm_order_data)->add();
        return $add_bool;
    }
    
    //同步订单商品到CRM
    private function sync_crm_order_goods($order_goods_list){
        foreach($order_goods_list as $k=>$order_goods){
            $crm_order_goods_list[] = array(
                'order_goods_id'=>$order_goods['order_goods_id'],
                'order_id'=>$order_goods['order_id'],
                'order_goods_type'=>1,
                'goods_code'=>$order_goods['goods_code'],
                'goods_extern_code'=>$order_goods['goods_extern_code'],
                'goods_category_code'=>$order_goods['goods_category_code'],
                'goods_name'=>$order_goods['goods_name'],
                'goods_materiel_code'=>$order_goods['goods_materiel_code'],
                'goods_price'=>$order_goods['goods_price'],
                'goods_number'=>$order_goods['goods_number'],
                'create_time'=>$order_goods['create_time'],
                'update_time'=>$order_goods['update_time'],
            );
        }
        $add_bool = M('crm_order_goods_info')->addAll($crm_order_goods_list);
        return $add_bool;
    }
    
    //同步订单支付信息
    private function sync_crm_order_pay($pay_list){
        return M('crm_order_payment_info')->addAll($pay_list);
    }
    
    //同步订单商品折扣信息
    private function sync_order_goods_discount($goods_discount_list){
        return M('crm_order_goods_discount_info')->addAll($goods_discount_list);
    }
}
