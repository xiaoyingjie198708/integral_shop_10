<?php

namespace Home\Controller;

class CouponCodeController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('CouponCode')->where($where)->count(),10);
        $list = D('CouponCode')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    //修改
    public function edit(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $update_bool = D('CouponCodeEdit')->where(array('coupon_code_id'=>$coupon_code_id))->data(array('coupon_status'=>10,'update_time'=>date('Y-m-d H:i:s')))->save();
        if(!$update_bool) $this->oldAjaxReturn (0, '修改请求失败', 0);
        $this->oldAjaxReturn(0, '修改请求成功', 1);
    }
    
    //启用
    public function coupon_enable(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $coupon_code_id = explode(',', trim($coupon_code_id, ','));
        $where['coupon_code_id'] = array('in',$coupon_code_id);
        $update_bool = D('CouponCode')->where($where)->data(array('coupon_status'=>30,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($update_bool) $this->oldAjaxReturn (0, '启用成功', 1);
        else $this->oldAjaxReturn (0, '启用失败', 0);
    }
    
    //停用
    public function coupon_stop(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $coupon_code_id = explode(',', trim($coupon_code_id, ','));
        $where['coupon_code_id'] = array('in',$coupon_code_id);
        $update_bool = D('CouponCode')->where($where)->data(array('coupon_status'=>31,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($update_bool) $this->oldAjaxReturn (0, '停用成功', 1);
        else $this->oldAjaxReturn (0, '停用失败', 0);
    }
    
    //查看详情
    public function info(){
        $info = D('CouponCode')->where($this->get_where())->find();
        $this->assign('info', $info);
        $this->display();
    }
    
    //维护关联关系
    public function relation_info(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $coupon_info = D('CouponCode')->where(array('coupon_code_id'=>$coupon_code_id))->find();
        $this->assign('info', $coupon_info);
        //关联商家
        if($coupon_info['coupon_use_type'] == 2){
            $relation_list = M('coupon_code_relation_info')->where(array('coupon_code_id'=>$coupon_info['coupon_code_id']))->select();
            $relation_ids = getFieldsByKey($relation_list, 'relation_id');
            $shops_list = D('Shops')->where(array('shops_id'=>array('in',$relation_ids)))->getField('shops_id,shops_name',true);
            foreach($relation_list as $k=>$relation){
                $relation_list[$k]['shops_name'] = $shops_list[$relation['relation_id']];
            }
        }
        //关联分类
        if($coupon_info['coupon_use_type'] == 3){
            $coupon_code_id = $coupon_info['coupon_code_id'];
            $coupon_relation_ids = M('coupon_code_relation_info')->where(array('coupon_code_id'=>$coupon_code_id))->getField('relation_id',true);
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
            $mod =  M('coupon_code_relation_info as exclude,'.C('DB_PREFIX').'goods_base_info as goods');
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
            $mod =  M('coupon_code_relation_exclude_goods as exclude,'.C('DB_PREFIX').'goods_base_info as goods');
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
    
    //生成优惠码页面
    public function get_code_info(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $max_count = D('CouponCode')->where(array('coupon_code_id'=>$coupon_code_id))->getField('max_count');
        $send_count = M('coupon_code_info')->where(array('coupon_code_id'=>$coupon_code_id))->count();
        $this->assign('max_count',$max_count);
        $this->assign('send_count',$send_count);
        $this->assign('coupon_code_id', $coupon_code_id);
        $content = $this->fetch('add_code_info');
        $this->oldAjaxReturn(0, $content, 1);
    }
    //生成优惠码
    public function add_code(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $send_nember = I('send_number',0,'intval');
        if(!$send_nember) $this->oldAjaxReturn ('send_number', '请输入本次生成的数量', 0);
        $coupon_info = D('CouponCode')->where(array('coupon_code_id'=>$coupon_code_id))->find();
        $send_count = M('coupon_code_info')->where(array('coupon_code_id'=>$coupon_code_id))->count();
        if($coupon_info['max_count'] - $send_count < $send_nember) $this->oldAjaxReturn ('send_number', '本次生成码的数量已经大于可用的数量', 0);
        $coupon_code_data = array();
        for($i = 0; $i<$send_nember; $i++){
            $coupon_code_data[] = array(
                'coupon_code_id'=>$coupon_code_id,
                'coupon_code'=>  createCouponCode(),
                'valid_start_time'=>$coupon_info['valid_start_time'],
                'valid_end_time'=>$coupon_info['valid_end_time'],
                'coupon_status'=>10,//未使用
                'create_time'=>  date('Y-m-d H:i:s'),
                'update_time'=>  date('Y-m-d H:i:s'),
                'max_use_count'=>$coupon_info['max_use_count'],
				'use_count'=>0
            );
        }
        $add_bool = M('coupon_code_info')->addAll($coupon_code_data);
        if($add_bool) $this->oldAjaxReturn (0, '生成成功', 1);
        else $this->oldAjaxReturn (0, '生成失败', 0);
    }
    
    //优惠码信息
    public function code_info(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $coupon_info = D('CouponCode')->where(array('coupon_code_id'=>$coupon_code_id))->find();
        $where = array('coupon_code_id'=>$coupon_code_id);
        $page = new \Org\My\Page(M('coupon_code_info')->where($where)->count(),10);
        $list = M('coupon_code_info')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $this->assign('info', $coupon_info);
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display('coupon_code_info');
    }
    
    public function export_code(){
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        $where = array('coupon_code_id'=>$coupon_code_id);
        $info = M('coupon_code_base_info')->where($where)->find();
        $export_model = new \Org\My\Export('coupon_code_info');
        $export_model->limit = 50000;
        $export_model->setCount(M('coupon_code_info')->where($where)->count());
        $list = $export_model->where($where)->order('create_time asc')->getExportData();
        foreach($list as $k=>$code_info){
            $code_info['coupon_status'] = id2name('code_status', $code_info['coupon_status']);
            $list[$k] = $code_info;
        }
        //导出的模版
        $export_fields = array(
            'coupon_code'=>'优惠码编码',
            'coupon_status'=>'使用状态',
            'create_time'=>'创建时间'
        );
        //导出的数据
        $export_model->title = '优惠码数据';
        $export_model->execl_fields = $export_fields;
        $export_model->setHeaderName($info['coupon_name']);
        $export_model->setData($list);
        $export_page = $export_model->export();
        exit($export_page);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('CouponCode/index'),
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
        $where['coupon_status'] = array('in',array(21,30,31));
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['coupon_code_name'] = array('like','%'.$seatch.'%');
        $coupon_code_id = I('coupon_code_id','','trim,htmlspecialchars');
        if($coupon_code_id) $where['coupon_code_id'] = $coupon_code_id;
        return $where;
    }
}
