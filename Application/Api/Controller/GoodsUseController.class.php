<?php


namespace Api\Controller;

/**
 *商品销售详细信息表
 * @author xiao.yingjie
 */
class GoodsUseController extends BaseController{
    
    public function sync(){
        $this->express_order(1, 1000);
        $this->use_code_order(1, 1000);
        $this->member_use_code_use(1, 1000);
        $this->express_order_use(1, 1000);
        $this->crm_goods_order(1, 1000);
        $this->other_goods_order(1,1000);
    }
    //快递订单商品
    private function express_order($pageIndex=1, $page_size=10){
        $where = $this->get_order_where();
        $count = M('order_base_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('order_base_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('pay_time asc')->select();
        $order_ids = getFieldsByKey($list, 'order_id');
        $order_goods_list = M('order_goods_info')->where(array('order_id'=>array('in',$order_ids)))->select();
        $order_for_order_ids = array();
        foreach($list as $k=>$order_info) $order_for_order_ids[$order_info['order_id']] = $order_info;
        foreach($order_goods_list as $k=>$order_goods_info){
            $order_info = $order_for_order_ids[$order_goods_info['order_id']];
            $discount_value=0.00;$pay_cash = 0.00;$pay_point=0;$real_pay_cash=0.00;$real_pay_point = 0;$real_pay_storage_money = 0.00;$real_pay_coupon_value = 0.00;$real_pay_coupon_code_value=0.00;$use_coupon = '';$use_coupon_code = '';
            $order_goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id']))->select();
            $coupon_category_type = 0;
            $payment_type = M('order_payment_info')->where(array('order_id'=>$order_info['order_id']))->getField('payment_type');
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
                $exist_count = M('goods_use_detail_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id']))->count();
                if(!$exist_count){
                    $goods_use_detail_arr[] = array(
                        'order_cur_id'=>$order_info['order_cur_id'],
                        'shops_code'=>$order_goods_info['shops_code'],
                        'order_id'=>$order_goods_info['order_id'],
                        'order_goods_id'=>$order_goods_info['order_goods_id'],
                        'goods_code'=>$order_goods_info['goods_code'],
                        'goods_name'=>$order_goods_info['goods_name'],
                        'integral'=>$order_goods_info['integral'],
                        'max_integral'=>$order_goods_info['max_integral'],
                        'goods_price'=>$order_goods_info['goods_price'],
                        'goods_number'=>$order_goods_info['goods_number'],
                        'delivery_type'=>1,
                        'pay_cash'=>$pay_cash,
                        'pay_point'=>$pay_point,
                        'real_pay_cash'=>$real_pay_cash,
                        'real_pay_point'=>$real_pay_point,
                        'real_pay_storage_money'=>$real_pay_storage_money,
                        'real_pay_coupon_value'=>$real_pay_coupon_value,
                        'real_pay_coupon_code_value'=>$real_pay_coupon_code_value,
                        'use_coupon'=>$use_coupon,
                        'use_coupon_code'=>$use_coupon_code,
                        'use_status'=>1,
                        'create_time'=>$order_goods_info['create_time'],
                        'update_time'=>$order_goods_info['create_time'],
                        'member_id'=>$order_info['member_id'],
                        'pay_time'=>$order_info['pay_time'],
                        'order_status'=>$order_info['order_status'],
                        'payment_type'=>$payment_type,
                        'coupon_category_type'=>$coupon_category_type
                    );
                }
            }
            if($order_goods_info['order_goods_type'] = 6){//大礼包
                    $relation_goods = json_decode($order_goods_info['relation_goods'],true);
                    foreach($relation_goods as $j=>$relation_goods_info){
                        $shops_code = M('goods_base_info')->where(array('goods_code'=>$relation_goods_info['goods_code']))->getField('shops_code');
                        if($order_goods_info['integral']){
                            $pay_cash = $relation_goods_info['goods_price'] * $order_goods_info['goods_number'] * $relation_goods_info['number'];
                            $pay_point = $relation_goods_info['max_integral'] * $order_goods_info['goods_number'] * $relation_goods_info['number'];
                            $temp_discount_value = price_format($discount_value/($order_goods_info['goods_price']  * $order_goods_info['goods_number'])) * ($relation_goods_info['goods_price'] * $relation_goods_info['number']);
                            if($j == (count($relation_goods) - 1)) $temp_discount_value = $discount_value - $temp_discount_value * (count($relation_goods) - 1);
                            $real_pay_cash = $pay_cash - $temp_discount_value;
                         }else{
                            $pay_cash = $relation_goods_info['goods_price'] * $order_goods_info['goods_number'] * $relation_goods_info['number'];
                            $temp_discount_value = price_format($discount_value/($order_goods_info['goods_price'] * $order_goods_info['goods_number'])) * ($relation_goods_info['goods_price'] * $relation_goods_info['number']);
                            $temp_real_pay_point = price_format($real_pay_point/($order_goods_info['max_integral']* $order_goods_info['goods_number'])) * ($relation_goods_info['max_integral'] * $relation_goods_info['number']);
                            if($j == (count($relation_goods) - 1)) {
                                $temp_discount_value = $discount_value - $temp_discount_value * (count($relation_goods) - 1);
                                $temp_real_pay_point = $real_pay_point - $temp_real_pay_point * (count($relation_goods) - 1);
                            }
                            $real_pay_cash = $pay_cash - $temp_discount_value - ($temp_real_pay_point/C('INTEGRAL_RATE')['point']) * C('INTEGRAL_RATE')['rmb'];
                         }
                        $exist_count = M('goods_use_detail_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id']))->count();
                        if(!$exist_count){
                            $goods_use_detail_arr[] = array(
                               'order_cur_id'=>$order_info['order_cur_id'],
                               'shops_code'=>$shops_code,
                               'order_id'=>$order_goods_info['order_id'],
                               'order_goods_id'=>$order_goods_info['order_goods_id'],
                               'goods_code'=>$relation_goods_info['goods_code'],
                               'goods_name'=>$relation_goods_info['goods_name'],
                               'integral'=>$order_goods_info['integral'],
                               'max_integral'=>$relation_goods_info['max_integral'],
                               'goods_price'=>$relation_goods_info['goods_price'],
                               'goods_number'=>$order_goods_info['goods_number'] * $relation_goods_info['number'],
                               'delivery_type'=>1,
                               'pay_cash'=>$pay_cash,
                               'pay_point'=>$pay_point,
                               'real_pay_cash'=>$real_pay_cash,
                               'real_pay_point'=>$real_pay_point,
                               'real_pay_storage_money'=>$real_pay_storage_money,
                               'real_pay_coupon_value'=>$real_pay_coupon_value,
                               'use_coupon'=>$use_coupon,
                               'use_coupon_code'=>$use_coupon_code,
                               'use_status'=>1,
                               'create_time'=>$order_goods_info['create_time'],
                               'update_time'=>$order_goods_info['create_time'],
                               'member_id'=>$order_info['member_id'],
                               'pay_time'=>$order_info['pay_time'],
                               'order_status'=>$order_info['order_status'],
                               'payment_type'=>$payment_type,
                               'coupon_category_type'=>$coupon_category_type
                           );
                    }
                }
            }
        }
        //添加到订单详细商品表中
        $all_bool = M('goods_use_detail_info')->addAll($goods_use_detail_arr);
        $last_date = $list[count($list) - 1]['pay_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->express_order(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('pay_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'goods_sale-info.log','','w'); 
            return true;
	}
    }
    
    //自提订单商品
    private function use_code_order($pageIndex=1, $page_size=10){
       $where = $this->get_use_code_where();
        $count = M('member_use_code')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('member_use_code')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('create_time asc')->select();
        foreach($list as $k=>$info){
            $goods_info = M('goods_base_info')->where(array('goods_code'=>$info['goods_code']))->field('goods_code,goods_name,integral,max_integral,goods_price_pc')->find();
            if($info['order_goods_id']){
                $order_info = M('order_base_info')->where(array('order_id'=>$info['order_id']))->field('order_cur_id,shops_code,pay_time,create_time,order_status,order_id')->find();
                $order_cur_id = $order_info['order_cur_id'];
                $shops_code = $order_info['shops_code'];
                $create_time = $order_info['create_time'];
                $pay_time = $order_info['pay_time'];
                $payment_type = M('order_payment_info')->where(array('order_id'=>$order_info['order_id']))->getField('payment_type');
                if(!$payment_type) $payment_type = 0;
                $coupon_category_type = 0;
                $order_goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$info['order_goods_id']))->select();
                $discount_value=0.00;$pay_cash = 0.00;$pay_point=0;$real_pay_cash=0.00;$real_pay_point = 0;$real_pay_storage_money = 0.00;$real_pay_coupon_value = 0.00;$real_pay_coupon_code_value=0.00;$use_coupon = '';$use_coupon_code = '';
                foreach($order_goods_discount_list as $j=>$goods_discount){
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
                //
                $order_goods_info = M('order_goods_info')->where(array('order_goods_id'=>$info['order_goods_id']))->find();
                $goods_number = $order_goods_info['goods_number'];
                if($discount_value || $real_pay_coupon_value || $real_pay_coupon_code_value || $real_pay_point || $real_pay_storage_money){
                   if($goods_number > 1){
                     $use_ids = M('member_use_code')->where(array('order_goods_id'=>$info['order_goods_id']))->order('create_time asc')->getField('use_id',true);
                     foreach($use_ids as $j=>$use_id){
                         if($info['use_id'] == $use_id) $index = $j + 1;
                     }
                     if($index != $goods_number){
                         $real_pay_coupon_value = price_format($real_pay_coupon_value/$goods_number);
                         $real_pay_coupon_code_value = price_format($real_pay_coupon_code_value/$goods_number);
                         $real_pay_point = price_format($real_pay_point/$goods_number);
                         $real_pay_storage_money = price_format($real_pay_storage_money/$goods_number);
                         $discount_value = price_format($discount_value/$goods_number);
                     }else{
                         $real_pay_coupon_value -= price_format($real_pay_coupon_value/$goods_number) * ($goods_number - 1);
                         $real_pay_coupon_code_value -= price_format($real_pay_coupon_code_value/$goods_number) * ($goods_number - 1);
                         $real_pay_point -= price_format($real_pay_point/$goods_number) * ($goods_number - 1);
                         $real_pay_storage_money -= price_format($real_pay_storage_money/$goods_number) * ($goods_number - 1);
                         $discount_value -= price_format($discount_value/$goods_number) * ($goods_number - 1);
                     }
                   }
                }
                //大礼包
                if($order_goods_info['order_goods_type'] == 6){
                    $relation_goods_list = json_decode($order_goods_info['relation_goods'], true);
                    $sy_coupon_value = $real_pay_coupon_value;
                    $use_coupon_value = $real_pay_coupon_value;
                    $sy_coupon_code_value = $real_pay_coupon_code_value;
                    $use_coupon_code_value = $real_pay_coupon_code_value;
                    $sy_point = $real_pay_point;
                    $use_point = $real_pay_point;
                    $sy_storage_money = $real_pay_storage_money;
                    $use_storage_money = $real_pay_storage_money;
                    $sy_discount_value = $discount_value;
                    $use_discount_value = $discount_value;
                    foreach($relation_goods_list as $j=>$relation_goods){
                         //使用的优惠券
                         $real_pay_coupon_value = price_format($relation_goods['goods_price']/$order_goods_info['goods_price']) * $use_coupon_value;
                         if($sy_coupon_value >= $real_pay_coupon_value) $sy_coupon_value = $sy_coupon_value - $real_pay_coupon_value;
                         else $real_pay_coupon_value = $sy_coupon_value;
                         //使用的优惠码
                         $real_pay_coupon_code_value = price_format($relation_goods['goods_price']/$order_goods_info['goods_price']) * $use_coupon_code_value;
                         if($sy_coupon_value >= $real_pay_coupon_code_value) $sy_coupon_code_value = $sy_coupon_code_value - $real_pay_coupon_code_value;
                         else $real_pay_coupon_code_value = $sy_coupon_code_value;
                         //使用的积分
                         $real_pay_point = price_format($relation_goods['max_integral']/$order_goods_info['max_integral']) * $use_point;
                         if($sy_point >= $real_pay_point) $sy_point = $sy_point - $real_pay_point;
                         else $real_pay_point = $sy_point;
                         //使用的储值卡
                         $real_pay_storage_money = price_format($relation_goods['goods_price']/$order_goods_info['goods_price']) * $use_storage_money;
                         if($sy_storage_money >= $real_pay_storage_money) $sy_storage_money = $sy_storage_money - $real_pay_storage_money;
                         else $real_pay_storage_money = $sy_storage_money;
                         //使用的总优惠
                         $discount_value = price_format($relation_goods['goods_price']/$order_goods_info['goods_price']) * $use_coupon_value;
                         if($sy_discount_value >= $discount_value) $sy_discount_value = $sy_discount_value - $discount_value;
                         else $discount_value = $sy_discount_value;
                        if($relation_goods['goods_code'] == $info['goods_code']){
                            $cur_goods_info = $relation_goods;
                            break;
                        }
                    }
                   $integral = $order_goods_info['integral'];
                   $max_integral = $cur_goods_info['max_integral'];
                   $goods_price = $cur_goods_info['goods_price'];
                }else{
                    $integral = $order_goods_info['integral'];
                    $max_integral = $order_goods_info['max_integral'];
                    $goods_price = $order_goods_info['goods_price'];
                }
                 //计算应付金额+应付积分
                if($integral){
                  $pay_cash = $goods_price;
                  $pay_point = $max_integral;
                  $real_pay_cash = $pay_cash - $discount_value;
                }else{
                   $pay_cash = $goods_price;
                   $real_pay_cash = $pay_cash - $discount_value - ($real_pay_point/C('INTEGRAL_RATE')['point']) * C('INTEGRAL_RATE')['rmb'];
                }
                $order_type = 1;//资源管理平台
            }else{
                $order_cur_id = $info['order_id'];
                $create_time = $info['create_time'];
                $pay_time = $info['create_time'];
                $order_type = 2;//其它平台
                $integral = $goods_info['integral'];
                $max_integral = $goods_info['max_integral'];
                $goods_price = $goods_info['goods_price_pc'];
            }
            if($info['order_goods_id']) $exist_count = M('goods_use_detail_info')->where(array('order_goods_id'=>$info['order_goods_id']))->count();
            else $exist_count = M('goods_use_detail_info')->where(array('use_code'=>$info['use_code']))->count();
            if(!$exist_count){
                $order_info = M('order_base_info')->where(array('order_id'=>$info['order_id']))->field('order_cur_id,shops_code,pay_time,create_time,order_status,order_id')->find();
                $shops_code = M('goods_base_info')->where(array('goods_code'=>$info['goods_code']))->getField('shops_code');
                $goods_use_detail_arr[] = array(
                    'order_cur_id'=>$order_cur_id,
                    'shops_code'=>$shops_code,
                    'order_id'=>$info['order_id'],
                    'order_goods_id'=>$info['order_goods_id'],
                    'goods_code'=>$info['goods_code'],
                    'goods_name'=>$goods_info['goods_name'],
                    'integral'=>$integral,
                    'max_integral'=>$max_integral,
                    'goods_price'=>$goods_price,
                    'goods_number'=>1,
                    'delivery_type'=>2,
                    'pay_cash'=>$pay_cash,
                    'pay_point'=>$pay_point,
                    'real_pay_cash'=>$real_pay_cash,
                    'real_pay_point'=>$real_pay_point,
                    'real_pay_storage_money'=>$real_pay_storage_money,
                    'real_pay_coupon_value'=>$real_pay_coupon_value,
                    'use_coupon'=>$use_coupon,
                    'use_coupon_code'=>$use_coupon_code,
                    'use_status'=>1,
                    'create_time'=>$create_time,
                    'update_time'=>$create_time,
                    'member_id'=>$info['member_id'],
                    'pay_time'=>$pay_time,
                    'order_type'=>$order_type,
                    'use_code'=>$info['use_code'],
                    'order_status'=>$order_info['order_status'],
                    'payment_type'=>$payment_type,
                    'coupon_category_type'=>$coupon_category_type
                );
            }
        }
        M('goods_use_detail_info')->addAll($goods_use_detail_arr);
        $last_date = $list[count($list) - 1]['create_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->use_code_order(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('create_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'use_code_order-info.log','','w'); 
            return true;
	}
    }
    //快递订单商品
    private function crm_goods_order($pageIndex=1, $page_size=10){
        $where = $this->get_crm_goods_order_where();
        $count = M('order_goods_info as order_goods,tm_goods_base_info as goods')->where('order_goods.goods_code = goods.goods_code')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $order_goods_list = M('order_goods_info as order_goods,tm_goods_base_info as goods')->where('order_goods.goods_code = goods.goods_code')->where($where)->field('order_goods.*')->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('order_goods.update_time asc')->select();
        foreach($order_goods_list as $k=>$order_goods_info){
            $order_info = M('order_base_info')->where(array('order_id'=>$order_goods_info['order_id']))->find();
            $payment_type = M('order_payment_info')->where(array('order_id'=>$order_info['order_id']))->getField('payment_type');
            if(!$payment_type) $payment_type = 0;
            $coupon_category_type = 0;
            $discount_value=0.00;$pay_cash = 0.00;$pay_point=0;$real_pay_cash=0.00;$real_pay_point = 0;$real_pay_storage_money = 0.00;$real_pay_coupon_value = 0.00;$real_pay_coupon_code_value=0.00;$use_coupon = '';$use_coupon_code = '';
            $order_goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id']))->select();
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
                $exist_count = M('goods_use_detail_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id']))->count();
                if(!$exist_count){
                    $goods_use_detail_arr[] = array(
                        'order_cur_id'=>$order_info['order_cur_id'],
                        'shops_code'=>$order_goods_info['shops_code'],
                        'order_id'=>$order_goods_info['order_id'],
                        'order_goods_id'=>$order_goods_info['order_goods_id'],
                        'goods_code'=>$order_goods_info['goods_code'],
                        'goods_name'=>$order_goods_info['goods_name'],
                        'integral'=>$order_goods_info['integral'],
                        'max_integral'=>$order_goods_info['max_integral'],
                        'goods_price'=>$order_goods_info['goods_price'],
                        'goods_number'=>$order_goods_info['goods_number'],
                        'delivery_type'=>1,
                        'pay_cash'=>$pay_cash,
                        'pay_point'=>$pay_point,
                        'real_pay_cash'=>$real_pay_cash,
                        'real_pay_point'=>$real_pay_point,
                        'real_pay_storage_money'=>$real_pay_storage_money,
                        'real_pay_coupon_value'=>$real_pay_coupon_value,
                        'real_pay_coupon_code_value'=>$real_pay_coupon_code_value,
                        'use_coupon'=>$use_coupon,
                        'use_coupon_code'=>$use_coupon_code,
                        'use_status'=>1,
                        'create_time'=>$order_goods_info['create_time'],
                        'update_time'=>$order_goods_info['create_time'],
                        'member_id'=>$order_info['member_id'],
                        'pay_time'=>$order_info['pay_time'],
                        'order_type'=>1,
                        'use_code'=>'',
                        'order_status'=>$order_info['order_status'],
                        'payment_type'=>$payment_type,
                        'coupon_category_type'=>$coupon_category_type
                    );
                }
            }
        }
        //添加到订单详细商品表中
        $all_bool = M('goods_use_detail_info')->addAll($goods_use_detail_arr);
        $last_date = $order_goods_list[count($order_goods_list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->crm_goods_order(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'crm_goods_order-info.log','','w'); 
            return true;
	}
    }
    
     //快递订单商品,发货
    private function express_order_use($pageIndex=1, $page_size=10){
        $where = $this->get_express_use_where();
        $count = M('order_base_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('order_base_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->field('order_id,update_time')->order('update_time asc')->select();
        foreach($list as $k=>$info){
            $data = array('use_status'=>2,'use_time'=>$info['update_time'],'update_time'=>date('Y-m-d H:i:s'));
            M('goods_use_detail_info')->where(array('order_id'=>$info['order_id'],'use_status'=>1))->data($data)->save();
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->express_order_use(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'express_use-info.log','','w'); 
            return true;
	}
    }
    
     //快递订单商品,发货
    private function member_use_code_use($pageIndex=1, $page_size=10){
        $where = $this->get_use_code_use_where();
        $count = M('member_use_code')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('member_use_code')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->field('use_code,update_time')->order('update_time asc')->select();
        foreach($list as $k=>$info){
            $data = array('use_status'=>2,'use_time'=>$info['update_time'],'update_time'=>date('Y-m-d H:i:s'));
            M('goods_use_detail_info')->where(array('use_code'=>$info['use_code'],'use_status'=>1))->data($data)->save();
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->member_use_code_use(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'use_code_use-info.log','','w');
            return true;
	}
    }
    
    //快递订单商品
    private function other_goods_order($pageIndex=1, $page_size=10){
        $where = $this->get_other_goods_order_where();
        $count = M('order_goods_info as order_goods,tm_goods_base_info as goods')->where('order_goods.goods_code = goods.goods_code')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $order_goods_list = M('order_goods_info as order_goods,tm_goods_base_info as goods')->where('order_goods.goods_code = goods.goods_code')->where($where)->field('order_goods.*')->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('order_goods.update_time asc')->select();
        foreach($order_goods_list as $k=>$order_goods_info){
            $order_info = M('order_base_info')->where(array('order_id'=>$order_goods_info['order_id']))->find();
            $payment_type = M('order_payment_info')->where(array('order_id'=>$order_info['order_id']))->getField('payment_type');
            if(!$payment_type) $payment_type = 0;
            $coupon_category_type = 0;
            $discount_value=0.00;$pay_cash = 0.00;$pay_point=0;$real_pay_cash=0.00;$real_pay_point = 0;$real_pay_storage_money = 0.00;$real_pay_coupon_value = 0.00;$real_pay_coupon_code_value=0.00;$use_coupon = '';$use_coupon_code = '';
            $order_goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id']))->select();
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
                $exist_count = M('goods_use_detail_info')->where(array('order_goods_id'=>$order_goods_info['order_goods_id']))->count();
                if(!$exist_count){
                    $goods_use_detail_arr[] = array(
                        'order_cur_id'=>$order_info['order_cur_id'],
                        'shops_code'=>$order_goods_info['shops_code'],
                        'order_id'=>$order_goods_info['order_id'],
                        'order_goods_id'=>$order_goods_info['order_goods_id'],
                        'goods_code'=>$order_goods_info['goods_code'],
                        'goods_name'=>$order_goods_info['goods_name'],
                        'integral'=>$order_goods_info['integral'],
                        'max_integral'=>$order_goods_info['max_integral'],
                        'goods_price'=>$order_goods_info['goods_price'],
                        'goods_number'=>$order_goods_info['goods_number'],
                        'delivery_type'=>1,
                        'pay_cash'=>$pay_cash,
                        'pay_point'=>$pay_point,
                        'real_pay_cash'=>$real_pay_cash,
                        'real_pay_point'=>$real_pay_point,
                        'real_pay_storage_money'=>$real_pay_storage_money,
                        'real_pay_coupon_value'=>$real_pay_coupon_value,
                        'real_pay_coupon_code_value'=>$real_pay_coupon_code_value,
                        'use_coupon'=>$use_coupon,
                        'use_coupon_code'=>$use_coupon_code,
                        'use_status'=>1,
                        'create_time'=>$order_goods_info['create_time'],
                        'update_time'=>$order_goods_info['create_time'],
                        'member_id'=>$order_info['member_id'],
                        'pay_time'=>$order_info['pay_time'],
                        'order_type'=>1,
                        'use_code'=>'',
                        'order_status'=>$order_info['order_status'],
                        'payment_type'=>$payment_type,
                        'coupon_category_type'=>$coupon_category_type
                    );
                }
            }
        }
        //添加到订单详细商品表中
        $all_bool = M('goods_use_detail_info')->addAll($goods_use_detail_arr);
        $last_date = $order_goods_list[count($order_goods_list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->other_goods_order(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'other_goods_order-info.log','','w'); 
            return true;
	}
    }
    /*----------------------------------------------------受保护方法------------------------------------------------*/
    private function get_order_where(){
        $where = array();
        //未支付
        $where['pay_status'] = 12;
        $where['delivery_type'] = 1;//邮寄
        $where['shops_code'] = array('neq','');
        $modifytime = \Org\My\MyLog::read_log('pay_time', 'goods_sale-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['pay_time'] = array('gt',$modifytime);
        return $where;
    }
    
    
    private function get_use_code_where(){
        $where = array();
        $modifytime = \Org\My\MyLog::read_log('create_time', 'use_code_order-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['create_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_express_use_where(){
        $where['order_status'] = 4;
        $where['delivery_type'] = 1;
        $modifytime = \Org\My\MyLog::read_log('update_time', 'express_use-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_use_code_use_where(){
        $where['status'] = 20;
        $modifytime = \Org\My\MyLog::read_log('update_time', 'use_code_use-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_crm_goods_order_where(){
        $where = array();
        $where['goods.goods_type'] = array('in',array(3,4,5,6,7));
        $where['order_goods.order_goods_status'] = array('in',array(4,8));
        $modifytime = \Org\My\MyLog::read_log('update_time', 'crm_goods_order-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['order_goods.update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_other_goods_order_where(){
        $where = array();
        $where['goods.goods_type'] = 8;
        $where['order_goods.order_goods_status'] = array('in',array(4,8));
        $modifytime = \Org\My\MyLog::read_log('update_time', 'other_goods_order-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['order_goods.update_time'] = array('gt',$modifytime);
        return $where;
    }
    
}
