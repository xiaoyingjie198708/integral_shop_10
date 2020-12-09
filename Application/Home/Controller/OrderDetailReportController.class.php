<?php


namespace Home\Controller;

class OrderDetailReportController extends BaseController{
    
    public function index(){
        $mod = new \Think\Model();
        $fields = 'info.order_cur_id,info.order_status,info.create_time,info.pay_time,info.shops_code,info.pay_value,order_goods.goods_name,order_goods.goods_number,order_goods.order_goods_id,order_goods.order_goods_id,order_goods.goods_price,order_goods.integral,order_goods.max_integral,order_goods.goods_code';
        $where = $this->get_order_where();
        $page = new \Org\My\Page($mod->table('tm_order_goods_info as order_goods,tm_order_base_info as info')->where('order_goods.order_id = info.order_id')->where($where)->count(),10);
        $list = $mod->table('tm_order_goods_info as order_goods,tm_order_base_info as info')->where('order_goods.order_id = info.order_id')->where($where)->field($fields)->limit($page->firstRow .','.$page->listRows)->order('info.update_time desc')->select();
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_cost_prices = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_cost_price',true);
        foreach($list as $k=>$order_info){
            $order_info['shops_name'] = $shops_names[$order_info['shops_code']];
            //固定积分
            if($order_info['integral']){
                if($order_info['goods_price'] && $order_info['max_integral']) {
                    $order_info['show_price'] = "￥".$order_info['goods_price'].'+'.$order_info['max_integral'].'积分';
                    $order_info['show_total_price'] = "￥".($order_info['goods_price'] * $order_info['goods_number']).'+'.($order_info['max_integral'] * $order_info['goods_number']).'积分';
                }
                if($order_info['goods_price'] && !$order_info['max_integral']) {
                    $order_info['show_price'] = "￥".$order_info['goods_price'];
                    $order_info['show_total_price'] = "￥".($order_info['goods_price'] * $order_info['goods_number']);
                }
                if(!$order_info['goods_price'] && $order_info['max_integral']) {
                    $order_info['show_price'] = $order_info['max_integral'].'积分';
                    $order_info['show_total_price'] = ($order_info['max_integral'] * $order_info['goods_number']).'积分';
                }
            }else{
                $order_info['show_price'] = "￥".$order_info['goods_price'];
                $order_info['show_total_price'] = "￥".($order_info['goods_price'] * $order_info['goods_number']);
            }
            $order_info['goods_cost_price'] = $goods_cost_prices[$order_info['goods_code']];
            if($order_info['pay_value'] < 0) $order_info['pay_value'] = 0;
            $list[$k] = $order_info;
        }
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->search($this->order_list_search());
        $this->display(); 
    }
    
    //快捷查看
    public function quick_view() {
        $order_goods_id = I('order_goods_id', '','trim,htmlspecialchars');
        $mod = new \Think\Model();
        $fields = 'info.pay_time,info.is_invoice,info.invoice_status,info.order_id,info.parent_id,order_goods.order_goods_id';
        $where['order_goods.order_goods_id'] = $order_goods_id;
        $order_goods_info = $mod->table('tm_order_goods_info as order_goods,tm_order_base_info as info')->where('order_goods.order_id = info.order_id')->where($where)->field($fields)->find();
        $order_goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_goods_id))->select();
        foreach($order_goods_discount_list as $k=>$goods_discount){
            //优惠券
            if($goods_discount['discount_type'] == 3) {
                $order_goods_info['coupon_code'] = $goods_discount['discount_code'];
                $order_goods_info['coupon_value'] = $goods_discount['discount_value'];
                $discount_value += $goods_discount['discount_value'];
            }
            //优惠码
            if($goods_discount['discount_type'] == 4) {
                $order_goods_info['couponcode_code'] = $goods_discount['discount_code'];
                $order_goods_info['coupon_code_value'] = $goods_discount['discount_value'];
                $discount_value += $goods_discount['discount_value'];
            }
            //积分
            if($goods_discount['discount_type'] == 5) $order_goods_info['use_point'] = $goods_discount['discount_code'];
            //储值卡
            if($goods_discount['discount_type'] == 6) $order_goods_info['storage_money'] = $goods_discount['discount_value'];
        }
        $order_goods_info['discount_value'] = $discount_value;
        $this->assign('order_goods_info', $order_goods_info);
        $contant = $this->fetch('quick_view');
        $this->oldAjaxReturn(0, $contant, 1);
    }
    
    public function export(){
        $limit = 50000;
        $mod = new \Think\Model();
        $where = $this->get_order_where();
        $export_model = new \Org\My\Export('order_goods_info');
        $export_model->limit = $limit;
        $export_model->setCount($mod->table('tm_order_goods_info as order_goods,tm_order_base_info as info')->where('order_goods.order_id = info.order_id')->where($where)->count());
        $fields = 'info.order_cur_id,info.order_status,info.create_time,info.pay_time,info.shops_code,info.pay_value,order_goods.goods_name,order_goods.goods_number,order_goods.order_goods_id,order_goods.order_goods_id,order_goods.goods_price,order_goods.integral,order_goods.max_integral,order_goods.goods_code,info.is_invoice,info.invoice_status';
        $list = $mod->table('tm_order_goods_info as order_goods,tm_order_base_info as info')->where('order_goods.order_id = info.order_id')->where($where)->field($fields)->order('info.update_time desc')->select();
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_cost_prices = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_cost_price',true);
        foreach($list as $k=>$order_info){
            $order_info['shops_name'] = $shops_names[$order_info['shops_code']];
            //固定积分
            if($order_info['integral']){
                if($order_info['goods_price'] && $order_info['max_integral']) {
                    $order_info['show_price'] = "￥".$order_info['goods_price'].'+'.$order_info['max_integral'].'积分';
                    $order_info['show_total_price'] = "￥".($order_info['goods_price'] * $order_info['goods_number']).'+'.($order_info['max_integral'] * $order_info['goods_number']).'积分';
                }
                if($order_info['goods_price'] && !$order_info['max_integral']) {
                    $order_info['show_price'] = "￥".$order_info['goods_price'];
                    $order_info['show_total_price'] = "￥".($order_info['goods_price'] * $order_info['goods_number']);
                }
                if(!$order_info['goods_price'] && $order_info['max_integral']) {
                    $order_info['show_price'] = $order_info['max_integral'].'积分';
                    $order_info['show_total_price'] = ($order_info['max_integral'] * $order_info['goods_number']).'积分';
                }
            }else{
                $order_info['show_price'] = "￥".$order_info['goods_price'];
                $order_info['show_total_price'] = "￥".($order_info['goods_price'] * $order_info['goods_number']);
            }
            $order_info['goods_cost_price'] = $goods_cost_prices[$order_info['goods_code']];
            if($order_info['pay_value'] < 0) $order_info['pay_value'] = 0;
            //转化数据
            $order_info['order_status'] = id2name($order_info['order_status'], 'order_status');
            $order_info['return_status'] = '已回款';
            $order_info['return_time'] = $order_info['pay_time'];
            $order_info['is_invoice'] = id2name($order_info['is_invoice'], 'is_invoice');
            $order_info['invoice_status'] = id2name($order_info['invoice_status'], 'invoice_status');
            $order_goods_discount_list = M('order_goods_discount_info')->where(array('order_goods_id'=>$order_info['order_goods_id']))->select();
            foreach($order_goods_discount_list as $j=>$goods_discount){
                //优惠券
                if($goods_discount['discount_type'] == 3) {
                    $order_info['coupon_code'] = $goods_discount['discount_code'];
                    $order_info['coupon_value'] = $goods_discount['discount_value'];
                    $discount_value += $goods_discount['discount_value'];
                }
                //优惠码
                if($goods_discount['discount_type'] == 4) {
                    $order_info['couponcode_code'] = $goods_discount['discount_code'];
                    $order_info['coupon_code_value'] = $goods_discount['discount_value'];
                    $discount_value += $goods_discount['discount_value'];
                }
                //积分
                if($goods_discount['discount_type'] == 5) $order_info['use_point'] = $goods_discount['discount_code'];
                //储值卡
                if($goods_discount['discount_type'] == 6) $order_info['storage_money'] = $goods_discount['discount_value'];
            }
            $order_info['discount_value'] = $discount_value;
            $list[$k] = $order_info;
        }
        //导出的模版
        $export_fields = $this->export_fields();
        //导出的数据
        $export_model->title = '订单统计明细数据';
        $export_model->execl_fields = $export_fields;
        $export_model->setData($list);
        $export_page = $export_model->export();
        exit($export_page);
    }
    /*---------------------------------------------------受保护方法------------------------------------------------------*/
    //搜索条件
    private function get_order_where(){
        $where = array();
        //已支付订单
        $where['info.pay_status'] = 12;
        $order_status = I('order_status',0,'intval');
        if($order_status && !in_array(0, $order_status)) $where['info.order_status'] = array('in',$order_status);
        else $where['info.order_status'] = array('neq',0);
        $shops_codes = $this->check_access_for_report();
        $shops_code = I('shops_code','','trim,htmlspecialchars');
        if(is_array($shops_codes)){
            if($shops_code && !in_array('null', $shops_code)) $where['info.shops_code'] = array('in',array_intersect($shops_codes, $shops_code));
           else $where['info.shops_code'] = 'notall';
        }else{
            if($shops_code && !in_array('null', $shops_code)) $where['info.shops_code'] = array('in',$shops_code);
             else $where['info.shops_code'] = array('neq','');
        }
        $pay_start = I('pay_start','','trim,htmlspecialchars');
        $pay_end = I('pay_end','','trim,htmlspecialchars');
        if($pay_start && !$pay_end) $where['info.create_time'] = array('gt',$pay_start);
        if(!$pay_start && $pay_end) $where['info.create_time'] = array('lt',$pay_end);
        if($pay_start && $pay_end) $where['info.create_time'] = array('between',$pay_start.','.$pay_end);
        return $where;
    }
    
    //订单搜索条件
    private function order_list_search() {
        $shops_list = M('shops_base_info')->getField('shops_code,shops_name',true);
        return array(
            'url' => U('OrderDetailReport/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('OrderDetailReport/export').'"><i class="icon-download icon-white"></i> 导出报表</a>',
            ),
            'main' => array(
                 array(
                    'name' => 'shops_code[]',
                    'show_name' => '商家名称',
//                    'tip' => '请选择商家名称',
                    'select' => $shops_list,
                    'type' => 'select',
                    'multiple'=>true,
                     'param_name' => 'shops_code',
                ),
                array(
                    'name' => 'order_status[]',
                    'show_name' => '订单状态',
//                    'tip' => '请选择订单状态',
                    'select' => array(1=>'待处理',2=>'已确认',3=>'待发货',4=>'已发货',7=>'确认收货',8=>'订单完成'),
                    'type' => 'select',
                    'multiple'=>true,
                    'param_name' => 'order_status',
                )
            ),
            'other' => array(
                array(
                    'name' => array('pay_start', 'pay_end'),
                    'show_name' => '下单时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                )
            )
        );
    }
    //验证管理员操作商品的其他权限
    private function check_access_for_report() {
        if(check_admin_access('show_odr_shops_list_all',1,'other')){ //验证是否可以查看所有SKU
            return null;
        }elseif(check_admin_access('show_odr_shops_list_shops',1,'other')){ //验证是否可以查看本商家SKU
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
    
    //导出模板
    private function export_fields(){
        return array(
            'order_cur_id'=>'订单编号',
            'order_status'=>'订单状态',
            'shops_name'=>'商家名称',
            'goods_name'=>'商品名称',
            'goods_number'=>'商品数量',
            'show_price'=>'商品销售价',
            'goods_cost_price'=>'商品结算价',
            'show_total_price'=>'订单总价',
            'pay_value'=>'收款金额',
            'pay_time'=>'支付时间',
            'return_status'=>'回款状态',
            'return_time'=>'回款时间',
            'is_invoice'=>'是否需要发票',
            'invoice_status'=>'开票状态',
            'coupon_value'=>'优惠券总金额',
            'coupon_code'=>'优惠券编号',
            'coupon_code_value'=>'优惠码总金额',
            'couponcode_code'=>'优惠码编号',
            'use_point'=>'使用积分',
            'discount_value'=>'折扣总金额',
            'storage_money'=>'使用储值卡',
        );
    }
}
