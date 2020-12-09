<?php

namespace Home\Controller;

/**
 * 规格管理
 *
 * @author xiao.yingjie
 */
class GoodsSpecificationController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('GoodsSpecification')->where($where)->count(),10);
        $list = D('GoodsSpecification')->where($where)->limit($page->firstRow.','.$page->listRows)->order('specification_sort asc,create_time desc')->select();
        $type_ids = getFieldsByKey($list, 'type_id');
        $goods_types = M('goods_type')->where(array('type_id'=>array('in',$type_ids),'type_status'=>array('neq',0)))->getField('type_id,type_name',true);
        foreach($list as $k=>$info) $list[$k]['type_name'] = $goods_types[$info['type_id']];
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    public function get_add_page(){
        $goods_types = M('goods_type')->where(array('type_status'=>array('neq',0)))->getField('type_id,type_name',true);
        $this->assign('goods_type_list', $goods_types);
        $content = $this->fetch('add');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    public function add(){
        $goods_specification = D('GoodsSpecification');
        if(!$goods_specification->create()) {
            $error = each($goods_specification->getError());
            $this->oldAjaxReturn($error['key'],$error['value'],0);
        }else{
            $goods_specification->specification_id = (new \Org\Util\ThinkString())->uuid();
            $count = $goods_specification->where(array('specification_name'=>$goods_specification->specification_name,'type_id'=>$goods_specification->type_id,'specification_status'=>array('neq',0)))->count();
            if($count) $this->oldAjaxReturn ('category_name', '分类名称已经存在，请重新输入', 0);
            //状态默认是有效
            $goods_specification->specification_status = I('specification_status',1,'intval');
            $goods_specification->create_time = date('Y-m-d H:i:s');
            $goods_specification->update_time = date('Y-m-d H:i:s');
            $add_bool = $goods_specification->add();
            if(!$add_bool) $this->oldAjaxReturn(0,'添加失败',0);
            $this->oldAjaxReturn(0,'添加成功',1);
        }
    }
    
    public function get_update_page(){
        $goods_types = M('goods_type')->where(array('type_status'=>array('neq',0)))->getField('type_id,type_name',true);
        $this->assign('goods_type_list', $goods_types);
        $info = D('GoodsSpecification')->where($this->get_where())->find();
        $this->assign('info', $info);
        $content = $this->fetch('update');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    public function update(){
        $goods_specification = D('GoodsSpecification');
        if(!$goods_specification->create()) {
            $error = each($goods_specification->getError());
            $this->oldAjaxReturn($error['key'],$error['value'],0);
        }else{
            $count = $goods_specification->where(array('specification_name'=>$goods_specification->specification_name,'type_id'=>$goods_specification->type_id,'specification_status'=>array('neq',0),'specification_id'=>array('neq',$goods_specification->specification_id)))->count();
            if($count) $this->oldAjaxReturn ('category_name', '分类名称已经存在，请重新输入', 0);
            //状态默认是有效
            $goods_specification->specification_status = I('specification_status',1,'intval');
            $goods_specification->update_time = date('Y-m-d H:i:s');
            $update_bool = $goods_specification->save();
            if(!$update_bool) $this->oldAjaxReturn(0,'修改失败',0);
            $this->oldAjaxReturn(0,'修改成功',1);
        }
    }
    
    public function change_status(){
        $specification_status = I('specification_status',0,'intval');
        $update_bool = D('GoodsSpecification')->where($this->get_where())->data(array('specification_status'=>$specification_status,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($update_bool) $this->oldAjaxReturn (0, '修改成功', 1);
        else $this->oldAjaxReturn (0, '修改失败', 0);
    }
    
    public function delete(){
        //TODO 先不限制
        $del_bool = D('GoodsSpecification')->where($this->get_where())->data(array('specification_status'=>0,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($del_bool) $this->oldAjaxReturn (0, '删除成功', 1);
        else $this->oldAjaxReturn (0, '删除失败', 0);
    }
    
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        $goods_types = M('goods_type')->where(array('type_status'=>array('neq',0)))->getField('type_id,type_name',true);
        return array(
            'url'=>U('GoodsSpecification/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'规格名称',
                    'width'=>250,
                    'tip'=>'请输入规格名称'
                )
            ),
            'other'=>array(
                array(
                    'name'=>'search_type_id',
                    'show_name'=>'规格类型',
                    'tip'=>'请选择规格类型',
                    'select'=>$goods_types,
                    'type'=>'select'
                ),
                array(
                    'name'=>'search_status',
                    'show_name'=>'规格状态',
                    'tip'=>'请选择规格状态',
                    'select'=>C('specification_status'),
                    'type'=>'select'
                )
            )
        );
    }
    
    private function get_where(){
        $where = array();
        $where['specification_status'] = array('neq',0);
        $specification_id = I('specification_id','','trim,htmlspecialchars');
        if($specification_id) $where['specification_id'] = $specification_id;
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['specification_name'] = array('like','%'.$seatch.'%');
        return $where;
    }
}
