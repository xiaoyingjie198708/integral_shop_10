<?php


namespace Home\Controller;

/**
 * 客户预约管理
 *
 * @author xiao.yingjie
 */
class MemberAppointmentController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('MemberAppointment')->where($where)->count(),10);
        $list = D('MemberAppointment')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        foreach($list as $k=>$info){
            $list[$k]['shops_name'] = $shops_names[$info['shops_code']];
            $list[$k]['app_time'] = date('Y-m-d H:i:s',  strtotime($info['app_time']));
        }
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    public function get_add_page(){
        $content = $this->fetch('add');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    public function add(){
        $mod = D('MemberAppointment');
        if(!$mod->create()) {
            $error = each($mod->getError());
            $this->oldAjaxReturn($error['key'],$error['value'],0);
        }else{
            $mod->app_status = 1;
            $mod->create_time = date('Y-m-d H:i:s');
            $mod->update_time = date('Y-m-d H:i:s');
            $shops_id = M('admin_user_relation')->where(array('uid'=>  session('admin_uid'),'type'=>1))->getField('relation_id');
            if($shops_id) $shops_code = M('shops_base_info')->where(array('shops_id'=>$shops_id))->getField('shops_code');
            else $shops_code = '';
            $mod->shops_code = $shops_code;
            $save_bool = $mod->add();
            if($save_bool) $this->oldAjaxReturn (0, '添加成功', 1);
            else $this->oldAjaxReturn (0, '添加失败', 0);
        }
    }
    
    public function get_update_page(){
        $id = I('id',0,'intval');
        $info = D('MemberAppointment')->where(array('id'=>$id))->find();
        $info['app_time'] = date('Y-m-d H:i:s',  strtotime($info['app_time']));
        $this->assign('info', $info);
        $content = $this->fetch('update');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
     public function update(){
        $mod = D('MemberAppointment');
        if(!$mod->create()) {
            $error = each($mod->getError());
            $this->oldAjaxReturn($error['key'],$error['value'],0);
        }else{
            $mod->update_time = date('Y-m-d H:i:s');
            $save_bool = $mod->save();
            if($save_bool) $this->oldAjaxReturn (0, '更新成功', 1);
            else $this->oldAjaxReturn (0, '更新失败', 0);
        }
    }
    
    public function delete(){
        $id = I('id',0,'intval');
        $bool = D('MemberAppointment')->where(array('id'=>$id))->delete();
        if($bool) $this->oldAjaxReturn (0, '删除成功', 1);
        else $this->oldAjaxReturn (0, '删除失败', 0);
    }
    
    public function export(){
        $limit = 50000;
        $where = $this->get_where();
        $export_model = new \Org\My\Export('member_appointment');
        $export_model->limit = $limit;
        $export_model->setCount(D('MemberAppointment')->where($where)->count());
        //数据
        $list = $export_model->where($where)->getExportData();
        //导出的数据
        $export_model->title = '客户预约数据';
        $export_model->execl_fields = array(
            'member_mobile'=>'联系电话',
            'member_name'=>'联系人',
            'goods_desc'=>'商品名称',
            'goods_num'=>'数量',
            'shops_name'=>'商家',
            'app_time'=>'预约时间',
        );
        $export_model->setData($list);
        $export_page = $export_model->export();
        exit($export_page);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('MemberAppointment/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('MemberAppointment/export').'"><i class="icon-download icon-white"></i> 导出</a>',
            ),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'联系电话',
                    'width'=>250,
                    'tip'=>'请输入联系电话'
                ),
                array(
                    'name'=>'goods_name',
                    'show_name'=>'商品名称',
                    'width'=>250,
                    'tip'=>'请输入商品名称'
                )
            ),
            'other'=>array(
                array(
                    'name' => array('app_start', 'app_end'),
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
        if($seatch) $where['member_mobile'] = array('like','%'.$seatch.'%');
        $goods_name = I('goods_name','','trim,htmlspecialchars');
        if($goods_name) $where['goods_desc'] = array('like','%'.$goods_name.'%');
        $app_start = I('app_start','','trim,htmlspecialchars');
        $app_end = I('app_end','','trim,htmlspecialchars');
        if($app_start && !$app_end) $where['app_time'] = array('gt',$app_start);
        if(!$app_start && $app_end) $where['app_time'] = array('lt',$app_end);
        if($app_start && $app_end) $where['app_time'] = array('between',$app_start.','.$app_end);
        return $where;
    }
}
