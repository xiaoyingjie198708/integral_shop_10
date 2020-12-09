<?php


namespace Home\Controller;

class SMSLogController extends BaseController{
    
    //日志列表
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(M('sms_log')->where($where)->count(),10);
        $list = M('sms_log')->where($where)->limit($page->firstRow.','.$page->listRows)->order('send_date desc')->select();
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display(); 
    }
    
    //获取发送页面信息
    public function get_send_info(){
        $content = $this->fetch('send_sms');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    //发送短信
    public function send_sms(){
        $data['phone'] = I('phone','','trim,htmlspecialchars');
        if(!$data['phone']) $this->oldAjaxReturn ('phone', '请输入手机号码', 0);
        if(!($data['phone'] && $this->checkMobile($data['phone']))) $this->oldAjaxReturn ('phone', '请输入正确的手机号码', 0);
        $data['content'] = I('sms_content','','trim,htmlspecialchars');
        if(!$data['content']) $this->oldAjaxReturn ('sms_content', '请输入短信内容', 0);
        $add_bool = sendSms($data);
        if($add_bool) $this->oldAjaxReturn (0, '发生成功', 1);
        else $this->oldAjaxReturn (0, '发送失败', 0);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('SMSLog/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'手机号码',
                    'width'=>250,
                    'tip'=>'请输入手机号码'
                )
            ),
        );
    }
    
    private function get_where(){
        $where = array();
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['phone'] = array('like','%'.$seatch.'%');
        return $where;
    }
}
