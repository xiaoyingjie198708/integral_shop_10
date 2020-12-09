<?php

namespace Home\Controller;

/**
 * 商品类型管理
 *
 * @author xiao.yingjie
 */
class GoodsTypeController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(M('goods_type')->where($where)->count(),10);
        $list = M('goods_type')->where($where)->limit($page->firstRow.','.$page->listRows)->order('create_time desc')->select();
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    //得到添加页面
    public function get_add_page(){
       $content = $this->fetch('add'); 
       $this->oldAjaxReturn(0, $content, 1);
    }
    
    //添加
    public function add(){
        $data['type_id'] = (new \Org\Util\ThinkString())->uuid();
        $data['type_name'] = I('type_name','','trim,htmlspecialchars');
        if(!$data['type_name']) $this->oldAjaxReturn ('type_name', '请输入商品类型名称', 0);
        $data['physics_type_id'] = I('physics_type_id',0,'intval');
//        if(!$data['physics_type_id']) $this->oldAjaxReturn ('physics_type_id', '请选择物理类别', 0);
        $where = array('type_name'=>$data['type_name'],'type_status'=>array('neq',0));
//        $where['physics_type_id'] = $data['physics_type_id'];
        $some_name_count = M('goods_type')->where($where)->count();
        if($some_name_count) $this->oldAjaxReturn ('type_name', '商品类型名称不能重复，请重新输入', 0);
        $data['type_status'] = I('type_status',1,'intval');
        $type_code = M('goods_type')->order('type_code desc')->getField('type_code');
        if($type_code) $type_code += 1;
        else $type_code = 10;
        $data['type_code'] = $type_code;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');
        $add_bool = M('goods_type')->data($data)->add();
        if($add_bool) $this->oldAjaxReturn (0, '添加成功', 1);
        $this->oldAjaxReturn (0, '添加失败', 0);
    }
    
    //得到添加页面
    public function get_update_page(){
        $info = M('goods_type')->where($this->get_where())->find();
        $this->assign('info', $info);
       $content = $this->fetch('update'); 
       $this->oldAjaxReturn(0, $content, 1);
    }
    
    //添加
    public function update(){
        $type_id = I('type_id','','trim,htmlspecialchars');
        $data['type_name'] = I('type_name','trim,htmlspecialchars');
        if(!$data['type_name']) $this->oldAjaxReturn ('type_name', '请输入商品类型名称', 0);
        $data['physics_type_id'] = I('physics_type_id',0,'intval');
//        if(!$data['physics_type_id']) $this->oldAjaxReturn ('physics_type_id', '请选择物理类别', 0);
        $where = array('type_name'=>$data['type_name'],'type_status'=>array('neq',0),'type_id'=>array('neq',$type_id));
//        $where['physics_type_id'] = $data['physics_type_id'];
        $some_name_count = M('goods_type')->where($where)->count();
        if($some_name_count) $this->oldAjaxReturn ('type_name', '商品类型名称不能重复，请重新输入', 0);
        $data['type_status'] = I('type_status',1,'intval');
        $data['update_time'] = date('Y-m-d H:i:s');
        $update_bool = M('goods_type')->where($this->get_where())->data($data)->save();
        if($update_bool) $this->oldAjaxReturn (0, '修改成功', 1);
        $this->oldAjaxReturn (0, '修改失败', 0);
    }
    
    //修改状态
    public function change_status(){
        $type_status = I('type_status',1,'intval');
        $update_bool = M('goods_type')->where($this->get_where())->data(array('type_status'=>$type_status,'update_time'=>date('Y-m-d H:i:s')))->save();
        if($update_bool) $this->oldAjaxReturn (0, '修改成功', 1);
        $this->oldAjaxReturn (0, '修改失败', 0);
    }
    
    //删除
    public function delete(){
        //TODO 其它条件待定
        $update_bool = M('goods_type')->where($this->get_where())->data(array('type_status'=>0,'update_time'=>date('Y-m-d H:i:s')))->save();
        if($update_bool) $this->oldAjaxReturn (0, '删除成功', 1);
        $this->oldAjaxReturn (0, '删除失败', 0);
    }
    
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('GoodsType/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'类型名称',
                    'width'=>250,
                    'tip'=>'请输入类型名称'
                )
            ),
        );
    }
    
    private function get_where(){
        $where = array();
        $where['type_status'] = array('neq',0);
        $type_id = I('type_id','','trim,htmlspecialchars');
        if($type_id) $where['type_id'] = $type_id;
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['type_name'] = array('like','%'.$seatch.'%');
        return $where;
    }
}
