<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\Controller;

/**
 * 
 *
 * @author xiao.yingjie
 */
class OrderBackupController extends OrderController{
    
    public function back_index(){
        $this->deal_back_order(1, 100);
    }
    /**
     * 支付完成，处理订单
     */
    public function deal_back_order($pageIndex=1, $page_size=10){
        $where = $this->get_back_order_where();
        $count = M('order_base_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $fields = 'order_id,order_cur_id,member_id,delivery_type,create_time,pay_time';
        $list = M('order_base_info')->where($where)->field($fields)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('pay_time asc')->select();
        foreach($list as $k=>$order_info){
            $is_part = M('order_base_info')->where(array('parent_id'=>$order_info['order_id']))->count();
            if($is_part) {
                continue;
            }else{
                $result = $this->deal_order($order_info);
                //接口错误
                if($result === FALSE) {
                    \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单编号：'.$order_info['order_cur_id'].'，拆分失败'."\n", 'backup-order-error.log',date('Y-m-d'));
                }else{
                    $where = array();
                    $where['info.pay_status'] = 12;
                    $where['order_goods.order_goods_status'] = array('lt',8);
                    $where['info.shops_code'] = array('neq','');
                    $where['order_goods.goods_extern_code'] = array('neq','');
                    $where['info.order_cur_id'] = $order_info['order_cur_id'];
                    $this->syncBuyRecharge($where);
                    \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单编号：'.$order_info['order_cur_id'].'，拆分成功'."\n", 'backup-order-success.log',date('Y-m-d'));
                }
            }
        }
    }
    
    //同步储值卡和权益包
    private function syncBuyRecharge($where){
        $pageIndex=1;$page_size=10;
        //商品类型
        $goods_types = array(3=>'100000004',4=>'100000005',5=>'100000006');
        //商品优惠对应的码表 TODO 优惠码暂无
        $discount_code = array(3=>'02010000',4=>'02010000',5=>'02030000');
        //现金支付码表
        $pay_code = array(1=>'01010302',2=>'01010303',3=>'01010305');
        $count = M('order_goods_info as order_goods,tm_order_base_info as info')->where('order_goods.order_id=info.order_id')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('order_goods_info as order_goods,tm_order_base_info as info')->where('order_goods.order_id=info.order_id')->field('order_goods.*,info.member_id,info.pay_time')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('info.pay_time asc')->select();
        $member_ids = getFieldsByKey($list, 'member_id');
        $member_mobiles = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        foreach($list as $k=>$order_goods_info){
            $member_id = $order_goods_info['member_id'];
            $mobilePhone = $member_mobiles[$member_id];
            //支付信息
            $PayInfo = array();$pay_value = 0;
            $goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id']))->select();
            //TODO 测试用
//            $goods_discount_list = array(array('discount_value'=>6,'discount_type'=>3));
            foreach($goods_discount_list as $k=>$goods_discount){
                if(in_array($goods_discount['discount_type'], array(3,4,5))) {
                    $PayInfo[] = array('pay_value'=>$goods_discount['discount_value'],'type'=>$discount_code[$goods_discount['discount_type']]);
                    $pay_value += $goods_discount['discount_value'];
                }
            }
            //订单支付信息
            $order_info = M('order_base_info')->where(array('order_id'=>$order_goods_info['order_id']))->field('order_id,parent_id')->find();
            $pay_order_id = $order_info['order_id'];
            //如果是子订单则获取父订单ID，去查询支付信息
            if($order_info['parent_id']) $pay_order_id = $order_info['parent_id'];
            $payment_info = M('order_payment_info')->where(array('order_id'=>$pay_order_id))->find();
            //TODO 测试用,正式去掉
//            $payment_info = array('payment_type'=>1);
            if($payment_info){
                $PayInfo[] = array('pay_value'=>$order_goods_info['goods_price'] - $pay_value,'type'=>$pay_code[$payment_info['payment_type']]);
            }
            if(!$mobilePhone || !$order_goods_info['goods_extern_code']){
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单商品ID：'.$order_goods_info['order_goods_id'].'，同步失败,缺少手机号码或者商品编码,手机号码'.$mobilePhone.'，CRM商品ID:'.$order_goods_info['goods_extern_code']."\n", 'buy-recharge-error.log',date('Y-m-d'));
            }else{
		$goods_type = M('goods_base_info')->where(array('goods_code'=>$order_goods_info['goods_code']))->getField('goods_type');
                $producttype = $goods_types[$goods_type];
                //根据购买的数量调用
                for($i=0;$i<$order_goods_info['goods_number'];$i++){
                    $client = new \SoapClient(C('CRM_WSAL_API_URL'),['soap_version' => SOAP_1_1]);
                    $parm = array(
                        'ValidateCode'=>'CrmConfig',
                        'UserType'=>'100000003',
                        'mobilePhone'=>$mobilePhone,
                        'productId'=>$order_goods_info['goods_extern_code'],
                        'buyMoney'=>$order_goods_info['goods_price'],
                        'producttype'=>$producttype,
                        'PayInfo'=>  json_encode($PayInfo)
                    );
                    $rs = $client->BuyRecharge(array('request'=>$parm));
                    $result = $rs->BuyRechargeResult;
                    if(intval($result->ResultCode) === 0){
                        \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单商品ID：'.$order_goods_info['order_goods_id'].'，同步成功,手机号码'.$mobilePhone.'，CRM商品ID:'.$order_goods_info['goods_extern_code']."\n", 'buy-recharge-success.log',date('Y-m-d'));
                        //更新订单商品信息
                        M('order_goods_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id']))->data(array('order_goods_status'=>8,'update_time'=>  date('Y-m-d H:i:s')))->save();
                        M('goods_use_detail_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id']))->data(array('use_status'=>2,'update_time'=>  date('Y-m-d H:i:s')))->save();
                    }else{
                        \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单商品ID：'.$order_goods_info['order_goods_id'].'，同步失败,失败原因,'.$result->ResultDesc.',手机号码'.$mobilePhone.'，CRM商品ID:'.$order_goods_info['goods_extern_code']."\n", 'buy-recharge-error.log',date('Y-m-d'));
                    }
                }
            }
        }
    }
    
    private function get_back_order_where(){
        $where = array();
        //未处理
        $where['order_status'] = 1;
        //已支付
        $where['pay_status'] = 12;
        $where['pay_time'] = array('lt',date('Y-m-d'));
        return $where;
    }
}
