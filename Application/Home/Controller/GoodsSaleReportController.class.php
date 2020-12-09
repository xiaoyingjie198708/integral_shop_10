<?php


namespace Home\Controller;

/**
 *商品销售流水报表
 * @author xiao.yingjie
 */
class GoodsSaleReportController extends BaseController{
    
    public function index(){
        $mod = M('goods_use_detail_info');
        $where = $this->get_order_where();
        $page = new \Org\My\Page($mod->where($where)->count(),10);
        $list = $mod->where($where)->limit($page->firstRow .','.$page->listRows)->order('pay_time desc')->select();
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_cost_prices = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_cost_price',true);
        $member_ids =  getFieldsByKey($list, 'member_id');
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        $order_ids = getFieldsByKey($list, 'order_id');
        $order_times = M('order_base_info')->where(array('order_id'=>array('in',$order_ids)))->getField('order_id,create_time',true);
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
            $order_info['member_account'] = $member_accounts[$order_info['member_id']];
            $order_info['order_time'] = $order_times[$order_info['order_id']];
            $list[$k] = $order_info;
        }
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->search($this->order_list_search());
        $this->display();
    }
    
    //快捷查看
    public function quick_view() {
        $detail_id = I('detail_id',0,'intval');
        $info = M('goods_use_detail_info')->where(array('id'=>$detail_id))->find();
        $info['ss_rmb'] = $info['real_pay_cash'] - $info['reverse_cash'];
        $info['ss_point'] = $info['real_pay_point'] - $info['reverse_point'];
        $info['ss_czk'] = $info['real_pay_storage_money'] - $info['reverse_storage_money'];
        $this->assign('info', $info);
        $contant = $this->fetch('quick_view');
        $this->oldAjaxReturn(0, $contant, 1);
    }
    
    public function export(){
        $limit = 50000;
        $where = $this->get_order_where();
        $export_model = new \Org\My\Export('goods_use_detail_info');
        $export_model->limit = $limit;
        $export_model->setCount($export_model->where($where)->count());
        $list = $export_model->where($where)->order('pay_time desc')->getExportData();
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_cost_prices = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_cost_price',true);
        $member_ids =  getFieldsByKey($list, 'member_id');
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        $order_ids = getFieldsByKey($list, 'order_id');
        $order_times = M('order_base_info')->where(array('order_id'=>array('in',$order_ids)))->getField('order_id,create_time',true);
        foreach($list as $k=>$info){
            $info['shops_name'] = $shops_names[$info['shops_code']];
            //固定积分
            if($info['integral']){
                if($info['goods_price'] && $info['max_integral']) {
                    $info['show_price'] = "￥".$info['goods_price'].'+'.$info['max_integral'].'积分';
                    $info['show_total_price'] = "￥".($info['goods_price'] * $info['goods_number']).'+'.($info['max_integral'] * $info['goods_number']).'积分';
                }
                if($info['goods_price'] && !$info['max_integral']) {
                    $info['show_price'] = "￥".$info['goods_price'];
                    $info['show_total_price'] = "￥".($info['goods_price'] * $info['goods_number']);
                }
                if(!$info['goods_price'] && $info['max_integral']) {
                    $info['show_price'] = $info['max_integral'].'积分';
                    $info['show_total_price'] = ($info['max_integral'] * $info['goods_number']).'积分';
                }
            }else{
                $info['show_price'] = "￥".$info['goods_price'];
                $info['show_total_price'] = "￥".($info['goods_price'] * $info['goods_number']);
            }
            $info['ss_rmb'] = $info['real_pay_cash'] - $info['reverse_cash'];
            $info['ss_point'] = $info['real_pay_point'] - $info['reverse_point'];
            $info['ss_czk'] = $info['real_pay_storage_money'] - $info['reverse_storage_money'];
            $info['goods_cost_price'] = $goods_cost_prices[$info['goods_code']];
            if($info['pay_value'] < 0) $info['pay_value'] = 0;
            $info['member_account'] = $member_accounts[$info['member_id']];
            $info['order_time'] = $order_times[$info['order_id']];
            $list[$k] = $info;
        }
        //导出的模版
        $export_fields = $this->export_fields();
        //导出的数据
        $export_model->title = '商品销售报表';
        $export_model->execl_fields = $export_fields;
        $export_model->setData($list);
        $export_page = $export_model->export();
        exit($export_page);
    }
    /*---------------------------------------------------受保护方法------------------------------------------------------*/
    //搜索条件
    private function get_order_where(){
        $where = array();
        $shops_codes = $this->check_access_for_report();
        $shops_code = I('shops_code','','trim,htmlspecialchars');
        if(is_array($shops_codes)){
            if($shops_code && !in_array('null', $shops_code)) $where['shops_code'] = array('in',array_intersect($shops_codes, $shops_code));
           else $where['shops_code'] = array('in',$shops_codes);
        }else{
            if($shops_code && !in_array('null', $shops_code)) $where['shops_code'] = array('in',$shops_code);
             else $where['shops_code'] = array('neq','');
        }
        $pay_start = I('pay_start','','trim,htmlspecialchars');
        $pay_end = I('pay_end','','trim,htmlspecialchars');
        if($pay_start && !$pay_end) $where['pay_time'] = array('gt',$pay_start);
        if(!$pay_start && $pay_end) $where['pay_time'] = array('lt',$pay_end);
        if($pay_start && $pay_end) $where['pay_time'] = array('between',$pay_start.','.$pay_end);
        $order_cur_id = I('order_cur_id','','trim,htmlspecialchars');
        if($order_cur_id) $where['order_cur_id'] = $order_cur_id;
        $goods_search = I('goods_search','','trim,htmlspecialchars');
        if($goods_search) $where['goods_code|goods_name'] = array('like',"'%".$goods_search."%'");
        $member_account = I('member_account','','trim,htmlspecialchars');
        if($member_account){
           $member_ids = M('user_member_base_info')->where(array('member_account'=>array('like',"'%".$member_account."%'")))->getField('member_id',true);
           $where['member_id'] = array('in',$member_ids);
        }
        $use_status = I('use_status',0,'intval');
        if($use_status) $where['use_status'] = $use_status;
        return $where;
    }
    
    //订单搜索条件
    private function order_list_search() {
        $where = array('shops_status'=>array('neq',0));
        $access_shops_codes = $this->check_access_for_report();
        if(is_array($access_shops_codes)){
            $where['shops_code'] = array('in',$access_shops_codes);
        }
        $shops_list = M('shops_base_info')->where($where)->getField('shops_code,shops_name',true);
        return array(
            'url' => U('GoodsSaleReport/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('GoodsSaleReport/export').'"><i class="icon-download icon-white"></i> 导出报表</a>',
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
                    'name' => array('pay_start', 'pay_end'),
                    'show_name' => '支付时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                )
            ),
            'other'=>array(
                array(
                    'name'=>'order_cur_id',
                    'show_name'=>'订单编号',
                    'type' => 'input'
                ),
                array(
                    'name'=>'goods_search',
                    'show_name'=>'商品名称/编码',
                    'type' => 'input'
                ),
                array(
                    'name'=>'member_account',
                    'show_name'=>'会员账号',
                    'type' => 'input'
                ),
                array(
                    'name' => 'use_status',
                    'show_name' => '核销状态',
                    'select' => array(0=>'全部',1=>'未核销',2=>'已核销'),
                    'type' => 'select'
                )
            )
        );
    }
    //验证管理员操作商品的其他权限
    private function check_access_for_report() {
        if(check_admin_access('show_goods_sale_shops_list_all',1,'other')){
            return null;
        }elseif(check_admin_access('show_goods_sale_shops_list_shops',1,'other')){
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
    
    //导出模板
    private function export_fields(){
        return array(
            'order_cur_id'=>'订单编号',
            'member_account'=>'会员账号',
            'shops_name'=>'商家名称',
            'goods_code'=>'商品编码',
            'goods_name'=>'商品名称',
            'show_price'=>'销售价',
            'goods_cost_price'=>'结算价',
            'order_time'=>'购买时间',
            'pay_time'=>'支付时间',
            'use_time'=>'核销时间',
            'goods_number'=>'商品数量',
            'pay_cash'=>'应付金额',
            'pay_point'=>'应付积分',
            'real_pay_cash'=>'实付金额',
            'real_pay_point'=>'实付积分',
            'real_pay_storage_money'=>'实付储值卡',
            'real_pay_coupon_value'=>'实付优惠券',
            'real_pay_coupon_code_value'=>'实付优惠码',
            'reverse_cash'=>'退款金额',
            'reverse_point'=>'退款积分',
            'reverse_storage_money'=>'退款储值卡',
            'ss_rmb'=>'实收金额',
            'ss_point'=>'实收积分',
            'ss_czk'=>'实收储值卡',
        );
    }
}
