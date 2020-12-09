<?php


namespace Api\Controller;

class OrderController extends BaseController{
    
    public function sync(){
        //取消订单
        $this->cancel_order(1, 1000);
        //处理支付的订单
        $this->deal_pay_order(1, 1000);
        //设置订单商品收货
        $this->finish_order_goods(1, 1000);
        //设置订单收货
        $this->finish_order(1,1000);
        //赠送发票
        $this->gift_coupon(1, 100);
    }
    public function temp_cancel_order(){
        $where = array();
        $where['order_cur_id'] = '2018092200048420';
        $order_info = M('order_base_info')->where($where)->find();
        $result = $this->cancel_order_info($order_info);
        var_dump($result);exit;
    }
    /**
     * 同步CRM商品
     * @param type $pageIndex
     * @param type $page_size
     * @return boolean
     */
    private function cancel_order($pageIndex=1, $page_size=10){
        $where = $this->get_order_where();
        $count = M('order_base_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $fields = 'order_id,order_cur_id,member_id,order_status,update_time';
        $list = M('order_base_info')->where($where)->field($fields)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        foreach($list as $k=>$order_info){
            $result = $this->cancel_order_info($order_info);
            //接口错误
            if($result === FALSE) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单编号：'.$order_info['order_cur_id'].'，取消失败'."\n", 'cancel-order-error.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->cancel_order(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'cancel-order-info.log','','w'); 
            return true;
	}
    }
    
    /**
     * 支付完成，处理订单
     */
    public function deal_pay_order($pageIndex=1, $page_size=10){
        $where = $this->get_part_order_where();
        $count = M('order_base_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $fields = 'order_id,order_cur_id,member_id,delivery_type,create_time,pay_time';
        $list = M('order_base_info')->where($where)->field($fields)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('pay_time asc')->select();
        foreach($list as $k=>$order_info){
            $result = $this->deal_order($order_info);
            //接口错误
            if($result === FALSE) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单编号：'.$order_info['order_cur_id'].'，拆分失败'."\n", 'part-order-error.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['pay_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->deal_pay_order(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('create_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'part-order-info.log','','w'); 
            return true;
	}
    }
    
    /**
     * 取消商品
     * @param type $order_info
     */
    private function cancel_order_info($order_info){
        //积分
        $use_points = M('order_pay_discount_info')->where(array('order_id'=>$order_info['order_id'],'discount_type'=>4))->getField('discount_code');
//        //调用存储过程添加积分
//        if($use_points){
//            $data['member_id'] = $order_info['member_id'];
//            $data['point'] = intval($use_points);
//            $data['OrderId'] = $order_info['order_cur_id'];
//            $result = changePoints($data);
//        }
        //储值卡
        $use_money = M('order_pay_discount_info')->where(array('order_id'=>$order_info['order_id'],'discount_type'=>5))->getField('discount_value');
//        if($use_money){
//            $data = array();
//            $data['MemberId'] = $order_info['member_id'];
//            $data['OrderId'] = $order_info['order_cur_id'];
//            $data['UseBalance'] = $use_money;
//            $result = refundTicket($data);
//        }
        //使用优惠码
        $use_coupon_code = M('order_pay_discount_info')->where(array('order_id'=>$order_info['order_id'],'discount_type'=>2))->getField('discount_code');
        //使用优惠券
        $use_couponcode = M('order_pay_discount_info')->where(array('order_id'=>$order_info['order_id'],'discount_type'=>3))->getField('discount_code');
        M('order_base_info')->startTrans();
        //如果有积分或者储值卡生成退货订单
        $reverse_order_bool = true;
        if($use_points || $use_money){
            $reverse_data = array('order_id'=>$order_info['order_id'],'forward_point'=>$use_points,'forward_storage_money'=>$use_money,'reason'=>'自动取消订单生成退货单');
            $reverse_order_bool = cancel_reverse_order($reverse_data);
        }
        $cancel_order_data['cancel_way'] = 3;
        $cancel_order_data['update_time'] = date('Y-m-d H:i:s');
        //订单取消,待处理的订单设置成取消
        if($order_info['order_status'] == 1) {
            $cancel_order_data['order_status'] = 11;
        }
        $order_bool = M('order_base_info')->where(array('order_id'=>$order_info['order_id']))->data($cancel_order_data)->save();
        //订单商品取消
        $cancel_order_goods_data['order_goods_status'] = 11;
        $cancel_order_goods_data['update_time'] = date('Y-m-d H:i:s');
        $order_goods_bool = M('order_goods_info')->where(array('order_id'=>$order_info['order_id']))->data($cancel_order_goods_data)->save();
        //退还库存
        $order_goods_list = M('order_goods_info')->where(array('order_id'=>$order_info['order_id']))->select();
        foreach($order_goods_list as $k=>$order_goods){
            M('goods_stock_info')->where(array('goods_code'=>$order_goods['goods_code']))->setDec('occupy_stocks',$order_goods['goods_number']);
        }
        //退还优惠码
        if($use_coupon_code) {
            M('coupon_code_info')->where(array('coupon_code'=>$use_coupon_code))->setDec('use_count',1);
            M('coupon_code_info')->where(array('coupon_code'=>$use_coupon_code))->data(array('coupon_status'=>10))->save();
        }
        //退还优惠券
        if($use_couponcode){
            M('coupon_user_info')->where(array('coupon_code'=>$use_coupon_code))->data(array('coupon_status'=>10))->save();
        }
        //订单日志
        $loginfo_data['order_id'] = $order_info['order_id'];
        $loginfo_data['comment'] = '支付超三十分钟，订单自动取消';
        $loginfo_data['type'] = 1;
        $loginfo_data['operate_user_id'] = '';
        $loginfo_data['create_time'] = date("Y-m-d H:i:s");
        $loginfo_data['update_time'] = date("Y-m-d H:i:s");
        $log_bool = M('order_loginfo')->data($loginfo_data)->add();
        if($order_bool && $order_goods_bool && $log_bool && $reverse_order_bool){
            M('order_base_info')->commit();
            return true;
        }else{
            M('order_base_info')->rollback();
            return FALSE;
        }
    }
    
    //处理支付订单
    protected function deal_order($order_info){
        $mod = M('order_base_info');
        $order_id = $order_info['order_id'];
        $mod->startTrans();
        //拆单
        $part_bool = part_order($order_id);
        if(!$part_bool) $error[] = '拆单失败';
        $order_ids = $mod->where(array('parent_id'=>$order_id))->getField('order_id',true);
        if(!$order_ids) $order_ids[] = $order_id;
        $order_goods_list = M('order_goods_info')->where(array('order_id'=>array('in',$order_ids)))->select();
        $goods_codes = getFieldsByKey($order_goods_list, 'goods_code');
        $goods_params = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,is_object,service_type,goods_type,timeout_time',true);
        foreach($order_goods_list as $k=>$order_goods){
            $goods_param = $goods_params[$order_goods['goods_code']];
            //添加销量
            $update_sale_bool = M('goods_base_info')->where(array('goods_code'=>$order_goods['goods_code']))->setInc('sale_number',$order_goods['goods_number']);
            if($update_sale_bool === FALSE) $error[] = '添加销量失败';
            //如果是虚拟商品或者是自提的商品，发放消费码和提货码
            if(!$goods_param['is_object'] || ($goods_param['is_object'] && $order_info['delivery_type'] == 2)){
                if(!in_array($goods_param['goods_type'], array(3,4,5,6,7,8))){
                    if(!$goods_param['is_object']) $code_type = 1;
                    else $code_type = 2;
                    $goods_number = $order_goods['goods_number'];
                    //判断是否是使用积分+现金的形式
                    //过期时间,大于当前时间
                    if($goods_param['timeout_time'] > date('Y-m-d')){
                        $valid_type = 3;
                        $valid_time = strtotime($goods_param['timeout_time']);
                    }else{
                        $valid_type = $this->get_point_and_money($order_goods, $order_info);
                        if($valid_type == 1){
                            $valid_time = strtotime(date("Y-m-d".' 23:59:59',strtotime("+3 month")));
                        }else{
                            $valid_time = strtotime(date("Y-m-d".' 23:59:59',strtotime("+1 year")));
                        }  
                    }
//                    $valid_time = strtotime(date("Y-m-d H:i:s",strtotime("+3 month")));
//                    //固定钱+积分或者是积分为0的
//                    if(($order_goods['integral'] && $order_goods['goods_price']) || !$order_goods['max_integral']){
//                        $valid_type = 2;
//                        $valid_time = strtotime(date("Y-m-d H:i:s",strtotime("+1 year")));
//                    }
                    if($goods_param['goods_type'] == 2){
                        $relation_goods_list = json_decode($order_goods['relation_goods'],true);
                        for($i=0;$i<$goods_number;$i++){
                            foreach($relation_goods_list as $j=>$relation_goods){
                                $use_code_arr[] = array(
                                    'use_id'=>(new \Org\Util\ThinkString())->uuid(),
                                    'member_id'=>$order_info['member_id'],
                                    'order_id'=>$order_goods['order_id'],
                                    'order_goods_id'=>$order_goods['order_goods_id'],
                                    'use_code'=>  createUserCode(),
                                    'code_type'=>$code_type,
                                    'status'=>10,
                                    'create_time'=>  date('Y-m-d H:i:s'),
                                    'update_time'=>  date('Y-m-d H:i:s'),
                                    'goods_code'=>$relation_goods['goods_code'],
                                    'is_valid'=>1,
                                    'valid_type'=>$valid_type,
                                    'valid_time'=>$valid_time,
                                    'shops_code'=>$order_goods['shops_code'],
                                );
                            }
                        }
                    }else{
                        for($i=0;$i<$goods_number;$i++){
                            $use_code_arr[] = array(
                                'use_id'=>(new \Org\Util\ThinkString())->uuid(),
                                'member_id'=>$order_info['member_id'],
                                'order_id'=>$order_goods['order_id'],
                                'order_goods_id'=>$order_goods['order_goods_id'],
                                'use_code'=>  createUserCode(),
                                'code_type'=>$code_type,
                                'status'=>10,
                                'create_time'=>  date('Y-m-d H:i:s'),
                                'update_time'=>  date('Y-m-d H:i:s'),
                                'goods_code'=>$order_goods['goods_code'],
                                'is_valid'=>1,
                                'valid_type'=>$valid_type,
                                'valid_time'=>$valid_time,
                                'shops_code'=>$order_goods['shops_code'],
                            );
                        }
                    }
                }
                //更新订单商品信息
                $update_order_goods_bool = M('order_goods_info')->where(array('order_goods_id'=>$order_goods['order_goods_id']))->data(array('order_goods_status'=>4,'update_time'=>  date('Y-m-d H:i:s')))->save();
                if(!$update_order_goods_bool) $error[] = '更新订单商品发货信息失败';
            }
        }
        $use_codel_bool = true;
        if($use_code_arr) $use_codel_bool = M('member_use_code')->addAll($use_code_arr);
         if(!$use_codel_bool) $error[] = '添加会员消费码失败';
        //判断是否全部发货
        foreach($order_ids as $k=>$temp_order_id){
            $all_fend = true;
            $order_goods_status_arr = M('order_goods_info')->where(array('order_id'=>$temp_order_id))->getField('order_goods_status',true);
            foreach($order_goods_status_arr as $j=>$order_goods_status) if($order_goods_status != 4) $all_fend = false;
            $all_order_bool = true;
            if($all_fend) $all_order_bool = $mod->where(array('order_id'=>$temp_order_id))->data (array('order_status'=>4,'update_time'=>  date('Y-m-d H:i:s')))->save();
            if(!$all_order_bool) $error[] = '更新订单信息失败';
        }
        if(empty($error)){
            $mod->commit();
            return true;
        }else{
            $mod->rollback();
            return false; 
        }
    }
    
    //订单完成
    private function finish_order_goods($pageIndex=1, $page_size=10){
        $where = $this->get_order_goods_where();
        $count = M('order_goods_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $fields = 'order_goods_id,order_goods_type,relation_goods,goods_number';
        $list = M('order_goods_info')->where($where)->field($fields)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        foreach($list as $k=>$order_goods_info){
            $goods_number = 0;
            if($order_goods_info['order_goods_type'] == 6){
                $relation_goods_list = json_decode($order_goods_info['relation_goods'],true);
                foreach($relation_goods_list as $j=>$relation_goods){
                   $goods_number += $relation_goods['number'] * $order_goods_info['goods_number']; 
                }
            }else{
                $goods_number = $order_goods_info['goods_number'];
            }
            $use_count = M('member_use_code')->where(array('status'=>20,'order_goods_id'=>$order_goods_info['order_goods_id']))->count();
            if($use_count == $goods_number){
                M('order_goods_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id']))->data(array('order_goods_status'=>8,'update_time'=>  date('Y-m-d H:i:s')))->save();
            }
        }
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->finish_order_goods(++$pageIndex, $page_size);
	}
    }
    
    //订单完成
    private function finish_order($pageIndex=1, $page_size=10){
        $where = array('order_status'=>4);
        $count = M('order_base_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $fields = 'order_id,delivery_type,update_time';
        $list = M('order_base_info')->where($where)->field($fields)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        foreach($list as $k=>$order_info){
            $order_goods_status = M('order_goods_info')->where(array('order_id'=>$order_info['order_id']))->getField('order_goods_status',true);
            $order_goods_status = array_unique($order_goods_status);
            if($order_goods_status[0] == 8 && count($order_goods_status) == 1){
                 M('order_base_info')->where(array('order_id'=>$order_info['order_id']))->data(array('order_status'=>8,'update_time'=>  date('Y-m-d H:i:s')))->save();
            }
            //快递形式
            if($order_info['delivery_type'] == 1){
                $last_date = strtotime("+15 day",  strtotime($order_info['update_time']));
                if(time() > $last_date){
                    M('order_base_info')->where(array('order_id'=>$order_info['order_id']))->data(array('order_status'=>8,'update_time'=>  date('Y-m-d H:i:s')))->save();
                    M('order_goods_info')->where(array('order_id'=>$order_info['order_id']))->data(array('order_goods_status'=>8,'update_time'=>  date('Y-m-d H:i:s')))->save();
                }
            }
        }
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->finish_order_goods(++$pageIndex, $page_size);
	}
    }
    
    //赠送优惠券
    private function gift_coupon($pageIndex=1, $page_size=10){
        $where = $this->get_gift_coupon_where();
        $count = M('order_goods_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('order_goods_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        foreach($list as $k=>$order_goods_info){
            $activity_id = M('promotion_bundles_goods_info')->where(array('goods_code'=>$order_goods_info['goods_code']))->getField('activity_id');
            $coupon_relation_list = M('promotion_bundles_goods_info')->where(array('type'=>3,'activity_id'=>$activity_id))->select();
            if($coupon_relation_list){
                $member_id = M('order_base_info')->where(array('order_id'=>$order_goods_info['order_id']))->getField('member_id');
                foreach($coupon_relation_list as $j=>$coupon_relation){
                    $coupon_info = M('coupon_base_info')->where(array('coupon_code'=>$coupon_relation['goods_code']))->find();
                    $exist_coupon = M('coupon_user_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id'],'coupon_code'=>$coupon_info['coupon_code']))->find();
                    if($exist_coupon)  continue;
                    $coupon_user_data[] = array(
                        'member_id'=>$member_id,
                        'coupon_id'=>$coupon_info['coupon_id'],
                        'coupon_code'=>$coupon_info['coupon_code'],
                        'coupon_value'=>$coupon_info['coupon_value'],
                        'is_pc'=>$coupon_info['is_pc'],
                        'is_wap'=>$coupon_info['is_wap'],
                        'is_app'=>$coupon_info['is_app'],
                        'valid_start_time'=>$coupon_info['valid_start_time'],
                        'valid_end_time'=>$coupon_info['valid_end_time'],
                        'coupon_status'=>10,
                        'update_time'=>  date('Y-m-d H:i:s'),
                        'create_time'=>  date('Y-m-d H:i:s'),
                        'order_goods_id'=>$order_goods_info['order_goods_id'],
                        'coupon_user_id'=>(new \Org\Util\ThinkString())->uuid()
                    );
                }
            }
        }
        if($coupon_user_data) M('coupon_user_info')->addAll($coupon_user_data);
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->deal_pay_order(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'gift_coupon-info.log','','w'); 
            return true;
	}
    }
    
    private function get_order_where(){
        $where = array();
        //未处理,用户删除
        $where['order_status'] = array('in',array(1,20));
        $where['cancel_way'] = 0;
        //未支付
        $where['pay_status'] = array('in',array(10,11));
        $last_time = date("Y-m-d H:i:s",strtotime("-1800 seconds"));
        $where['create_time'] = array('lt',$last_time);
        $modifytime = \Org\My\MyLog::read_log('update_time', 'cancel-order-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_part_order_where(){
        $where = array();
        //未处理
        $where['order_status'] = 1;
        //已支付
        $where['pay_status'] = 12;
        $modifytime = \Org\My\MyLog::read_log('create_time', 'part-order-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['pay_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_order_goods_where(){
        $where['order_goods_status'] = 4;//已发货的
        return $where;
    }
    
    private function get_gift_coupon_where(){
        $where['order_goods_status'] = array('in',array(4,8));//已发货的
        $where['order_goods_type'] = 2;//赠品
        $where['is_primary'] = 1;
        $modifytime = \Org\My\MyLog::read_log('update_time', 'gift_coupon-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    /**
     * 获取积分和人民币
     * @param type $order_goods_info
     * @param type $order_info
     * @return int
     */
    protected function get_point_and_money($order_goods_info,$order_info){
//            $order_info = $order_for_order_ids[$order_goods_info['order_id']];
            $discount_value=0.00;$pay_cash = 0.00;$pay_point=0;$real_pay_cash=0.00;$real_pay_point = 0;$real_pay_storage_money = 0.00;$real_pay_coupon_value = 0.00;$real_pay_coupon_code_value=0.00;$use_coupon = '';$use_coupon_code = '';
            $order_goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id']))->select();
            $coupon_category_type = 0;
            $payment_type = M('order_payment_info')->where(array('order_id'=>$order_goods_info['order_id']))->getField('payment_type');
            if(!$payment_type) $payment_type = 0;
            foreach($order_goods_discount_list as $k=>$goods_discount){
                //优惠券
                if($goods_discount['discount_type'] == 3) {
                    $order_goods_info['coupon_code'] = $goods_discount['discount_code'];
                    $order_goods_info['coupon_value'] = $goods_discount['discount_value'];
                    $discount_value += $goods_discount['discount_value'];
                    $real_pay_coupon_value += $goods_discount['discount_value'];
                    $use_coupon = $goods_discount['discount_code'];
                    $coupon_category_type = M('coupon_base_info')->where(array('coupon_code'=>$use_coupon))->getField('coupon_category_type');
                }
                //优惠码
                if($goods_discount['discount_type'] == 4) {
                    $order_goods_info['couponcode_code'] = $goods_discount['discount_code'];
                    $order_goods_info['coupon_code_value'] = $goods_discount['discount_value'];
                    $discount_value += $goods_discount['discount_value'];
                    $real_pay_coupon_code_value += $goods_discount['discount_value'];
                    $use_coupon_code = $goods_discount['discount_code'];
                    $coupon_code_id = M('coupon_code_info')->where(array('coupon_code'=>$use_coupon_code))->getField('coupon_code_id');
                    $coupon_category_type = M('coupon_code_base_info')->where(array('coupon_code_id'=>$coupon_code_id))->getField('coupon_category_type');
                }
                //积分
                if($goods_discount['discount_type'] == 5) $real_pay_point = $goods_discount['discount_code'];
                //储值卡
                if($goods_discount['discount_type'] == 6) {
                    $real_pay_storage_money = $goods_discount['discount_value'];
                    $discount_value += $goods_discount['discount_value'];
                }
            }
            if($order_goods_info['order_goods_type'] == 1 || $order_goods_info['order_goods_type'] == 2){
                //主商品
                if($order_goods_info['is_primary']){
                     //计算应付金额+应付积分
                     if($order_goods_info['integral']){
                       $pay_cash = $order_goods_info['goods_price'] * $order_goods_info['goods_number'];
                       $pay_point = $order_goods_info['max_integral'] * $order_goods_info['goods_number'];
                       $real_pay_cash = $pay_cash - $discount_value;
                    }else{
                       $pay_cash = $order_goods_info['goods_price'] * $order_goods_info['goods_number'];
                       $real_pay_cash = $pay_cash - $discount_value - ($real_pay_point/C('INTEGRAL_RATE')['point']) * C('INTEGRAL_RATE')['rmb'];
                    }
            }
        }
        //支付现金+优惠券+优惠码+储值卡
        if($real_pay_storage_money || $real_pay_cash || $discount_value){
            return 2;
        }else{
            return 1;
        }
    }
}
