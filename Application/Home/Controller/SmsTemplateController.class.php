<?php


namespace Home\Controller;

/**
 *短信模板
 * @author xiao.yingjie
 */
class SmsTemplateController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $count = M('sms_template_info')->where($where)->count();
        $page = new \Think\Page($count,10);
        $list = M('sms_template_info')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    public function add(){
        if(IS_AJAX){
            $data['template_name'] = I('template_name','','trim,htmlspecialchars');
            if(!$data['template_name']) $this->oldAjaxReturn ('template_name', '请输入模板名称', 0);
            $data['table_name'] = I('table_name','','trim,htmlspecialchars');
            if(!$data['table_name']) $this->oldAjaxReturn ('table_name', '请输入操作表', 0);
            $data['params'] = I('params','','trim,htmlspecialchars');
            $data['placeholder'] = I('placeholder','','trim,htmlspecialchars');
            $data['last_mark'] = I('last_mark','','trim,htmlspecialchars');
            $data['last_mark_value'] = I('last_mark_value','','trim,htmlspecialchars');
            if($data['last_mark'] && !$data['last_mark_value']) $this->oldAjaxReturn ('last_mark_value', '请输入条件初始值', 0);
            $data['phone_field'] = I('phone_field','member_id','trim,htmlspecialchars');
            if(!$data['phone_field']) $this->oldAjaxReturn ('phone_field', '请输入手机号码字段', 0);
            $data['sms_template'] = I('sms_template','','trim,htmlspecialchars');
            if(!$data['sms_template']) $this->oldAjaxReturn ('template_name', '请输入短信模板', 0);
            $data['status'] = 1;
            $data['create_time'] = time();
            $data['update_time'] = time();
            $add_bool = M('sms_template_info')->data($data)->add();
            if($add_bool) $this->oldAjaxReturn (0, '添加成功', 1);
            else $this->oldAjaxReturn (0, '添加失败', 0);
        }
        $this->display();
    }
    
    public function update(){
        $id = I('id',0,'intval');
        if(IS_AJAX){
           $data['template_name'] = I('template_name','','trim,htmlspecialchars');
            if(!$data['template_name']) $this->oldAjaxReturn ('template_name', '请输入模板名称', 0);
            $data['table_name'] = I('table_name','','trim,htmlspecialchars');
            if(!$data['table_name']) $this->oldAjaxReturn ('table_name', '请输入操作表', 0);
            $data['params'] = I('params','','trim,htmlspecialchars');
            $data['placeholder'] = I('placeholder','','trim,htmlspecialchars');
            $data['last_mark'] = I('last_mark','','trim,htmlspecialchars');
            $data['last_mark_value'] = I('last_mark_value','','trim,htmlspecialchars');
            if($data['last_mark'] && !$data['last_mark_value']) $this->oldAjaxReturn ('last_mark_value', '请输入条件初始值', 0);
            $data['phone_field'] = I('phone_field','member_id','trim,htmlspecialchars');
            if(!$data['phone_field']) $this->oldAjaxReturn ('phone_field', '请输入手机号码字段', 0);
            $data['sms_template'] = I('sms_template','','trim,htmlspecialchars');
            if(!$data['sms_template']) $this->oldAjaxReturn ('template_name', '请输入短信模板', 0);
            $data['update_time'] = time();
            $update_bool = M('sms_template_info')->where(array('id'=>$id))->data($data)->save();
            if($update_bool) $this->oldAjaxReturn (0, '修改成功', 1);
            else $this->oldAjaxReturn (0, '修改失败', 0);
        }
        $info = M('sms_template_info')->where(array('id'=>$id))->find();
        $this->assign('info', $info);
        $this->display();
    }
    
    public function change_status(){
        $id = I('id',0,'intval');
        $status = I('status',0,'intval');
        $update_bool = M('sms_template_info')->where(array('id'=>$id))->data(array('status'=>$status,'update_time'=>time()))->save();
        if($update_bool) $this->oldAjaxReturn (0, '成功', 1);
        else $this->oldAjaxReturn (0, '失败', 0);
    }
    
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('SmsTemplate/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'关键字',
                    'width'=>250,
                    'tip'=>'请输入模板名称/表名'
                )
            ),
        );
    }
    private function get_where(){
        $where = array();
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['table_name|sms_template'] = array('like','%'.$seatch.'%');
        return $where;
    }
}
