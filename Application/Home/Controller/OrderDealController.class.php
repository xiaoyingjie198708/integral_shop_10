<?php

namespace Home\Controller;

class OrderDealController extends BaseController{
    
    public function index(){
        $this->display();
    }
    
    public function deal_order(){
        $order_cur_id = I('order_cur_id','','trim,htmlspecialchars');
        $send_sms = I('send_sms',0,'intval');
        $order_info = M('order_base_info')->where(array('order_cur_id'=>$order_cur_id))->find();
        if($order_info['order_status'] == 11)  $this->oldAjaxReturn('', '已推过', 0);
        $result = $this->cancel_order_info($order_info);
        //接口错误
        if($result === FALSE) {
            \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单编号：'.$order_info['order_cur_id'].'，取消失败'."\n", 'error-cancel-order-error.log',date('Y-m-d'));
            $this->oldAjaxReturn('', '取消失败', 0);
        }else{
            if($send_sms){
                $phone = M('user_member_base_info')->where(array('member_id'=>$order_info['member_id']))->getField('member_account');
                $data['phone'] = $phone;
                $data['content'] = '尊敬的会员您好，您的秒杀订单未支付成功，如已扣减积分稍后会退回，消费码随即作废。给您带来不便敬请谅解，请继续关注其他活动。';
                sendSms($data);
            }
            \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单编号：'.$order_info['order_cur_id'].'，取消成功'."\n", 'error-cancel-order.log',date('Y-m-d'));
            $this->oldAjaxReturn('', '取消成功', 0);
        }
    }
    
    public function reverse(){
        $this->display();
    }
    
    public function reverse_order(){
        $data['OrderId'] = I('order_cur_id','','trim');
        $data['member_id'] = I('member_id','','trim');
        $data['point'] = I('reverse_point',0,'intval');
        $res = changePoints($data);
        if($res) $this->oldAjaxReturn (0, '退积分成功', 1);
        else $this->oldAjaxReturn (0, '退积分失败', 1);
    }


    /**
     * 取消商品
     * @param type $order_info
     */
    private function cancel_order_info($order_info){
        //积分
        $use_points = M('order_pay_discount_info')->where(array('order_id'=>$order_info['order_id'],'discount_type'=>4))->getField('discount_code');
        //储值卡
        $use_money = M('order_pay_discount_info')->where(array('order_id'=>$order_info['order_id'],'discount_type'=>5))->getField('discount_value');
        M('order_base_info')->startTrans();
        //如果有积分或者储值卡生成退货订单
        $reverse_order_bool = true;
        if($use_points || $use_money){
            $reverse_data = array('order_id'=>$order_info['order_id'],'forward_point'=>$use_points,'forward_storage_money'=>$use_money,'reason'=>'秒杀订单未支付成功，系统退单');
            $reverse_order_bool = cancel_reverse_order($reverse_data);
        }
        $cancel_order_data['cancel_way'] = 3;
        $cancel_order_data['update_time'] = date('Y-m-d H:i:s');
        //订单取消,待处理的订单设置成取消
//        if($order_info['order_status'] == 1) {
//            $cancel_order_data['order_status'] = 11;
//        }
        $cancel_order_data['order_status'] = 11;
        $order_bool = M('order_base_info')->where(array('order_id'=>$order_info['order_id']))->data($cancel_order_data)->save();
        //订单商品取消
        $cancel_order_goods_data['order_goods_status'] = 11;
        $cancel_order_goods_data['update_time'] = date('Y-m-d H:i:s');
        $order_goods_bool = M('order_goods_info')->where(array('order_id'=>$order_info['order_id']))->data($cancel_order_goods_data)->save();
        //退还库存
//        $order_goods_list = M('order_goods_info')->where(array('order_id'=>$order_info['order_id']))->select();
//        foreach($order_goods_list as $k=>$order_goods){
//            M('goods_stock_info')->where(array('goods_code'=>$order_goods['goods_code']))->setDec('occupy_stocks',$order_goods['goods_number']);
//        }
        //订单日志
        $loginfo_data['order_id'] = $order_info['order_id'];
        $loginfo_data['comment'] = '系统原因，错误订单退单';
        $loginfo_data['type'] = 1;
        $loginfo_data['operate_user_id'] = '';
        $loginfo_data['create_time'] = date("Y-m-d H:i:s");
        $loginfo_data['update_time'] = date("Y-m-d H:i:s");
        $log_bool = M('order_loginfo')->data($loginfo_data)->add();
        //处理消费码
        M('member_use_code')->where(array('order_id'=>$order_info['order_id']))->data(array('status'=>0,'remark'=>'秒杀订单未支付成功，系统退单','update_time'=>  date('Y-m-d H:i:s')))->save();
        if($order_bool && $order_goods_bool && $log_bool && $reverse_order_bool){
            M('order_base_info')->commit();
            return true;
        }else{
            M('order_base_info')->rollback();
            return FALSE;
        }
    }
}
