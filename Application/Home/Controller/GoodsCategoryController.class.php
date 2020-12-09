<?php


namespace Home\Controller;
/**
 * 商品分类管理
 *
 * @author xiao.yingjie
 */
class GoodsCategoryController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $list = M('goods_category')->where($where)->order('create_time asc')->select();
        $list = \Org\My\Tree::_getCustomTree($list,0,0,'goods_category_id','goods_category_parent_id');
        $this->assign('list',$list);
        $this->display();
    }
    
    public function add(){
        if(IS_AJAX){
            $data['goods_category_id'] = (new \Org\Util\ThinkString())->uuid();
            $data['goods_category_parent_id'] = I('parent_id','','trim,htmlspecialchars');
            $data['goods_category_path'] = I('path','','trim,htmlspecialchars');
            $level = count(explode('_', $data['goods_category_path']));
            $data['goods_category_level'] = $level;
            $data['goods_category_name'] = I('goods_category_name','','trim,htmlspecialchars');
            if(!$data['goods_category_name']) $this->oldAjaxReturn ('goods_category_name', '请输入分类名称', 0);
            $ex_where = array('goods_category_parent_id'=>$data['goods_category_parent_id'],'goods_category_name'=>$data['goods_category_name'],'goods_category_status'=>array('neq',0));
            $info = M('goods_category')->where($ex_where)->find();
            if($info) $this->oldAjaxReturn ('goods_category_name', '分类名称已经存在，请重新输入', 0);
            $data['goods_category_status'] = I('goods_category_status',1,'intval');
            if(!in_array($data['goods_category_status'], array(1,2))) $this->oldAjaxReturn (0, '状态值异常', 0);
            $data['goods_category_desc'] = I('goods_category_desc','','trim,htmlspecialchars');
            $data['goods_category_summary'] = I('goods_category_summary','','trim,htmlspecialchars');
            $data['goods_category_seo_info'] = I('goods_category_seo_info','','trim,htmlspecialchars');
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = date('Y-m-d H:i:s');
            //分类编码
            $goods_category_code = I('goods_category_code','','trim,htmlspecialchars');
            if($goods_category_code){
                $count = M('goods_category')->where(array('goods_category_code'=>$goods_category_code))->count();
                if($count) $this->oldAjaxReturn ('goods_category_code', '请重新输入分类编码', 0);
            }else{
                $goods_category_code = M('goods_category')->order('goods_category_code desc')->getField('goods_category_code');
                if($goods_category_code) $goods_category_code = $goods_category_code + 1;
                else $goods_category_code = 1001;
            }
            $data['goods_category_code'] = $goods_category_code;
            //是否子节点
            $data['child_node'] = $level > 1 ? 1 : 0;
            $add_bool = M('goods_category')->data($data)->add();
            if(!$add_bool) $this->oldAjaxReturn (0, '添加分类失败', 0);
            $this->oldAjaxReturn($data['goods_category_name'], '添加成功', 1);
        }
        $root_serials = M('goods_category')->where(array('goods_category_status'=>array('neq',0),'goods_category_level'=>1))->order('create_time asc')->select();
        $this->assign('root_serials',$root_serials);
        $this->display();
    }
    
    public function update(){
        if(IS_AJAX){
            $goods_category_id = I('goods_category_id','','trim,htmlspecialchars');
            $data['goods_category_parent_id'] = I('parent_id','','trim,htmlspecialchars');
            $data['goods_category_path'] = I('path','','trim,htmlspecialchars');
            $level = count(explode('_', $data['goods_category_path']));
            $data['goods_category_level'] = $level;
            $data['goods_category_name'] = I('goods_category_name','','trim,htmlspecialchars');
            if(!$data['goods_category_name']) $this->oldAjaxReturn ('goods_category_name', '请输入分类名称', 0);
            $ex_where = array('goods_category_parent_id'=>$data['goods_category_parent_id'],'goods_category_name'=>$data['goods_category_name'],'goods_category_status'=>array('neq',0),'goods_category_id'=>array('neq',$goods_category_id));
            $info = M('goods_category')->where($ex_where)->find();
            if($info) $this->oldAjaxReturn ('goods_category_name', '分类名称已经存在，请重新输入', 0);
            $data['goods_category_status'] = I('goods_category_status',1,'intval');
            if(!in_array($data['goods_category_status'], array(1,2))) $this->oldAjaxReturn (0, '状态值异常', 0);
            $data['goods_category_desc'] = I('goods_category_desc','','trim,htmlspecialchars');
            $data['goods_category_summary'] = I('goods_category_summary','','trim,htmlspecialchars');
            $data['goods_category_seo_info'] = I('goods_category_seo_info','','trim,htmlspecialchars');
            //分类编码
            $goods_category_code = I('goods_category_code','','trim,htmlspecialchars');
            if($goods_category_code){
                $count = M('goods_category')->where(array('goods_category_code'=>$goods_category_code,'goods_category_id'=>array('neq',$goods_category_id)))->count();
                if($count) $this->oldAjaxReturn ('goods_category_code', '请重新输入分类编码', 0);
            }else{
                $this->oldAjaxReturn ('goods_category_code', '请输入分类编码', 0);
            }
            $data['goods_category_code'] = $goods_category_code;
            $data['update_time'] = date('Y-m-d H:i:s');
            //是否子节点
            $data['child_node'] = $level > 1 ? 1 : 0;
            $add_bool = M('goods_category')->where($this->get_where())->data($data)->save();
            if(!$add_bool) $this->oldAjaxReturn (0, '修改分类失败', 0);
            $this->oldAjaxReturn($data['goods_category_name'], '修改成功', 1);
        }
        $info = M('goods_category')->where($this->get_where())->find();
        $this->assign('info', $info);
        $this->assign('parent_serial_list', $this->getParentSerial($info['goods_category_id']));
        $this->display();
    }
    
    //获取子分类
    public function getChildCategory(){
        $child_id = I('child_id','','trim,htmlspecialchars');
        $parent_category = M('goods_category')->where(array('goods_category_parent_id'=>I('id','','trim,htmlspecialchars')))->order('create_time asc')->select();
        foreach($parent_category as $k=>$info) if($info['goods_category_id'] == $child_id) unset ($parent_category[$k]);
        if(!$parent_category) $this->oldAjaxReturn(0,0,0);
        $this->assign('parent_category',$parent_category);
        $content = $this->fetch('child');
        $this->oldAjaxReturn($content,0,1);
    }
    
    //删除
    public function delete(){
        $goods_category_id = I('goods_category_id','','trim,htmlspecialchars');
        $count = M('goods_category')->where(array('goods_category_parent_id'=>$goods_category_id,'goods_category_status'=>array('neq',0)))->count();
        if($count) $this->oldAjaxReturn (0, '请删除子分类', 0);
        $del_bool = M('goods_category')->where($this->get_where())->data(array('goods_category_status'=>0,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($del_bool) $this->oldAjaxReturn (0, '删除成功', 1);
        $this->oldAjaxReturn (0, '删除失败', 0);
    }
    
    public function change_status($goods_category_id,$status){
        $info = M('goods_category')->where($this->get_where())->find();
        if($status == 2){
            $child_count = M('goods_category')->where(array('goods_category_parent_id'=>$goods_category_id,'goods_category_status'=>1))->count();
            if($child_count) $this->oldAjaxReturn (0, '请先下架全部子分类', 0);
        }else{
            if($info['goods_category_parent_id']){
                $parent_info = M('goods_category')->where(array('goods_category_id'=>$info['goods_category_parent_id']))->find();
                if($parent_info['goods_category_status'] != 1) $this->oldAjaxReturn (0, '请先上架父分类', 0);
            }
        }
        $update_bool = M('goods_category')->where($this->get_where())->data(array('goods_category_status'=>$status,'update_time'=>date('Y-m-d H:i:s')))->save();
        if($update_bool === FALSE) $this->oldAjaxReturn (0, '修改状态失败', 0);
        $this->oldAjaxReturn(0, '修改状态成功', 1);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_where(){
        $where = array();
        $where['goods_category_status'] = array('neq',0);
        $category_code = I('category_code','','trim,htmlspecialchars');
        if($category_code) $where['goods_category_code'] = $category_code;
        $goods_category_id = I('goods_category_id','','trim,htmlspecialchars');
        if($goods_category_id) $where['goods_category_id'] = $goods_category_id;
        return $where;
    }
    
    //获取分类的所有父分类
    private function getParentSerial($goods_category_id){
        $info = M('goods_category')->where(array('goods_category_id'=>$goods_category_id))->find();
        $parent_ids = explode('_', $info['goods_category_path']);
        if(!in_array(0, $parent_ids)) $parent_ids[] = 0;
        foreach($parent_ids as $k=>$parent_id){
            if($parent_id == $serial_id) unset ($parent_ids[$k]);
        }
        $where = array('goods_category_parent_id'=>array('in',implode(',',$parent_ids)));
        $serial_list = M('goods_category')->where($where)->order('create_time asc')->select();
        foreach($serial_list as $serial){
            foreach($parent_ids as $parent_id){
                if($parent_id == $serial['goods_category_id']){$serial['selected'] = true; break;}
            }
            if($serial['goods_category_id'] != $goods_category_id) $new_serial_list[$serial['goods_category_level']][] = $serial;
        }
        return $new_serial_list;
    }
}
