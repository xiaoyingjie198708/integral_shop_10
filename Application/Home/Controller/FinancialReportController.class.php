<?php

namespace Home\Controller;

/**
 * 财务报表总表
 */
class FinancialReportController extends BaseController{
    
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
            $shops_order['total_value'] += $order_info['total_value'];
            $shops_order['total_pay_value'] += $order_info['total_value'];
            if($order_info['pay_value'] > 0) $shops_order['pay_value'] += $order_info['pay_value'];
            $shops_order['order_ids'][] = $order_info['order_id'];
            $shops_order_list[$order_info['shops_code']] = $shops_order;
        }
        foreach($list as $k=>$shops_info){
            //初始化
            $reverse_point=0;$reverse_storage_money=0;
            $shops_order = $shops_order_list[$shops_info['shops_code']];
            $order_ids = $shops_order['order_ids'];
            //产品数量
            $goods_numbers = M('order_goods_info')->where(array('order_id'=>array('in',$order_ids)))->getField('goods_number',true);
            $shops_order['goods_number'] = array_sum($goods_numbers);
            //退款说明
            $reverse_moneyss = M('reverse_money_info')->where(array('reverse_status'=>2,'forward_order_id'=>array('in',$order_ids)))->getField('reverse_order_id,reverse_value,reverse_point,reverse_storage_money',true);
            $reverse_values = getFieldsByKey($reverse_moneyss, 'reverse_value');
            if($reverse_values) $shops_info['reverse_value'] = array_sum ($reverse_values);
            //退还积分
            $reverse_points = getFieldsByKey($reverse_moneyss, 'reverse_point');
            if($reverse_points) $reverse_point = array_sum ($reverse_points);
            //退还储值卡
            $reverse_storage_moneys = getFieldsByKey($reverse_moneyss, 'reverse_storage_money');
            if($reverse_storage_moneys) $reverse_storage_money = array_sum ($reverse_storage_moneys);
            
            //优惠券，优惠码，积分初始化
            $pay_discount_list = M('order_pay_discount_info')->where(array('order_id'=>array('in',$order_ids)))->select();
            $coupon_value = 0;$coupon_code_value = 0;$total_point = 0;$storage_money=0;
            foreach($pay_discount_list as $j=>$pay_discount){
                 //优惠码金额
                if($pay_discount['discount_type'] == 2) $coupon_code_value += $pay_discount['discount_value'];
                //优惠券金额
                if($pay_discount['discount_type'] == 3) $coupon_value += $pay_discount['discount_value'];
                //积分
                if($pay_discount['discount_type'] == 4) $total_point += intval($pay_discount['discount_code']);
                //积分
                if($pay_discount['discount_type'] == 5) $storage_money += $pay_discount['discount_value'];
            }
            $shops_order['coupon_value'] = $coupon_value;
            $shops_order['coupon_code_value'] = $coupon_code_value;
            $shops_order['pay_value'] = $shops_order['pay_value'] - $shops_info['reverse_value'];
            $shops_order['total_point'] = $total_point - $reverse_point;
            $shops_order['storage_money'] = $storage_money - $reverse_storage_money;
            if($shops_order) $shops_info = array_merge($shops_info,$shops_order);
            $list[$k] = $shops_info;
            //合计总数
            $total_info['order_count'] += $shops_info['order_count'];
            $total_info['goods_number'] += $shops_info['goods_number'];
            $total_info['total_value'] += $shops_info['total_value'];
            $total_info['total_pay_value'] += $shops_info['total_pay_value'];
            $total_info['reverse_value'] += $shops_info['reverse_value'];
            $total_info['pay_value'] += $shops_info['pay_value'];
            $total_info['total_point'] += $shops_info['total_point'];
            $total_info['storage_money'] += $shops_info['storage_money'];
            $total_info['coupon_value'] += $shops_info['coupon_value'];
            $total_info['coupon_code_value'] += $shops_info['coupon_code_value'];
            
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
            $shops_order['total_value'] += price_format($order_info['total_value']);
            $shops_order['total_pay_value'] += price_format($order_info['total_value']);
            if($order_info['pay_value'] > 0) $shops_order['pay_value'] += price_format ($order_info['pay_value']);
            else $shops_order['pay_value'] += price_format (0);
            $shops_order['order_ids'][] = $order_info['order_id'];
            $shops_order_list[$order_info['shops_code']] = $shops_order;
        }
        foreach($list as $k=>$shops_info){
            //初始化
            $reverse_point=0;$reverse_storage_money=0;
            $shops_order = $shops_order_list[$shops_info['shops_code']];
            $order_ids = $shops_order['order_ids'];
            //产品数量
            $goods_numbers = M('order_goods_info')->where(array('order_id'=>array('in',$order_ids)))->getField('goods_number',true);
            $shops_order['goods_number'] = array_sum($goods_numbers);
            //退款说明
            $reverse_moneyss = M('reverse_money_info')->where(array('reverse_status'=>2,'forward_order_id'=>array('in',$order_ids)))->getField('reverse_order_id,reverse_value,reverse_point,reverse_storage_money',true);
            $reverse_values = getFieldsByKey($reverse_moneyss, 'reverse_value');
            if($reverse_values) $shops_info['reverse_value'] = array_sum ($reverse_values);
            //退还积分
            $reverse_points = getFieldsByKey($reverse_moneyss, 'reverse_point');
            if($reverse_points) $reverse_point = array_sum ($reverse_points);
            //退还储值卡
            $reverse_storage_moneys = getFieldsByKey($reverse_moneyss, 'reverse_storage_money');
            if($reverse_storage_moneys) $reverse_storage_money = array_sum ($reverse_storage_moneys);
            
            //优惠券，优惠码，积分初始化
            $pay_discount_list = M('order_pay_discount_info')->where(array('order_id'=>array('in',$order_ids)))->select();
            $coupon_value = 0;$coupon_code_value = 0;$total_point = 0;$storage_money=0;
            foreach($pay_discount_list as $j=>$pay_discount){
                 //优惠码金额
                if($pay_discount['discount_type'] == 2) $coupon_code_value += $pay_discount['discount_value'];
                //优惠券金额
                if($pay_discount['discount_type'] == 3) $coupon_value += $pay_discount['discount_value'];
                //积分
                if($pay_discount['discount_type'] == 4) $total_point += intval($pay_discount['discount_code']);
                //积分
                if($pay_discount['discount_type'] == 5) $storage_money += $pay_discount['discount_value'];
            }
            $shops_order['coupon_value'] = price_format($coupon_value);
            $shops_order['coupon_code_value'] = price_format($coupon_code_value);
            $shops_order['pay_value'] = price_format($shops_order['pay_value'] - $shops_info['reverse_value']);
            $shops_order['total_point'] = intval($total_point - $reverse_point);
            $shops_order['storage_money'] = price_format($storage_money - $reverse_storage_money);
            $shops_order['order_count'] = intval($shops_order['order_count']);
            if($shops_order) $shops_info = array_merge($shops_info,$shops_order);
            $list[$k] = $shops_info;
            //合计总数
            $total_info['order_count'] += $shops_info['order_count'];
            $total_info['goods_number'] += $shops_info['goods_number'];
            $total_info['total_value'] += $shops_info['total_value'];
            $total_info['total_pay_value'] += $shops_info['total_pay_value'];
            $total_info['reverse_value'] += $shops_info['reverse_value'];
            $total_info['pay_value'] += $shops_info['pay_value'];
            $total_info['total_point'] += $shops_info['total_point'];
            $total_info['storage_money'] += $shops_info['storage_money'];
            $total_info['coupon_value'] += $shops_info['coupon_value'];
            $total_info['coupon_code_value'] += $shops_info['coupon_code_value'];
            
        }
        if($total_info) {$total_info['shops_name']='总计';$list[] = $total_info;}
        
        //导出的模版
        $export_fields = $this->export_fields();
        //导出的数据
        $export_model->title = '总报表数据';
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
        if($shops_code  && !in_array('null', $shops_code)) $where['shops_code'] = array('in',$shops_code);
        else $where['shops_code'] = array('neq','');
        $pay_start = I('pay_start','','trim,htmlspecialchars');
        $pay_end = I('pay_end','','trim,htmlspecialchars');
        if($pay_start && !$pay_end) $where['pay_time'] = array('gt',$pay_start);
        if(!$pay_start && $pay_end) $where['pay_time'] = array('lt',$pay_end);
        if($pay_start && $pay_end) $where['pay_time'] = array('between',$pay_start.','.$pay_end);
        $create_start = I('create_start','','trim,htmlspecialchars');
        $create_end = I('create_end','','trim,htmlspecialchars');
        if($create_start && !$create_end) $where['create_time'] = array('gt',$create_start);
        if(!$create_start && $create_end) $where['create_time'] = array('lt',$create_end);
        if($create_start && $create_end) $where['create_time'] = array('between',$create_start.','.$create_end);
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
            'url' => U('FinancialReport/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('FinancialReport/export').'"><i class="icon-download icon-white"></i> 导出报表</a>',
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
                )
            )
        );
    }
    //验证管理员操作商品的其他权限
    private function check_access_for_report() {
        if(check_admin_access('show_fr_shops_list_all',1,'other')){ //验证是否可以查看所有SKU
            return null;
        }elseif(check_admin_access('show_fr_shops_list_shops',1,'other')){ //验证是否可以查看本商家SKU
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
            'total_value'=>'订单总金额',
            'total_pay_value'=>'收款金额',
            'reverse_value'=>'退款金额',
            'pay_value'=>'净金额',
            'total_point'=>'净积分',
            'storage_money'=>'净储值卡',
            'coupon_value'=>'优惠券金额',
            'coupon_code_value'=>'优惠码金额'
        );
    }
}
