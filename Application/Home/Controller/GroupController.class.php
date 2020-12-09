<?php
namespace Home\Controller;
class GroupController extends BaseController {
    //用户组列表
    public function index(){
        $field = 'id,name,status';
        $where = $this->get_where();
        $page = new \Org\My\Page(M('admin_group')->where($where)->count(),10);
        $group = M('admin_group')->where($where)->field($field)->limit($page->firstRow.','.$page->listRows)->select();
        $this->search($this->get_search());
        $this->setJs('jquery.uniform,select2.min,jquery.dataTables.min,unicorn.tables,jquery.gritter.min');
        $this->assign('page',$page->show());
        $this->assign('admin_group',$group);
        $this->display('index');
    }
    //修改用户组状态
    public function change_group_status($id,$status) {
        $id = M('admin_group')->where(array('id'=>$id))->data(array('status'=>$status))->save();
        if(!$id) $this->oldAjaxReturn(0,'修改失败',0);
        $this->oldAjaxReturn(0,0,1);
    }
    //添加用户组
    public function add() {
        if(IS_AJAX){
            $data['name'] = I('name','','trim,htmlspecialchars');
            $data['status'] = I('status',1,'intval');
            $group = I('group');
            if(!$data['name']) $this->oldAjaxReturn('username','请输入用户组名称',0);
            $group_mod = M('admin_group');
            $some_group = $group_mod->where(array('name'=>$data['name']))->find();
            if($some_group) $this->oldAjaxReturn('name','用户组名称已存在',0);
            $id = $group_mod->data($data)->add();
            if(!$id) $this->oldAjaxReturn(0,'保存数据失败',0);
            $this->oldAjaxReturn($data['name'],'添加用户组成功',1);
        }
        $this->setJs('jquery.gritter.min');
        $this->display('add');
    }

    //修改用户组
    public function update() {
        $data['id'] = I('id',0,'intval');
        $data['name'] = I('name','','trim,htmlspecialchars');
        $data['status'] = I('status',0,'intval');

        if(!$data['name']) $this->oldAjaxReturn('name','请输入用户组名称',0);
        $group = M('admin_group');
        $some_group = $group->where(array('name'=>$data['name'],'id'=>array('neq',$data['id'])))->find();
        if($some_group) $this->oldAjaxReturn('name','用户组名称已存在',0);
        $id = $group->data($data)->save();
        $this->oldAjaxReturn(0,0,1);
    }
    //删除用户组
    public function delete() {
        $id = I('id',0,'intval');
        $user = M('admin_group');
        $count = $user->where(array('id'=>$id))->delete();
        if(!$count) $this->oldAjaxReturn(0,'删除失败',0);
        $this->oldAjaxReturn($id,0,1);
    }
    //设置用户组权限
    public function set_group_access() {
        $id = I('id',0,'intval');
        $rule = M('admin_rule');
        $rules = $rule->where(array('status'=>1))->select();
        if(!$id) $this->oldAjaxReturn(0,'参数错误',0);
        $group_info = M('admin_group')->find($id);
        if($group_info['rules']) {
            $group_rules = explode(',',$group_info['rules']);
            for($i=0;$i<count($rules);$i++) {
                if(in_array($rules[$i]['id'],$group_rules)) $rules[$i]['checked'] = 'checked';
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
        $this->assign('id',$id);
        $content = $this->fetch('access');
        $this->oldAjaxReturn(0,$content,1);
    }
    //设置用户组权限
    public function set_group_rules() {
        $rules = I('rules','','trim');
        $rules = trim($rules,',');
        $id = I('id',0,'intval');
        M('admin_group')->where(array('id'=>$id))->data(array('rules'=>$rules))->save();
        $this->oldAjaxReturn(0,0,1);
    }
    //获取用户组信息
    public function get_group_info() {
        $group = M('admin_group')->find(I('id',0,'intval'));
        $this->assign('group',$group);
        $content = $this->fetch('update');
        $this->oldAjaxReturn(0,$content,1);
    }
    
    public function get_add_info(){
        $content = $this->fetch('add');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('Group/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'用户组名称',
                    'width'=>250,
                    'tip'=>'请输入用户组名称'
                )
            ),
        );
    }
    
   private function get_where(){
        $where = array();
        $where['id'] = array('gt',1);
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['name'] = array('like','%'.$seatch.'%');
        return $where;
    }
}