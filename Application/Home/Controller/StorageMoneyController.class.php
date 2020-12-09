<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

/**
 * 储值卡报表
 *
 * @author xiao.yingjie
 */
class StorageMoneyController extends BaseController{
    
   public function index(){
        $mod = M('goods_use_detail_info');
        $where = $this->get_where();
        $count = count($mod->alias('a')->join('tm_goods_base_info b on a.goods_code=b.goods_code','LEFT')->where($where)->group('a.order_goods_id')->field('a.id')->select());
        $page = new \Org\My\Page($count, 10);
        $list = $mod->alias('a')->join('tm_goods_base_info b on a.goods_code=b.goods_code','LEFT')->where($where)->where($where)->group('a.order_goods_id')->field('a.*')->limit($page->firstRow . ',' . $page->listRows)->order('a.pay_time desc')->select();
//        $shops_codes = getFieldsByKey($list, 'shops_code');
//        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $member_ids = getFieldsByKey($list, 'member_id');
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account');
        foreach($list as $k=>$info){
//            $info['shops_name'] = $shops_names[$info['shops_code']];
            $info['member_account'] = $member_accounts[$info['member_id']];
            $child_list = M('goods_use_detail_info')->where(array('order_goods_id'=>$info['order_goods_id']))->select();
            $pay_cash = 0;
            $real_pay_cash = 0;
            $real_pay_storage_money = 0;
            $wh_value = 0;
            $coupon_code = '';
            foreach($child_list as $j=>$child_info){
                $pay_cash += $child_info['pay_cash'];
                $real_pay_cash += $child_info['real_pay_cash'];
                $real_pay_storage_money += $child_info['real_pay_storage_money'];
                //文惠券
                if($child_info['coupon_category_type'] == 2) {
                    $wh_value += $child_info['real_pay_coupon_value'] + $child_info['real_pay_coupon_code_value'];
                    //优惠券
                    if($child_info['use_coupon']){
                        $coupon_code = $child_info['use_coupon'].'(优惠券)';
                    }
                    //优惠码
                    if($child_info['use_coupon_code']){
                        $coupon_code = $child_info['use_coupon_code'].'(优惠码)';
                    }
                }
            }
            $info['pay_cash'] = $pay_cash;
            $info['real_pay_cash'] = $real_pay_cash;
            $info['real_pay_storage_money'] = $real_pay_storage_money;
            $info['wh_value'] = $wh_value;
            $info['real_total_value'] = $real_pay_cash + $real_pay_storage_money + $wh_value;
            $info['coupon_code'] = $coupon_code;
            //总计
            $total_data['pay_cash'] += $pay_cash;
            $total_data['real_pay_cash'] += $real_pay_cash;
            $total_data['real_pay_storage_money'] += $real_pay_storage_money;
            $total_data['wh_value'] += $wh_value;
            $total_data['real_total_value'] += $info['real_total_value'];
            $list[$k] = $info;
        }
        $this->search($this->order_list_search());
        $this->assign('list', $list);
        $this->assign('total_data', $total_data);
        $this->assign('page', $page->show());
        $this->display();
    }
    
    //导出报表
    public function export(){
        $where = $this->get_where();
        $export_model = new \Org\My\Export('goods_use_detail_info');
        $export_model->limit = 20000;
        $count = count($export_model->alias('a')->join('tm_goods_base_info b on a.goods_code=b.goods_code','LEFT')->where($where)->group('a.order_goods_id')->field('a.id')->select());
        $export_model->setCount($count);
        $list = $export_model->alias('a')->join('tm_goods_base_info b on a.goods_code=b.goods_code','LEFT')->where($where)->group('a.order_goods_id')->field('a.*')->order('a.pay_time desc')->getExportData();
//        $shops_codes = getFieldsByKey($list, 'shops_code');
//        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $member_ids = getFieldsByKey($list, 'member_id');
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account');
        foreach($list as $k=>$info){
//            $info['shops_name'] = $shops_names[$info['shops_code']];
            $info['member_account'] = $member_accounts[$info['member_id']];
            $child_list = M('goods_use_detail_info')->where(array('order_goods_id'=>$info['order_goods_id']))->select();
            $pay_cash = 0;
            $real_pay_cash = 0;
            $real_pay_storage_money = 0;
            $wh_value = 0;
            $coupon_code = '';
            foreach($child_list as $j=>$child_info){
                $pay_cash += $child_info['pay_cash'];
                $real_pay_cash += $child_info['real_pay_cash'];
                $real_pay_storage_money += $child_info['real_pay_storage_money'];
                //文惠券
                if($child_info['coupon_category_type'] == 2) {
                    $wh_value += $child_info['real_pay_coupon_value'] + $child_info['real_pay_coupon_code_value'];
                    //优惠券
                    if($child_info['use_coupon']){
                        $coupon_code = $child_info['use_coupon'].'(优惠券)';
                    }
                    //优惠码
                    if($child_info['use_coupon_code']){
                        $coupon_code = $child_info['use_coupon_code'].'(优惠码)';
                    }
                }
            }
            $info['order_status'] = id2name('order_status', $info['order_status']);
            $info['payment_type'] = id2name('payment_type', $info['payment_type']);
            $info['pay_cash'] = price_format($pay_cash);
            $info['real_pay_cash'] = price_format($real_pay_cash);
            $info['real_pay_storage_money'] = price_format($real_pay_storage_money);
            $info['wh_value'] = price_format($wh_value);
            $info['real_total_value'] = price_format($real_pay_cash + $real_pay_storage_money + $wh_value);
            $info['coupon_code'] = $coupon_code;
             //总计
            $total_data['pay_cash'] += $pay_cash;
            $total_data['real_pay_cash'] += $real_pay_cash;
            $total_data['real_pay_storage_money'] += $real_pay_storage_money;
            $total_data['wh_value'] += $wh_value;
            $total_data['real_total_value'] += $info['real_total_value'];
            $total_data['pay_cash'] = price_format($total_data['pay_cash']);
            $total_data['real_pay_cash'] = price_format($total_data['real_pay_cash']);
            $total_data['real_pay_storage_money'] = price_format($total_data['real_pay_storage_money']);
            $total_data['wh_value'] = price_format($total_data['wh_value']);
            $total_data['real_total_value'] = price_format($total_data['real_total_value']);
            $list[$k] = $info;
        }
        $row_count = count($list);
        $total_data['merage'] = 'A'.($row_count+3).':D'.($row_count+3);
        $total_data['order_cur_id'] = '合计';
        $list[] = $total_data;
         //导出的模版
        $export_fields = $this->export_fields();
        //导出的数据
        $export_model->title = '储值卡订单详情报表';
        $export_model->execl_fields = $export_fields;
        $export_model->setData($list);
        $export_model->setHeaderName('储值卡订单详情报表');
        $export_page = $export_model->export();
        exit($export_page);
    }
    /*---------------------------------------------------受保护方法------------------------------------------------------*/
    //订单搜索条件
    private function order_list_search() {
        return array(
            'url' => U('StorageMoney/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('StorageMoney/export').'"><i class="icon-download icon-white"></i> 导出报表</a>',
            ),
            'main' => array(
                 array(
                    'name' => 'payment_type[]',
                    'show_name' => '支付方式',
                    'select' => array(1=>'支付宝',2=>'银联支付',3=>'微信支付',4=>'文惠券'),
                    'type' => 'select',
                     'multiple'=>true,
                     'param_name' => 'payment_type',
                ),
                array(
                    'name' => 'order_status[]',
                    'show_name' => '订单状态',
                    'select' => array(1=>'待处理',2=>'已确认',3=>'待发货',4=>'已发货',7=>'确认收货',8=>'订单完成'),
                    'type' => 'select',
                    'multiple'=>true,
                    'param_name' => 'order_status',
                ),
                array(
                    'name' => 'label_id',
                    'type' => 'hidden',
                )
            ),
            'other' => array(
                array(
                    'name' => array('pay_start', 'pay_end'),
                    'show_name' => '支付时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                )
            )
        );
    }
    
    private function get_where(){
        $label_id = I('label_id','','trim,htmlspecialchars');
        $label_ids = explode('_', $label_id);
        if(count($label_ids) == 1){
            $parent_label_id = $label_ids[0];
            $label_ids = M('goods_label')->where(array('label_parent_id'=>$parent_label_id))->getField('label_id',true);
            $label_ids[] = $parent_label_id;
        }
        if($label_id){
            $goods_ids = M('goods_label_relation')->where(array('label_id'=>array('in',$label_ids)))->getField('goods_id',true);
            $goods_codes = M('goods_base_info')->where(array('goods_id'=>array('in',$goods_ids)))->getField('goods_code',true);
            $where['a.goods_code'] = array('in',$goods_codes);
        }
        $payment_type = I('payment_type',0,'intval');
        $order_status = I('order_status',0,'intval');
        $pay_bool = false;
        //文惠券
        if(in_array(4, $payment_type)){
            $where['a.coupon_category_type'] = 2;
            foreach($payment_type as $k=>$value){
                if($value == 4) unset ($payment_type[$k]);
            }
            $pay_bool = true;
        }
        if(in_array(0, $payment_type)){
            foreach($payment_type as $k=>$value){
                if($value == 0) unset ($payment_type[$k]);
            }
        }
        if($payment_type) {
            $where['a.payment_type'] = array('in',$payment_type);
            $pay_bool = true;
        }
        if(!$pay_bool){
            $where['a.payment_type'] = array('neq',4);
        }
        if(in_array(0, $order_status)){
            foreach($order_status as $k=>$value){
                if($value == 0) unset ($order_status[$k]);
            }
        }
        if($order_status) {
            $where['a.order_status'] = array('in',$order_status);
        }else{
            $where['a.order_status'] = array('gt',0);
        }
        $pay_start = I('pay_start','','trim,htmlspecialchars');
        $pay_end = I('pay_end','','trim,htmlspecialchars');
        if($pay_start && !$pay_end) $where['a.pay_time'] = array('gt',$pay_start);
        if(!$pay_start && $pay_end) $where['a.pay_time'] = array('lt',$pay_end);
        if($pay_start && $pay_end) $where['a.pay_time'] = array('between',array($pay_start,$pay_end));
        $where['b.goods_type'] = 5;
        return $where;
    }
    
    private function export_fields(){
        return array(
            'order_cur_id'=>'订单编号',
            'order_status'=>'订单状态',
            'member_account'=>'会员账号',
            'goods_name'=>'商品名称',
            'pay_cash'=>'订单总价',
            'real_pay_cash'=>'收现金额',
            'real_pay_storage_money'=>'储值卡金额',
            'wh_value'=>'文惠券金额',
            'real_total_value'=>'收款总额',
            'pay_time'=>'支付时间',
            'payment_type'=>'支付方式',
            'coupon_code'=>'文惠券编码'
        );
    }
    
    
}
