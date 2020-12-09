<?php


namespace Home\Controller;

/**
 *商品消费结算报表
 * @author xiao.yingjie
 */
class GoodsUseReportController extends BaseController{
    
    public function index(){
        $mod = M('goods_use_detail_info');
        $where = $this->get_order_where();
        $page = new \Org\My\Page($mod->where($where)->count(),10);
        $list = $mod->where($where)->limit($page->firstRow .','.$page->listRows)->order('use_time desc')->select();
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
        $list = $export_model->where($where)->order('use_time desc')->getExportData();
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $goods_cost_prices = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_cost_price',true);
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
            $info['settlement_status'] = id2name('settlement_status',$info['settlement_status']);
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
    
    public function submit_check(){
        $use_ids = I('use_ids','','trim,htmlspecialchars');
        $use_ids = explode(',', trim($use_ids,','));
        if(count($use_ids) == 0) $this->oldAjaxReturn ('', '请至少选择1个未结算的商品', 0);
        $count = M('goods_use_detail_info')->where(array('id'=>array('in',$use_ids),'settlement_status'=>1))->count();
        if($count == 0) $this->oldAjaxReturn ('', '请至少选择1个未结算的商品', 0);
        M('goods_use_detail_info')->where(array('id'=>array('in',$use_ids),'settlement_status'=>1))->data(array('settlement_status'=>2))->save();
        $this->oldAjaxReturn ('', '保存成功', 1);
    }
    /*---------------------------------------------------受保护方法------------------------------------------------------*/
    //搜索条件
    private function get_order_where(){
        $where = array();
        $where['use_status'] = 2;//已消费
        $shops_codes = $this->check_access_for_report();
        $shops_code = I('shops_code','','trim,htmlspecialchars');
        if(is_array($shops_codes)){
            if($shops_code && !in_array('null', $shops_code)) $where['shops_code'] = array('in',array_intersect($shops_codes, $shops_code));
            else $where['shops_code'] = array('in',$shops_codes);
        }else{
            if($shops_code && !in_array('null', $shops_code)) $where['shops_code'] = array('in',$shops_code);
             else $where['shops_code'] = array('neq','');
        }
        $goods_name = I('goods_name','','trim,htmlspecialchars');
        if($goods_name) $where['goods_name'] = array('like','%'.$goods_name.'%');
        $pay_start = I('use_start','','trim,htmlspecialchars');
        $pay_end = I('use_end','','trim,htmlspecialchars');
        if($pay_start && !$pay_end) $where['use_time'] = array('gt',$pay_start);
        if(!$pay_start && $pay_end) $where['use_time'] = array('lt',$pay_end);
        if($pay_start && $pay_end) $where['use_time'] = array('between',$pay_start.','.$pay_end);
        $settlement_status = I('settlement_status',0,'intval');
        if($settlement_status) $where['settlement_status'] = $settlement_status;
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
            'url' => U('GoodsUseReport/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('GoodsUseReport/export').'"><i class="icon-download icon-white"></i> 导出报表</a>',
            ),
            'main' => array(
                 array(
                    'name' => 'shops_code[]',
                    'show_name' => '商家名称',
                    'select' => $shops_list,
                    'type' => 'select',
                    'multiple'=>true,
                     'param_name' => 'shops_code',
                ),
                array(
                    'name' => 'goods_name',
                    'show_name' => '商品名称',
                    'tip' => '请输入商品名称'
                ),
                array(
                    'name' => 'settlement_status',
                    'show_name' => '结算状态',
                    'select' => array(0=>'全部',1=>'未结算',2=>'已结算'),
                    'type' => 'select'
                ),
                array(
                    'name' => array('use_start', 'use_end'),
                    'show_name' => '核销时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                )
            ),
        );
    }
    //验证管理员操作商品的其他权限
    private function check_access_for_report() {
        if(check_admin_access('show_goods_use_shops_list_all',1,'other')){ //验证是否可以查看所有SKU
            return null;
        }elseif(check_admin_access('show_goods_use_shops_list_shops',1,'other')){ //验证是否可以查看本商家SKU
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
    
    //导出模板
    private function export_fields(){
        return array(
            'order_cur_id'=>'订单编号',
            'shops_name'=>'商家名称',
            'goods_code'=>'商品编码',
            'goods_name'=>'商品名称',
            'show_price'=>'销售价',
            'goods_cost_price'=>'结算单价',
            'pay_time'=>'购买时间',
            'settlement_status'=>'结算状态',
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
