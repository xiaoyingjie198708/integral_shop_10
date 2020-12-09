<?php


namespace Home\Controller;

/**
 * 反向订单
 *
 */
class ReverseOrderController extends BaseController{
    
    public function index(){
        $where = $this->order_list_where();
        $page = new \Org\My\Page(D('ReverseOrder')->where($where)->count(), 10);
        $this->assign('page', $page->show());
        $limit = $page->firstRow . ',' . $page->listRows;
        $order_list = D('ReverseOrder')->where($where)->limit($limit)->order('create_time desc')->select();
        $shops_codes = getFieldsByKey($order_list, 'shops_code');
        $shops_names = D('Shops')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        //
        $member_ids = getFieldsByKey($order_list, 'member_id');
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        foreach($order_list as $k=>$order_info){
            $order_list[$k]['shops_name'] = $shops_names[$order_info['shops_code']];
            $order_list[$k]['member_account'] = $member_accounts[$order_info['member_id']];
        }
        $this->search($this->order_list_search());
        $this->assign('order_list', $order_list);
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
    
    //获取添加备注
    public function get_order_comment($order_id){
        $this->assign('order_id', $order_id);
        $contant = $this->fetch('comment');
        $this->oldAjaxReturn(0, $contant, 1);
    }
    
    //添加备注
    public function add_comment(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        $comment = I('order_comment','','trim,htmlspecialchars');
        $data = array(
            'order_id'=>$order_id,
            'user_id'=>  session('admin_uid'),
            'comment'=>$comment,
            'create_time'=>  date('Y-m-d H:i:s')
        );
       $add_bool = M('reverse_order_comment')->data($data)->add();
       if($add_bool) $this->oldAjaxReturn (0, '添加成功', 1);
       else $this->oldAjaxReturn (0, '添加失败', 0);
    }
    
    //取消订单
    public function get_update_order($order_id,$order_status){
        $this->assign('order_id', $order_id);
        $this->assign('order_status', $order_status);
        $content = $this->fetch('update_order');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    //取消订单
    public function update_order(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        $order_status = I('order_status',0,'intval');
        $reason = I('reason','','trim,htmlspecialchars');
        if(!$reason) $this->oldAjaxReturn (0, '请输入备注', 0);
        $mod = D('ReverseOrder');
        $where = array('reverse_order_id'=>$order_id);
        $order_info = $mod->where($where)->find();
        if($order_info['order_status'] != 10) $this->oldAjaxReturn (0, '该订单不能操作', 0);
        $mod->startTrans();
        $order_bool = $mod->where($where)->data(array('order_status'=>$order_status,'update_time'=>  date('Y-m-d H:i:s')))->save();
        $data = array(
            'order_id'=>$order_id,
            'user_id'=>  session('admin_uid'),
            'comment'=>$reason,
            'create_time'=>  date('Y-m-d H:i:s')
        );
       $comment_bool = M('reverse_order_comment')->data($data)->add();
       //审核通过退货
       $reverse_money_bool = true;
       if($order_info['order_type'] == 2 && $order_status == 30){
           $reverse_order_goods_list = M('reverse_order_goods_info')->where(array('reverse_order_id'=>$order_info['reverse_order_id']))->getField('forward_order_goods_id,numbers',true);
           $order_goods_ids = array_keys($reverse_order_goods_list);
           $order_goods_list = M('order_goods_info')->where(array('order_goods_id'=>array('in',$order_goods_ids)))->select();
           foreach($order_goods_list as $k=>$order_goods){
               $reverse_numbers = $reverse_order_goods_list[$order_goods['order_goods_id']];
               //数量的权重，已退的数量/购买的数量
               $number_weight = $reverse_numbers/$order_goods['goods_number'];
               //固定积分
               if($order_goods['integral']){
                   if($order_goods['goods_price'] > 0){
                       $discount_values = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods['order_goods_id'],'discount_type'=>array('in',array(3,4))))->getField('discount_value',true);
                       if($discount_values) $forward_value += $order_goods['goods_price'] * ($order_goods['goods_number'] * $number_weight) - array_sum ($discount_values);
                       else $forward_value += $order_goods['goods_price'] * ($order_goods['goods_number'] * $number_weight);
                   }
                   $forward_point += $order_goods['max_integral'] * ($order_goods['goods_number'] * $number_weight);
               }else{
                    $discount_values = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods['order_goods_id']))->getField('discount_value',true);
                    if($discount_values) $forward_value += $order_goods['goods_price'] * ($order_goods['goods_number'] * $number_weight) - array_sum ($discount_values);
                    else $forward_value += $order_goods['goods_price'] * ($order_goods['goods_number'] * $number_weight);
                    $use_point = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods['order_goods_id'],'discount_type'=>5))->getField('discount_code',true);
                    if($use_point) $forward_point += $use_poin * $number_weightt;
               }
                //储值卡金额
                $forward_storage_money = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods['order_goods_id'],'discount_type'=>6))->getField('discount_value');
                if($forward_storage_money) $forward_total_storage_money += $forward_storage_money * $number_weight;
           }
           $forward_total_storage_money = floatval($forward_total_storage_money);
           $reverse_money_data = array();
           $reverse_money_data['reverse_order_id'] = $order_info['reverse_order_id'];
           $reverse_money_data['forward_order_id'] = $order_info['forward_order_id'];
           $reverse_money_data['forward_value'] = $forward_value - $forward_total_storage_money;
           $reverse_money_data['forward_point'] = intval($forward_point);
           $reverse_money_data['forward_storage_money'] = $forward_total_storage_money;
           $reverse_money_data['create_time'] = date('Y-m-d H:i:s');
           $reverse_money_data['update_time'] = date('Y-m-d H:i:s');
           $reverse_money_bool = M('reverse_money_info')->data($reverse_money_data)->add();
           $this->update_use_code($order_info['forward_order_id'], 1);
       }else{
           $this->update_use_code($order_info['forward_order_id'], 2);
       }
       if($order_bool && $comment_bool && $reverse_money_bool){
           $mod->commit();
           $this->oldAjaxReturn(0, '操作成功', 1);
       }else{
           $mod->rollback();
           $this->oldAjaxReturn(0, '操作失败', 0);
       }
    }
    
    //订单完成
    public function order_complete(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        M('reverse_order_base_info')->startTrans();
        $reverse_order_bool = M('reverse_order_base_info')->where(array('reverse_order_id'=>$order_id))->data(array('order_status'=>50,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($reverse_order_bool){
            M('reverse_order_base_info')->commit();
            $this->oldAjaxReturn(0, '操作成功', 1);
        }else{
            M('reverse_order_base_info')->rollback();
            $this->oldAjaxReturn(0, '操作失败', 0);
        }
    }
    
    
    //申请退换货
    public function get_reverse_money(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        $money_info = M('reverse_money_info')->where(array('reverse_order_id'=>$order_id))->find();
        $this->assign('money_info', $money_info);
        $content = $this->fetch('reverse_money');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    //退款
    public function reverse_money(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        $money_info = M('reverse_money_info')->where(array('reverse_order_id'=>$order_id))->find();
        $data['reverse_value'] = I('reverse_value',0.00,'floatval');
        $data['reverse_serial_number'] = I('reverse_serial_number','','trim,htmlspecialchars');
        $data['poundage'] = I('poundage',0.00,'floatval');
        $data['reverse_point'] = I('reverse_point',0,'intval');
        $data['reverse_storage_money'] = I('reverse_storage_money',0.00,'floatval');
        $data['comment'] = I('order_comment','','trim,htmlspecialchars');
        if($money_info['forward_value'] > 0){
            if(!$data['reverse_value']) $this->oldAjaxReturn ('reverse_value', '请输入退款金额', 0);
            if($money_info['forward_value'] < $data['reverse_value']) $this->oldAjaxReturn ('reverse_value', '退款金额不能大于支付的金额', 0);
            if(!$data['reverse_serial_number']) $this->oldAjaxReturn ('reverse_serial_number', '请输入退款流水号', 0);
        }
        if($money_info['forward_storage_money'] > 0){
            if(!$data['reverse_storage_money']) $this->oldAjaxReturn ('reverse_storage_money', '请输入退还储值卡金额', 0);
            if($money_info['forward_storage_money'] < $data['reverse_storage_money']) $this->oldAjaxReturn ('reverse_storage_money', '退还的储值卡金额不能大于使用的金额', 0);
        }
        if($money_info['forward_point'] > 0){
            if(!$data['reverse_point']) $this->oldAjaxReturn ('reverse_point', '请输入退还积分', 0);
            if($money_info['forward_point'] < $data['reverse_point']) $this->oldAjaxReturn ('reverse_point', '退还的积分不能大于使用的积分', 0);
        }
//        if($data['reverse_point'] > 0){
//            $reverse_order_info = M('reverse_order_base_info')->where(array('reverse_order_id'=>$order_id))->find();
//            //调用存储过程添加积分
//            $point_data['member_id'] = $reverse_order_info['member_id'];
//            $point_data['point'] = $data['reverse_point'];
//            $point_data['OrderId'] = $reverse_order_info['forward_order_cur_id'];
//            $result = changePoints($point_data);
//        }
//        if($data['reverse_storage_money'] > 0){
//            $reverse_order_info = M('reverse_order_base_info')->where(array('reverse_order_id'=>$order_id))->find();
//            //调用CRM退还储值卡金额
//            $money_data['MemberId'] = $reverse_order_info['member_id'];
//            $money_data['UseBalance'] = $data['reverse_storage_money'];
//            $money_data['OrderId'] = $reverse_order_info['forward_order_cur_id'];
//            $result = refundTicket($money_data);
//        }
        //提交退款
        $data['reverse_status'] = 4;
        $data['update_time'] = date('Y-m-d H:i:s');
        M('reverse_money_info')->startTrans();
        $reverse_money_bool = M('reverse_money_info')->where(array('reverse_order_id'=>$order_id))->data($data)->save();
        $reverse_order_bool = M('reverse_order_base_info')->where(array('reverse_order_id'=>$order_id))->data(array('order_status'=>60,'update_time'=>  date('Y-m-d H:i:s')))->save();
        //设置报表的退换货数据
        //$this->reverse_goods_money($order_id, $data);
        if($reverse_money_bool && $reverse_order_bool){
            M('reverse_money_info')->commit();
            $this->oldAjaxReturn(0, '提交退款成功', 1);
        }else{
            M('reverse_money_info')->rollback();
            $this->oldAjaxReturn(0, '提交退款失败', 0);
        }
    }
    /*-----------------------------------------------------------------受保护方法------------------------------------------------------------------*/
    //订单条件组合
    private function order_list_where() {
        //接收参数
        $order_cur_id = I('order_cur_id','','trim');//订单编号
        //搜索条件
        $create_start = I('create_start', '', 'trim'); //下单开始时间
        $create_end = I('create_end', '', 'trim'); //下单结束时间
        $order_status = I('order_status',0,'intval'); //订单状态
        $order_type = I('order_type',0,'intval'); //订单类型
        $member_id = I('member_id','','trim');
        $shops_code = I('shops_code','','trim');
        //定义条件数组
        $where = array();
        if($order_cur_id) $where['order_cur_id|forward_order_cur_id'] = array('like','%'.$order_cur_id.'%');
        //下单时间
        if ($create_start) $where['create_time'] = array('egt', $create_start);
        if ($create_end) $where['create_time'] = array('elt', $create_end);
        if ($create_end && $create_start) $where['create_time'] = array('BETWEEN', array($create_start, $create_end));
        //订单状态
        if($order_status) $where['order_status'] = $order_status;
        //订单类型
        if ($order_type) $where['order_type'] = $order_type;
        if($member_id) $where['member_id'] = array('like','%'.$member_id.'%');
        if($shops_code) $where['shops_code'] = $shops_code;
        return $where;
    }

    //订单搜索条件
    private function order_list_search() {
        $shops_list = M('shops_base_info')->where(array('shops_status'=>1))->order('create_time asc')->getField('shops_code,shops_name,create_time',true);
        return array(
            'url' => U('ReverseOrder/index'),
            'main' => array(
                array(
                    'name' => 'order_cur_id',
                    'show_name' => '订单编号',
                    'tip' => '请输入订单编号'
                ),
                array(
                    'name' => 'order_type',
                    'show_name' => '订单类型',
                    'tip' => '请选择订单类型',
                    'select' => C('reverse_order_type'),
                    'type' => 'select'
                ),
                array(
                    'name' => 'order_status',
                    'show_name' => '订单状态',
                    'tip' => '请选择订单状态',
                    'select' => C('reverse_order_status'),
                    'type' => 'select'
                )
            ),
            'other' => array(
                array(
                    'name' => 'member_id',
                    'show_name' => '会员账号',
                    'tip' => '请输入会员账号'
                ),
                array(
                    'name' => array('create_start', 'create_end'),
                    'show_name' => '申请时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                ),
                array(
                    'name' => 'shops_code',
                    'show_name' => '商家名称',
                    'tip' => '请选择商家名称',
                    'select' => $shops_list,
                    'type' => 'select'
                )
            )
        );
    }
    
    /**
     * 更新码
     * @param type $forward_order_id
     * @param type $type 1-无效，2-恢复可用
     */
    private function update_use_code($forward_order_id,$type){
        $change_status = 0;
        if($type == 2) $change_status = 10;
        M('member_use_code')->where(array('order_id'=>$forward_order_id,'status'=>30))->data(array('status'=>$change_status,'update_time'=>  date('Y-m-d H:i:s')))->save();
    }
}
