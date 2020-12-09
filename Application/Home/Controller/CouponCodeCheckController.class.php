<?php


namespace Home\Controller;

class CouponCodeCheckController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('CouponCodeCheck')->where($where)->count(),10);
        $list = D('CouponCodeCheck')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    //审核通过
    public function check_success(){
        $coupon_code_ids = I('coupon_code_ids','','trim,htmlspecialchars');
        $coupon_code_ids = explode(',', trim($coupon_code_ids,','));
        $coupon_check = D('CouponCodeCheck');
        $where['coupon_code_id'] = array('in',$coupon_code_ids);
        $check_coupon_list = $coupon_check->where($where)->select();
        foreach($check_coupon_list as $k=>$check_coupon_info){
            if($check_coupon_info['coupon_status'] != 20) {
                $errors[] = '优惠码：'.$check_coupon_info['coupon_code_name'].',不是待审核状态，不能被审核通过';
                break;
            }
            $formal_coupon_id = $check_coupon_info['formal_coupon_code_id'];
            $exist_coupon = D('CouponCode')->where(array('coupon_code_id'=>$formal_coupon_id))->find();
            $coupon_base_info = $check_coupon_info;
            //设置优惠码的值
            unset($coupon_base_info['formal_coupon_code_id']);
            unset($coupon_base_info['edit_coupon_code_id']);
            unset($coupon_base_info['admin_id']);
            $coupon_base_info['coupon_code_id'] = $formal_coupon_id;
            //审核通过
            $coupon_base_info['coupon_status'] = 21;
            $coupon_base_info['check_time'] = date('Y-m-d H:i:s');
            $coupon_base_info['update_time'] = date('Y-m-d H:i:s');
            if($exist_coupon){
                $coupon_bool = D('CouponCode')->where(array('coupon_code_id'=>$formal_coupon_id))->data($coupon_base_info)->save();
            }else{
                $coupon_bool = D('CouponCode')->data($coupon_base_info)->add();
            }
            //优惠码关联
            M('coupon_code_relation_info')->where(array('coupon_code_id'=>$formal_coupon_id))->delete();
            $check_coupon_relation_list = M('coupon_code_relation_info_check')->where(array('coupon_code_id'=>$check_coupon_info['coupon_code_id']))->select();
            $coupon_relation_list = array();
            foreach($check_coupon_relation_list as $j=>$check_coupon_relation_info){
                unset($check_coupon_relation_info['id']);
                $check_coupon_relation_info['coupon_code_id'] = $formal_coupon_id;
                $coupon_relation_list[] = $check_coupon_relation_info;
            }
            $coupon_relation_bool = true;
            if($coupon_relation_list) $coupon_relation_bool = M('coupon_code_relation_info')->addAll($coupon_relation_list);
            //排除商品
            M('coupon_code_relation_exclude_goods')->where(array('coupon_code_id'=>$formal_coupon_id))->delete();
            $check_coupon_exclude_list = M('coupon_code_relation_exclude_goods_check')->where(array('coupon_code_id'=>$check_coupon_info['coupon_code_id']))->select();
            $coupon_exclude_list = array();
            foreach($check_coupon_exclude_list as $j=>$check_coupon_exclude_info){
                unset($check_coupon_exclude_info['id']);
                $check_coupon_exclude_info['coupon_code_id'] = $formal_coupon_id;
                $coupon_exclude_list[] = $check_coupon_exclude_info;
            }
            $coupon_exclude_bool = true;
            if($coupon_exclude_list) $coupon_exclude_bool = M('coupon_code_relation_exclude_goods')->addAll($coupon_exclude_list);
            //审核优惠码更新
            $update_check_coupon_data = array(
                'coupon_status'=>21,
                'admin_id'=>  session('admin_uid'),
                'update_time'=>  date('Y-m-d H:i:s'),
                'check_time'=>  date('Y-m-d H:i:s')
            );
            $check_coupon_bool = $coupon_check->where(array('coupon_code_id'=>$check_coupon_info['coupon_code_id']))->data($update_check_coupon_data)->save();
            if(!($check_coupon_bool && $coupon_bool && $coupon_exclude_bool && $coupon_relation_bool)) $errors[] = '优惠码：'.$check_coupon_info['coupon_name'].',审核失败';
        }
        if(empty($errors)){
            $coupon_check->commit();
            $this->oldAjaxReturn(0, '审核成功', 1);
        }else{
            $coupon_check->rollback();
            $this->oldAjaxReturn(0, implode('; ', $errors), 0);
        }
    }
    
    //获取审核拒绝页面
    public function get_check_fail(){
        $coupon_code_ids = I('coupon_code_ids','','trim,htmlspecialchars');
        $this->assign('coupon_code_ids', $coupon_code_ids);
        $content = $this->fetch('fail_info');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    //审核拒绝
    public function check_fail(){
        $coupon_code_ids = I('coupon_code_ids','','trim,htmlspecialchars');
        $coupon_code_ids = explode(',', trim($coupon_code_ids,','));
        if(!$coupon_code_ids) $this->oldAjaxReturn (0, '参数错误', 0);
        $refuse_reason = I('refuse_reason','','trim,htmlspecialchars');
        if(!$refuse_reason) $this->oldAjaxReturn ('refuse_reason', '请输入拒绝原因', 0);
        $coupon_check = D('CouponCodeCheck');
        $where['coupon_code_id'] = array('in',$coupon_code_ids);
        $check_coupon_list = $coupon_check->where($where)->select();
        foreach($check_coupon_list as $k=>$check_coupon_info){
            if($check_coupon_info['coupon_status'] != 20) {
                $errors[] = '优惠码：'.$check_coupon_info['coupon_code_name'].',不是待审核状态，不能被审核';
                break;
            }
            $edit_coupon_code_ids[] = $check_coupon_info['edit_coupon_code_id'];
        }
        $coupon_check->startTrans();
        $check_coupon_data['admin_id'] = session('admin_uid');
        $check_coupon_data['refuse_reason'] = $refuse_reason;
        $check_coupon_data['coupon_status'] = 22;
        $check_coupon_data['update_time'] = date('Y-m-d H:i:s');
        $check_coupon_data['check_time'] = date('Y-m-d H:i:s');
        $check_coupon_bool = $coupon_check->where($where)->data($check_coupon_data)->save();
        $edit_coupon_data['refuse_reason'] = $refuse_reason;
        $edit_coupon_data['update_time'] = date('Y-m-d H:i:s');
        $edit_coupon_data['check_time'] = date('Y-m-d H:i:s');
        $edit_coupon_data['coupon_status'] = 10;
        $where = array('coupon_code_id'=>array('in',$edit_coupon_code_ids));
        $edit_coupon_bool = D('CouponCodeEdit')->where($where)->data($edit_coupon_data)->save();
        if(empty($errors) && $check_coupon_bool && $edit_coupon_bool){
            $coupon_check->commit();
            $this->oldAjaxReturn(0, '提交成功', 1);
        }else{
            $coupon_check->rollback();
            $this->oldAjaxReturn(0, implode('; ', $errors), 0);
        }
    }
    
     //查看详情
    public function info(){
        $info = D('CouponCodeCheck')->where($this->get_where())->find();
        $this->assign('info', $info);
        $this->display();
    }
    
    //维护关联关系
    public function relation_info(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $coupon_info = D('CouponCodeCheck')->where(array('coupon_code_id'=>$coupon_code_id))->find();
        $this->assign('info', $coupon_info);
        //关联商家
        if($coupon_info['coupon_use_type'] == 2){
            $relation_list = M('coupon_code_relation_info_check')->where(array('coupon_code_id'=>$coupon_info['coupon_code_id']))->select();
            $relation_ids = getFieldsByKey($relation_list, 'relation_id');
            $shops_list = D('Shops')->where(array('shops_id'=>array('in',$relation_ids)))->getField('shops_id,shops_name',true);
            foreach($relation_list as $k=>$relation){
                $relation_list[$k]['shops_name'] = $shops_list[$relation['relation_id']];
            }
        }
        //关联分类
        if($coupon_info['coupon_use_type'] == 3){
            $coupon_code_id = $coupon_info['coupon_code_id'];
            $coupon_relation_ids = M('coupon_code_relation_info_check')->where(array('coupon_code_id'=>$coupon_code_id))->getField('relation_id',true);
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
        //绑定商品
        if($coupon_info['coupon_use_type'] == 4){
            $mod =  M('coupon_code_relation_info_check as exclude,'.C('DB_PREFIX').'goods_base_info as goods');
            $where['exclude.coupon_code_id'] = $coupon_info['coupon_code_id'];
            $field = 'exclude.id,goods.goods_code,goods.goods_name,goods.goods_materiel_code,goods.shops_code';
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
            $mod =  M('coupon_code_relation_exclude_goods_check as exclude,'.C('DB_PREFIX').'goods_base_info as goods');
            $where['exclude.coupon_code_id'] = $coupon_info['coupon_code_id'];
            $field = 'exclude.id,goods.goods_code,goods.goods_name,goods.goods_materiel_code,goods.shops_code';
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
        $this->display('relation_info');
        
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('CouponCodeCheck/index'),
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
        $where['coupon_status'] = array('in',array(20,21,22));
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['coupon_code_name'] = array('like','%'.$seatch.'%');
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        if($coupon_code_id) $where['coupon_code_id'] = $coupon_code_id;
        return $where;
    }
}
