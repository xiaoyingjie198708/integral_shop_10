<?php
namespace Home\Controller;
class AdminController extends BaseController {
    //管理员列表
    public function index(){
        $field = 'id,username,status,realname';
        $where = $this->get_where();
        $page = new \Org\My\Page(M('admin_user')->where($where)->count(),10);
        $user = M('admin_user')->where($where)->field($field)->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
        $group = M('admin_group_access')->join('__ADMIN_GROUP__ ON __ADMIN_GROUP_ACCESS__.group_id = __ADMIN_GROUP__.id')->select();
        for($i=0;$i<count($user);$i++) {
            for($j=0;$j<count($group);$j++) {
                if($group[$j]['uid'] == $user[$i]['id']) $user[$i]['group_name'] .= $group[$j]['name'].($group[$j]['status'] != 1 ? '（无效）' : '').'、';
            }
        }
        $this->search($this->get_search());
        $this->setJs('jquery.uniform,select2.min,jquery.dataTables.min,unicorn.tables,jquery.gritter.min');
        $this->assign('page',$page->show());
        $this->assign('admin_user',$user);
        $this->display('index');
    }
    //修改管理员状态
    public function change_user_status($id,$status) {
        $id = M('admin_user')->where(array('id'=>$id))->data(array('status'=>$status))->save();
        if(!$id) $this->oldAjaxReturn(0,'修改失败',0);
        $this->oldAjaxReturn(0,0,1);
    }
    //密码重置
    public function reset_password() {
        $id = I('id','','trim,htmlspecialchars');
        M('admin_user')->where(array('id'=>$id))->data(array('password'=>$this->password(C('DEFAULT_ADMIN_PASSWORD'))))->save();
        $this->oldAjaxReturn(0,0,1);
    }
    //添加管理员
    public function add() {
        $user_id = (new \Org\Util\ThinkString())->uuid();
        $data['id'] = $user_id;
        $data['username'] = I('username','','trim,htmlspecialchars');
        $data['realname'] = I('realname','','trim,htmlspecialchars');
        $data['status'] = I('status',1,'intval');
        $group = I('group');
        $relation = I('relation');
        if(!$data['username']) $this->oldAjaxReturn('username','请输入管理员帐号',0);
        if(!$data['realname']) $this->oldAjaxReturn('realname','请输入真实姓名',0);
        if(!$group) $this->oldAjaxReturn('group','请选择所属分组',0);
        $user_mod = M('admin_user');
        $some_user = $user_mod->where(array('username'=>$data['username']))->find();
        if($some_user) $this->oldAjaxReturn('username','管理员帐号已存在',0);
        $data['create_time'] = time();
        $data['password'] = $this->password(C('DEFAULT_ADMIN_PASSWORD'));
        $id = $user_mod->data($data)->add();
        if(!$id) $this->oldAjaxReturn(0,'保存数据失败',0);
        //关联管理组
        $group_data = array();
        for($i=0;$i<count($group);$i++) $group_data[] = array('uid'=>$user_id,'group_id'=>$group[$i]);
        M('admin_group_access')->addAll($group_data);
        //关联商铺
        $relation_data = array();
        for($i=0;$i<count($relation);$i++) $relation_data[] = array('uid'=>$user_id,'type'=>1,'relation_id'=>$relation[$i],'create_time'=>time());
        M('admin_user_relation')->addAll($relation_data);
        $this->oldAjaxReturn($data['username'],'添加管理员成功',1);
    }

    //修改管理员
    public function update() {
        $data['id'] = I('id','','trim,htmlspecialchars');
        $data['realname'] = I('realname','','trim,htmlspecialchars');
        $data['status'] = I('status',0,'intval');
        $group = I('group');
        $relation = I('relation');
        if(!$data['realname']) $this->oldAjaxReturn('realname','请输入管理员名称',0);
        if(!$group) $this->oldAjaxReturn('group','请选择所属分组',0);
        $user = M('admin_user');
        $id = $user->data($data)->save();
        //修改关联管理组
        M('admin_group_access')->where(array('uid'=>$data['id']))->delete();
        $group_data = array();
        for($i=0;$i<count($group);$i++) {
            $group_data[] = array('uid'=>$data['id'],'group_id'=>$group[$i]);
        }
        M('admin_group_access')->addAll($group_data);
        //修改商家
        M('admin_user_relation')->where(array('uid'=>$data['id'],'type'=>1))->delete();
        $relation_data = array();
        for($i=0;$i<count($relation);$i++) $relation_data[] = array('uid'=>$data['id'],'type'=>1,'relation_id'=>$relation[$i],'create_time'=>time());
        M('admin_user_relation')->addAll($relation_data);
        $this->oldAjaxReturn(0,0,1);
    }
    //删除管理员
    public function delete() {
        $id = I('id','','trim,htmlspecialchars');
        if($id == C('DEFAULT_ADMIN_ID')) $this->oldAjaxReturn(0,'请不要把系统管理员给删除噢，亲~',0);
        $user = M('admin_user');
        $count = $user->where(array('id'=>$id))->delete();
        if(!$count) $this->oldAjaxReturn(0,'删除失败',0);
        M('admin_group_access')->where(array('uid'=>$id))->delete();
        M('admin_user_relation')->where(array('uid'=>$id))->delete();
        $this->oldAjaxReturn($id,0,1);
    }
    //设置管理员附加权限
    public function set_user_access() {
        $rule = M('admin_rule');
        $rules = $rule->where(array('status'=>1))->select();
        $uid = I('id','','trim,htmlspecialchars');
        if(!$uid) $this->oldAjaxReturn(0,'参数错误',0);
        $user_info = M('admin_user')->find($uid);
        if($user_info['rules']) {
            $user_other_rules = explode(',',$user_info['rules']);
            for($i=0;$i<count($rules);$i++) {
                if(in_array($rules[$i]['id'],$user_other_rules)) $rules[$i]['checked'] = 'checked';
            }
        }
        $rules = \Org\My\Tree::getTree($rules);
        $mod_list = A('Access')->mod_list;
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
        $this->assign('uid',$uid);
        $content = $this->fetch('access');
        $this->oldAjaxReturn(0,$content,1);
    }
    //设置管理员附加权限
    public function set_user_other_rules() {
        $rules = I('rules','','trim');
        $rules = trim($rules,',');
        $uid = I('uid','','trim,htmlspecialchars');
        M('admin_user')->where(array('id'=>$uid))->data(array('rules'=>$rules))->save();
        $this->oldAjaxReturn(0,0,1);
    }

    //获取管理员信息
    public function get_user_info() {
        $info = M('admin_user')->where(array('id'=>I('id','','trim,htmlspecialchars')))->find();
        $user_group_ids = M('admin_group_access')->where(array('uid'=>$info['id']))->getField('group_id',true);
        $group = M('admin_group')->select();
        for($i=0;$i<count($group);$i++) {
            if(in_array($group[$i]['id'],$user_group_ids)) $group[$i]['select'] = 'selected';
            if($group[$i]['status'] != 1) $group[$i]['name'] = $group[$i]['name'].'（无效）';
        }
        $relations = M('shops_base_info')->where(array('shops_status'=>1))->select();
        $user_relation_ids = M('admin_user_relation')->where(array('uid'=>$info['id'],'type'=>1))->getField('relation_id',true);
        for($i=0;$i<count($relations);$i++) {
            if(in_array($relations[$i]['shops_id'],$user_relation_ids)) $relations[$i]['select'] = 'selected';
        }
        $this->assign('info',$info);
        $this->assign('groups',$group);
        $this->assign('relations',$relations);
        $content = $this->fetch('update');
        $this->oldAjaxReturn(0,$content,1);
    }
    
    public function get_add_info(){
        $group = M('admin_group')->where(array('status'=>1))->select();
        $relations = M('shops_base_info')->where(array('shops_status'=>1))->select();
        $this->assign('groups',$group);
        $this->assign('relations',$relations);
        $content = $this->fetch('add');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        $group_list = M('admin_group')->where(array('status'=>1))->getField('id,name',true);
        return array(
            'url'=>U('Admin/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'用户名',
                    'width'=>250,
                    'tip'=>'请输入用户名'
                ),
                array(
                    'name'=>'group_id',
                    'show_name'=>'用户分组',
                    'tip'=>'请选择用户分组',
                    'select'=>$group_list,
                    'type'=>'select'
                )
            ),
        );
    }
    
   private function get_where(){
        $where = array();
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['username'] = array('like','%'.$seatch.'%');
        $group_id = I('group_id',0,'intval');
        if($group_id){
            $uids = M('admin_group_access')->where(array('group_id'=>$group_id))->getField('uid',true);
            foreach($uids as $k=>$uid) if($uid == '1') unset ($uids[$k]);
            $where['id'] = array('in',$uids);
        }else{
            $where['id'] = array('neq','1');
        }
        return $where;
    }
}