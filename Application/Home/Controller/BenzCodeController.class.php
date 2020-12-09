<?php


namespace Home\Controller;

/**
 *奔驰卡号管理
 * @author xiao.yingjie
 */
class BenzCodeController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(M('other_use_code')->where($where)->count(), 10);
        $list = M('other_use_code')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('update_time desc')->select();
        $order_ids = getFieldsByKey($list, 'order_id');
        $order_cur_ids = M('order_base_info')->where(array('order_id'=>array('in',$order_ids)))->getField('order_id,order_cur_id',true);
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_lists = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_name,goods_price_pc,integral,max_integral,shops_code',true);
        foreach($list as $k=>$info) {
            $list[$k]['order_cur_id'] = $order_cur_ids[$info['order_id']];
            $list[$k]['shops_name'] = $shops_names[$info['shops_code']];
            $order_goods = $goods_lists[$info['goods_code']];
            $list[$k]['goods_name'] = $order_goods['goods_name'];
            if($order_goods['integral']){
                if($order_goods['goods_price_pc'] && ($order_goods['max_integral'] >0))  $goods_price = '￥'.$order_goods['goods_price_pc'] .' + ' .$order_goods['max_integral'].'积分';
                if(!$order_goods['goods_price_pc'] && $order_goods['max_integral']) $goods_price = $order_goods['max_integral'].'积分';
                if(($order_goods['goods_price_pc']>0) && !$order_goods['max_integral']) $goods_price = '￥'.$order_goods['goods_price_pc'];
            }else{
                $goods_price = '￥'.$order_goods['goods_price_pc'];
            }
            $list[$k]['goods_price'] = $goods_price;
        }
        $this->search($this->get_search());
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->display();
    }
    
    //快捷查看
    public function quick_view() {
        $code_id = I('code_id',0,'intval');
        $info = M('other_use_code')->where(array('id'=>$code_id))->find();
        $this->assign('info', $info);
        $contant = $this->fetch('quick_view');
        $this->oldAjaxReturn(0, $contant, 1);
    }
    
    public function export(){
        $limit = 50000;
        $where = $this->get_where();
        $export_model = new \Org\My\Export('other_use_code');
        $export_model->limit = $limit;
        $export_model->setCount(M('other_use_code')->where($where)->count());
        //数据
        $list = $export_model->where($where)->getExportData();
        $order_ids = getFieldsByKey($list, 'order_id');
        $order_cur_ids = M('order_base_info')->where(array('order_id'=>array('in',$order_ids)))->getField('order_id,order_cur_id',true);
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_lists = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_name,goods_price_pc,integral,max_integral,shops_code',true);
        foreach($list as $k=>$info) {
            $list[$k]['order_cur_id'] = $order_cur_ids[$info['order_id']];
            $list[$k]['shops_name'] = $shops_names[$info['shops_code']];
            $order_goods = $goods_lists[$info['goods_code']];
            $list[$k]['goods_name'] = $order_goods['goods_name'];
            if($order_goods['integral']){
                if($order_goods['goods_price_pc'] && ($order_goods['max_integral'] >0))  $goods_price = '￥'.$order_goods['goods_price_pc'] .' + ' .$order_goods['max_integral'].'积分';
                if(!$order_goods['goods_price_pc'] && $order_goods['max_integral']) $goods_price = $order_goods['max_integral'].'积分';
                if(($order_goods['goods_price_pc']>0) && !$order_goods['max_integral']) $goods_price = '￥'.$order_goods['goods_price_pc'];
            }else{
                $goods_price = '￥'.$order_goods['goods_price_pc'];
            }
            $list[$k]['goods_price'] = $goods_price;
            $list[$k]['code_type'] = id2name('benz_code_type', $info['code_type']);
            $list[$k]['status'] = id2name('benz_status', $info['status']);
        }
        //导出的数据
        $export_model->title = '奔驰用车卡数据';
        $export_model->execl_fields = array(
            'order_cur_id'=>'订单编号',
            'user_name'=>'会员账号',
            'shops_name'=>'商家',
            'goods_name'=>'商品名称',
            'goods_price'=>'商品价格',
            'code_type'=>'卡类型',
            'use_code'=>'卡账号',
            'code_pwd'=>'卡密码',
            'status'=>'状态',
            'create_time'=>'购买时间',
            'valid_time'=>'有效期截至',
            'active_time'=>'激活时间',
            'use_time'=>'使用时间'
        );
        $export_model->setData($list);
        $export_page = $export_model->export();
        exit($export_page);
    }
    /*----------------------------------------------------受保护方法----------------------------------------------------------*/
    private function get_where(){
        $where = array();
        $member_account = I('member_account','','trim,htmlspecialchars');
        if($member_account) $where['user_name'] = array('like','%'.$member_account.'%');
        $code_type = I('code_type',0,'intval');
        if($code_type) $where['code_type'] = $code_type;
        $benz_status = I('benz_status',0,'intval');
        if($benz_status) $where['status'] = $benz_status;
        $create_start = I('create_start','','trim,htmlspecialchars');
        $create_end = I('create_end','','trim,htmlspecialchars');
        if($create_start && !$create_end) $where['create_time'] = array('gt',$create_start);
        if(!$create_start && $create_end) $where['create_time'] = array('lt',$create_end);
        if($create_start && $create_end) $where['create_time'] = array('between',array($create_start,$create_end));
        $use_start = I('use_start','','trim,htmlspecialchars');
        $use_end = I('use_end','','trim,htmlspecialchars');
        if($use_start && !$use_end) $where['use_time'] = array('gt',$use_start);
        if(!$use_start && $use_end) $where['use_time'] = array('lt',$use_end);
        if($use_start && $use_end) $where['use_time'] = array('between',array($use_start,$use_end));
        //权限
        $shops_codes = $this->check_access_for_benz();
        if(is_array($shops_codes)) $where['shops_code'] = array('in',$shops_codes);
        return $where;
    }
    
    //订单搜索条件
    private function get_search() {
        return array(
            'url' => U('BenzCode/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('BenzCode/export').'"><i class="icon-download icon-white"></i> 导出</a>',
            ),
            'main' => array(
                array(
                    'name' => 'member_account',
                    'show_name' => '会员账号',
                    'tip' => '请输入会员账号'
                ),
                array(
                    'name' => 'code_type',
                    'show_name' => '卡类型',
                    'tip' => '请选择卡类型',
                    'select' => C('benz_code_type'),
                    'type' => 'select'
                ),
                array(
                    'name' => 'benz_status',
                    'show_name' => '使用状态',
                    'tip' => '请选择状态',
                    'select' => C('benz_status'),
                    'type' => 'select'
                )
            ),
            'other' => array(
                array(
                    'name' => array('create_start', 'create_end'),
                    'show_name' => '购买时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                ),
                array(
                    'name' => array('use_start', 'use_end'),
                    'show_name' => '使用时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                ),
            )
        );
    }
    
    //验证管理员操作奔驰用车卡的其他权限
    function check_access_for_benz() {
        if(check_admin_access('show_benz_code_list_all',1,'other')){
            return null;
        }elseif(check_admin_access('show_benz_code_list_shops',1,'other')){
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
}
