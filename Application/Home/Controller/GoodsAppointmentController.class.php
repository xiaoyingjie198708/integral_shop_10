<?php


namespace Home\Controller;

/**
 * 商品预约管理
 */
class GoodsAppointmentController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(M('user_appointment')->where($where)->count(),10);
        $list = M('user_appointment')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $member_ids = getFieldsByKey($list, 'member_id');
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        foreach($list as $k=>$info){
            $info['member_account'] = $member_accounts[$info['member_id']];
            $info['shops_name'] = $shops_names[$info['shops_code']];
            $list[$k] = $info;
        }
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    public function export(){
        $limit = 50000;
        $where = $this->get_where();
        $export_model = new \Org\My\Export('user_appointment');
        $export_model->limit = $limit;
        $export_model->setCount(M('user_appointment')->where($where)->count());
        //数据
        $list = $export_model->where($where)->getExportData();
        $member_ids = getFieldsByKey($list, 'member_id');
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        foreach($list as $k=>$info){
            $info['member_account'] = $member_accounts[$info['member_id']];
            $info['shops_name'] = $shops_names[$info['shops_code']];
            $info['platform'] = id2name('platform', $info['platform']);
            $list[$k] = $info;
        }
        //导出的数据
        $export_model->title = '商品预约数据';
        $export_model->execl_fields = array(
            'goods_code'=>'商品编码',
            'goods_name'=>'商品名称',
            'member_account'=>'会员账号',
            'platform'=>'终端',
            'shops_name'=>'商家',
            'create_time'=>'预约时间',
        );
        $export_model->setData($list);
        $export_page = $export_model->export();
        exit($export_page);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('GoodsAppointment/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('GoodsAppointment/export').'"><i class="icon-download icon-white"></i> 导出</a>',
            ),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'商品名称',
                    'width'=>250,
                    'tip'=>'请输入商品名称'
                )
            ),
            'other'=>array(
                array(
                    'name' => array('create_start', 'create_end'),
                    'show_name' => '预约时间',
                    'tip' => array('请选择开始时间', '请选择结束时间'),
                    'type' => 'date'
                )
            )
        );
    }
    
    private function get_where(){
        $where = array();
        $seatch = I('seatch','','trim,htmlspecialchars');
        $create_start = I('create_start','','trim,htmlspecialchars');
        $create_end = I('create_end','','trim,htmlspecialchars');
        if($seatch) $where['goods_name'] = array('like','%'.$seatch.'%');
        if($create_start && !$create_end) $where['create_time'] = array('gt',$create_start);
        if($create_start && $create_end) $where['create_time'] = array('lt',$create_end);
        if($create_start && $create_end) $where['create_time'] = array('between',array($create_start,$create_end));
        return $where;
    }
}
