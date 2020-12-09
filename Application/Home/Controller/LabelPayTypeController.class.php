<?php


namespace Home\Controller;

class LabelPayTypeController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $list = D('GoodsLabel')->where($where)->order('create_time asc')->select();
        $total_yinlian = 0;
        $total_zhifubao = 0;
        $total_weixin = 0;
        $total_wenhuiquan = 0;
        $total_chuzhika = 0;
        $parent_label_arr = array();
        foreach ($list as $k=>$info){
             if($info['label_level'] == 2) {$info['label_path'] = '0_'.$info['label_parent_id'];}
            else {$info['label_path'] = '0';$info['label_parent_id']='0';}
            if($info['label_level'] == 2){
                $goods_ids = M('goods_label_relation')->where(array('label_id'=>$info['label_id']))->getField('goods_id',true);
                $order_where = $this->get_order_where();
                $order_where['info.goods_id'] = array('in',$goods_ids);
                $goods_detail_list = M('goods_use_detail_info as detail,tm_goods_base_info info')->where('detail.goods_code=info.goods_code')->where($order_where)->field('detail.*')->select();
                $yinlian = 0;
                $zhifubao = 0;
                $weixin = 0;
                $wenhuiquan = 0;
                $chuzhika = 0;
                foreach($goods_detail_list as $j=>$detail_info){
                    if($detail_info['coupon_category_type'] == 2) {
                        $wenhuiquan += $detail_info['real_pay_coupon_value'] + $detail_info['real_pay_coupon_code_value'];
                    }
                    if($detail_info['payment_type'] == 1){//支付宝
                        $zhifubao += $detail_info['real_pay_cash'];
                    }elseif($detail_info['payment_type'] == 2){//银联
                        $yinlian += $detail_info['real_pay_cash'];
                    }elseif($detail_info['payment_type'] == 3){//微信支付
                        $weixin += $detail_info['real_pay_cash'];
                    }
                    $chuzhika += $detail_info['real_pay_storage_money'];
                }
                //二级标签
                $info['yinlian'] = $yinlian;
                $info['zhifubao'] = $zhifubao;
                $info['weixin'] = $weixin;
                $info['wenhuiquan'] = $wenhuiquan;
                $info['chuzhika'] = $chuzhika;
                $info['heji'] = $yinlian + $zhifubao + $weixin + $wenhuiquan + $chuzhika;
                //一级标签
                $parent_label = $parent_label_arr[$info['label_parent_id']];
                $parent_label['yinlian'] += $info['yinlian'];
                $parent_label['zhifubao'] += $info['zhifubao'];
                $parent_label['weixin'] += $info['weixin'];
                $parent_label['wenhuiquan'] += $info['wenhuiquan'];
                $parent_label['chuzhika'] += $info['chuzhika'];
                $parent_label_arr[$info['label_parent_id']] = $parent_label;
                //总的统计
                $total_yinlian += $yinlian;
                $total_zhifubao += $zhifubao;
                $total_weixin += $weixin;
                $total_wenhuiquan += $wenhuiquan;
                $total_chuzhika += $chuzhika;
            }
            $list[$k] = $info;
        }
        $total_data['yinlian'] = $total_yinlian;
        $total_data['zhifubao'] = $total_zhifubao;
        $total_data['weixin'] = $total_weixin;
        $total_data['wenhuiquan']  = $total_wenhuiquan;
        $total_data['chuzhika'] = $total_chuzhika;
        $total_data['heji'] = $total_yinlian + $total_zhifubao + $total_weixin + $total_wenhuiquan + $total_chuzhika;
        $list = \Org\My\Tree::_getCustomTree($list,0,0,'label_id','label_parent_id');
        foreach ($list as $k=>$info){
            $temp_data = $parent_label_arr[$info['label_id']];
            if($temp_data){
                $info['yinlian'] = $temp_data['yinlian'];
                $info['zhifubao'] = $temp_data['zhifubao'];
                $info['weixin'] = $temp_data['weixin'];
                $info['wenhuiquan'] = $temp_data['wenhuiquan'];
                $info['chuzhika'] = $temp_data['chuzhika'];
                $info['heji'] = $temp_data['yinlian'] + $temp_data['zhifubao'] + $temp_data['weixin'] + $temp_data['wenhuiquan'] + $temp_data['chuzhika'];
                $list[$k] = $info;
            }
            if(!$info['heji']) unset ($list[$k]);
        }
        $this->search($this->get_search());
        $this->assign('list', $list);
        $this->assign('total_data', $total_data);
        $this->display();
    }
    
    //导出报表
    public function export(){
        $where = $this->get_where();
        $export_model = new \Org\My\Export('goods_label');
        $export_model->limit = 50000;
        $export_model->setCount(M('goods_label')->where($where)->count());
        $list = $export_model->where($where)->order('create_time asc')->getExportData();
        $total_yinlian = 0;
        $total_zhifubao = 0;
        $total_weixin = 0;
        $total_wenhuiquan = 0;
        $total_chuzhika = 0;
        $parent_label_arr = array();
        $parent_names = array();
        foreach ($list as $k=>$info){
            if($info['label_level'] == 2){
                $info['label_path'] = '0_'.$info['label_parent_id'];
                $goods_ids = M('goods_label_relation')->where(array('label_id'=>$info['label_id']))->getField('goods_id',true);
                $order_where = $this->get_order_where();
                $order_where['info.goods_id'] = array('in',$goods_ids);
                $goods_detail_list = M('goods_use_detail_info as detail,tm_goods_base_info info')->where('detail.goods_code=info.goods_code')->where($order_where)->field('detail.*')->select();
                $yinlian = 0;
                $zhifubao = 0;
                $weixin = 0;
                $wenhuiquan = 0;
                $chuzhika = 0;
                foreach($goods_detail_list as $j=>$detail_info){
                    if($detail_info['coupon_category_type'] == 2) {
                        $wenhuiquan += $detail_info['real_pay_coupon_value'] + $detail_info['real_pay_coupon_code_value'];
                    }
                    if($detail_info['payment_type'] == 1){//支付宝
                        $zhifubao += $detail_info['real_pay_cash'];
                    }elseif($detail_info['payment_type'] == 2){//银联
                        $yinlian += $detail_info['real_pay_cash'];
                    }elseif($detail_info['payment_type'] == 3){//微信支付
                        $weixin += $detail_info['real_pay_cash'];
                    }
                    $chuzhika += $detail_info['real_pay_storage_money'];
                }
                //二级标签
                $info['yinlian'] = $yinlian;
                $info['zhifubao'] = $zhifubao;
                $info['weixin'] = $weixin;
                $info['wenhuiquan'] = $wenhuiquan;
                $info['chuzhika'] = $chuzhika;
                $info['heji'] = $yinlian + $zhifubao + $weixin + $wenhuiquan + $chuzhika;
                //一级标签
                $parent_label = $parent_label_arr[$info['label_parent_id']];
                $parent_label['yinlian'] += $info['yinlian'];
                $parent_label['zhifubao'] += $info['zhifubao'];
                $parent_label['weixin'] += $info['weixin'];
                $parent_label['wenhuiquan'] += $info['wenhuiquan'];
                $parent_label['chuzhika'] += $info['chuzhika'];
                $parent_label_arr[$info['label_parent_id']] = $parent_label;
                //总的统计
                $total_yinlian += $yinlian;
                $total_zhifubao += $zhifubao;
                $total_weixin += $weixin;
                $total_wenhuiquan += $wenhuiquan;
                $total_chuzhika += $chuzhika;
            }else{
                $info['label_path'] = '0';
                $info['label_parent_id']='0';
                $parent_names[$info['label_id']] = $info['label_name'];
            }
            $list[$k] = $info;
        }
        $total_data['yinlian'] = $total_yinlian;
        $total_data['zhifubao'] = $total_zhifubao;
        $total_data['weixin'] = $total_weixin;
        $total_data['wenhuiquan']  = $total_wenhuiquan;
        $total_data['chuzhika'] = $total_chuzhika;
        $total_data['heji'] = $total_yinlian + $total_zhifubao + $total_weixin + $total_wenhuiquan + $total_chuzhika;
        $list = \Org\My\Tree::_getCustomTree($list,0,0,'label_id','label_parent_id');
        foreach ($list as $k=>$info){
            $temp_data = $parent_label_arr[$info['label_id']];
            if($temp_data){
                $info['yinlian'] = $temp_data['yinlian'];
                $info['zhifubao'] = $temp_data['zhifubao'];
                $info['weixin'] = $temp_data['weixin'];
                $info['wenhuiquan'] = $temp_data['wenhuiquan'];
                $info['chuzhika'] = $temp_data['chuzhika'];
                $info['heji'] = $temp_data['yinlian'] + $temp_data['zhifubao'] + $temp_data['weixin'] + $temp_data['wenhuiquan'] + $temp_data['chuzhika'];
                $list[$k] = $info;
            }
            //正式去掉注释
            if(!$info['heji']) unset ($list[$k]);
        }
        $total_data['label_name'] = '合计';
        if($list) $list[] = $total_data;
        //格式化数据格式
        $temp_list = array();
        $parent_list = array();
        $first_flag = true;
        foreach($list as $k=>$info){
            $info['yinlian'] = price_format($info['yinlian']);
            $info['zhifubao'] = price_format($info['zhifubao']);
            $info['weixin'] = price_format($info['weixin']);
            $info['wenhuiquan'] = price_format($info['wenhuiquan']);
            $info['chuzhika'] = price_format($info['chuzhika']);
            $info['heji'] = price_format($info['heji']);
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
        $export_model->title = '收款方式总报表数据';
        $export_model->execl_fields = $export_fields;
        $export_model->setHeaderName('收款方式汇总报表');
        $export_model->setData($temp_list);
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
            'url'=>U('LabelPayType/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('LabelPayType/export').'"><i class="icon-download icon-white"></i> 导出报表</a>',
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
            'yinlian'=>'银联',
            'zhifubao'=>'支付宝',
            'weixin'=>'微信',
            'wenhuiquan'=>'文惠券',
            'chuzhika'=>'储值卡',
            'heji'=>'合计'
        );
    }
}
