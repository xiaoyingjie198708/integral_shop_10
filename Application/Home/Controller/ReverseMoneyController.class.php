<?php


namespace Home\Controller;

class ReverseMoneyController extends BaseController{
    
    public function index(){
        $mod = M('reverse_money_info');
        $where = $this->get_where();
        $page = new \Org\My\Page($mod->where($where)->count(), 10);
        $list = $mod->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('update_time desc')->select();
        $reverse_order_ids = getFieldsByKey($list, 'reverse_order_id');
        $reverse_order_list = M('reverse_order_base_info')->where(array('reverse_order_id'=>array('in',$reverse_order_ids)))->getField('reverse_order_id,order_cur_id,forward_order_cur_id,member_id',true);
        foreach ($list as $k=>$info){
            $reverse_info = $reverse_order_list[$info['reverse_order_id']];
            $info['order_cur_id'] = $reverse_info['order_cur_id'];
            $info['forward_order_cur_id'] = $reverse_info['forward_order_cur_id'];
            $info['member_account'] = M('user_member_base_info')->where(array('member_id'=>$reverse_info['member_id']))->getField('member_account');
            $info['payment_type'] = M('order_payment_info')->where(array('order_id'=>$info['forward_order_id']))->getField('payment_type');
            $list[$k] = $info;
        }
        $this->search($this->get_search());
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->display('index'); 
    }
    
    
    //快捷查看
    public function quick_view() {
        $this->assign('order_id', I('order_id', ''));
        $contant = $this->fetch('quick_view');
        $this->oldAjaxReturn(0, $contant, 1);
    }
    
    //切换标签
    public function toggle_label($order_id, $type) {
        $info = D('ReverseOrder')->where(array('reverse_order_id'=>$order_id))->find();
        $this->assign('info', $info);
        $reverse_money = M('reverse_money_info')->where(array('reverse_order_id'=>$info['reverse_order_id']))->find();
        $this->assign('reverse_money', $reverse_money);
        if ($type == 2) {
            $order_goods_list = M('reverse_order_goods_info')->where(array('reverse_order_id'=>$order_id))->select();
            $shops_codes = getFieldsByKey($order_goods_list, 'shops_code');
            $shops_names = D('Shops')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
            foreach ($order_goods_list as $k=>$order_goods){
                $forward_order_goods = M('order_goods_info')->where(array('order_goods_id'=>$order_goods['forward_order_goods_id']))->find();
                //数量的权重，已退的数量/购买的数量
               $number_weight = $order_goods['numbers']/$forward_order_goods['goods_number'];
                if($forward_order_goods['integral']){
                    if($forward_order_goods['goods_price'] && ($forward_order_goods['max_integral'] >0))  $goods_price = '￥'.$forward_order_goods['goods_price'] .' + ' .$forward_order_goods['max_integral'].'积分';
                    if(!$forward_order_goods['goods_price'] && $forward_order_goods['max_integral']) $goods_price = $forward_order_goods['max_integral'].'积分';
                    if(($forward_order_goods['goods_price']>0) && !$forward_order_goods['max_integral']) $goods_price = '￥'.$forward_order_goods['goods_price'];
                }else{
                    $goods_price = '￥'.$forward_order_goods['goods_price'];
                }
                $goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods['forward_order_goods_id']))->select();
                foreach($goods_discount_list as $j=>$goods_discount){
                    //优惠券
                    if($goods_discount['discount_type'] == 3){
                        $order_goods_list[$k]['coupon_txt'] = $goods_discount['discount_code'].'/'.($goods_discount['discount_value'] *$number_weight);
                    }
                    //优惠码
                    if($goods_discount['discount_type'] == 4){
                        $order_goods_list[$k]['coupon_code_txt'] = $goods_discount['discount_code'].'/'.($goods_discount['discount_value']*$number_weight);
                    }
                    //积分
                    if($goods_discount['discount_type'] == 5){
                        $order_goods_list[$k]['use_integral'] = intval($goods_discount['discount_code'])*$number_weight;
                    }
                    //优惠码
                    if($goods_discount['discount_type'] == 6){
                        $order_goods_list[$k]['storage_money'] = $goods_discount['discount_value']*$number_weight;
                    }
                }
                $order_goods_list[$k]['goods_price'] = $goods_price;
                $order_goods_list[$k]['shops_name'] = $shops_names[$order_goods['shops_code']];
            }
            $this->assign('order_goods_list', $order_goods_list);
        }elseif($type == 3){
            $comment_list = M('reverse_order_comment')->where(array('order_id'=>$order_id))->select();
            $user_ids = getFieldsByKey($comment_list, 'user_id');
            $usernames = M('admin_user')->where(array('id'=>array('in',$user_ids)))->getField('id,username',true);
            foreach($comment_list as $k=>$comment) $comment_list[$k]['user_name'] = $usernames[$comment['user_id']];
            $this->assign('comment_list', $comment_list);
        }
        $this->assign('type', $type);
        $contant = $this->fetch('toggle_label');
        $this->oldAjaxReturn(0, $contant, 1);
    }
    
    //取消订单
    public function get_update_order($order_id,$order_status){
        $this->assign('order_id', $order_id);
        $this->assign('order_status', $order_status);
        $content = $this->fetch('update_order');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    public function update_order(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        $order_status = I('order_status',0,'intval');
        $reason = I('reason','','trim,htmlspecialchars');
        if(!$reason) $this->oldAjaxReturn (0, '请输入备注', 0);
        $reverse_money_info = M('reverse_money_info')->where(array('reverse_order_id'=>$order_id))->find();
        M('reverse_money_info')->startTrans();
        $data = array(
            'order_id'=>$order_id,
            'user_id'=>  session('admin_uid'),
            'comment'=>$reason,
            'create_time'=>  date('Y-m-d H:i:s')
        );
       $comment_bool = M('reverse_order_comment')->data($data)->add();
        //审核通过
        if($order_status == 1){
            $reverse_status = 2;
            $order_status = 50;
            $this->reverse_goods_money($order_id, $reverse_money_info);
        }else{
            $reverse_status = 5;
            $order_status = 70;
        }
        $reverse_money_bool = M('reverse_money_info')->where(array('reverse_order_id'=>$order_id))->data(array('reverse_status'=>$reverse_status,'update_time'=>  date('Y-m-d H:i:s')))->save();
        $reverse_bool = M('reverse_order_base_info')->where(array('reverse_order_id'=>$order_id))->data(array('order_status'=>$order_status,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($comment_bool && $reverse_bool && $reverse_money_bool){
            M('reverse_money_info')->commit();
            $this->oldAjaxReturn(0, '提交审核成功', 1);
        }else{
            M('reverse_money_info')->rollback();
            $this->oldAjaxReturn(0, '提交审核失败', 0);
        }
    }
    
    private function get_search(){
        return array(
            'url' => U('ReverseMoney/index'),
            'main' => array(
                array(
                    'name' => 'reverse_status',
                    'show_name' => '退款状态',
                    'tip' => '请选择退款状态',
                    'select' => array(4=>'提交退款',2=>'已退款',5=>'审核拒绝'),
                    'type' => 'select'
                )
            )
        ); 
    }
    
    private function get_where(){
        $reverse_status = I('reverse_status',0,'intval');
        if($reverse_status) $where['reverse_status'] = $reverse_status;
        else $where['reverse_status'] = array('gt',1);
        return $where;
    }
    
    /*
     * 退还到具体商品的金额,$data['reverse_value'],$data['reverse_point'],$data['reverse_storage_money']
     */
    private function reverse_goods_money($order_id,$data){
        $reverse_order_goods_list = M('reverse_order_goods_info')->where(array('reverse_order_id'=>$order_id))->select();
        $reverse_money_goods_list = array();$reverse_point_goods_list = array();$reverse_storage_money_goods_list = array();
        $total_money=0;$total_point=0;$total_storage_money=0;
        foreach($reverse_order_goods_list as $k=>$reverse_order_goods){
            $reverse_number = $reverse_order_goods['numbers'];
            $goods_use_detail_list = M('goods_use_detail_info')->where(array('order_goods_id'=>$reverse_order_goods['forward_order_goods_id']))->select();
            //大礼包
            if($reverse_order_goods['order_goods_type'] == 6){
                $relation_goods = M('order_goods_info')->where(array('order_goods_id'=>$reverse_order_goods['forward_order_goods_id']))->getField('relation_goods');
                $relation_goods = json_decode($relation_goods,true);
                foreach($relation_goods as $j=>$temp_relation_goods){
                    $temp_reverse_number = $reverse_number * $temp_relation_goods['number'];
                    foreach ($goods_use_detail_list as $i=>$goods_use_detail){
                        if($temp_relation_goods['goods_code'] == $goods_use_detail['goods_code']){
                            if($temp_reverse_number >= $goods_use_detail['goods_number']){
                                $goods_use_detail['reverse_number'] = $goods_use_detail['goods_number'];
                                $temp_reverse_number -= $goods_use_detail['goods_number'];
                            }elseif($reverse_number > 0){
                                $goods_use_detail['reverse_number'] = $temp_reverse_number;
                            }else{
                                break;
                            }
                            if($goods_use_detail['real_pay_cash']){
                                $reverse_money_goods_list[] = $goods_use_detail;
                                $total_money += $goods_use_detail['real_pay_cash'];
                            }
                            if($goods_use_detail['real_pay_point']){
                                $reverse_point_goods_list[] = $goods_use_detail;
                                $total_point += $goods_use_detail['real_pay_point'];
                            }
                            if($goods_use_detail['real_pay_storage_money']){
                                $reverse_storage_money_goods_list[] = $goods_use_detail;
                                $total_storage_money += $goods_use_detail['real_pay_storage_money'];
                            }
                        }
                    }
                }
            }else{
                foreach($goods_use_detail_list as $j=>$goods_use_detail){
                    if($reverse_number >= $goods_use_detail['goods_number']){
                        $goods_use_detail['reverse_number'] = $goods_use_detail['goods_number'];
                        $reverse_number -= $goods_use_detail['goods_number'];
                    }elseif($reverse_number > 0){
                        $goods_use_detail['reverse_number'] = $reverse_number;
                    }else{
                        break;
                    }
                    if($goods_use_detail['real_pay_cash']){
                        $reverse_money_goods_list[] = $goods_use_detail;
                        $total_money += $goods_use_detail['real_pay_cash'];
                    }
                    if($goods_use_detail['real_pay_point']){
                        $reverse_point_goods_list[] = $goods_use_detail;
                        $total_point += $goods_use_detail['real_pay_point'];
                    }
                    if($goods_use_detail['real_pay_storage_money']){
                        $reverse_storage_money_goods_list[] = $goods_use_detail;
                        $total_storage_money += $goods_use_detail['real_pay_storage_money'];
                    }
                }
            }
        }
        //需要退还人民币
        if($data['reverse_value']){
            $total_number = count($reverse_money_goods_list);
            $yt_money = 0;
            foreach($reverse_money_goods_list as $k=>$reverse_goods){
                if($k == ($total_number - 1)){
                    $reverse_cash = $data['reverse_value'] - $yt_money;
                }else{
                    $reverse_cash = price_format($reverse_goods['real_pay_cash']/$total_money) * $data['reverse_value'];
                    $yt_money += $reverse_cash;
                }
                M('goods_use_detail_info')->where(array('id'=>$reverse_goods['id']))->data(array('use_status'=>3,'reverse_cash'=>$reverse_cash,'update_time'=>  date('Y-m-d H:i:s')))->save();
            }
        }
        //需要退还积分
        if($data['reverse_point']){
            $total_number = count($reverse_point_goods_list);
            $yt_point = 0;
            foreach($reverse_point_goods_list as $k=>$reverse_goods){
                if($k == ($total_number - 1)){
                    $real_pay_point = $data['reverse_point'] - $yt_point;
                }else{
                    $real_pay_point = price_format($reverse_goods['real_pay_point']/$total_point) * $data['reverse_point'];
                    $yt_point += $real_pay_point;
                }
                M('goods_use_detail_info')->where(array('id'=>$reverse_goods['id']))->data(array('use_status'=>3,'real_pay_point'=>$real_pay_point,'update_time'=>  date('Y-m-d H:i:s')))->save();
            }
        }
        //需要退还储值卡
        if($data['reverse_storage_money']){
            $total_number = count($reverse_storage_money_goods_list);
            $yt_storage_money = 0;
            foreach($reverse_storage_money_goods_list as $k=>$reverse_goods){
                if($k == ($total_number - 1)){
                    $real_pay_storage_money = $data['reverse_storage_money'] - $yt_storage_money;
                }else{
                    $real_pay_storage_money = price_format($reverse_goods['real_pay_storage_money']/$total_storage_money) * $data['reverse_storage_money'];
                    $yt_storage_money += $real_pay_storage_money;
                }
                M('goods_use_detail_info')->where(array('id'=>$reverse_goods['id']))->data(array('use_status'=>3,'real_pay_storage_money'=>$real_pay_storage_money,'update_time'=>  date('Y-m-d H:i:s')))->save();
            }
        }
    }
    
}
