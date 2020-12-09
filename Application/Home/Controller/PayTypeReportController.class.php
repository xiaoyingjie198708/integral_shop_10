<?php


namespace Home\Controller;

/**
 * 收款方式汇总报表
 */
class PayTypeReportController extends BaseController{
    
    public function index(){
        $mod = D('Order');
        $shops_where = $this->get_shops_where();
        $list = M('shops_base_info')->where($shops_where)->select();
        $where = $this->get_order_where();
        $order_list = $mod->where($where)->select();
        $shops_order_list = array();
        foreach($order_list as $k=>$order_info){
            $shops_order = $shops_order_list[$order_info['shops_code']];
            $shops_order['order_count'] += 1;
            if($order_info['pay_value'] > 0) $shops_order['pay_value'] += $order_info['pay_value'];
            $shops_order['order_ids'][] = $order_info['order_id'];
            $shops_order_list[$order_info['shops_code']] = $shops_order;
        }
        foreach($list as $k=>$shops_info){
            $shops_order = $shops_order_list[$shops_info['shops_code']];
            $order_ids = $shops_order['order_ids'];
            //产品数量
            $goods_numbers = M('order_goods_info')->where(array('order_id'=>array('in',$order_ids)))->getField('goods_number',true);
            $shops_order['goods_number'] = array_sum($goods_numbers);
            $payment_list = M('order_payment_info')->where(array('order_id'=>array('in',$order_ids)))->select();
            //支付宝，微信，银联初始化
            $upop_value = 0;$ali_value = 0;$weixin_value = 0;
            foreach($payment_list as $j=>$payment_info){
                //支付宝
                if($payment_info['payment_type'] == 1) $ali_value += $payment_info['payment_fee'];
                //银联支付
                if($payment_info['payment_type'] == 2) $upop_value += $payment_info['payment_fee'];
                //微信支付
                if($payment_info['payment_type'] == 3) $weixin_value += $payment_info['payment_fee'];
            }
            $shops_order['upop_value'] = $upop_value;
            $shops_order['ali_value'] = $ali_value;
            $shops_order['weixin_value'] = $weixin_value;
            if($shops_order) $shops_info = array_merge($shops_info,$shops_order);
            $list[$k] = $shops_info;
            //合计
            $total_info['order_count'] += $shops_info['order_count'];
            $total_info['goods_number'] += $shops_info['goods_number'];
            $total_info['upop_value'] += $shops_info['upop_value'];
            $total_info['ali_value'] += $shops_info['ali_value'];
            $total_info['weixin_value'] += $shops_info['weixin_value'];
            $total_info['pay_value'] += $shops_info['pay_value'];
        }
        $this->assign('total_info', $total_info);
        $this->assign('list', $list);
        $this->search($this->order_list_search());
        $this->display();
    }
    
    public function export(){
        $limit = 50000;
        $mod = D('Order');
        $shops_where = $this->get_shops_where();
        $export_model = new \Org\My\Export('shops_base_info');
        $export_model->limit = $limit;
        $export_model->setCount(M('shops_base_info')->where($shops_where)->count());
        $list = M('shops_base_info')->where($shops_where)->select();
        $where = $this->get_order_where();
        $order_list = $mod->where($where)->select();
        $shops_order_list = array();
        foreach($order_list as $k=>$order_info){
            $shops_order = $shops_order_list[$order_info['shops_code']];
            $shops_order['order_count'] += 1;
            if($order_info['pay_value'] > 0) $shops_order['pay_value'] += price_format ($order_info['pay_value']);
            $shops_order['order_ids'][] = $order_info['order_id'];
            $shops_order_list[$order_info['shops_code']] = $shops_order;
        }
        foreach($list as $k=>$shops_info){
            $shops_order = $shops_order_list[$shops_info['shops_code']];
            $shops_order['order_count'] = intval($shops_order['order_count']);
            $order_ids = $shops_order['order_ids'];
            //产品数量
            $goods_numbers = M('order_goods_info')->where(array('order_id'=>array('in',$order_ids)))->getField('goods_number',true);
            $shops_order['goods_number'] = array_sum($goods_numbers);
            $payment_list = M('order_payment_info')->where(array('order_id'=>array('in',$order_ids)))->select();
            //支付宝，微信，银联初始化
            $upop_value = 0;$ali_value = 0;$weixin_value = 0;
            foreach($payment_list as $j=>$payment_info){
                //支付宝
                if($payment_info['payment_type'] == 1) $ali_value += $payment_info['payment_fee'];
                //银联支付
                if($payment_info['payment_type'] == 2) $upop_value += $payment_info['payment_fee'];
                //微信支付
                if($payment_info['payment_type'] == 3) $weixin_value += $payment_info['payment_fee'];
            }
            $shops_order['upop_value'] = price_format($upop_value);
            $shops_order['ali_value'] = price_format($ali_value);
            $shops_order['weixin_value'] = price_format($weixin_value);
            if($shops_order) $shops_info = array_merge($shops_info,$shops_order);
            $shops_order['goods_number'] = intval($shops_order['goods_number']);
            $shops_order['pay_value'] = price_format($shops_order['pay_value']);
            $list[$k] = $shops_info;
            //合计
            $total_info['order_count'] += intval($shops_info['order_count']);
            $total_info['goods_number'] += intval($shops_info['goods_number']);
            $total_info['upop_value'] += price_format($shops_info['upop_value']);
            $total_info['ali_value'] += price_format($shops_info['ali_value']);
            $total_info['weixin_value'] += price_format($shops_info['weixin_value']);
            $total_info['pay_value'] += price_format($shops_info['pay_value']);
        }
        if($total_info) {$total_info['shops_name']='总计';$list[] = $total_info;}
        
        //导出的模版
        $export_fields = $this->export_fields();
        //导出的数据
        $export_model->title = '收款方式数据';
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
        $where['pay_status'] = 12;
        $order_status = I('order_status',0,'intval');
        if($order_status && !in_array(0, $order_status)) $where['order_status'] = array('in',$order_status);
        else $where['order_status'] = array('neq',0);
        $shops_code = I('shops_code','','trim,htmlspecialchars');
        if($shops_code && !in_array('null', $shops_code)) $where['shops_code'] = array('in',$shops_code);
        $pay_start = I('pay_start','','trim,htmlspecialchars');
        $pay_end = I('pay_end','','trim,htmlspecialchars');
        if($pay_start && !$pay_end) $where['pay_time'] = array('gt',$pay_start);
        if(!$pay_start && $pay_end) $where['pay_time'] = array('lt',$pay_end);
        if($pay_start && $pay_end) $where['pay_time'] = array('between',$pay_start.','.$pay_end);
        return $where;
    }
    
    private function get_shops_where(){
        $where = array();
        $shops_codes = $this->check_access_for_report();
        $shops_code = I('shops_code','','trim,htmlspecialchars');
        if(is_array($shops_codes)){
            if($shops_code && !in_array('null', $shops_code)) $where['shops_code'] = array('in',  array_intersect($shops_codes, $shops_code));
            else $where['shops_code'] = '';
        }else{
            if($shops_code && !in_array('null', $shops_code)) $where['shops_code'] = array('in',$shops_code);
        }
        return $where;
    }
    
    //订单搜索条件
    private function order_list_search() {
        $shops_list = M('shops_base_info')->getField('shops_code,shops_name',true);
        return array(
            'url' => U('PayTypeReport/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('PayTypeReport/export').'"><i class="icon-download icon-white"></i> 导出报表</a>',
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
                    'show_name' => '支付时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                )
            )
        );
    }
    //验证管理员操作商品的其他权限
    private function check_access_for_report() {
        if(check_admin_access('show_ptr_shops_list_all',1,'other')){ //验证是否可以查看所有SKU
            return null;
        }elseif(check_admin_access('show_ptr_shops_list_shops',1,'other')){ //验证是否可以查看本商家SKU
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
    
    //导出模板
    private function export_fields(){
        return array(
            'shops_name'=>'商家名称',
            'order_count'=>'订单数量',
            'goods_number'=>'产品数量',
            'upop_value'=>'银联',
            'ali_value'=>'支付宝',
            'weixin_value'=>'微信',
            'pay_value'=>'合计',
        );
    }
}
