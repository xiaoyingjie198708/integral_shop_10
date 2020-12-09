<?php


namespace Home\Controller;

class LabelFinancialReportController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $list = D('GoodsLabel')->where($where)->order('create_time asc')->select();
        $total_real_pay_cash = 0;
        $total_wh_value = 0;
        $total_real_storage_money = 0;
        $total_reverse_cash = 0;
        $total_reverse_storage_money = 0;
        $parent_label_arr = array();
        foreach ($list as $k=>$info){
             if($info['label_level'] == 2) {$info['label_path'] = '0_'.$info['label_parent_id'];}
            else {$info['label_path'] = '0';$info['label_parent_id']='0';}
            if($info['label_level'] == 2){
                $goods_ids = M('goods_label_relation')->where(array('label_id'=>$info['label_id']))->getField('goods_id',true);
                $order_where = $this->get_order_where();
                $order_where['info.goods_id'] = array('in',$goods_ids);
                $goods_detail_list = M('goods_use_detail_info as detail,tm_goods_base_info info')->where('detail.goods_code=info.goods_code')->where($order_where)->field('detail.*')->select();
                $real_pay_cash = 0;
                $wh_value = 0;
                $real_storage_money = 0;
                $reverse_cash = 0;
                $reverse_storage_money = 0;
                foreach($goods_detail_list as $j=>$detail_info){
                    $real_pay_cash += $detail_info['real_pay_cash'];
                    $real_storage_money += $detail_info['real_pay_storage_money'];
                    $reverse_cash += $detail_info['reverse_cash'];
                    $reverse_storage_money += $detail_info['reverse_storage_money'];
                    if($detail_info['coupon_category_type'] == 2) {
                        $wh_value += $detail_info['real_pay_coupon_value'] + $detail_info['real_pay_coupon_code_value'];
                    }
                }
                //二级标签
                $info['real_pay_cash'] = $real_pay_cash;
                $info['wh_value'] = $wh_value;
                $info['real_storage_money'] = $real_storage_money;
                $info['reverse_cash'] = $reverse_cash;
                $info['reverse_storage_money'] = $reverse_storage_money;
                $info['real_total_value'] = $real_pay_cash + $wh_value + $real_storage_money;
                $info['reverse_total_value'] = $reverse_cash + $reverse_storage_money;
                $info['clear_real_money'] = $info['real_total_value'] - $info['reverse_total_value'];
                //一级标签
                $parent_label = $parent_label_arr[$info['label_parent_id']];
                $parent_label['real_pay_cash'] += $info['real_pay_cash'];
                $parent_label['wh_value'] += $info['wh_value'];
                $parent_label['real_storage_money'] += $info['real_storage_money'];
                $parent_label['reverse_cash'] += $info['reverse_cash'];
                $parent_label['reverse_storage_money'] += $info['reverse_storage_money'];
                $parent_label['real_total_value'] += $info['real_total_value'];
                $parent_label['reverse_total_value'] += $info['reverse_total_value'];
                $parent_label['clear_real_money'] += $info['clear_real_money'];
                $parent_label_arr[$info['label_parent_id']] = $parent_label;
                //总的统计
                $total_real_pay_cash += $real_pay_cash;
                $total_wh_value += $wh_value;
                $total_real_storage_money += $real_storage_money;
                $total_reverse_cash += $reverse_cash;
                $total_reverse_storage_money += $reverse_storage_money;
            }
            $list[$k] = $info;
        }
        $total_data['real_pay_cash'] = $total_real_pay_cash;
        $total_data['wh_value'] = $total_wh_value;
        $total_data['real_storage_money'] = $total_real_storage_money;
        $total_data['reverse_cash']  = $total_reverse_cash;
        $total_data['reverse_storage_money'] = $total_reverse_storage_money;
        $total_data['real_total_value'] = $total_real_pay_cash + $total_wh_value + $total_real_storage_money;
        $total_data['reverse_total_value'] = $total_reverse_cash + $total_reverse_storage_money;
        $total_data['clear_real_money'] += $total_data['real_total_value'] - $total_data['reverse_total_value'];
        $list = \Org\My\Tree::_getCustomTree($list,0,0,'label_id','label_parent_id');
        foreach ($list as $k=>$info){
            $temp_data = $parent_label_arr[$info['label_id']];
            if($temp_data){
                $info['real_pay_cash'] = $temp_data['real_pay_cash'];
                $info['wh_value'] = $temp_data['wh_value'];
                $info['real_storage_money'] = $temp_data['real_storage_money'];
                $info['reverse_cash'] = $temp_data['reverse_cash'];
                $info['reverse_storage_money'] = $temp_data['reverse_storage_money'];
                $info['real_total_value'] = $temp_data['real_total_value'];
                $info['reverse_total_value'] = $temp_data['reverse_total_value'];
                $info['clear_real_money'] = $temp_data['clear_real_money'];
                $list[$k] = $info;
            }
            //金额为空去掉
            if(!$info['real_pay_cash'] && !$info['wh_value'] && !$info['real_storage_money']) unset ($list[$k]);
        }
        $this->search($this->get_search());
        $this->assign('list', $list);
        $this->assign('total_data', $total_data);
        $this->display();
    }
    
    public function get_label_list(){
        $label_parent_id = I('id','','trim,htmlspecialchars');
        $where = array('label_status'=>array('neq',0),'label_parent_id'=>$label_parent_id);
        $label_list = M('goods_label')->where($where)->getField('label_id,label_name',true);
        $this->ajaxReturn($label_list);
    }
    
    public function export(){
        $where = $this->get_where();
        $export_model = new \Org\My\Export('goods_label');
        $export_model->limit = 50000;
        $export_model->setCount(M('goods_label')->where($where)->count());
        $list = $export_model->where($where)->order('create_time asc')->getExportData();
        $total_real_pay_cash = 0;
        $total_wh_value = 0;
        $total_real_storage_money = 0;
        $total_reverse_cash = 0;
        $total_reverse_storage_money = 0;
        $parent_label_arr = array();
        $parent_names = array();
        foreach ($list as $k=>$info){
             if($info['label_level'] == 2) {$info['label_path'] = '0_'.$info['label_parent_id'];}
            else {
                $info['label_path'] = '0';$info['label_parent_id']='0';
                $parent_names[$info['label_id']] = $info['label_name'];
            }
            if($info['label_level'] == 2){
                $goods_ids = M('goods_label_relation')->where(array('label_id'=>$info['label_id']))->getField('goods_id',true);
                $order_where = $this->get_order_where();
                $order_where['info.goods_id'] = array('in',$goods_ids);
                $goods_detail_list = M('goods_use_detail_info as detail,tm_goods_base_info info')->where('detail.goods_code=info.goods_code')->where($order_where)->field('detail.*')->select();
                $real_pay_cash = 0;
                $wh_value = 0;
                $real_storage_money = 0;
                $reverse_cash = 0;
                $reverse_storage_money = 0;
                foreach($goods_detail_list as $j=>$detail_info){
                    $real_pay_cash += $detail_info['real_pay_cash'];
                    $real_storage_money += $detail_info['real_pay_storage_money'];
                    $reverse_cash += $detail_info['reverse_cash'];
                    $reverse_storage_money += $detail_info['reverse_storage_money'];
                    if($detail_info['coupon_category_type'] == 2) {
                        $wh_value += $detail_info['real_pay_coupon_value'] + $detail_info['real_pay_coupon_code_value'];
                    }
                }
                //二级标签
                $info['real_pay_cash'] = $real_pay_cash;
                $info['wh_value'] = $wh_value;
                $info['real_storage_money'] = $real_storage_money;
                $info['reverse_cash'] = $reverse_cash;
                $info['reverse_storage_money'] = $reverse_storage_money;
                $info['real_total_value'] = $real_pay_cash + $wh_value + $real_storage_money;
                $info['reverse_total_value'] = $reverse_cash + $reverse_storage_money;
                $info['clear_real_money'] = $info['real_total_value'] + $info['reverse_total_value'];
                //一级标签
                $parent_label = $parent_label_arr[$info['label_parent_id']];
                $parent_label['real_pay_cash'] += $info['real_pay_cash'];
                $parent_label['wh_value'] += $info['wh_value'];
                $parent_label['real_storage_money'] += $info['real_storage_money'];
                $parent_label['reverse_cash'] += $info['reverse_cash'];
                $parent_label['reverse_storage_money'] += $info['reverse_storage_money'];
                $parent_label['real_total_value'] += $info['real_total_value'];
                $parent_label['reverse_total_value'] += $info['reverse_total_value'];
                $parent_label['clear_real_money'] += $info['clear_real_money'];
                $parent_label_arr[$info['label_parent_id']] = $parent_label;
                //总的统计
                $total_real_pay_cash += $real_pay_cash;
                $total_wh_value += $wh_value;
                $total_real_storage_money += $real_storage_money;
                $total_reverse_cash += $reverse_cash;
                $total_reverse_storage_money += $reverse_storage_money;
            }
            $list[$k] = $info;
        }
        $total_data['real_pay_cash'] = $total_real_pay_cash;
        $total_data['wh_value'] = $total_wh_value;
        $total_data['real_storage_money'] = $total_real_storage_money;
        $total_data['reverse_cash']  = $total_reverse_cash;
        $total_data['reverse_storage_money'] = $total_reverse_storage_money;
        $total_data['real_total_value'] = $total_real_pay_cash + $total_wh_value + $total_real_storage_money;
        $total_data['reverse_total_value'] = $total_reverse_cash + $total_reverse_storage_money;
        $total_data['clear_real_money'] += $total_data['real_total_value'] - $total_data['reverse_total_value'];
        $list = \Org\My\Tree::_getCustomTree($list,0,0,'label_id','label_parent_id');
        foreach ($list as $k=>$info){
            $temp_data = $parent_label_arr[$info['label_id']];
            if($temp_data){
                $info['real_pay_cash'] = $temp_data['real_pay_cash'];
                $info['wh_value'] = $temp_data['wh_value'];
                $info['real_storage_money'] = $temp_data['real_storage_money'];
                $info['reverse_cash'] = $temp_data['reverse_cash'];
                $info['reverse_storage_money'] = $temp_data['reverse_storage_money'];
                $info['real_total_value'] = $temp_data['real_total_value'];
                $info['reverse_total_value'] = $temp_data['reverse_total_value'];
                $info['clear_real_money'] = $temp_data['clear_real_money'];
                $list[$k] = $info;
            }
            //金额为空去掉
            if(!$info['real_pay_cash'] && !$info['wh_value'] && !$info['real_storage_money']) unset ($list[$k]);
        }
        $total_data['label_name'] = '合计';
        if($list) $list[] = $total_data;
        $temp_list = array();
        $parent_list = array();
        $first_flag = true;
        foreach($list as $k=>$info){
            if($info['label_level'] == 2) $info['parent_name'] = $parent_names[$info['label_parent_id']];
            $info['real_pay_cash'] = price_format($info['real_pay_cash']);
            $info['wh_value'] = price_format($info['wh_value']);
            $info['real_storage_money'] = price_format($info['real_storage_money']);
            $info['reverse_cash'] = price_format($info['reverse_cash']);
            $info['reverse_storage_money'] = price_format($info['reverse_storage_money']);
            $info['real_total_value'] = price_format($info['real_total_value']);
            $info['reverse_total_value'] = price_format($info['reverse_total_value']);
            $info['clear_real_money'] = price_format($info['clear_real_money']);
            if($info['label_level'] == 2){
                if($first_flag) $info['first_flag'] = true;
                $info['parent_name'] = $parent_names[$info['label_parent_id']];
                $first_flag = false;
                $last_label_id = M('goods_label')->where(array('label_parent_id'=>$info['label_parent_id']))->order('create_time desc')->getField('label_id');
                if($last_label_id == $info['label_id']) {
                    $info['last_flag'] = true;
                    $temp_list[] = $info;
                    $temp_list[] = $parent_list[$info['label_parent_id']];
                }else{
                    $temp_list[] = $info;
                }
            }elseif($info['label_level'] == 1){
                $parent_list[$info['label_id']] = $info;unset($list[$k]);
                $first_flag = true;
            }else{
                $total_data = $info;
            }
        }
        $total_data['label_level'] = 0;
        $temp_list[] = $total_data;
        $init_row = 3;
        $first_num = 0;
        foreach($temp_list as $k=>$info){
            if($info['first_flag']) $first_num = $k + $init_row;
            if($info['last_flag']) {
                $info['vertical_center'] = 'A'.$first_num;
                $info['merage'] = 'A'.$first_num.':A'.( $k + $init_row );
            }
            if($info['label_level'] == 1) {
                $info['parent_name'] = $info['label_name'];
                $info['merage'] = 'A'.($k+$init_row).':B'.($k+$init_row);
                $info['color'] = '#ABABAB';
            }
            if($info['label_level'] == 0) {
                $info['parent_name'] = $info['label_name'];
                $info['merage'] = 'A'.($k+$init_row).':B'.($k+$init_row);
                $info['color'] = '#8A2BE2';
            }
            $temp_list[$k] = $info;
        }
        //导出的模版
        $export_fields = $this->export_fields();
        //导出的数据
        $export_model->title = '总报表数据';
        $export_model->execl_fields = $export_fields;
        $export_model->setData($temp_list);
        $export_model->setHeaderName('财务总报表');
        $export_page = $export_model->export();
        exit($export_page);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        $label_id = I('label_id');
        $category = array();
        $where = array('label_level'=>1,'label_status'=>array('neq',0));
        $label_arr[] = M('goods_label')->where($where)->getField('label_id,label_name',true);
        if($label_id) {
            unset($where['label_level']);
            $label_id = explode('_',$label_id);
            for($i=0;$i<count($label_id);$i++) {
                $where['label_parent_id'] = $label_id[$i];
                $temp = M('goods_label')->where($where)->getField('label_id,label_name',true);
                if($temp) $label_arr[] = $temp;
            }
        }
        return array(
            'url'=>U('LabelFinancialReport/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('LabelFinancialReport/export').'"><i class="icon-download icon-white"></i> 导出报表</a>',
            ),
            'main'=>array(
                array(
                    'name' => array('pay_start', 'pay_end'),
                    'show_name' => '支付时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                ),
                array(
                    'name'=>'label_id',
                    'show_name'=>'标签名称',
                    'tip'=>'请选择标签名称',
                    'select'=>$label_arr,
                    'url'=>U('LabelFinancialReport/get_label_list'), //返回的一维数组  id=>name  参数接受的是id
                    'type'=>'select-ajax'
                ),
            )
        );
    }
    
    private function get_where(){
        $where = array();
        $where['label_status'] = array('gt',0);
        $label_id = I('label_id','','trim,htmlspecialchars');
        $label_ids = explode('_', $label_id);
        if(count($label_ids) == 1){
            $parent_label_id = $label_ids[0];
            $label_ids = M('goods_label')->where(array('label_parent_id'=>$parent_label_id))->getField('label_id',true);
            $label_ids[] = $parent_label_id;
        }
        if($label_id) $where['label_id'] = array('in',$label_ids);
        return $where;
    }
    
    private function get_order_where(){
        $where = array();
        $where['order_type'] = 1;
        $pay_start = I('pay_start','','trim,htmlspecialchars');
        $pay_end = I('pay_end','','trim,htmlspecialchars');
        $this->assign('pay_start', $pay_start);
        $this->assign('pay_end', $pay_end);
        if($pay_start && !$pay_end) $where['detail.pay_time'] = array('gt',$pay_start);
        if(!$pay_start && $pay_end) $where['detail.pay_time'] = array('lt',$pay_end);
        if($pay_start && $pay_end) $where['detail.pay_time'] = array('between',array($pay_start,$pay_end));
        return $where;
    }
    
    private function export_fields(){
        return array(
            'parent_name'=>'父级标签名称',
            'label_name'=>'标签名称',
            'real_pay_cash'=>'收取金额',
            'wh_value'=>'文惠券',
            'real_storage_money'=>'储值卡金额',
            'real_total_value'=>'收款总额',
            'reverse_cash'=>'退现金额',
            'reverse_storage_money'=>'退储值卡金额',
            'reverse_total_value'=>'退款总额',
            'clear_real_money'=>'净金额',
        );
    }
}
