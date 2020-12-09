<?php


namespace Home\Controller;

class ReverseMoneyReportController extends BaseController{
    
    public function index(){
        $mod = D('Order');
        $shops_where = $this->get_shops_where();
        $list = M('shops_base_info')->where($shops_where)->select();
        $where = $this->get_order_where();
        //已支付订单
        $where['pay_status'] = 12;
        foreach($list as $k=>$shops_info){
            $where['shops_code'] = $shops_info['shops_code'];
            $order_ids = $mod->where($where)->getField('order_id',true);
            //退款说明
            $reverse_moneyss = M('reverse_money_info')->where(array('reverse_status'=>2,'forward_order_id'=>array('in',$order_ids)))->getField('reverse_order_id,reverse_value,reverse_point,reverse_storage_money',true);
            $reverse_values = getFieldsByKey($reverse_moneyss, 'reverse_value');
            if($reverse_values) $shops_info['reverse_value'] = array_sum ($reverse_values);
            //退还积分
            $reverse_points = getFieldsByKey($reverse_moneyss, 'reverse_point');
            if($reverse_points) $shops_info['reverse_point'] = array_sum ($reverse_points);
            //退还储值卡
            $reverse_storage_moneys = getFieldsByKey($reverse_moneyss, 'reverse_storage_money');
            if($reverse_storage_moneys) $shops_info['reverse_storage_money'] = array_sum ($reverse_storage_moneys);
            $list[$k] = $shops_info;
            //合计
            $total_info['reverse_value'] += $shops_info['reverse_value'];
            $total_info['reverse_point'] += $shops_info['reverse_point'];
            $total_info['reverse_storage_money'] += $shops_info['reverse_storage_money'];
        }
        $this->assign('total_info', $total_info);
        $this->assign('list', $list);
        $this->search($this->order_list_search());
        $this->display();
    }
    
    //退货订单
    public function details(){
        $where = $this->get_order_where();
        $page = new \Org\My\Page(M('reverse_order_base_info')->where($where)->count(), 10);
        $this->assign('page', $page->show());
        $list = M('reverse_order_base_info')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('create_time desc')->select();
        $order_ids = getFieldsByKey($list, 'forward_order_id');
        $order_shops_codes = D('Order')->where(array('order_id'=>array('in',$order_ids)))->getField('order_id,shops_code',true);
        $shops_codes = array_values($order_shops_codes);
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $reverse_order_ids = getFieldsByKey($list, 'reverse_order_id');
        $reverse_money_list = M('reverse_money_info')->where(array('reverse_order_id'=>array('in',$reverse_order_ids)))->getField('reverse_order_id,reverse_value,reverse_point,reverse_storage_money',true);
        foreach($list as $k=>$reverse_info){
            $reverse_goods_list = M('reverse_order_goods_info')->where(array('reverse_order_id'=>$reverse_info['reverse_order_id']))->field('goods_name,numbers')->select();
            $goods_names = getFieldsByKey($reverse_goods_list, 'goods_name');
            $goods_numbers = getFieldsByKey($reverse_goods_list, 'numbers');
            $reverse_info['goods_name'] = implode($goods_names, ',');
            $reverse_info['goods_number'] = implode($goods_numbers, ',');
            $reverse_money = $reverse_money_list[$reverse_info['reverse_order_id']];
            $reverse_info['shops_name'] = $shops_names[$order_shops_codes[$reverse_info['forward_order_id']]];
            $reverse_info['reverse_value'] = $reverse_money['reverse_value'];
            $reverse_info['reverse_point'] = $reverse_money['reverse_point'];
            $reverse_info['reverse_storage_money'] = $reverse_money['reverse_storage_money'];
            $list[$k] = $reverse_info;
        }
        $this->search($this->detail_list_search());
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->display();
    }
    
    public function quick_view(){
        $reverse_order_id = I('reverse_order_id','','trim,htmlspecialchars');
        $info = M('reverse_money_info')->where(array('reverse_order_id'=>$reverse_order_id))->find();
        $this->assign('info', $info);
        $content = $this->fetch('quick_view');
        $this->oldAjaxReturn(0, $content, 1);
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
        //已支付订单
        $where['pay_status'] = 12;
        foreach($list as $k=>$shops_info){
            $where['shops_code'] = $shops_info['shops_code'];
            $order_ids = $mod->where($where)->getField('order_id',true);
            //退款说明
            $reverse_moneyss = M('reverse_money_info')->where(array('reverse_status'=>2,'forward_order_id'=>array('in',$order_ids)))->getField('reverse_order_id,reverse_value,reverse_point,reverse_storage_money',true);
            $reverse_values = getFieldsByKey($reverse_moneyss, 'reverse_value');
            if($reverse_values) $shops_info['reverse_value'] = array_sum ($reverse_values);
            //退还积分
            $reverse_points = getFieldsByKey($reverse_moneyss, 'reverse_point');
            if($reverse_points) $shops_info['reverse_point'] = array_sum ($reverse_points);
            //退还储值卡
            $reverse_storage_moneys = getFieldsByKey($reverse_moneyss, 'reverse_storage_money');
            if($reverse_storage_moneys) $shops_info['reverse_storage_money'] = array_sum ($reverse_storage_moneys);
            $shops_info['reverse_value'] = price_format($shops_info['reverse_value']);
            $shops_info['reverse_point'] = intval($shops_info['reverse_point']);
            $shops_info['reverse_storage_money'] = price_format($shops_info['reverse_storage_money']);
            $list[$k] = $shops_info;
            //合计
            $total_info['reverse_value'] += $shops_info['reverse_value'];
            $total_info['reverse_point'] += $shops_info['reverse_point'];
            $total_info['reverse_storage_money'] += $shops_info['reverse_storage_money'];
        }
        if($total_info) {$total_info['shops_name']='总计';$list[] = $total_info;}
        //导出的模版
        $export_fields = $this->export_fields();
        //导出的数据
        $export_model->title = '退款汇总报表数据';
        $export_model->execl_fields = $export_fields;
        $export_model->setData($list);
        $export_page = $export_model->export();
        exit($export_page);
    }
    
    public function export_details(){
        $limit = 500;
        $mod = D('Order');
        $where = $this->get_order_where();
        $export_model = new \Org\My\Export('shops_base_info');
        $export_model->limit = $limit;
        $export_model->setCount(M('reverse_order_base_info')->where($where)->count());
        $list = M('reverse_order_base_info')->where($where)->order('create_time desc')->select();
        $order_ids = getFieldsByKey($list, 'forward_order_id');
        $order_shops_codes = D('Order')->where(array('order_id'=>array('in',$order_ids)))->getField('order_id,shops_code',true);
        $shops_codes = array_values($order_shops_codes);
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $reverse_order_ids = getFieldsByKey($list, 'reverse_order_id');
        $reverse_money_list = M('reverse_money_info')->where(array('reverse_order_id'=>array('in',$reverse_order_ids)))->getField('reverse_order_id,reverse_value,reverse_point,reverse_storage_money',true);
        foreach($list as $k=>$reverse_info){
            $reverse_goods_list = M('reverse_order_goods_info')->where(array('reverse_order_id'=>$reverse_info['reverse_order_id']))->field('goods_name,numbers')->select();
            $goods_names = getFieldsByKey($reverse_goods_list, 'goods_name');
            $goods_numbers = getFieldsByKey($reverse_goods_list, 'numbers');
            $reverse_info['goods_name'] = implode($goods_names, ',');
            $reverse_info['goods_number'] = implode($goods_numbers, ',');
            $reverse_money = $reverse_money_list[$reverse_info['reverse_order_id']];
            $reverse_info['shops_name'] = $shops_names[$order_shops_codes[$reverse_info['forward_order_id']]];
            $reverse_info['reverse_value'] = $reverse_money['reverse_value'];
            $reverse_info['reverse_point'] = $reverse_money['reverse_point'];
            $reverse_info['reverse_storage_money'] = $reverse_money['reverse_storage_money'];
            $reverse_info['order_status'] = id2name($reverse_info['order_status'], 'reverse_order_status');
            $list[$k] = $reverse_info;
        }
        //导出的模版
        $export_fields = $this->export_detail_fields();
        //导出的数据
        $export_model->title = '退货订单明细报表';
        $export_model->execl_fields = $export_fields;
        $export_model->setData($list);
        $export_page = $export_model->export();
        exit($export_page);
    }
    /*---------------------------------------------------受保护方法------------------------------------------------------*/
    //搜索条件
    private function get_order_where(){
        $where = array();
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
        $reverse_start = I('reverse_start','','trim,htmlspecialchars');
        $reverse_end = I('reverse_end','','trim,htmlspecialchars');
        if($reverse_start || $reverse_end){
            $reverse_where = array('reverse_status'=>2);
            if($reverse_start && !$reverse_end) $reverse_where['update_time'] = array('gt',$reverse_start);
            if(!$reverse_start && $reverse_end) $reverse_where['update_time'] = array('lt',$reverse_end);
            if($reverse_start && $reverse_end) $reverse_where['update_time'] = array('between',$reverse_start.','.$reverse_end);
            $order_ids = M('reverse_money_info')->where($reverse_where)->getField('forward_order_id',true);
            $where['order_id'] = array('in',$order_ids);
        }
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
            'url' => U('ReverseMoneyReport/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('ReverseMoneyReport/export').'"><i class="icon-download icon-white"></i> 导出报表</a>',
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
                    'name' => array('reverse_start', 'reverse_end'),
                    'show_name' => '退款时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                ),
                array(
                    'name' => array('pay_start', 'pay_end'),
                    'show_name' => '回款时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                )
            )
        );
    }
    
    //订单搜索条件
    private function detail_list_search() {
        $shops_list = M('shops_base_info')->getField('shops_code,shops_name',true);
        return array(
            'url' => U('ReverseMoneyReport/details'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('ReverseMoneyReport/export_details').'"><i class="icon-download icon-white"></i> 导出报表</a>',
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
        if(check_admin_access('show_rmr_shops_list_all',1,'other')){ //验证是否可以查看所有SKU
            return null;
        }elseif(check_admin_access('show_rmr_shops_list_shops',1,'other')){ //验证是否可以查看本商家SKU
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
    
    //导出模板
    private function export_fields(){
        return array(
            'shops_name'=>'商家名称',
            'order_count'=>'退款金额',
            'goods_number'=>'退回积分',
            'total_value'=>'退回储值卡',
        );
    }
    
    //导出模板
    private function export_detail_fields(){
        return array(
            'shops_name'=>'商家名称',
            'order_cur_id'=>'订单编号',
            'goods_name'=>'商品名称',
            'goods_number'=>'商品数量',
            'order_status'=>'退货状态',
            'reverse_value'=>'退货金额',
            'reverse_point'=>'退回积分',
            'reverse_storage_money'=>'退回储值卡',
            'create_time'=>'退货时间',
            'comment'=>'退货原因',
        );
    }
}
