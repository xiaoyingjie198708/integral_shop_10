<?php


namespace Home\Controller;

/**
 * 品牌管理
 *
 * @author xiao.yingjie
 */
class BrandController extends BaseController{
    
    //列表
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('Brand')->where($where)->count(),10);
        $list = D('Brand')->where($where)->limit($page->firstRow.','.$page->listRows)->order('brand_sort asc,create_time desc')->select();
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    //添加
    public function add(){
        if(IS_AJAX){
            $brand = D('Brand');
            if(!$brand->create()) {
                $error = each($brand->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $brand_id = (new \Org\Util\ThinkString())->uuid();
                $brand->brand_id = $brand_id;
                $count = $brand->where(array('brand_name'=>$brand->brand_name,'brand_status'=>array('neq',0)))->count();
                if($count) $this->oldAjaxReturn ('brand_name', '品牌名称已经存在，请重新输入', 0);
                $brand_code = $brand->order('brand_code desc')->getField('brand_code');
                if($brand_code) $brand_code += 1;
                else $brand_code = 1000;
                $brand->brand_code = $brand_code;
                //状态默认是上架
                $brand->brand_status = I('brand_status',1,'intval');
                $brand->create_time = date('Y-m-d H:i:s');
                $brand->update_time = date('Y-m-d H:i:s');
                $brand_name = $brand->brand_name;
                $type_ids = I('type_id','','trim,htmlspecialchars');
                $type_brand_relation_data = array();
                foreach($type_ids as $type_id){
                    $type_brand_relation_data[] = array(
                        'type_id'=>$type_id,
                        'brand_id'=>$brand_id,
                        'create_time'=>  date('Y-m-d H:i:s'),
                        'update_time'=>  date('Y-m-d H:i:s')
                    );
                }
                $brand->startTrans();
                $relation_bool = true;
                if($type_brand_relation_data) $relation_bool = M('goods_type_brand_relation')->addAll($type_brand_relation_data);
                $count = $brand->add();
                if($count && $relation_bool) {
                    $brand->commit();
                    $this->oldAjaxReturn($brand_name,0,1);
                }else{
                    $brand->rollback();
                    $this->oldAjaxReturn(0,'添加失败',0);
                }
            }
        }
        $type_list = M('goods_type')->where(array('type_status'=>1))->order('create_time asc')->select();
        $this->assign('type_list', $type_list);
        $this->display();
    }
    
    //更新
    public function update(){
        $brand_id = I('brand_id','','trim,htmlspecialchars');
        if(IS_AJAX){
            $brand = D('Brand');
            if(!$brand->create()) {
                $error = each($brand->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $count = $brand->where(array('brand_name'=>$brand->brand_name,'brand_status'=>array('neq',0),'brand_id'=>array('neq',$brand_id)))->count();
                if($count) $this->oldAjaxReturn ('brand_name', '品牌名称已经存在，请重新输入', 0);
                //状态默认是上架
                $brand->brand_status = I('brand_status',1,'intval');
                $brand->update_time = date('Y-m-d H:i:s');
                $brand_name = $brand->brand_name;
                $type_ids = I('type_id','','trim,htmlspecialchars');
                $type_brand_relation_data = array();
                foreach($type_ids as $type_id){
                    $type_brand_relation_data[] = array(
                        'type_id'=>$type_id,
                        'brand_id'=>$brand_id,
                        'create_time'=>  date('Y-m-d H:i:s'),
                        'update_time'=>  date('Y-m-d H:i:s')
                    );
                }
                $brand->startTrans();
                $del_bool = M('goods_type_brand_relation')->where(array('brand_id'=>$brand_id))->delete();
                $relation_bool = true;
                if($type_brand_relation_data) $relation_bool = M('goods_type_brand_relation')->addAll($type_brand_relation_data);
                $count = $brand->where($this->get_where())->save();
                if($count === FALSE || $del_bool === FALSE || $relation_bool === FALSE) {
                    $brand->rollback();
                    $this->oldAjaxReturn(0,'修改失败',0);
                }else{
                    $brand->commit();
                    $this->oldAjaxReturn($brand_name,0,1);
                }
            }
        }
        $info = D('Brand')->where($this->get_where())->find();
        $this->assign('info', $info);
        $type_list = M('goods_type')->where(array('type_status'=>1))->order('create_time asc')->select();
        $relation_type_ids = M('goods_type_brand_relation')->where(array('brand_id'=>$brand_id))->getField('type_id',true);
        foreach($type_list as $k=>$type){
            if(in_array($type['type_id'], $relation_type_ids)) $type_list[$k]['checked'] = true;
        }
        $this->assign('type_list', $type_list);
        $this->display();
    }
    
    //删除
    public function delete(){
        $goods_count = M('goods_base_info')->where(array('brand_id'=>I('brand_id','','trim,htmlspecialchars'),'goods_status'=>array('neq',0)))->count();
        if($goods_count) $this->oldAjaxReturn (0, '关联品牌的有 '.$goods_count.' 个商品，请先取消关联', 0);
        $del_bool = D('Brand')->where($this->get_where())->data(array('brand_status'=>0,'update_time'=>date('Y-m-d H:i:s')))->save();
        if($del_bool) $this->oldAjaxReturn (0, '删除成功', 1);
        $this->oldAjaxReturn(0, '删除失败', 0);
    }
    
    //修改状态
    public function change_barnd_status(){
        $barnd_status = I('brand_status',0,'intval');
        $update_bool = D('Brand')->where($this->get_where())->data(array('brand_status'=>$barnd_status,'update_time'=>date('Y-m-d H:i:s')))->save();
        if($update_bool) $this->oldAjaxReturn (0, '修改成功', 1);
        $this->oldAjaxReturn(0, '修改失败', 0);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('Brand/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'品牌名称',
                    'width'=>250,
                    'tip'=>'请输入品牌名称'
                ),
                array(
                    'name'=>'search_brand_status',
                    'show_name'=>'状态',
                    'tip'=>'请选择状态',
                    'select'=>C('brand_status'),
                    'type'=>'select'
                )
            ),
        );
    }
    
    private function get_where(){
        $where = array();
        $where['brand_status'] = array('neq',0);
        $brand_id = I('brand_id','','trim,htmlspecialchars');
        if($brand_id) $where['brand_id'] = $brand_id;
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['brand_name'] = array('like','%'.$seatch.'%');
        $search_brand_status = I('search_brand_status',0,'intval');
        if($search_brand_status) $where['brand_status'] = $search_brand_status;
        return $where;
    }
}
