<?php
namespace Home\Controller;
class IndexController extends CommonController {
    //登录页面 登录状态直接渲染主页面
    public function index(){
	    if(!$this->isLogin()) $this->display('login');
        else $this->redirect('Self/index');
    }
    //验证码
    public function verify($width=120,$height=48,$verifyName='verify') {
        $verify = new \Org\My\Verify($width,$height,$verifyName);
        $verify->doimg();
    }
    //验证登录
    public function login($username='',$password='',$verify='') {
        if(!$username) $this->ajaxReturn(array('data'=>'username','info'=>'请输入用户名','status'=>0));
        if(!$password) $this->ajaxReturn(array('data'=>'password','info'=>'请输入密码','status'=>0));
        if(!$verify) $this->ajaxReturn(array('data'=>'verify','info'=>'请输入验证码','status'=>0));
//        if(!$this->checkVerify($verify)) $this->ajaxReturn(array('data'=>'verify','info'=>'验证码不正确','status'=>0));
//        $info = M('admin_user')->where(array('username'=>$username))->find();
//        if(!$info) $this->ajaxReturn(array('data'=>'username','info'=>'用户名不存在','status'=>0));
//        if($info['password'] != $this->password($password)) $this->ajaxReturn(array('data'=>'password','info'=>'密码不正确','status'=>0));
//        if(!$info['status']) $this->ajaxReturn(array('data'=>'username','info'=>'用户被禁用','status'=>0));
//        session('admin_uid',$info['id']);
//        session('admin_username',$info['username']);
        session('admin_uid',1);
        session('admin_username','xiao');
//        M('admin_login_history')->add(array('admin_id'=>$info['id'],'login_ip'=>get_client_ip(),'time'=>time())); //添加登录记录
        $this->ajaxReturn(array('status'=>1));
    }
    //登出
    public function logout() {
        session(null);
        $this->redirect('index');
    }
}