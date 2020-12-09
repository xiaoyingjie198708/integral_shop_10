<?php
namespace Home\Controller;
class MenuController extends BaseController {
    //菜单列表
    public function index(){
        $menu = M('admin_menu')->order('sort asc')->select();
        $menu_parent = \Org\My\Tree::getTree($menu);
        $menu_son_arr = array();
        for($i=0;$i<count($menu_parent);$i++) {
            $menu_son_arr[$menu_parent[$i]['id']] = \Org\My\Tree::_getTree($menu,$menu_parent[$i]['id']);
        }
        $this->assign('menu_son_arr',$menu_son_arr);
        $this->display('index');
    }
    public function get_add_info(){
        $this->assign('parent_menu',M('admin_menu')->where(array('p_id'=>0))->order('sort asc')->select());
        $content = $this->fetch('add');
        $this->oldAjaxReturn(0, $content, 1);
    }
    //添加菜单
    public function add() {
        $menu_mod = M('admin_menu');
        $data['p_id'] = I('p_id',0,'intval');
        $data['path'] = I('path','','trim,htmlspecialchars');
        $data['url'] = I('url','','trim,htmlspecialchars,ucfirst');
        $data['name'] = I('name','','trim,htmlspecialchars');
        $data['sort'] = I('sort',0,'intval');
        $data['is_show'] = I('is_show',0,'intval');
        if($data['p_id'] == 0) $data['icon'] = 'icon-cog';
        if(!$data['name']) $this->oldAjaxReturn('name','请输入菜单名称',0);
//        if($data['p_id'] != 0 && !$data['url']) $this->oldAjaxReturn('url','请输入URL',0);
        $parent_ids = explode('-',trim($data['path'],'-'));
        $parent_count = count($parent_ids);
        $data['lv'] = $parent_count;
        $some_menu = $menu_mod->where(array('name'=>$data['name'],'p_id'=>$parent_ids[$parent_count - 1]))->find();
        if($some_menu) $this->oldAjaxReturn('name','存在相同的菜单名称',0);
        $id = $menu_mod->data($data)->add();
        if(!$id) $this->oldAjaxReturn(0,'保存数据失败',0);
        $this->oldAjaxReturn(0,'添加菜单成功',1);
    }
    //获取子菜单
    public function getChildMenu() {
        $menu_mod = M('admin_menu');
        $parent_menu = $menu_mod->where(array('p_id'=>I('id',0,'intval')))->order('sort asc')->select();
        if(!$parent_menu) $this->oldAjaxReturn(0,0,0);
        $this->assign('parent_menu',$parent_menu);
        $content = $this->fetch('child');
        $this->oldAjaxReturn($content,0,1);
    }
    //修改菜单
    public function update() {
        $p_id = I('p_id',0,'intval');
        $data['id'] = I('id',0,'intval');
        $data['url'] = I('url','','trim,htmlspecialchars');
        $data['name'] = I('name','','trim,htmlspecialchars');
        $data['sort'] = I('sort',0,'intval');
        $data['is_show'] = I('is_show',0,'intval');
        if(!$data['name']) $this->oldAjaxReturn('name','请输入菜单名称',0);
//        if($p_id != 0 && !$data['url']) $this->oldAjaxReturn('url','请输入URL',0);
        $menu = M('admin_menu');
        $where['p_id'] = $p_id;
        $where['name'] = $data['name'];
        $where['id'] = array('neq',$data['id']);
        $menu_info = $menu->where($where)->find();
        if($menu_info) $this->oldAjaxReturn('name', '存在相同的菜单名称', 0);
        $id = $menu->data($data)->save();
        if(!$id) $this->oldAjaxReturn(0,'修改失败',0);
        $this->oldAjaxReturn($data['name'],$data['id'],1);
    }
    //删除菜单
    public function delete() {
        $id = I('id',0,'intval');
        $menu = M('admin_menu');
        $info = $menu->where(array('p_id'=>$id))->find();
        if($info) $this->oldAjaxReturn(0,'此菜单有子菜单，不可删除',0);
        $count = $menu->where(array('id'=>$id))->delete();
        if(!$count) $this->oldAjaxReturn(0,'删除失败',0);
        $this->oldAjaxReturn($id,0,1);
    }
    //获取菜单信息
    public function get_menu_info() {
        $info = M('admin_menu')->find(I('id',0,'intval'));
        $this->assign('info',$info);
        $content = $this->fetch('update');
        $this->oldAjaxReturn(0,$content,1);
    }
}