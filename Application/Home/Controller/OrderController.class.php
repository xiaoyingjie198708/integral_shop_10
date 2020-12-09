<?php


namespace Home\Controller;

class OrderController extends BaseController{
    
    public function index(){
        $where = $this->order_list_where();
        $page = new \Org\My\Page(D('Order')->where($where)->count(), 10);
        $this->assign('page', $page->show());
        $limit = $page->firstRow . ',' . $page->listRows;
        $order_list = D('Order')->where($where)->limit($limit)->order('create_time desc')->select();
        $member_ids = getFieldsByKey($order_list, 'member_id');
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        $order_ids = getFieldsByKey($order_list, 'order_id');
        $payment_serial_numbers = M('order_payment_serial_number_info')->where(array('order_id'=>array('in',$order_ids)))->getField('order_id,payment_serial_number',true);
        foreach($order_list as $k=>$order_info){
            $order_list[$k]['member_account'] = $member_accounts[$order_info['member_id']];
            $child_count =  D('Order')->where(array('parent_id'=>$order_info['order_id']))->count();
            if(!$order_info['shops_code'] && !$child_count) $order_list[$k]['base_order'] = true;
            else $order_list[$k]['base_order'] = false;
            $order_list[$k]['payment_serial_number'] = $payment_serial_numbers[$order_info['order_id']];
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
        $info = D('Order')->where(array('order_id'=>$order_id))->find();
        if(!$info['shops_code']) $info['child_order'] = false;
        else $info['child_order'] = true;
        if($info['province']) {
            $where['city_id'] = array('in',array($info['province'],$info['city'],$info['county']));
            $city_names = M('city')->where($where)->getField('city_id,city_name',true);
            $info['province'] = $city_names[$info['province']];
            $info['city'] = $city_names[$info['city']];
            $info['county'] = $city_names[$info['county']];
        }
        $info['shops_name'] = D('Shops')->where(array('shops_code'=>$info['shops_code']))->getField('shops_name');
        $pay_discount_list = M('order_pay_discount_info')->where(array('order_id'=>$order_id))->select();
        foreach($pay_discount_list as $k=>$pay_discount_info){
            if($pay_discount_info['discount_type'] == 2 && $pay_discount_info['discount_value']) $info['coupon_code_value'] = $pay_discount_info['discount_value'];
            if($pay_discount_info['discount_type'] == 3 && $pay_discount_info['discount_value']) $info['coupon_value'] = $pay_discount_info['discount_value'];
            if($pay_discount_info['discount_type'] == 4) $info['total_point'] = $pay_discount_info['discount_code'];
            if($pay_discount_info['discount_type'] == 5) $info['total_storage_money'] = $pay_discount_info['discount_value'];
        }
        //判断是否可以退换货
        if(in_array($info['order_status'], array(4,7,8,20))) $info['is_reverse'] = 1;
        if($info['pay_total_cash'] < 0) $info['pay_total_cash'] = 0.00;
        $this->assign('info', $info);
        if ($type == 2) {
            $order_goods_list = M('order_goods_info')->where(array('order_id'=>$order_id))->select();
            $shops_codes = getFieldsByKey($order_goods_list, 'shops_code');
            $shops_names = D('Shops')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
            foreach ($order_goods_list as $k=>$order_goods){
                $order_goods_list[$k]['total_value'] = $order_goods['goods_price'] * $order_goods['goods_number'];
                $order_goods_list[$k]['shops_name'] = $shops_names[$order_goods['shops_code']];
                $goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods['order_goods_id']))->select();
                $discount_values = getFieldsByKey($goods_discount_list, 'discount_value');
                $order_goods_list[$k]['total_discount_value'] = array_sum($discount_values);
                //展示价格
                $order_goods_list[$k]['show_price'] = '￥'.  price_format($order_goods['goods_price']);
                if($order_goods['integral'] && $order_goods['goods_price'] && $order_goods['max_integral'])  $order_goods_list[$k]['show_price'] = '￥'.$order_goods['goods_price'] .' + ' .$order_goods['max_integral'].'积分';
                if($order_goods['integral'] && !$order_goods['goods_price'] && $order_goods['max_integral']) $order_goods_list[$k]['show_price'] = $order_goods['max_integral'].'积分';
                if($order_goods['integral'] && $order_goods['goods_price'] && !$order_goods['max_integral']) $order_goods_list[$k]['show_price'] = '￥'.$order_goods['goods_price'];
                foreach($goods_discount_list as $j=>$goods_discount){
                    //优惠券
                    if($goods_discount['discount_type'] == 3){
                        $order_goods_list[$k]['coupon_txt'] = $goods_discount['discount_code'].'/'.$goods_discount['discount_value'];
                    }
                    //优惠码
                    if($goods_discount['discount_type'] == 4){
                        $order_goods_list[$k]['coupon_code_txt'] = $goods_discount['discount_code'].'/'.$goods_discount['discount_value'];
                    }
                    //积分
                    if($goods_discount['discount_type'] == 5){
                        $order_goods_list[$k]['use_integral'] = $goods_discount['discount_code'];
                    }
                    //优惠码
                    if($goods_discount['discount_type'] == 6){
                        $order_goods_list[$k]['storage_money'] = $goods_discount['discount_value'];
                    }
                }
            }
            $this->assign('order_goods_list', $order_goods_list);
        }elseif($type == 3 && $info['is_invoice']){
            $invoice_info = M('order_invoice_info')->where(array('order_id'=>$order_id))->find();
            if($invoice_info['user_id']) $invoice_info['user_name'] =  M('admin_user')->where(array('id'=>$invoice_info['user_id']))->getField('username');
            $this->assign('invoice_info', $invoice_info);
        }elseif ($type == 4 && $info['pay_status'] == 12) {
            $pay_list = M('order_payment_info')->where(array('order_id'=>$order_id))->select();
            $this->assign('pay_list', $pay_list);
        }elseif($type == 5){
            $comment_list = M('order_comment')->where(array('order_id'=>$order_id))->select();
            $user_ids = getFieldsByKey($comment_list, 'user_id');
            $usernames = M('admin_user')->where(array('id'=>array('in',$user_ids)))->getField('id,username',true);
            foreach($comment_list as $k=>$comment) $comment_list[$k]['user_name'] = $usernames[$comment['user_id']];
            $this->assign('comment_list', $comment_list);
        }else{
            $express_list = M('order_express')->where(array('order_id'=>$order_id))->select();
            $this->assign('express_list', $express_list);
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
       $add_bool = M('order_comment')->data($data)->add();
       if($add_bool) $this->oldAjaxReturn (0, '添加成功', 1);
       else $this->oldAjaxReturn (0, '添加失败', 0);
    }
    
    //确认开票
    public function makesure_invoice(){
        $mod = D('Order');
        $order_ids = I('order_ids','','trim,htmlspecialchars');
        $order_ids = explode(',', trim($order_ids, ','));
        $order_list = $mod->where(array('order_id'=>array('in',$order_ids)))->select();
        foreach($order_list as $k=>$order_info){
            if($order_info['pay_type'] == 1 && $order_info['pay_status'] != 12){
                $errors[] = '订单编号：'.$order_info['order_cur_id'].' 未支付，不能开发票';
                break;
            }
            if(!$order_info['pay_total_cash']){
                $errors[] = '订单编号：'.$order_info['order_cur_id'].' 没有支付现金，不能开发票';
                break;
            }
            if($order_info['invoice_status'] == 2){
                $errors[] = '订单编号：'.$order_info['order_cur_id'].' 已经开过发票，不能重复开发票';
                break;
            }
        }
        if($errors) $this->oldAjaxReturn (0, implode (' ;', $errors), 0);
        $mod->startTrans();
        $order_bool = $mod->where(array('order_id'=>array('in',$order_ids)))->data(array('invoice_status'=>2,'update_time'=>  date('Y-m-d H:i:s')))->save();
        $save_data = array('user_id'=>  session('admin_uid'),'open_time'=>date('Y-m-d H:i:s'),'invoice_status'=>2,'update_time'=>  date('Y-m-d H:i:s'));
        $invoice_bool = M('order_invoice_info')->where(array('order_id'=>array('in',$order_ids)))->data($save_data)->save();
        if($order_bool === FALSE ||  $invoice_bool === FALSE){
            $mod->rollback();
            $this->oldAjaxReturn(0, '开票失败', 0);
        }else{
            $mod->commit();
            $this->oldAjaxReturn(0, '开票成功', 1);
        }
    }
    
    //订单发货
    public function get_express_info($order_id){
        $this->assign('order_id', $order_id);
        $contant = $this->fetch('express_info');
        $this->oldAjaxReturn(0, $contant, 1);
    }
    
    //订单发货
    public function deliver_goods(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        $shipper_name = I('shipper_name','','trim,htmlspecialchars');
        $express_order = I('express_order','','trim,htmlspecialchars');
        if(!$shipper_name) $this->oldAjaxReturn ('shipper_name', '请选择快递公司', 0);
        if(!$express_order) $this->oldAjaxReturn ('express_order', '请输入快递单号', 0);
        $mod = D('Order');
        $where = array('order_id'=>$order_id);
        $order_info = $mod->where($where)->find();
        if($order_info['order_status'] > 4) $this->oldAjaxReturn (0, '该订单不能发货', 0);
        $mod->startTrans();
        $order_bool = $mod->where($where)->data(array('order_status'=>4,'update_time'=>  date('Y-m-d H:i:s')))->save();
        $order_goods_bool = M('order_goods_info')->where($where)->data(array('order_goods_status'=>4,'update_time'=>  date('Y-m-d H:i:s')))->save();
        $express_data = array(
            'order_id'=>$order_id,
            'shipper_name'=>$shipper_name,
            'express_order'=>$express_order,
            'create_time'=>  date('Y-m-d H:i:s')
        );
       $express_bool = M('order_express')->data($express_data)->add();
       if($order_bool && $order_goods_bool && $express_bool){
           $mod->commit();
           $this->oldAjaxReturn(0, '发货成功', 1);
       }else{
           $mod->rollback();
           $this->oldAjaxReturn(0, '发货失败', 0);
       }
    }
    
    //取消订单
    public function get_cancel_info($order_id){
        $this->assign('order_id', $order_id);
        $content = $this->fetch('cancel');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    //取消订单
    public function cancel(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        $reason = I('reason','','trim,htmlspecialchars');
        if(!$reason) $this->oldAjaxReturn ('reason', '请输入取消原因', 0);
        $mod = D('Order');
        $where = array('order_id'=>$order_id);
        $order_info = $mod->where($where)->find();
        if($order_info['order_status'] > 4) $this->oldAjaxReturn (0, '该订单不能取消', 0);
        $mod->startTrans();
        $cancel_bool = cancel_order($order_info);
        $data = array(
            'order_id'=>$order_id,
            'user_id'=>  session('admin_uid'),
            'comment'=>$reason,
            'create_time'=>  date('Y-m-d H:i:s')
        );
       $comment_bool = M('order_comment')->data($data)->add();
       if($cancel_bool && $comment_bool){
           $mod->commit();
           $this->oldAjaxReturn(0, '取消成功', 1);
       }else{
           $mod->rollback();
           $this->oldAjaxReturn(0, '取消失败', 0);
       }
    }
    
    //导出
    public function export(){
        $limit = 50000;
        $where = $this->order_list_where();
        $export_model = new \Org\My\Export('order_base_info');
        $export_model->limit = $limit;
        $export_model->setCount(D('Order')->where($where)->count());
        $page = new \Think\Page(D('Order')->where($where)->count(),$limit);
        //数据
        $order_list = D('Order')->where($where)->limit($page->firstRow.','.$page->listRows)->order('create_time desc')->select();
        $member_ids = getFieldsByKey($order_list, 'member_id');
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        $order_ids = getFieldsByKey($order_list, 'order_id');
        $payment_serial_numbers = M('order_payment_serial_number_info')->where(array('order_id'=>array('in',$order_ids)))->getField('order_id,payment_serial_number',true);
        $shops_codes = getFieldsByKey($order_list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $new_order_list = array();
        foreach($order_list as $k=>$order_info){
            $pay_status = $order_info['pay_status'];
            $order_info['order_status'] = id2name('order_status', $order_info['order_status']);
            $order_info['order_internal_status'] = id2name('order_internal_status', $order_info['order_internal_status']);
            $order_info['pay_status'] = id2name('pay_status', $order_info['pay_status']);
            $order_info['order_type'] = id2name('order_type', $order_info['order_type']);
            $order_info['order_way'] = id2name('order_way', $order_info['order_way']);
            $order_info['is_invoice'] = id2name('is_invoice', $order_info['is_invoice']);
            $order_info['invoice_status'] = id2name('invoice_status', $order_info['invoice_status']);
            $order_info['member_account'] = $member_accounts[$order_info['member_id']];
            $order_info['payment_serial_number'] = $payment_serial_numbers[$order_info['order_id']];
            $order_info['shops_name'] = $shops_names[$order_info['shops_code']];
            if($order_info['province']){
                $citys = M('city')->where(array('city_id'=>array('in',array($order_info['province'],$order_info['city'],$order_info['county']))))->getField('city_id,city_name',true);
                $order_info['province'] = $citys[$order_info['province']];
                $order_info['city'] = $citys[$order_info['city']];
                $order_info['county'] = $citys[$order_info['county']];
            }
            $order_goods_list = M('order_goods_info')->where(array('order_id'=>$order_info['order_id']))->select();
            foreach($order_goods_list as $j=>$order_goods){
               $excel_order_info = $order_info;
               $excel_order_info['goods_name'] = $order_goods['goods_name'];
               $excel_order_info['goods_number'] = $order_goods['goods_number'];
               $excel_order_info['goods_price'] = $order_goods['goods_price'];
               $goods_pay_value = $order_goods['goods_number'] * $order_goods['goods_price'];
               $goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods['order_goods_id']))->select();
               foreach($goods_discount_list as $j=>$goods_discount){
                   //优惠码或者优惠券
                   if($goods_discount['discount_type'] == 3 || $goods_discount['discount_type'] == 4) $goods_pay_value -= $goods_discount['discount_value'];
                   //积分
                   if($goods_discount['discount_type'] == 5){
                       $use_point = $goods_discount['discount_code'];
                       if($order_goods['integral']){
                           $use_point -= $order_goods['goods_number'] * $order_goods['max_integral'];
                       }
                       $goods_pay_value -= ($use_point* C('INTEGRAL_RATE')['point'])/C('INTEGRAL_RATE')['rmb'];
                   }
               }
               if($pay_status == 12) $excel_order_info['goods_pay_value'] = price_format($goods_pay_value);
               else $excel_order_info['goods_pay_value'] = price_format(0);
               $new_order_list[] = $excel_order_info;
            }
        }
        //导出的模版
        $export_fields = $this->export_fields();
        //导出的数据
        $export_model->title = '订单数据';
        $export_model->execl_fields = $export_fields;
        $export_model->setData($new_order_list);
        $export_page = $export_model->export();
        exit($export_page);
    }
    
    //确认收货
    public function receive_order(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        $mod = D('Order');
        $where = array('order_id'=>$order_id);
        $order_info = $mod->where($where)->find();
        if($order_info['order_status'] != 4) $this->oldAjaxReturn (0, '该订单不能操作确认收货', 0);
        $mod->startTrans();
        $order_bool = $mod->where($where)->data(array('order_status'=>7,'update_time'=>date('Y-m-d H:i:s')))->save();
        //订单是
        $where['order_goods_status'] = 4;
        $order_goods_bool = M('order_goods_info')->where($where)->data(array('order_goods_status'=>7,'update_time'=>date('Y-m-d H:i:s')))->save();
        if($order_goods_bool === FALSE) $order_goods_bool = FALSE;
        else { $order_goods_bool = true;}
       if($order_bool && $order_goods_bool){
           $mod->commit();
           $this->oldAjaxReturn(0, '操作成功', 1);
       }else{
           $mod->rollback();
           $this->oldAjaxReturn(0, '操作失败', 0);
       }
    }
    
    //退换货信息
    public function get_reverse_info(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        $order_goods_ids_str = I('order_goods_id','','trim,htmlspecialchars');
        $order_goods_ids = explode(',', trim($order_goods_ids_str,','));
        $goods_list = M('order_goods_info')->where(array('order_goods_id'=>array('in',$order_goods_ids)))->select();
        $this->assign('goods_list', $goods_list);
        $this->assign('order_id', $order_id);
        $this->assign('order_goods_id', $order_goods_ids_str);
        $content = $this->fetch('reverse');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    //退换货
    public function reverse_order(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        $mod = D('Order');
        $where = array('order_id'=>$order_id);
        $order_info = $mod->where($where)->find();
        if(!in_array($order_info['order_status'], array(4,7,8,20))) $this->oldAjaxReturn (0, '该订单不能操作退换货', 0);
        $comment = I('reason','','trim,htmlspecialchars');
        if(!$comment) $this->oldAjaxReturn ('reason', '请输入订单退换货原因', 0);
        $order_goods_ids_str = I('order_goods_id','','trim,htmlspecialchars');
        $order_goods_ids = explode(',', trim($order_goods_ids_str,','));
        $order_type = I('order_type','','trim,htmlspecialchars');
        $goods_list = M('order_goods_info')->where(array('order_goods_id'=>array('in',$order_goods_ids)))->select();
        $reverse_order_id = (new \Org\Util\ThinkString())->uuid();
        foreach($goods_list as $k=>$order_goods){
            $return_number = I('goods_'.$order_goods['order_goods_id'],0,'intval');
            if($return_number < 0) $this->oldAjaxReturn ('goods_'.$order_goods['order_goods_id'], '数量必须是大于0的整数', 0);
            if($return_number > 0){
                if(in_array($order_goods['order_goods_status'], array(13,14))) $this->oldAjaxReturn ('goods_'.$order_goods['order_goods_id'], '已经退换货过的商品，不能退换货操作', 0);
                if($return_number > $order_goods['goods_number']) $this->oldAjaxReturn ('goods_'.$order_goods['order_goods_id'], '退换货数量超过购买数量', 0);
                $reverse_goods_data[] = array(
                    'order_goods_id'=>(new \Org\Util\ThinkString())->uuid(),
                    'forward_order_goods_id'=>$order_goods['order_goods_id'],
                    'reverse_order_id'=>$reverse_order_id,
                    'numbers'=>$return_number,
                    'order_goods_type'=>$order_goods['order_goods_type'],
                    'is_primary'=>$order_goods['is_primary'],
                    'primary_order_goods_id'=>$order_goods['primary_order_goods_id'],
                    'goods_code'=>$order_goods['goods_code'],
                    'goods_category_code'=>$order_goods['goods_category_code'],
                    'goods_type_code'=>$order_goods['goods_type_code'],
                    'goods_name'=>$order_goods['goods_name'],
                    'goods_materiel_code'=>$order_goods['goods_materiel_code'],
                    'goods_price'=>$order_goods['goods_price'],
                    'promotion_content'=>$order_goods['promotion_content'],
                    'shops_code'=>$order_goods['shops_code'],
                    'create_time'=>date('Y-m-d H:i:s')
                );
            }
        }
        if(!$reverse_goods_data || empty($reverse_goods_data)) $this->oldAjaxReturn (0, '选择的退换货的商品必须有一个数量大于0', 0);
        $reverse_order_data = array(
            'reverse_order_id'=>$reverse_order_id,
            'forward_order_id'=>$order_info['order_id'],
            'order_type'=>$order_type,
            'order_cur_id'=>  createOrderId(),
            'member_id'=>$order_info['member_id'],
            'create_time'=>  date('Y-m-d H:i:s'),
            'update_time'=>  date('Y-m-d H:i:s'),
            'shops_code'=>$order_info['shops_code'],
            'order_status'=>10,//未处理
            'forward_order_cur_id'=>$order_info['order_cur_id'],
            'comment'=>$comment
        );
        D('ReverseOrder')->startTrans();
        $reverse_order_bool = D('ReverseOrder')->data($reverse_order_data)->add();
        $reverse_order_goods_bool = M('reverse_order_goods_info')->addAll($reverse_goods_data);
        if($order_type == 1) $order_goods_status = 13;
        else $order_goods_status = 14;
        $order_goods_bool = M('order_goods_info')->where(array('order_goods_id'=>array('in',$order_goods_ids)))->data(array('order_goods_status'=>$order_goods_status,'update_time'=>date('Y-m-d H:i:s')))->save();
        //冻结消费码
        $this->freeze_use_code($reverse_goods_data);
        if($order_goods_bool && $reverse_order_bool && $reverse_order_goods_bool){
            D('ReverseOrder')->commit();
            $this->oldAjaxReturn(0, '操作成功', 1);
        }else{
            D('ReverseOrder')->rollback();
            $this->oldAjaxReturn(0, '操作失败', 0);
        }
    }
    
    //获取删除页面
    public function get_del_info($order_id){
        $this->assign('order_id', $order_id);
        $content = $this->fetch('del_info');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    //删除订单
    public function del_order(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        $reason = I('reason','','trim,htmlspecialchars');
        if(!$reason) $this->oldAjaxReturn ('reason', '请输入取消原因', 0);
        $mod = D('Order');
        $where = array('order_id'=>$order_id,'order_status'=>array('gt',0));
        $order_info = $mod->where($where)->find();
        if(!$order_info) $this->oldAjaxReturn (0, '订单不存在', 0);
        $mod->startTrans();
        $del_bool = $mod->where($where)->data(array('order_status'=>0,'update_time'=>  date('Y-m-d H:i:s')))->save();
        $del_goods_bool = M('order_goods_info')->where(array('order_id'=>$order_id))->data(array('order_goods_status'=>0,'update_time'=>  date('Y-m-d H:i:s')))->save();
        $data = array(
            'order_id'=>$order_id,
            'user_id'=>  session('admin_uid'),
            'comment'=>$reason.'【删除前状态】：'.$order_info['order_status'],
            'create_time'=>  date('Y-m-d H:i:s')
       );
       $use_code_data['status'] = 0;
       $use_code_data['remark'] = $reason;
       $use_code_data['update_time'] = date('Y-m-d H:i:s');
       $del_use_code_bool = M('member_use_code')->where(array('order_id'=>$order_id,'status'=>10))->data($use_code_data)->save();
       $comment_bool = M('order_comment')->data($data)->add();
       if($del_bool && $del_goods_bool && $comment_bool){
           $mod->commit();
           $this->oldAjaxReturn(0, '删除成功', 1);
       }else{
           $mod->rollback();
           $this->oldAjaxReturn(0, '删除失败', 0);
       }
    }
    
    public function print_order(){
        $order_id = I('order_id','','trim,htmlspecialchars');
        $info = D('Order')->where(array('order_id'=>$order_id))->find();
        if($info['province']) {
            $where['city_id'] = array('in',array($info['province'],$info['city'],$info['county']));
            $city_names = M('city')->where($where)->getField('city_id,city_name',true);
            $info['province'] = $city_names[$info['province']];
            $info['city'] = $city_names[$info['city']];
            $info['county'] = $city_names[$info['county']];
        }
        $order_goods_list = M('order_goods_info')->where(array('order_id'=>$order_id))->select();
            $shops_codes = getFieldsByKey($order_goods_list, 'shops_code');
            $shops_names = D('Shops')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
            foreach ($order_goods_list as $k=>$order_goods){
                $order_goods_list[$k]['total_value'] = $order_goods['goods_price'] * $order_goods['goods_number'];
                $order_goods_list[$k]['shops_name'] = $shops_names[$order_goods['shops_code']];
                $goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods['order_goods_id']))->select();
                $discount_values = getFieldsByKey($goods_discount_list, 'discount_value');
                $order_goods_list[$k]['total_discount_value'] = array_sum($discount_values);
                //展示价格
                $order_goods_list[$k]['show_price'] = '￥'.  price_format($order_goods['goods_price']);
                if($order_goods['integral'] && $order_goods['goods_price'] && $order_goods['max_integral'])  $order_goods_list[$k]['show_price'] = '￥'.$order_goods['goods_price'] .' + ' .$order_goods['max_integral'].'积分';
                if($order_goods['integral'] && !$order_goods['goods_price'] && $order_goods['max_integral']) $order_goods_list[$k]['show_price'] = $order_goods['max_integral'].'积分';
                if($order_goods['integral'] && $order_goods['goods_price'] && !$order_goods['max_integral']) $order_goods_list[$k]['show_price'] = '￥'.$order_goods['goods_price'];
                foreach($goods_discount_list as $j=>$goods_discount){
                    //优惠券
                    if($goods_discount['discount_type'] == 3){
                        $order_goods_list[$k]['coupon_txt'] = $goods_discount['discount_code'].'/'.$goods_discount['discount_value'];
                    }
                    //优惠码
                    if($goods_discount['discount_type'] == 4){
                        $order_goods_list[$k]['coupon_code_txt'] = $goods_discount['discount_code'].'/'.$goods_discount['discount_value'];
                    }
                    //积分
                    if($goods_discount['discount_type'] == 5){
                        $order_goods_list[$k]['use_integral'] = $goods_discount['discount_code'];
                    }
                    //优惠码
                    if($goods_discount['discount_type'] == 6){
                        $order_goods_list[$k]['storage_money'] = $goods_discount['discount_value'];
                    }
                }
            }
            $this->assign('info', $info);
            $this->assign('order_goods_list', $order_goods_list);
            $this->display();
    } 
    /*-----------------------------------------------------------------受保护方法------------------------------------------------------------------*/
    //订单条件组合
    private function order_list_where() {
        //接收参数
        $order_cur_id = I('order_cur_id','','trim');//订单编号
        //搜索条件
        $create_start = I('create_start', '', 'trim'); //下单开始时间
        $create_end = I('create_end', '', 'trim'); //下单结束时间
        $pay_start = I('pay_start', '', 'trim'); //支付开始时间
        $pay_end = I('pay_end', '', 'trim'); //支付结束时间
        $order_status = I('order_status',0,'intval'); //订单状态
        $payment_trade_no = I('payment_trade_no','','trim');
        $name = I('name','','trim'); //用户姓名
        $mobile = I('mobile','','trim'); //用户电话
        $order_type = I('order_type',0,'intval'); //订单类型
        $pay_status = I('pay_status',0,'intval'); //支付状态
        $order_way = I('order_way',0,'intval');
        //定义条件数组
        $where = array();
        if($order_cur_id) $where['order_cur_id'] = array('like','%'.$order_cur_id.'%');
        //下单时间
        if ($create_start) $where['create_time'] = array('egt', $create_start);
        if ($create_end) $where['create_time'] = array('elt', $create_end);
        if ($create_end && $create_start) $where['create_time'] = array('BETWEEN', array($create_start, $create_end));
        //支付时间
        if ($pay_start) $where['pay_time'] = array('egt', $pay_start);
        if ($pay_end) $where['pay_time'] = array('elt', $pay_end);
        if ($pay_end && $pay_start) $where['pay_time'] = array('BETWEEN', array($pay_start, $pay_end));
        if($payment_trade_no) $pay_where['payment_trade_no'] = array('like','%'.$payment_trade_no.'%');
        if($order_way) $where['order_way'] = $order_way;
        $payment_serial_number = I('payment_serial_number','','trim');
        $shops_code = I('shops_code','','trim');
        $member_account = I('member_account','','trim');
        $goods_name = I('goods_name','','trim');
        //订单状态
        if ($order_status != '') {
            $where['order_status'] = $order_status;
        } else {
            $where['order_status'] = array('neq', 0);
        }
        //用户姓名
        if ($name) $where['name'] = array('like', '%' . $name . '%');
        //用户电话
        if ($mobile) $where['mobile'] = array('like', '%' . $mobile . '%');
        //订单类型
        if ($order_type) $where['order_type'] = $order_type;
        //支付状态
        if ($pay_status ) $where['pay_status'] = $pay_status;
        //支付时间
        if($pay_where){
            $pay_order_ids = M('order_payment_info')->where($pay_where)->getField('order_id',true);
            $pay_order_ids = $pay_order_ids ? $pay_order_ids : array();
            $where['order_id'] = array('in',$pay_order_ids);
        }
         //订单权限
        $access_shops_codes = $this->check_access_for_order();
        if(is_array($access_shops_codes)){
            $where['shops_code'] = array('in',$access_shops_codes);
        }
        if($shops_code) $where['shops_code'] = $shops_code;
        if($member_account) {
            $member_ids = M('user_member_base_info')->where(array('member_account'=>array('like','%'.$member_account.'%')))->getField('member_id',true);
            $where['member_id'] = array('in',$member_ids);
        }
        if($goods_name){
            $temp_order_ids = M('order_goods_info')->where(array('goods_name|goods_code'=>array('like','%'.$goods_name.'%')))->getField('order_id',true);
            $temp_order_ids = $temp_order_ids ? $temp_order_ids : array();
            $t_order_ids = $where['order_id'];
            if($t_order_ids) $w_order_ids = array_uintersect($t_order_ids['in'],$temp_order_ids);
            else $w_order_ids = $temp_order_ids;
            $where['order_id'] = array('in',$w_order_ids);
        }
        if($payment_serial_number){
            $order_ids = M('order_payment_serial_number_info')->where(array('payment_serial_number'=>array('like','%'.$payment_serial_number.'%')))->getField('order_id',true);
            $t_order_ids = $where['order_id'];
            if($t_order_ids) $w_order_ids = array_uintersect($t_order_ids['in'],$order_ids);
            else $w_order_ids = $order_ids;
            $where['order_id'] = array('in',$w_order_ids);
        }
        return $where;
    }

    //订单搜索条件
    private function order_list_search() {
        $shops_list = M('shops_base_info')->where(array('shops_status'=>array('gt',0)))->getField('shops_code,shops_name',true);
        $shop_codes = $this->check_access_for_order();
        if(is_array($shop_codes)){
            foreach($shops_list as $k=>$shop_name) if(!in_array($k, $shop_codes)) unset ($shops_list[$k]);
        }
        return array(
            'url' => U('Order/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('Order/export').'"><i class="icon-download icon-white"></i> 导出订单</a>',
            ),
            'main' => array(
                array(
                    'name' => 'order_cur_id',
                    'show_name' => '订单编号',
                    'tip' => '请输入订单编号'
                ),
                array(
                    'name' => 'payment_serial_number',
                    'show_name' => '支付编号',
                    'tip' => '请输入支付编号'
                ),
                array(
                    'name' => 'order_status',
                    'show_name' => '订单状态',
                    'tip' => '请选择订单状态',
                    'select' =>array(1=>'待处理',4=>'已发货',7=>'确认收货',8=>'订单完成',10=>'取消中',11=>'取消完成',20=>'用户删除'),
                    'type' => 'select'
                )
            ),
            'other' => array(
                array(
                    'name' => array('create_start', 'create_end'),
                    'show_name' => '下单时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                ),
                array(
                    'name' => array('pay_start', 'pay_end'),
                    'show_name' => '支付时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                ),
                array(
                    'name' => 'payment_trade_no',
                    'show_name' => '支付流水号',
                    'tip' => '请输入支付流水号'
                ),
                array(
                    'name' => 'name',
                    'show_name' => '收货人姓名',
                    'tip' => '请输入收货人姓名'
                ),
                array(
                    'name' => 'mobile',
                    'show_name' => '收货人电话',
                    'tip' => '请输入收货人电话'
                ),
                array(
                    'name' => 'order_type',
                    'show_name' => '订单类型',
                    'tip' => '请选择订单类型',
                    'select' => C('order_type'),
                    'type' => 'select'
                ),
                array(
                    'name' => 'pay_status',
                    'show_name' => '支付状态',
                    'tip' => '请选择支付状态',
                    'select' => C('pay_status'),
                    'type' => 'select'
                ),
                array(
                    'name' => 'order_way',
                    'show_name' => '下单平台',
                    'tip' => '请选择下单平台',
                    'select' => C('order_way'),
                    'type' => 'select'
                ),
                array(
                    'name' => 'member_account',
                    'show_name' => '会员账号',
                    'tip' => '请输入会员账号'
                ),
                array(
                    'name' => 'goods_name',
                    'show_name' => '商品名称/商品编码',
                    'tip' => '请输入商品名称/商品编码'
                ),
                array(
                    'name' => 'shops_code',
                    'show_name' => '商家名称',
                    'tip' => '请选择商家名称',
                    'select' => $shops_list,
                    'type' => 'select'
                ),
            )
        );
    }
    
    //导出模板
    private function export_fields(){
        return array(
            'order_cur_id'=>'订单编号',
            'member_account'=>'会员账号',
            'payment_serial_number'=>'支付编号',
            'order_status'=>'订单状态',
            'order_internal_status'=>'内部状态',
            'pay_status'=>'支付状态',
            'order_type'=>'订单类型',
            'order_way'=>'下单设备',
            'total_value'=>'订单总金额',
            'pay_value'=>'订单已付总金额',
            'pay_total_cash'=>'订单应付总金额',
            'is_invoice'=>'是否开票',
            'invoice_status'=>'开票状态',
            'name'=>'收货人姓名',
            'province'=>'省份',
            'city'=>'城市',
            'county'=>'县/区',
            'address'=>'地址',
            'mobile'=>'电话',
            'user_remark'=>'备注',
            'create_time'=>'下单时间',
            'shops_name'=>'商户名称',
            'goods_name'=>'商品名称',
            'goods_number'=>'商品数量',
            'goods_price'=>'商品单价（定价）',
            'goods_pay_value'=>'实收款'
        );
    }
    
    //验证管理员操作商品的其他权限
    function check_access_for_order() {
        if(check_admin_access('show_order_list_all',1,'other')){ //验证是否可以查看所有SKU
            return null;
        }elseif(check_admin_access('show_order_list_shops',1,'other')){ //验证是否可以查看本商家SKU
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
    /**
     * 冻结使用码
     */
    private function freeze_use_code($reverse_order_goods_list){
//        $reverse_order_goods_list = M('reverse_order_goods_info')->where(array('reverse_order_id'=>$reverse_order_id))->select();
        $reverse_use_code_list = array();
        foreach($reverse_order_goods_list as $k=>$reverse_order_goods){
            $reverse_number = $reverse_order_goods['numbers'];
            $goods_use_code_list = M('member_use_code')->where(array('order_goods_id'=>$reverse_order_goods['forward_order_goods_id']))->select();
            //大礼包
            if($reverse_order_goods['order_goods_type'] == 6){
                $relation_goods = M('order_goods_info')->where(array('order_goods_id'=>$reverse_order_goods['forward_order_goods_id']))->getField('relation_goods');
                $relation_goods = json_decode($relation_goods,true);
                foreach($relation_goods as $j=>$temp_relation_goods){
                    $temp_reverse_number = $reverse_number * $temp_relation_goods['number'];
                    foreach ($goods_use_code_list as $i=>$goods_use_detail){
                        if($temp_relation_goods['goods_code'] == $goods_use_detail['goods_code']){
                            if($temp_reverse_number > 0){
                                $temp_reverse_number -= 1;
                            }else{
                                break;
                            }
                            $reverse_use_code_list[] = $goods_use_detail;
                        }
                    }
                }
            }else{
                foreach($goods_use_code_list as $j=>$goods_use_detail){
                    if($reverse_number > 0){
                        $reverse_number -= 1;
                    }else{
                        break;
                    }
                    $reverse_use_code_list[] = $goods_use_detail;
                }
            }
        }
        $use_codes = getFieldsByKey($reverse_use_code_list, 'use_code');
        $code_data['remark'] = '退换货冻结消费码';
        $code_data['status'] = 30;
        $code_data['update_time'] = date('Y-m-d H:i:s');
        M('member_use_code')->where(array('use_code'=>array('in',$use_codes),'status'=>10))->data($code_data)->save();
        
    }
}
