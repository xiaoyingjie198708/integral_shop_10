<?php
namespace Home\Controller;
class CommonController extends \Think\Controller {
    /**
    +----------------------------------------------------------
    * 构造函数
    +----------------------------------------------------------
    */
    function __construct(){
        parent::__construct();
        if($_REQUEST) {
            foreach($_REQUEST as $k=>$v) {
                if(is_string($v)) $_REQUEST[$k] = urldecode($v);
            }
        }
        if($_POST) {
            foreach($_POST as $k=>$v) {
                if(is_string($v)) $_POST[$k] = urldecode($v);
            }
        }
        if($_GET) {
            foreach($_GET as $k=>$v) {
                if(is_string($v)) $_GET[$k] = urldecode($v);
            }
        }
    }
    //判断是否登录
    public function isLogin() {
		return session('admin_uid') ? true : false;
    }
    //检查验证码
    public function checkVerify($value,$key='verify') {
        $verify = new \Org\My\Verify();
        return $verify->check($value,$key);
    }
    //密码加密
    public function password($string) {
        return md5(md5($string));
    }
    //添加js到页面底部
    public function setJs($js) {
        $js = explode(',',$js);
        $this->assign('other_js',$js);
    }
    //旧版ajax返回
    public function oldAjaxReturn($data,$info,$status) {
        $arr = array(
            'data'=>$data,
            'info'=>$info,
            'status'=>$status
        );
        $this->ajaxReturn($arr);
    }
    //筛选公共方法
    public function search($list=array()) {
        $this->assign('search_list',$list);
        $this->assign('search_page_name',CONTROLLER_NAME.'_'.ACTION_NAME);
        $content = $this->fetch('Public/search');
        $this->assign('search_box',$content);
    }

   //检查固定电话是否合法
    public function checkTel($tel) {
        return preg_match("/^(010|02\d{1}|0[3-9]\d{2})-\d{7,9}(-\d+)?$/",trim($tel));
    }
    //检查手机号是否合法
    public function checkMobile($mobile) {
        return preg_match("/^1[3578]\d{9}$/",trim($mobile));
    }
    //检查email是否合法
    public function checkEmail($email) {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    }

    //获取城市列表
    public function getCity($parent_id = 0){
        $city_list = M('city')->where(array('parent_id'=>$parent_id))->order('displayorder asc')->select();
        $this->ajaxReturn($city_list);
    }
}