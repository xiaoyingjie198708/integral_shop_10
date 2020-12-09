<?php


namespace Home\Controller;

/**
 *标签管理
 * @author xiao.yingjie
 */
class GoodsLabelController extends BaseController{
    
    //标签列表
    public function index(){
        $where = $this->get_where();
//        $page = new \Org\My\Page(D('GoodsLabel')->where($where)->count(),200);
//        $list = D('GoodsLabel')->where($where)->limit($page->firstRow.','.$page->listRows)->order('create_time asc')->select();
        $list = D('GoodsLabel')->where($where)->order('create_time asc')->select();
        foreach ($list as $k=>$info){
            if($info['label_level'] == 2) {$info['label_path'] = '0_'.$info['label_parent_id'];}
            else {$info['label_path'] = '0';$info['label_parent_id']='0';}
            $list[$k] = $info;
        }
        $list = \Org\My\Tree::_getCustomTree($list,0,0,'label_id','label_parent_id');
        $this->search($this->get_search());
//        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    //添加
    public function add(){
        if(IS_AJAX){
            $goods_label = D('GoodsLabel');
            if(!$goods_label->create()) {
                $error = each($goods_label->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $label_id = (new \Org\Util\ThinkString())->uuid();
                $goods_label->label_id = $label_id;
                $count = $goods_label->where(array('label_name'=>$goods_label->label_name,'label_status'=>array('neq',0)))->count();
                if($count) $this->oldAjaxReturn ('label_name', '商品标签名称已经存在，请重新输入', 0);
                //状态默认是有效
                $goods_label->label_status = I('label_status',1,'intval');
                $goods_label->create_time = date('Y-m-d H:i:s');
                $goods_label->update_time = date('Y-m-d H:i:s');
                $goods_label->label_icon = '';
                $goods_label_name = $goods_label->label_name;
                $goods_label->label_parent_id = trim($goods_label->label_parent_id);
                if($goods_label->label_parent_id) $goods_label->label_level = 2;
                else $goods_label->label_level = 1;
//                $type_ids = I('type_id','','trim,htmlspecialchars');
//                $type_label_relation_data = array();
//                foreach($type_ids as $type_id){
//                    $type_label_relation_data[] = array(
//                        'type_id'=>$type_id,
//                        'label_id'=>$label_id,
//                        'create_time'=>  date('Y-m-d H:i:s'),
//                        'update_time'=>  date('Y-m-d H:i:s')
//                    );
//                }
                $goods_label->startTrans();
//                $relation_bool = true;
//                if($type_label_relation_data) $relation_bool = M('goods_type_label_relation')->addAll($type_label_relation_data);
                $count = $goods_label->add();
                if($count === FALSE) {
                    $goods_label->rollback();
                    $this->oldAjaxReturn(0,'添加失败',0);
                }else{
                    $goods_label->commit();
                    $this->oldAjaxReturn($goods_label_name,0,1);
                }
            }
        }
//        $type_list = M('goods_type')->where(array('type_status'=>1))->order('create_time asc')->select();
//        $this->assign('type_list', $type_list);
        $label_list = M('goods_label')->where(array('label_level'=>1,'label_status'=>array('neq',0)))->order('label_sort asc')->select();
        $this->assign('label_list', $label_list);
        $this->display();
    }
    
    //更新
    public function update(){
        $goods_label_id = I('label_id','','trim,htmlspecialchars');
        if(IS_AJAX){
            $goods_label = D('GoodsLabel');
            if(!$goods_label->create()) {
                $error = each($goods_label->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $count = $goods_label->where(array('label_name'=>$goods_label->label_name,'label_status'=>array('neq',0),'label_id'=>array('neq',$goods_label_id)))->count();
                if($count) $this->oldAjaxReturn ('label_name', '标签名称已经存在，请重新输入', 0);
                //状态默认是上架
                $goods_label->label_status = I('label_status',1,'intval');
                $goods_label->update_time = date('Y-m-d H:i:s');
                $goods_label_name = $goods_label->label_name;
                $goods_label->label_icon = '';
                $goods_label->label_parent_id = trim($goods_label->label_parent_id);
                if($goods_label->label_parent_id) $goods_label->label_level = 2;
                else $goods_label->label_level = 1;
//                $del_bool = M('goods_type_label_relation')->where(array('label_id'=>$goods_label_id))->delete();
//                $type_ids = I('type_id','','trim,htmlspecialchars');
//                $type_label_relation_data = array();
//                foreach($type_ids as $type_id){
//                    $type_label_relation_data[] = array(
//                        'type_id'=>$type_id,
//                        'label_id'=>$goods_label_id,
//                        'create_time'=>  date('Y-m-d H:i:s'),
//                        'update_time'=>  date('Y-m-d H:i:s')
//                    );
//                }
                $goods_label->startTrans();
//                $relation_bool = true;
//                if($type_label_relation_data) $relation_bool = M('goods_type_label_relation')->addAll($type_label_relation_data);
                $count = $goods_label->where($this->get_where())->save();
                if($count === FALSE) {
                    $goods_label->rollback();
                    $this->oldAjaxReturn(0,'修改失败',0);
                }else{
                    $goods_label->commit();
                    $this->oldAjaxReturn($goods_label_name,0,1);
                }
            }
        }
        $info = D('GoodsLabel')->where($this->get_where())->find();
        $this->assign('info', $info);
//        $type_list = M('goods_type')->where(array('type_status'=>1))->order('create_time asc')->select();
//        $relation_type_ids = M('goods_type_label_relation')->where(array('label_id'=>$goods_label_id))->getField('type_id',true);
//        foreach($type_list as $k=>$type){
//            if(in_array($type['type_id'], $relation_type_ids)) $type_list[$k]['checked'] = true;
//        }
//        $this->assign('type_list', $type_list);
        $label_list = M('goods_label')->where(array('label_level'=>1,'label_status'=>array('neq',0)))->order('label_sort asc')->select();
        $this->assign('label_list', $label_list);
        $this->display();
    }
    
    //删除
    public function delete(){
        $goods_count = M('goods_base_info as goods,'.C('DB_PREFIX').'goods_label_relation as relation')->where('goods.goods_id = relation.goods_id')->where(array('relation.label_id'=>I('label_id','','trim,htmlspecialchars'),'goods.goods_status'=>array('neq',0)))->count();
        if($goods_count) $this->oldAjaxReturn (0, '关联标签的有 '.$goods_count.' 个商品，请先取消关联', 0);
        $child_count = D('GoodsLabel')->where(array('label_parent_id'=>I('label_id'),'label_status'=>array('neq',0)))->count();
        if($child_count) $this->oldAjaxReturn (0, '请先删除子标签', 0);
        $del_bool = D('GoodsLabel')->where($this->get_where())->data(array('label_status'=>0,'update_time'=>date('Y-m-d H:i:s')))->save();
        if($del_bool) $this->oldAjaxReturn (0, '删除成功', 1);
        $this->oldAjaxReturn(0, '删除失败', 0);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('GoodsLabel/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'标签名称',
                    'width'=>250,
                    'tip'=>'请输入标签名称'
                )
            ),
        );
    }
    
    private function get_where(){
        $where = array();
        $where['label_status'] = array('neq',0);
        $label_id = I('label_id','','trim,htmlspecialchars');
        if($label_id) $where['label_id'] = $label_id;
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['label_name'] = array('like','%'.$seatch.'%');
        return $where;
    }
}
