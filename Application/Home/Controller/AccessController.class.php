<?php
namespace Home\Controller;
class AccessController extends BaseController {
    public $mod_list = array(
        array('name'=>'系统模块','value'=>array('Access','Menu','Group','Admin')),
        array('name'=>'基础数据模块','value'=>array('Shops','GoodsType','SMSLog','SmsTemplate')),
        array('name'=>'商品属性模块','value'=>array('Brand','GoodsLabel','GoodsCategory')),
        array('name'=>'商品模块','value'=>array('EditGoodsBase','CheckGoodsBase','GoodsBase','GoodsStock','GoodsStockLog')),
        array('name'=>'券码模块','value'=>array('CouponEdit','CouponCheck','Coupon','CouponCodeEdit','CouponCodeCheck','CouponCode')),
        array('name'=>'促销模块','value'=>array('GiftEdit','GiftCheck','Gift')),
        array('name'=>'订单模块','value'=>array('Order','ReverseOrder','Settlement','MemberAppointment','UseCode','BenzCode','ReverseMoney')),
        array('name'=>'统计模块','value'=>array('FinancialReport','PayTypeReport','ReverseMoneyReport','OrderDetailReport','GoodsUseReport','GoodsSaleReport','LabelDetailReport','LabelFinancialReport','LabelPayType','LabelReverseMoney','StorageMoney')),
    );
    //权限列表
    public function index(){
        $rule = M('admin_rule');
        $controllers = $this->getController();
        foreach($controllers as $k=>$v) {
            $id = 0;
            $rule_info = $this->checkName(array('name'=>$k));
            if(!$rule_info) $id = $rule->add(array('name'=>$k,'title'=>'','status'=>1,'p_id'=>0,'lv'=>1,'type'=>1));
            else $id = $rule_info['id'];
            $data_child = array();
            for($i=0;$i<count($v);$i++) {
                $rule_child_info = $this->checkName(array('name'=>$k.'/'.$v[$i]));
                if(!$rule_child_info) $data_child[] = array('name'=>$k.'/'.$v[$i],'title'=>'','status'=>1,'p_id'=>$id,'lv'=>2,'type'=>1);
            }
            if($data_child) $rule->addAll($data_child);
        }
        $rules = $rule->select();
        $rules = \Org\My\Tree::getTree($rules);
        $mod_list = $this->mod_list;
        $return_rules = array();
        for($j=0;$j<count($mod_list);$j++) {
            if($mod_list[$j]['value']) {
                for($i=0;$i<count($rules);$i++) {
                    if(in_array(trim($rules[$i]['name']),$mod_list[$j]['value'])) {
                        $return_rules[$j]['name'] = $mod_list[$j]['name'];
                        $return_rules[$j]['value'][] = $rules[$i];
                    }
                }
            }else{
                $return_rules[$j]['name'] = $mod_list[$j]['name'];
                $return_rules[$j]['value'] = array();
            }
        }
        $this->assign('return_rules',$return_rules);
        $this->assign('controllers',$controllers);
        $this->setJs('jquery.uniform,select2.min,jquery.dataTables.min,unicorn.tables');
        $this->display('index');
    }
    //获取权限详情
    public function get_rule_info() {
        $info = M('admin_rule')->find(I('id',0,'intval'));
        $this->assign('info',$info);
        $content = $this->fetch('update');
        $this->oldAjaxReturn(0,$content,1);
    }
    //获取子权限
    public function getChildAccess() {
        $id = I('id',0,'intval');
        $id = $id - 1;
        if($id < 0) $this->oldAjaxReturn(0,0,0);
        $menu_mod = M('admin_rule');
        $parent_menu = $menu_mod->where(array('name'=>array('in',$this->mod_list[$id]['value'])))->select();
        if(!$parent_menu) $this->oldAjaxReturn(0,0,0);
        $this->assign('parent_menu',$parent_menu);
        $content = $this->fetch('child');
        $this->oldAjaxReturn($content,0,1);
    }
    public function get_add_info(){
        $this->assign('parent_rule',M('admin_rule')->where(array('p_id'=>0))->select());
        $content = $this->fetch('add');
        $this->oldAjaxReturn(0, $content, 1);
    }
    //添加权限
    public function add() {
        $rule_mod = M('admin_rule');
        $data['p_id'] = I('p_id',0,'intval');
        $data['title'] = I('title','','trim,htmlspecialchars');
        $data['name'] = I('name','','trim,htmlspecialchars');
        $data['condition'] = I('condition','','');
        $data['status'] = 1;
        $data['type'] = 1;
        if(!$data['name']) $this->oldAjaxReturn('name','请输入权限规则',0);
        if(!$data['title']) $this->oldAjaxReturn('title','请输入权限名称',0);
        $lv = ($data['p_id']) == 0 ? 1 : $rule_mod->where(array('id'=>$data['p_id']))->getField('lv')+1;
        $data['lv'] = $lv;
        if($this->checkName(array('name'=>$data['name']))) $this->oldAjaxReturn('name', '存在相同的权限规则', 0);
        $id = $rule_mod->data($data)->add();
        if(!$id) $this->oldAjaxReturn(0,'保存数据失败',0);
        $rule_mod->where(array('id'=>$data['p_id']))->data(array('status'=>1))->save();
        $this->oldAjaxReturn(0,'添加权限成功',1);
    }
    //修改权限
    public function update() {
        $data['id'] = I('id',0,'intval');
        $data['title'] = I('title','','trim,htmlspecialchars');
        $data['name'] = I('name','','trim,htmlspecialchars');
        $data['condition'] = I('condition','','');
        if(!$data['name']) $this->oldAjaxReturn('name','请输入权限标识',0);
        if(!$data['title']) $this->oldAjaxReturn('title','请输入权限名称',0);
        $rule = M('admin_rule');
        $where['name'] = $data['name'];
        $where['id'] = array('neq',$data['id']);
        if($this->checkName($where)) $this->oldAjaxReturn('name', '存在相同的权限规则', 0);
        $id = $rule->data($data)->save();
        if(!$id) $this->oldAjaxReturn(0,'修改失败',0);
        $this->oldAjaxReturn($data['name'].'（'.$data['title'].'）',$data['id'],1);
    }
    //删除权限
    public function delete() {
        $id = I('id',0,'intval');
        $rule = M('admin_rule');
        $info = $rule->where(array('p_id'=>$id))->find();
        if($info) $this->oldAjaxReturn(0,'此权限下有子权限，不可删除',0);
        $count = $rule->where(array('id'=>$id))->delete();
        if(!$count) $this->oldAjaxReturn(0,'删除失败',0);
        $this->oldAjaxReturn($id,0,1);
    }
    //设置权限是否生效
    public function setAccessStatus() {
        $id = I('id',0,'intval');
        $status = I('status',1,'intval');
        $rule = M('admin_rule');
        $info = $rule->find($id);
        $count = $rule->where(array('id'=>$id))->data(array('status'=>$status))->save();
        if(!$count) {
            $data = 0;
            if($info['lv'] == 1) {
                $data = $rule->where(array('p_id'=>$id))->field('id,status')->select();
                $data = $data ? $data : 0;
            }
            $this->oldAjaxReturn($data,'修改失败',0);
        }
        if($info['lv'] == 1) $rule->where(array('p_id'=>$info['id']))->data(array('status'=>$status))->save();
        else{
            if($status) $rule->where(array('id'=>$info['p_id']))->data(array('status'=>1))->save();
            else {
                $rs = $rule->where(array('status'=>1,'p_id'=>$info['p_id']))->find();
                if(!$rs) $rule->where(array('id'=>$info['p_id']))->data(array('status'=>0))->save();
            }
        }
        $this->oldAjaxReturn($count,0,1);
    }
    /**
     * 获取所有控制器
     * @param type $module
     */
    private function getController($module='Home'){
        if(empty($module)) return null;
        $module_path = APP_PATH . '/' . $module . '/Controller';  //控制器路径
        if(!is_dir($module_path)) return null;
        $module_path .= '/*.class.php';
        $ary_files = glob($module_path);
        $files = array();
        $files_var = array('Common','Base','Index','Self','MemberCommon');
        foreach ($ary_files as $file) {
            $filename = basename($file, C('DEFAULT_C_LAYER').'.class.php');
            if (!in_array($filename, $files_var)) {
                $files[$filename] = $this->getAction($filename);
            }
        }
        return $files;
    }
    /**
     * 获取控制器所有方法
     */
    private function getAction($controller,$module='Home'){
        if(empty($controller)) return null;
        $content = file_get_contents(APP_PATH . '/'.$module.'/Controller/'.$controller.'Controller.class.php');
        preg_match_all("/.*?public.*?function(.*?)\(.*?\)/i", $content, $matches);
        $functions = $matches[1];
        //排除部分方法
        $inherents_functions = array('_initialize','__construct','getActionName','isAjax','display','show','fetch','buildHtml','assign','__set','get','__get','__isset','__call','error','success','ajaxReturn','redirect','__destruct','_empty');
        foreach ($functions as $func){
            $func = trim($func);
            if(!in_array($func, $inherents_functions) && $func){
                $customer_functions[] = $func;
            }
        }
        return $customer_functions;
    }
    //验证是否存在的权限（重名验证）
    private function checkName($where) {
        return M('admin_rule')->where($where)->find();
    }
}