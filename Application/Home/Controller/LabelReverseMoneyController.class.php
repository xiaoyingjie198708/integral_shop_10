<?php

namespace Home\Controller;

class LabelReverseMoneyController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $list = D('GoodsLabel')->where($where)->order('create_time asc')->select();
        $total_reverse_cash = 0;$total_storage_money = 0;
        $parent_label_arr = array();
        foreach ($list as $k=>$info){
            if($info['label_level'] == 2){
                $info['label_path'] = '0_'.$info['label_parent_id'];
                $goods_ids = M('goods_label_relation')->where(array('label_id'=>$info['label_id']))->getField('goods_id',true);
                $order_where = $this->get_order_where();
                $order_where['info.goods_id'] = array('in',$goods_ids);
                $goods_detail_list = M('goods_use_detail_info as detail,tm_goods_base_info info')->where('detail.goods_code=info.goods_code')->where($order_where)->field('detail.*')->select();
                $reverse_cash = 0;$reverse_storage_money = 0;
                foreach($goods_detail_list as $j=>$goods_detail){
                    $reverse_cash += $goods_detail['reverse_cash'];
                    $reverse_storage_money += $goods_detail['reverse_storage_money'];
                }
                //二级标签
                $info['reverse_cash'] = $reverse_cash;
                $info['reverse_storage_money'] = $reverse_storage_money;
                $info['total_reverse_value'] = $reverse_cash + $reverse_storage_money;
                //一级标签
                //一级标签
                $parent_label = $parent_label_arr[$info['label_parent_id']];
                $parent_label['reverse_cash'] += $info['reverse_cash'];
                $parent_label['reverse_storage_money'] += $info['reverse_storage_money'];
                $parent_label['total_reverse_value'] += $info['total_reverse_value'];
                $parent_label_arr[$info['label_parent_id']] = $parent_label;
                
                $total_reverse_cash += $reverse_cash;
                $total_storage_money += $reverse_storage_money;
            }else{
                $info['label_path'] = '0';
                $info['label_parent_id']='0';
            }
            $list[$k] = $info;
        }
        $total_data['reverse_cash'] = $total_reverse_cash;
        $total_data['reverse_storage_money'] = $total_storage_money;
        $total_data['total_reverse_value'] = $total_reverse_cash + $total_storage_money;
        $list = \Org\My\Tree::_getCustomTree($list,0,0,'label_id','label_parent_id');
         foreach ($list as $k=>$info){
            $temp_data = $parent_label_arr[$info['label_id']];
            if($temp_data){
                $info['reverse_cash'] = $temp_data['reverse_cash'];
                $info['reverse_storage_money'] = $temp_data['reverse_storage_money'];
                $info['total_reverse_value'] = $temp_data['total_reverse_value'];
                $list[$k] = $info;
            }
        }
        $this->search($this->get_search());
        $this->assign('list', $list);
        $this->assign('total_data', $total_data);
        $this->display();
    }
    
    //详情
    public function details(){
        $where = $this->get_detail_where();
        $goods_detail_list = M('goods_use_detail_info as detail,tm_goods_base_info info')->where('detail.goods_code=info.goods_code')->where($where)->field('detail.*')->select();
        $shops_codes = getFieldsByKey($goods_detail_list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        foreach($goods_detail_list as $k=>$info){
            $info['shops_name'] = $shops_names[$info['shops_code']];
            $info['comment'] = M('reverse_order_base_info')->where(array('forward_order_id'=>$info['order_id']))->getField('comment');
            $goods_detail_list[$k] = $info;
        }
        $this->assign('list', $goods_detail_list);
        $this->search($this->get_details_search());
        $this->display();
    }
    
    public function export(){
        $where = $this->get_where();
        $export_model = new \Org\My\Export('goods_label');
        $export_model->limit = 50000;
        $export_model->setCount(M('goods_label')->where($where)->count());
        $list = $export_model->where($where)->order('create_time asc')->getExportData();
        $total_reverse_cash = 0;$total_storage_money = 0;
        $parent_label_arr = array();
        $parent_names = array();
        foreach ($list as $k=>$info){
            if($info['label_level'] == 2){
                $info['label_path'] = '0_'.$info['label_parent_id'];
                $goods_ids = M('goods_label_relation')->where(array('label_id'=>$info['label_id']))->getField('goods_id',true);
                $order_where = $this->get_order_where();
                $order_where['info.goods_id'] = array('in',$goods_ids);
                $goods_detail_list = M('goods_use_detail_info as detail,tm_goods_base_info info')->where('detail.goods_code=info.goods_code')->where($order_where)->field('detail.*')->select();
                $reverse_cash = 0;$reverse_storage_money = 0;
                foreach($goods_detail_list as $j=>$goods_detail){
                    $reverse_cash += $goods_detail['reverse_cash'];
                    $reverse_storage_money += $goods_detail['reverse_storage_money'];
                }
                //二级标签
                $info['reverse_cash'] = $reverse_cash;
                $info['reverse_storage_money'] = $reverse_storage_money;
                $info['total_reverse_value'] = $reverse_cash + $reverse_storage_money;
                //一级标签
                //一级标签
                $parent_label = $parent_label_arr[$info['label_parent_id']];
                $parent_label['reverse_cash'] += $info['reverse_cash'];
                $parent_label['reverse_storage_money'] += $info['reverse_storage_money'];
                $parent_label['total_reverse_value'] += $info['total_reverse_value'];
                $parent_label_arr[$info['label_parent_id']] = $parent_label;
                
                $total_reverse_cash += $reverse_cash;
                $total_storage_money += $reverse_storage_money;
            }else{
                $info['label_path'] = '0';
                $info['label_parent_id']='0';
                $parent_names[$info['label_id']] = $info['label_name'];
            }
            $list[$k] = $info;
        }
        $total_data['reverse_cash'] = $total_reverse_cash;
        $total_data['reverse_storage_money'] = $total_reverse_cash;
        $total_data['total_reverse_value'] = $total_reverse_cash + $total_storage_money;
        $list = \Org\My\Tree::_getCustomTree($list,0,0,'label_id','label_parent_id');
         foreach ($list as $k=>$info){
            $temp_data = $parent_label_arr[$info['label_id']];
            if($temp_data){
                $info['reverse_cash'] = $temp_data['reverse_cash'];
                $info['reverse_storage_money'] = $temp_data['reverse_storage_money'];
                $info['total_reverse_value'] = $temp_data['total_reverse_value'];
                $list[$k] = $info;
            }
        }
        $total_data['label_name'] = '合计';
        if($list) $list[] = $total_data;
        //格式化数据格式
        $temp_list = array();
        $parent_list = array();
        $first_flag = true;
        foreach($list as $k=>$info){
            $info['reverse_cash'] = price_format($info['reverse_cash']);
            $info['reverse_storage_money'] = price_format($info['reverse_storage_money']);
            $info['total_reverse_value'] = price_format($info['total_reverse_value']);
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
        $export_model->title = '退货总报表数据';
        $export_model->execl_fields = $export_fields;
        $export_model->setHeaderName('退货总报表');
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
            'url'=>U('LabelReverseMoney/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('LabelReverseMoney/export').'"><i class="icon-download icon-white"></i> 导出报表</a>',
            ),
            'main'=>array(
                array(
                    'name' => array('reverse_start', 'reverse_end'),
                    'show_name' => '退款时间',
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
    
    private function get_details_search(){
        return array(
            'url'=>U('LabelReverseMoney/details'),
//            'button'=>array(
//                '<a class="btn btn-success"  id="explode_order" data-url="'.U('LabelReverseMoney/details_export').'"><i class="icon-download icon-white"></i> 导出报表</a>',
//            ),
            'main'=>array(
                array(
                    'name' => array('reverse_start', 'reverse_end'),
                    'show_name' => '退款时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                ),
                array(
                    'name'=>'label_id',
                    'type'=>'hidden'
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
        $where['use_status'] = 3;//已退货
        $reverse_start = I('reverse_start','','trim,htmlspecialchars');
        $reverse_end = I('reverse_end','','trim,htmlspecialchars');
        if($reverse_start && !$reverse_end) $where['detail.update_time'] = array('gt',$reverse_start);
        if(!$reverse_start && $reverse_end) $where['detail.update_time'] = array('lt',$reverse_end);
        if($reverse_start && $reverse_end) $where['detail.update_time'] = array('between',array($reverse_start,$reverse_end));
        $this->assign('reverse_start', $reverse_start);
        $this->assign('reverse_end', $reverse_end);
        return $where;
    }
    
    private function get_detail_where(){
        $where['detail.use_status'] = 3;//已退货
        $label_id = I('label_id','','trim,htmlspecialchars');
        $label_ids = explode('_', $label_id);
        if(count($label_ids) == 1){
            $parent_label_id = $label_ids[0];
            $label_ids = M('goods_label')->where(array('label_parent_id'=>$parent_label_id))->getField('label_id',true);
            $label_ids[] = $parent_label_id;
        }
        if($label_id){
            $goods_ids = M('goods_label_relation')->where(array('label_id'=>array('in',$label_ids)))->getField('goods_id',true);
            $goods_codes = M('goods_base_info')->where(array('goods_id'=>array('in',$goods_ids)))->getField('goods_code',true);
            $where['info.goods_code'] = array('in',$goods_codes);
        }
        $this->assign('label_id', $label_id);
        $reverse_start = I('reverse_start','','trim,htmlspecialchars');
        $reverse_end = I('reverse_end','','trim,htmlspecialchars');
        $this->assign('reverse_start', $reverse_start);
        $this->assign('reverse_end', $reverse_end);
        if($reverse_start && !$reverse_end) $where['detail.update_time'] = array('gt',$reverse_start);
        if(!$reverse_start && $reverse_end) $where['detail.update_time'] = array('lt',$reverse_end);
        if($reverse_start && $reverse_end) $where['detail.update_time'] = array('between',array($reverse_start,$reverse_end));
        return $where;
    }
    
    private function export_fields(){
        return array(
            'parent_name'=>'父级标签名称',
            'label_name'=>'标签名称',
            'reverse_cash'=>'退现金额',
            'reverse_storage_money'=>'退储值卡金额',
            'total_reverse_value'=>'退款总金额',
        );
    }
}
