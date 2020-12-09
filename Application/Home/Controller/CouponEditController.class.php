<?php


namespace Home\Controller;

/**
 *优惠券编辑管理
 * @author xiao.yingjie
 */
class CouponEditController extends BaseController{
    
    private $coupon_label = array('基本信息','关联信息');
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('CouponEdit')->where($where)->count(),10);
        $list = D('CouponEdit')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    //添加优惠券
    public function add(){
        if(IS_AJAX){
            $coupon_edit = D('CouponEdit');
            if(!$coupon_edit->create()) {
                $error = each($coupon_edit->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $where['coupon_category_type'] = $coupon_edit->coupon_category_type;
                $where['coupon_name'] = $coupon_edit->coupon_name;
                $exist_coupon = $coupon_edit->where($where)->find();
                if($exist_coupon) $this->oldAjaxReturn ('coupon_name', '优惠券名称已经存在，请重新输入', 0);
                if(!$coupon_edit->coupon_value) $this->oldAjaxReturn ('coupon_value', '请输入优惠券金额', 0);
                if(!$coupon_edit->max_count) $this->oldAjaxReturn ('max_count', '请输入最大领取数', 0);
                if(strtotime($coupon_edit->valid_start_time) >= strtotime($coupon_edit->valid_end_time)) $this->oldAjaxReturn ('valid_end_time', '有效期结束时间必须大于开始时间', 0);
                if($coupon_edit->is_generalize){
                    if(strtotime($coupon_edit->generalize_start_time) >= strtotime($coupon_edit->generalize_end_time)) $this->oldAjaxReturn ('generalize_start_time', '推广有效期结束时间必须大于开始时间', 0);
                }
                $coupon_id = (new \Org\Util\ThinkString())->uuid();
                $coupon_edit->coupon_id = $coupon_id;
                $coupon_edit->formal_coupon_id = $coupon_id;
                //优惠券编码
                $coupon_code = $this->getCouponCode();
                $coupon_edit->coupon_code = $coupon_code;
                //编辑状态
                $coupon_edit->coupon_status = 10;
                $coupon_edit->create_time = date('Y-m-d H:i:s');
                $coupon_edit->update_time = date('Y-m-d H:i:s');
                $add_bool = $coupon_edit->add();
                if($add_bool) $this->oldAjaxReturn ($coupon_code, '添加成功', 1);
                else $this->oldAjaxReturn (0, '添加失败', 0);
            }
        }
        $this->assign('coupon_label', $this->coupon_label);
        $this->display();
    }
    
    //更新优惠券
    public function update(){
        $coupon_edit = D('CouponEdit');
        if(IS_AJAX){
            if(!$coupon_edit->create()) {
                $error = each($coupon_edit->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $where['coupon_category_type'] = $coupon_edit->coupon_category_type;
                $where['coupon_name'] = $coupon_edit->coupon_name;
                $where['coupon_code'] = array('neq',$coupon_edit->coupon_code);
                $exist_coupon = $coupon_edit->where($where)->find();
                if($exist_coupon) $this->oldAjaxReturn ('coupon_name', '优惠券名称已经存在，请重新输入', 0);
                if(!$coupon_edit->coupon_value) $this->oldAjaxReturn ('coupon_value', '请输入优惠券金额', 0);
                if(!$coupon_edit->max_count) $this->oldAjaxReturn ('max_count', '请输入最大领取数', 0);
                if(strtotime($coupon_edit->valid_start_time) >= strtotime($coupon_edit->valid_end_time)) $this->oldAjaxReturn ('valid_end_time', '有效期结束时间必须大于开始时间', 0);
                //编辑状态
                $coupon_edit->coupon_status = 10;
                $coupon_edit->update_time = date('Y-m-d H:i:s');
                $coupon_edit->is_wap = I('is_wap',0,'intval');
                $coupon_edit->is_pc = I('is_pc',0,'intval');
                $update_bool = $coupon_edit->where($this->get_where())->save();
                if($update_bool) $this->oldAjaxReturn ($coupon_code, '更新成功', 1);
                else $this->oldAjaxReturn (0, '更新失败', 0);
            }
        }
        $info = $coupon_edit->where($this->get_where())->find();
        $this->assign('info', $info);
        $this->display();
    }
    
    //查看详情
    public function info(){
        $info = D('CouponEdit')->where($this->get_where())->find();
        $this->assign('info', $info);
        $this->display();
    }
    
    //提交申请
    public function submit_check(){
        $coupon_edit = D('CouponEdit');
        $coupon_codes = I('coupon_codes','','trim,htmlspecialchars');
        $coupon_codes = explode(',', trim($coupon_codes,','));
        $where['coupon_code'] = array('in',$coupon_codes);
        //状态为编辑和审核失败
        $where['coupon_status'] = array('in',array(10,22));
        //有效期大于当前时间
        $where['valid_end_time'] = array('gt',date('Y-m-d H:i:s'));
        $coupon_list = $coupon_edit->where($where)->select();
        if(!$coupon_list) $this->oldAjaxReturn (0, '请选择要审核的有效优惠券', 0);
        $check_relation_list = array();
        $check_exclude_list = array();
        foreach($coupon_list as $k=>$coupon_info){
            $coupon_id = (new \Org\Util\ThinkString())->uuid();
            $edit_coupon_id = $coupon_info['coupon_id'];
            unset($coupon_list[$k]['check_time']);
            unset($coupon_list[$k]['refuse_reason']);
            //重新赋值
            $coupon_list[$k]['coupon_id'] = $coupon_id;
            $coupon_list[$k]['edit_coupon_id'] = $edit_coupon_id;
            //待审核
            $coupon_list[$k]['coupon_status'] = 20;
            $coupon_list[$k]['update_time'] = date('Y-m-d H:i:s');
            //绑定信息
            $relation_list = M('coupon_relation_info_edit')->where(array('coupon_id'=>$edit_coupon_id))->select();
            foreach($relation_list as $j=>$relation){
                unset($relation['id']);
                $relation['coupon_id'] = $coupon_id;
                $check_relation_list[] = $relation;
            }
            //排除商品
            $exclude_list = M('coupon_relation_exclude_goods_edit')->where(array('coupon_id'=>$edit_coupon_id))->select();
            foreach($exclude_list as $j=>$exclude){
                unset($exclude['id']);
                $exclude['coupon_id'] = $coupon_id;
                $check_exclude_list[] = $exclude;
            }
        }
        $coupon_edit->startTrans();
        $edit_coupon_bool = $coupon_edit->where($where)->data(array('coupon_status'=>20,'update_time'=>date('Y-m-d H:i:s')))->save();
        $check_coupon_bool = D('CouponCheck')->addAll($coupon_list);
        $check_relation_bool = true;
        if($check_relation_list) $check_relation_bool = M('coupon_relation_info_check')->addAll($check_relation_list);
        $check_exclude_bool = true;
        if($check_exclude_list) $check_exclude_bool = M('coupon_relation_exclude_goods_check')->addAll($check_exclude_list);
        if($edit_coupon_bool && $check_coupon_bool && $check_relation_bool && $check_exclude_bool){
            $coupon_edit->commit();
            $this->oldAjaxReturn(0, '提交审核成功', 1);
        }else{
            $coupon_edit->rollback();
            $this->oldAjaxReturn(0, '提交审核失败', 0);
        }
    }
    
    //关联商品
    public function relation_goods(){
        $coupon_id = I('coupon_id','','trim,htmlspecialchars');
        $goods_ids = I('goods_ids','','trim,htmlspecialchars');
        $goods_ids = explode(',', trim($goods_ids, ','));
        //去除已经存在的关联商品
        $relation_where['coupon_id'] = $coupon_id;
        $relation_where['relation_id'] = array('in',$goods_ids);
        $exist_relation_ids = M('coupon_relation_info_edit')->where($relation_where)->getField('relation_id',true);
        foreach($goods_ids as $k=>$goods_id){
            if(in_array($goods_id, $exist_relation_ids)){
                unset($goods_ids[$k]);
            }
        }
        if(empty($goods_ids) || !$goods_ids) $this->oldAjaxReturn (0, '没有要添加的关联商品', 1);
        //新添加的商品
        $coupon_info = D('CouponEdit')->where(array('coupon_id'=>$coupon_id))->find();
        $goods_base_list = D('GoodsBase')->where(array('goods_id'=>array('in',$goods_ids)))->select();
        $relation_edit_arr = array();
        foreach($goods_base_list as $k=>$goods_info){
            $relation_edit_arr[] = array(
                'coupon_id'=>$coupon_info['coupon_id'],
                'coupon_code'=>$coupon_info['coupon_code'],
                'relation_id'=>$goods_info['goods_id'],
                'relation_code'=>$goods_info['goods_code'],
                'create_time'=>  date('Y-m-d H:i:s')
            );
        }
        $relation_edit_bool = M('coupon_relation_info_edit')->addAll($relation_edit_arr);
        if($relation_edit_bool) $this->oldAjaxReturn (0, '添加成功', 1);
        else $this->oldAjaxReturn (0, '添加关联失败', 0);
    }
    
    //关联商家
    public function relation_shops(){
        $coupon_id = I('coupon_id','','trim,htmlspecialchars');
        $shops_ids = I('shops_ids','','trim,htmlspecialchars');
        $shops_ids = explode(',', trim($shops_ids,','));
        //去除已经存在的关联商家
        $relation_where['coupon_id'] = $coupon_id;
        $relation_where['relation_id'] = array('in',$shops_ids);
        $exist_relation_ids = M('coupon_relation_info_edit')->where($relation_where)->getField('relation_id',true);
        foreach($shops_ids as $k=>$shops_id){
            if(in_array($shops_id, $exist_relation_ids)){
                unset($shops_ids[$k]);
            }
        }
        if(empty($shops_ids) || !$shops_ids) $this->oldAjaxReturn (0, '没有要添加的关联商家', 1);
        //新添加的商家
        $coupon_info = D('CouponEdit')->where(array('coupon_id'=>$coupon_id))->find();
        $shops_list = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->select();
        $relation_edit_arr = array();
        foreach($shops_list as $k=>$shops_info){
            $relation_edit_arr[] = array(
                'coupon_id'=>$coupon_info['coupon_id'],
                'coupon_code'=>$coupon_info['coupon_code'],
                'relation_id'=>$shops_info['shops_id'],
                'relation_code'=>$shops_info['shops_code'],
                'create_time'=>  date('Y-m-d H:i:s')
            );
        }
        $relation_edit_bool = M('coupon_relation_info_edit')->addAll($relation_edit_arr);
        if($relation_edit_bool) $this->oldAjaxReturn (0, '添加成功', 1);
        else $this->oldAjaxReturn (0, '添加关联失败', 0);
    }
    
    //关联分类
    public function relation_category(){
        $coupon_id = I('coupon_id','','trim,htmlspecialchars');
        $coupon_info = D('CouponEdit')->where(array('coupon_id'=>$coupon_id))->find();
        $category_id = I('category_id','','trim,htmlspecialchars');
        $category_info = M('goods_category')->where(array('goods_category_id'=>$category_id))->find();
        $relation_data = array(
            'coupon_id'=>$coupon_info['coupon_id'],
            'coupon_code'=>$coupon_info['coupon_code'],
            'relation_id'=>$category_info['goods_category_id'],
            'relation_code'=>$category_info['goods_category_code'],
            'create_time'=>  date('Y-m-d H:i:s')
        );
        $relation_edit_bool = M('coupon_relation_info_edit')->data($relation_data)->add();
        if($relation_edit_bool) $this->oldAjaxReturn (0, '添加成功', 1);
        else $this->oldAjaxReturn (0, '添加关联失败', 0);
    }
    
    //关联排除商品
    public function relation_exclude_goods(){
        $coupon_id = I('coupon_id','','trim,htmlspecialchars');
        $goods_ids = I('goods_ids','','trim,htmlspecialchars');
        $goods_ids = explode(',', trim($goods_ids, ','));
        //去除已经关联的排除商品
        $relation_where['coupon_id'] = $coupon_id;
        $relation_where['relation_id'] = array('in',$goods_ids);
        $exists_goods_ids = M('coupon_relation_exclude_goods_edit')->where($relation_where)->getField('relation_id',true);
        foreach($goods_ids as $k=>$goods_id){
            if(in_array($goods_id, $exists_goods_ids)) unset($goods_ids[$k]);
        }
        if(empty($goods_ids) || !$goods_ids) $this->oldAjaxReturn (0, '没有新添加要关联的商品', 1);
        //关联新的排除商品
        $coupon_info = D('CouponEdit')->where(array('coupon_id'=>$coupon_id))->find();
        $goods_base_list = D('GoodsBase')->where(array('goods_id'=>array('in',$goods_ids)))->select();
        $relation_edit_arr = array();
        foreach($goods_base_list as $k=>$goods_info){
            $relation_edit_arr[] = array(
                'coupon_id'=>$coupon_info['coupon_id'],
                'coupon_code'=>$coupon_info['coupon_code'],
                'relation_id'=>$goods_info['goods_id'],
                'relation_code'=>$goods_info['goods_code'],
                'create_time'=>  date('Y-m-d H:i:s')
            );
        }
        $relation_edit_bool = M('coupon_relation_exclude_goods_edit')->addAll($relation_edit_arr);
        if($relation_edit_bool) $this->oldAjaxReturn (0, '添加成功', 1);
        else $this->oldAjaxReturn (0, '添加关联失败', 0);
    }
    
    //取消分类关联
    public function unrelation_category(){
        $coupon_id = I('coupon_id','','trim,htmlspecialchars');
        $category_id = I('category_id','','trim,htmlspecialchars');
        $relation_del_bool = M('coupon_relation_info_edit')->where(array('coupon_id'=>$coupon_id,'relation_id'=>$category_id))->delete();
        if($relation_del_bool) $this->oldAjaxReturn (0, '取消关联成功', 1);
        else $this->oldAjaxReturn (0, '取消关联失败', 0);
    }
    
    //取消关联
    public function del_relation(){
        $id = I('id',0,'intval');
        $relation_del_bool = M('coupon_relation_info_edit')->where(array('id'=>$id))->delete();
        if($relation_del_bool) $this->oldAjaxReturn (0, '取消关联成功', 1);
        else $this->oldAjaxReturn (0, '取消关联失败', 0);
    }
    
    public function exclude_goods_detail(){
        $coupon_code = I('coupon_code','','trim,htmlspecialchars');
        $coupon_info = D('CouponEdit')->where(array('coupon_code'=>$coupon_code))->find();
        $this->assign('coupon_info', $coupon_info);
        $where['relation.coupon_code'] = $coupon_code;
        $count = M('coupon_relation_exclude_goods_edit as relation,'.C('DB_PREFIX').'goods_base_info_edit as goods')->where('relation.relation_code = goods.goods_code')->where($where)->count();
        $page = new \Org\My\Page($count,10);
        $list = M('coupon_relation_exclude_goods_edit as relation,'.C('DB_PREFIX').'goods_base_info_edit as goods')->where('relation.relation_code = goods.goods_code')->where($where)->field('goods.*,relation.create_time as relation_time')->select();
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    //删除排除商品的关联
    public function del_exclude_goods(){
        $id = I('id',0,'intval');
        $relation_del_bool = M('coupon_relation_exclude_goods_edit')->where(array('id'=>$id))->delete();
        if($relation_del_bool) $this->oldAjaxReturn (0, '取消关联成功', 1);
        else $this->oldAjaxReturn (0, '取消关联失败', 0);
    }
    
    //维护关联关系
    public function relation(){
        $coupon_code = I('coupon_code','','trim,htmlspecialchars');
        $coupon_info = D('CouponEdit')->where(array('coupon_code'=>$coupon_code))->find();
        $this->assign('info', $coupon_info);
        //关联商家
        if($coupon_info['coupon_use_type'] == 2){
             $relation_list = M('coupon_relation_info_edit')->where(array('coupon_id'=>$coupon_info['coupon_id']))->select();
            $relation_ids = getFieldsByKey($relation_list, 'relation_id');
            $shops_list = D('Shops')->where(array('shops_id'=>array('in',$relation_ids)))->getField('shops_id,shops_name',true);
            foreach($relation_list as $k=>$relation){
                $relation_list[$k]['shops_name'] = $shops_list[$relation['relation_id']];
            }
        }
        //关联分类
        if($coupon_info['coupon_use_type'] == 3){
            $coupon_id = $coupon_info['coupon_id'];
            $coupon_relation_ids = M('coupon_relation_info_edit')->where(array('coupon_id'=>$coupon_id))->getField('relation_id',true);
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
            $mod =  M('coupon_relation_info_edit as exclude,'.C('DB_PREFIX').'goods_base_info as goods');
            $where['exclude.coupon_id'] = $coupon_info['coupon_id'];
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
            $mod =  M('coupon_relation_exclude_goods_edit as exclude,'.C('DB_PREFIX').'goods_base_info as goods');
            $where['exclude.coupon_id'] = $coupon_info['coupon_id'];
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
            'url'=>U('CouponEdit/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'优惠券名称/优惠券编码',
                    'width'=>250,
                    'tip'=>'请输入优惠券名称/优惠券编码'
                ),
            ),
        );
    }
    
    private function get_where(){
        $where = array();
        //编辑和拒绝状态
        $where['coupon_status'] = array('in',array(10,22));
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['coupon_name|coupon_code'] = array('like','%'.$seatch.'%');
        $coupon_code = I('coupon_code','','trim,htmlspecialchars');
        if($coupon_code) $where['coupon_code'] = $coupon_code;
        return $where;
    }
    
    private function getCouponCode(){
        $string = new \Org\Util\ThinkString();
        $coupon_code = $string->randString(6, 2);
        $exist = D('CouponEdit')->where(array('coupon_code'=>$coupon_code))->find();
        if($exist) $this->getCouponCode();
        else return $coupon_code;
    }
}
