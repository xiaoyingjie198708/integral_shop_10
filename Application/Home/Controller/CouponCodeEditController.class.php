<?php


namespace Home\Controller;

/**
 *优惠码编辑管理
 * @author xiao.yingjie
 */
class CouponCodeEditController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('CouponCodeEdit')->where($where)->count(),10);
        $list = D('CouponCodeEdit')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    //添加优惠码
    public function add(){
        if(IS_AJAX){
            $coupon_code_edit = D('CouponCodeEdit');
            if(!$coupon_code_edit->create()) {
                $error = each($coupon_code_edit->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $where['coupon_category_type'] = $coupon_code_edit->coupon_category_type;
                $where['coupon_code_name'] = $coupon_code_edit->coupon_code_name;
                $exist_coupon = $coupon_code_edit->where($where)->find();
                if($exist_coupon) $this->oldAjaxReturn ('coupon_code_name', '优惠码名称已经存在，请重新输入', 0);
                if(!$coupon_code_edit->coupon_value) $this->oldAjaxReturn ('coupon_value', '请输入优惠码金额', 0);
                if(!$coupon_code_edit->max_count) $this->oldAjaxReturn ('max_count', '请输入最大领取数', 0);
                if(!$coupon_code_edit->max_use_count) $this->oldAjaxReturn ('max_use_count', '请输入最大使用次数', 0);
                if(strtotime($coupon_code_edit->valid_start_time) >= strtotime($coupon_code_edit->valid_end_time)) $this->oldAjaxReturn ('valid_end_time', '有效期结束时间必须大于开始时间', 0);
                $coupon_code_name = $coupon_code_edit->coupon_code_name;
                $coupon_code_id = (new \Org\Util\ThinkString())->uuid();
                $coupon_code_edit->id = $coupon_code_id;
                $coupon_code_edit->coupon_code_id = $coupon_code_id;
                $coupon_code_edit->formal_coupon_code_id = $coupon_code_id;
                //编辑状态
                $coupon_code_edit->coupon_status = 10;
                $coupon_code_edit->create_time = date('Y-m-d H:i:s');
                $coupon_code_edit->update_time = date('Y-m-d H:i:s');
                $add_bool = $coupon_code_edit->add();
                if($add_bool) $this->oldAjaxReturn ($coupon_code_name, '添加成功', 1);
                else $this->oldAjaxReturn (0, '添加失败', 0);
            }
        }
        $this->display();
    }
    
    //更新优惠码
    public function update(){
        $coupon_code_edit = D('CouponCodeEdit');
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        if(IS_AJAX){
            if(!$coupon_code_edit->create()) {
                $error = each($coupon_code_edit->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $where['coupon_category_type'] = $coupon_code_edit->coupon_category_type;
                $where['coupon_code_name'] = $coupon_code_edit->coupon_code_name;
                $where['coupon_code_id'] = array('neq',$coupon_code_id);
                $exist_coupon = $coupon_code_edit->where($where)->find();
                if($exist_coupon) $this->oldAjaxReturn ('coupon_code_name', '优惠码名称已经存在，请重新输入', 0);
                if(!$coupon_code_edit->coupon_value) $this->oldAjaxReturn ('coupon_value', '请输入优惠码金额', 0);
                if(!$coupon_code_edit->max_count) $this->oldAjaxReturn ('max_count', '请输入最大领取数', 0);
                if(!$coupon_code_edit->max_use_count) $this->oldAjaxReturn ('max_use_count', '请输入最大使用次数', 0);
                if(strtotime($coupon_code_edit->valid_start_time) >= strtotime($coupon_code_edit->valid_end_time)) $this->oldAjaxReturn ('valid_end_time', '有效期结束时间必须大于开始时间', 0);
                //编辑状态
                $coupon_code_name = $coupon_code_edit->coupon_code_name;
                $coupon_code_edit->coupon_status = 10;
                $coupon_code_edit->update_time = date('Y-m-d H:i:s');
                $coupon_code_edit->is_wap = I('is_wap',0,'intval');
                $coupon_code_edit->is_pc = I('is_pc',0,'intval');
                $update_bool = $coupon_code_edit->where($this->get_where())->save();
                if($update_bool) $this->oldAjaxReturn ($coupon_code_name, '更新成功', 1);
                else $this->oldAjaxReturn (0, '更新失败', 0);
            }
        }
        $info = $coupon_code_edit->where($this->get_where())->find();
        $this->assign('info', $info);
        $this->display();
    }
    
    //查看详情
    public function info(){
        $info = D('CouponCodeEdit')->where($this->get_where())->find();
        $this->assign('info', $info);
        $this->display();
    }
    
    //提交申请
    public function submit_check(){
        $mode = D('CouponCodeEdit');
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $coupon_code_id = explode(',', trim($coupon_code_id,','));
        $where['coupon_code_id'] = array('in',$coupon_code_id);
        //状态为编辑和审核失败
        $where['coupon_status'] = array('in',array(10,22));
        //有效期大于当前时间
        $where['valid_end_time'] = array('gt',date('Y-m-d H:i:s'));
        $coupon_list = $mode->where($where)->select();
        if(!$coupon_list) $this->oldAjaxReturn (0, '请选择要审核的有效优惠码', 0);
        $check_relation_list = array();
        $check_exclude_list = array();
        foreach($coupon_list as $k=>$coupon_info){
            unset($coupon_list[$k]['id']);
            $coupon_id = (new \Org\Util\ThinkString())->uuid();
            $edit_coupon_id = $coupon_info['coupon_code_id'];
            //重新赋值
            unset($coupon_list[$k]['check_time']);
            unset($coupon_list[$k]['refuse_reason']);
            $coupon_list[$k]['coupon_code_id'] = $coupon_id;
            $coupon_list[$k]['edit_coupon_code_id'] = $edit_coupon_id;
            //待审核
            $coupon_list[$k]['coupon_status'] = 20;
            $coupon_list[$k]['update_time'] = date('Y-m-d H:i:s');
            //绑定信息
            $relation_list = M('coupon_code_relation_info_edit')->where(array('coupon_code_id'=>$edit_coupon_id))->select();
            foreach($relation_list as $j=>$relation){
                unset($relation['id']);
                $relation['coupon_code_id'] = $coupon_id;
                $check_relation_list[] = $relation;
            }
            //排除商品
            $exclude_list = M('coupon_code_relation_exclude_goods_edit')->where(array('coupon_code_id'=>$edit_coupon_id))->select();
            foreach($exclude_list as $j=>$exclude){
                unset($exclude['id']);
                $exclude['coupon_code_id'] = $coupon_id;
                $check_exclude_list[] = $exclude;
            }
        }
        $mode->startTrans();
        $edit_coupon_bool = $mode->where($where)->data(array('coupon_status'=>20,'update_time'=>date('Y-m-d H:i:s')))->save();
        $check_coupon_bool = D('CouponCodeCheck')->addAll($coupon_list);
        $check_relation_bool = true;
        if($check_relation_list) $check_relation_bool = M('coupon_code_relation_info_check')->addAll($check_relation_list);
        $check_exclude_bool = true;
        if($check_exclude_list) $check_exclude_bool = M('coupon_code_relation_exclude_goods_check')->addAll($check_exclude_list);
        if($edit_coupon_bool && $check_coupon_bool && $check_relation_bool && $check_exclude_bool){
            $mode->commit();
            $this->oldAjaxReturn(0, '提交审核成功', 1);
        }else{
            $mode->rollback();
            $this->oldAjaxReturn(0, '提交审核失败', 0);
        }
    }
    
    //关联商品
    public function relation_goods(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $goods_ids = I('goods_ids','','trim,htmlspecialchars');
        $goods_ids = explode(',', trim($goods_ids, ','));
        //去除已经存在的关联商品
        $relation_where['coupon_code_id'] = $coupon_code_id;
        $relation_where['relation_id'] = array('in',$goods_ids);
        $exist_relation_ids = M('coupon_code_relation_info_edit')->where($relation_where)->getField('relation_id',true);
        foreach($goods_ids as $k=>$goods_id){
            if(in_array($goods_id, $exist_relation_ids)){
                unset($goods_ids[$k]);
            }
        }
        if(empty($goods_ids) || !$goods_ids) $this->oldAjaxReturn (0, '没有要添加的关联商品', 1);
        //新添加的商品
        $goods_base_list = D('GoodsBase')->where(array('goods_id'=>array('in',$goods_ids)))->select();
        $relation_edit_arr = array();
        foreach($goods_base_list as $k=>$goods_info){
            $relation_edit_arr[] = array(
                'coupon_code_id'=>$coupon_code_id,
                'relation_id'=>$goods_info['goods_id'],
                'relation_code'=>$goods_info['goods_code'],
                'create_time'=>  date('Y-m-d H:i:s')
            );
        }
        $relation_edit_bool = M('coupon_code_relation_info_edit')->addAll($relation_edit_arr);
        if($relation_edit_bool) $this->oldAjaxReturn (0, '添加成功', 1);
        else $this->oldAjaxReturn (0, '添加关联失败', 0);
    }
    
    //关联商家
    public function relation_shops(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $shops_ids = I('shops_ids','','trim,htmlspecialchars');
        $shops_ids = explode(',', trim($shops_ids,','));
        //去除已经存在的关联商家
        $relation_where['coupon_code_id'] = $coupon_code_id;
        $relation_where['relation_id'] = array('in',$shops_ids);
        $exist_relation_ids = M('coupon_code_relation_info_edit')->where($relation_where)->getField('relation_id',true);
        foreach($shops_ids as $k=>$shops_id){
            if(in_array($shops_id, $exist_relation_ids)){
                unset($shops_ids[$k]);
            }
        }
        if(empty($shops_ids) || !$shops_ids) $this->oldAjaxReturn (0, '没有要添加的关联商家', 1);
        //新添加的商家
        $shops_list = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->select();
        $relation_edit_arr = array();
        foreach($shops_list as $k=>$shops_info){
            $relation_edit_arr[] = array(
                'coupon_code_id'=>$coupon_code_id,
                'relation_id'=>$shops_info['shops_id'],
                'relation_code'=>$shops_info['shops_code'],
                'create_time'=>  date('Y-m-d H:i:s')
            );
        }
        $relation_edit_bool = M('coupon_code_relation_info_edit')->addAll($relation_edit_arr);
        if($relation_edit_bool) $this->oldAjaxReturn (0, '添加成功', 1);
        else $this->oldAjaxReturn (0, '添加关联失败', 0);
    }
    
    //关联分类页面
    public function category_info(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $coupon_info = D('CouponCodeEdit')->where(array('id'=>$coupon_code_id))->find();
        $coupon_relation_ids = M('coupon_code_relation_info_edit')->where(array('coupon_code_id'=>$coupon_code_id))->getField('relation_id',true);
        $list = M('goods_category')->where(array('goods_category_status'=>array('neq',0)))->order('create_time asc')->select();
        $list = \Org\My\Tree::_getCustomTree($list,0,0,'goods_category_id','goods_category_parent_id');
        foreach($list as $k=>$category_info){
            if($category_info['child_node']){
                $child_count = M('goods_category')->where(array('goods_category_parent_id'=>$category_info['goods_category_id']))->count();
                if(!$child_count) $list[$k]['last_node'] = 1;
                if(in_array($category_info['goods_category_id'], $coupon_relation_ids)) $list[$k]['select_node'] = 1;
            }
        }
        $this->assign('coupon_info', $coupon_info);
        $this->assign('list',$list);
        $this->display();
    }
    
    //关联分类
    public function relation_category(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $category_id = I('category_id','','trim,htmlspecialchars');
        $category_info = M('goods_category')->where(array('goods_category_id'=>$category_id))->find();
        $relation_data = array(
            'coupon_code_id'=>$coupon_code_id,
            'relation_id'=>$category_info['goods_category_id'],
            'relation_code'=>$category_info['goods_category_code'],
            'create_time'=>  date('Y-m-d H:i:s')
        );
        $relation_edit_bool = M('coupon_code_relation_info_edit')->data($relation_data)->add();
        if($relation_edit_bool) $this->oldAjaxReturn (0, '添加成功', 1);
        else $this->oldAjaxReturn (0, '添加关联失败', 0);
    }
    //关联排除商品
    public function relation_exclude_goods(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $goods_ids = I('goods_ids','','trim,htmlspecialchars');
        $goods_ids = explode(',', trim($goods_ids, ','));
        //去除已经关联的排除商品
        $relation_where['coupon_code_id'] = $coupon_code_id;
        $relation_where['relation_id'] = array('in',$goods_ids);
        $exists_goods_ids = M('coupon_code_relation_exclude_goods_edit')->where($relation_where)->getField('relation_id',true);
        foreach($goods_ids as $k=>$goods_id){
            if(in_array($goods_id, $exists_goods_ids)) unset($goods_ids[$k]);
        }
        if(empty($goods_ids) || !$goods_ids) $this->oldAjaxReturn (0, '没有新添加要关联的商品', 1);
        //关联新的排除商品
        $goods_base_list = D('GoodsBase')->where(array('goods_id'=>array('in',$goods_ids)))->select();
        $relation_edit_arr = array();
        foreach($goods_base_list as $k=>$goods_info){
            $relation_edit_arr[] = array(
                'coupon_code_id'=>$coupon_code_id,
                'relation_id'=>$goods_info['goods_id'],
                'relation_code'=>$goods_info['goods_code'],
                'create_time'=>  date('Y-m-d H:i:s')
            );
        }
        $relation_edit_bool = M('coupon_code_relation_exclude_goods_edit')->addAll($relation_edit_arr);
        if($relation_edit_bool) $this->oldAjaxReturn (0, '添加成功', 1);
        else $this->oldAjaxReturn (0, '添加关联失败', 0);
    }
    
    //取消关联
    public function unrelation_category(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $category_id = I('category_id','','trim,htmlspecialchars');
        $relation_del_bool = M('coupon_code_relation_info_edit')->where(array('coupon_code_id'=>$coupon_code_id,'relation_id'=>$category_id))->delete();
        if($relation_del_bool) $this->oldAjaxReturn (0, '取消关联成功', 1);
        else $this->oldAjaxReturn (0, '取消关联失败', 0);
    }
    
    //取消关联
    public function del_relation(){
        $id = I('id',0,'intval');
        $relation_del_bool = M('coupon_code_relation_info_edit')->where(array('id'=>$id))->delete();
        if($relation_del_bool) $this->oldAjaxReturn (0, '取消关联成功', 1);
        else $this->oldAjaxReturn (0, '取消关联失败', 0);
    }
    
    //取消关联排除商品
    public function del_exclude_goods(){
        $id = I('id',0,'intval');
        $relation_del_bool = M('coupon_code_relation_exclude_goods_edit')->where(array('id'=>$id))->delete();
        if($relation_del_bool) $this->oldAjaxReturn (0, '取消关联成功', 1);
        else $this->oldAjaxReturn (0, '取消关联失败', 0);
    }
    
    //维护关联关系
    public function relation(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $coupon_info = D('CouponCodeEdit')->where(array('coupon_code_id'=>$coupon_code_id))->find();
        $this->assign('info', $coupon_info);
        //关联商家
        if($coupon_info['coupon_use_type'] == 2){
            $relation_list = M('coupon_code_relation_info_edit')->where(array('coupon_code_id'=>$coupon_info['coupon_code_id']))->select();
            $relation_ids = getFieldsByKey($relation_list, 'relation_id');
            $shops_list = D('Shops')->where(array('shops_id'=>array('in',$relation_ids)))->getField('shops_id,shops_name',true);
            foreach($relation_list as $k=>$relation){
                $relation_list[$k]['shops_name'] = $shops_list[$relation['relation_id']];
            }
        }
        //关联分类
        if($coupon_info['coupon_use_type'] == 3){
            $coupon_code_id = $coupon_info['coupon_code_id'];
            $coupon_relation_ids = M('coupon_code_relation_info_edit')->where(array('coupon_code_id'=>$coupon_code_id))->getField('relation_id',true);
            $list = M('goods_category')->where(array('goods_category_status'=>array('neq',0)))->order('create_time asc')->select();
            $list = \Org\My\Tree::_getCustomTree($list,0,0,'goods_category_id','goods_category_parent_id');
            foreach($list as $k=>$category_info){
                if($category_info['child_node']){
                    $child_count = M('goods_category')->where(array('goods_category_parent_id'=>$category_info['goods_category_id']))->count();
                    if(!$child_count) $list[$k]['last_node'] = 1;
                    if(in_array($category_info['goods_category_id'], $coupon_relation_ids)) $list[$k]['select_node'] = 1;
                }
            }
            $this->assign('list',$list);
        }
        //关联商品
        if($coupon_info['coupon_use_type'] == 4){
            $mod =  M('coupon_code_relation_info_edit as exclude,'.C('DB_PREFIX').'goods_base_info as goods');
            $where['exclude.coupon_code_id'] = $coupon_info['coupon_code_id'];
            $field = 'exclude.id,goods.goods_code,goods.goods_name,goods.goods_materiel_code,goods.shops_code,exclude.relation_id';
            $page = new \Org\My\Page($mod->where('exclude.relation_id = goods.goods_id')->where($where)->count(),10);
            $relation_list = $mod->where('exclude.relation_id = goods.goods_id')->where($where)->limit($page->firstRow.','.$page->listRows)->field($field)->select();
            $shops_codes = getFieldsByKey($relation_list, 'shops_code');
            $shops_list = D('Shops')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
            foreach($relation_list as $k=>$exclude_goods){
                $relation_list[$k]['shops_name'] = $shops_list[$exclude_goods['shops_code']];
            }
            $this->assign('relation_page', $page->show());
        }
        
        //排除商品
        if($coupon_info['coupon_use_type'] < 4){
            $mod =  M('coupon_code_relation_exclude_goods_edit as exclude,'.C('DB_PREFIX').'goods_base_info as goods');
            $where['exclude.coupon_code_id'] = $coupon_info['coupon_code_id'];
            $field = 'exclude.id,goods.goods_code,goods.goods_name,goods.goods_materiel_code,goods.shops_code,exclude.relation_id';
            $page = new \Org\My\Page($mod->where('exclude.relation_id = goods.goods_id')->where($where)->count(),10);
            $exclude_goods_list = $mod->where('exclude.relation_id = goods.goods_id')->where($where)->limit($page->firstRow.','.$page->listRows)->field($field)->select();
            $shops_codes = getFieldsByKey($exclude_goods_list, 'shops_code');
            $shops_list = D('Shops')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
            foreach($exclude_goods_list as $k=>$exclude_goods){
                $exclude_goods_list[$k]['shops_name'] = $shops_list[$exclude_goods['shops_code']];
            }
            $this->assign('exclude_goods_list', $exclude_goods_list);
            $this->assign('page', $page->show());
        }
        $this->assign('relation_list', $relation_list);
        if(I('type','') == 'info') $this->display('relation_info');
        else $this->display('relation');
        
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('CouponCodeEdit/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'优惠码名称',
                    'width'=>250,
                    'tip'=>'请输入优惠码名称'
                ),
            ),
        );
    }
    
    private function get_where(){
        $where = array();
        //编辑和拒绝状态
        $where['coupon_status'] = array('in',array(10,22));
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['coupon_code_name'] = array('like','%'.$seatch.'%');
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        if($coupon_code_id) $where['coupon_code_id'] = $coupon_code_id;
        return $where;
    }
}
