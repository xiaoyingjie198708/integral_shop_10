<?php
namespace Home\Controller;
class SelfController extends BaseController {
    /**
    +----------------------------------------------------------
    * 构造函数
    +----------------------------------------------------------
    */
    function __construct(){
        parent::__construct();
        $this->assign('menu_replace','yes');
        if(session('module_id') && session('module_id')) {
            session('module_id',null);
            session('action_id',null);
        }
    }
    //当前登录帐号信息
    public function index(){
        $user = M('admin_user')->find(session('admin_uid'));
        $login_mod = M('admin_login_history');
        $login_where = array('admin_id'=>session('admin_uid'));
        $login_num = $login_mod->where($login_where)->count();
        $login = $login_mod->where($login_where)->order('id desc')->limit('0,2')->select();
        $login = $login[1];
        $group_mod = M('admin_group_access access,'.C('DB_PREFIX').'admin_group group1');
        $user_group = $group_mod->where(array('access.uid'=>session('admin_uid')))->where('access.group_id=group1.id')->select();
        $user_group = getFieldsByKey($user_group,'name');
        $this->assign('login_num',$login_num);
        $this->assign('user',$user);
        $this->assign('login',$login);
        $this->assign('user_group',implode('、',$user_group));
        $this->display('index');
    }
    //登录明细
    public function login_history() {
        $mod = M('admin_login_history');
        $where = array('admin_id'=>session('admin_uid'));
        $page = new \Org\My\Page($mod->where($where)->count(),10);
        $login_history = $mod->where($where)->limit($page->firstRow.','.$page->listRows)->order('time desc')->select();
        $this->assign('login_history',$login_history);
        $this->assign('page',$page->show());
        $this->display('login_history');
    }
    //修改密码
    public function update_password($old_password='',$password='',$password2='') {
        if(IS_AJAX) {
            $user = M('admin_user')->find(session('admin_uid'));
            if($this->password($old_password) != $user['password']) $this->oldAjaxReturn('old_password','旧密码错误',0);
            if($this->password($password) == $user['password']) $this->oldAjaxReturn('password','新密码不能与旧密码相同',0);
            if($password == C('DEFAULT_ADMIN_PASSWORD')) $this->oldAjaxReturn('password','新密码不能与初始密码相同',0);
            if(strlen($password) < 6) $this->oldAjaxReturn('password','新密码长度至少6',0);
            if($password != $password2) $this->oldAjaxReturn('password2','两次密码输入不一致',0);
            $data = array(
                'id'=>session('admin_uid'),
                'password'=>$this->password($password)
            );
            $id = M('admin_user')->data($data)->save();
            if(!$id) $this->oldAjaxReturn(0,'修改失败',0);
            $this->oldAjaxReturn(0,0,1);
        }
        $this->display('update_password');
    }
}