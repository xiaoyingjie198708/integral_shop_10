<?php


namespace Home\Controller;

/**
 * 审核商品管理
 * @author xiao.yingjie
 */
class CheckGoodsBaseController extends BaseController{
    
   private $goods_label_info = array('基本信息','特性信息','销售信息','绑定信息');
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('GoodsChecked')->where($where)->count(),10);
        $list = D('GoodsChecked')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        foreach($list as $k=>$info) {
            $info['shops_name'] = $shops_names[$info['shops_code']];
            //展示价格
            $info['show_price'] = '￥'.  price_format($info['goods_price_pc']);
            if($info['integral'] && $info['goods_price_pc'] && $info['max_integral'])  $info['show_price'] = '￥'.$info['goods_price_pc'] .' + ' .$info['max_integral'].'积分';
            if($info['integral'] && !$info['goods_price_pc'] && $info['max_integral']) $info['show_price'] = $info['max_integral'].'积分';
            if($info['integral'] && $info['goods_price_pc'] && !$info['max_integral']) $info['show_price'] = '￥'.$info['goods_price_pc'];
            $list[$k] = $info;
        }
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display(); 
    }
    
     public function info(){
        $info = D('GoodsChecked')->where($this->get_where())->find();
        $category_ids = $this->getCategoryIds($info['goods_category_id']);
        $category_names = M('goods_category')->where(array('goods_category_id'=>array('in',$category_ids)))->order('goods_category_level asc')->select();
        $category_names = getFieldsByKey($category_names, 'goods_category_name');
        $info['category_name'] = implode('->', $category_names);
        $info['shops_name'] = M('shops_base_info')->where(array('shops_code'=>$info['shops_code']))->getField('shops_name');
        $info['type_name'] = M('goods_type')->where(array('type_id'=>$info['goods_type_id']))->getField('type_name');
        $info['brand_name'] = D('Brand')->where(array('brand_id'=>$info['brand_id']))->getField('brand_name');
        $label_list = M('goods_label_relation_check as relation,'.C('DB_PREFIX').'goods_label as label')->where('relation.label_id = label.label_id')->where(array('relation.goods_id'=>$info['goods_id']))->getField('label_name',true);
        $goods_img_list = explode(',', $info['product_default_pic']);
        if($info['product_pic']) $win_img_list = explode(',', trim($info['product_pic'],','));
        $special_list = M('goods_special_check')->where(array('goods_id'=>$info['goods_id']))->order('create_time asc')->select();
        foreach($special_list as $k=>$special_info){
            $special_info['special_content_pc'] = htmlspecialchars_decode(urldecode($special_info['special_content_pc']));
            $special_info['special_content_mobile'] = htmlspecialchars_decode(urldecode($special_info['special_content_mobile']));
            $special_list[$k] = $special_info;
        }
        $relation_content = M('relation_goods_info_check')->where(array('relation_id'=>$info['goods_id']))->getField('goods_sku_content');
        $relation_list = json_decode($relation_content,true);
        $spec_ids = array_keys($relation_list);
        $spec_names = M('product_specification')->where(array('specification_id'=>array('in',$spec_ids)))->getField('specification_id,specification_name',true);
        $relation_new_arr = array();
        foreach($relation_list as $spec_id=>$spec_value){
            $relation_new_arr[] = array('spec_name'=>$spec_names[$spec_id],'spec_value'=>$spec_value);
        }
        //礼包商品
        if($info['goods_type'] == 2){
            $relation_goods = json_decode($info['relation_goods'],true);
            $goods_codes = getFieldsByKey($relation_goods, 'goods_code');
            $goods_list = D('GoodsBase')->where(array('goods_code'=>array('in',$goods_codes)))->select();
            $shops_codes = getFieldsByKey($goods_list, 'shops_code');
            $shops_names = D('Shops')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
            foreach($goods_list as $k=>$good_info){
                $goods_list[$k]['shops_name'] = $shops_names[$good_info['shops_code']];
                foreach($relation_goods as $j=>$r_goods){
                    if($good_info['goods_code'] == $r_goods['goods_code']) {
                        $goods_list[$k]['goods_price'] = $r_goods['goods_price'];
                        $goods_list[$k]['max_integral'] = $r_goods['max_integral'];
                    }
                }
            }
            $this->assign('goods_list', $goods_list);
        }
        //积分
        if(!$info['integral'] && $info['max_integral']) $info['max_integral'] = ($info['max_integral']/C('INTEGRAL_RATE')['point']) * C('INTEGRAL_RATE')['rmb'];
        //库存
        $stock_info = M('goods_stock_info')->where(array('goods_code'=>$info['goods_code']))->find();
        $this->assign('stock_info', $stock_info);
        //预计上架
        if($info['pre_up_time']) $info['pre_up_time'] = date('Y-m-d H:i:s',  strtotime($info['pre_up_time']));
        else $info['pre_up_time'] = '';
        $this->assign('info', $info);
        $this->assign('label_list', $label_list);
        $this->assign('goods_img_list', $goods_img_list);
        $this->assign('win_img_list', $win_img_list);
        $this->assign('special_list', $special_list);
        $this->assign('spec_list', $relation_new_arr);
        $category_type = M('goods_type')->where(array('type_id'=>$info['goods_type_id']))->getField('physics_type_id');
        $this->getConf($category_type,$info['goods_id']);
        $this->assign('goods_label', $this->goods_label_info);
        //标签信息
        $checked_label_list = M('goods_label_relation_check relation,tm_goods_label as label')->where('relation.label_id = label.label_id')->where(array('relation.goods_id'=>$info['goods_id']))->field('label.label_id,label.label_name,label.label_parent_id')->order('label.create_time')->select();
        $label_parent_ids = getFieldsByKey($checked_label_list, 'label_parent_id');
        $parent_label_list = M('goods_label')->where(array('label_id'=>array('in',$label_parent_ids)))->field('label_id,label_name')->order('create_time asc')->select();
        $temp_label_list = array();
        foreach($parent_label_list as $k=>$parent_label){
            foreach($checked_label_list as $j=>$label_info){
                if($label_info['label_parent_id'] == $parent_label['label_id']){
                    $label_info['parent_name'] = $parent_label['label_name'];
                    $temp_label_list[] = $label_info;
                }
            }
        }
        $this->assign('checked_label_list', $temp_label_list);
        $this->display();
    }
    
    //审核通过
    public function check_success(){
        $goods_ids = I('goods_ids','','trim,htmlspecialchars');
        $goods_ids = explode(',', trim($goods_ids,','));
        //商品信息
        $where['goods_id'] = array('in',$goods_ids);
        $check_goods = D('GoodsChecked');
        $goods_list = $check_goods->where($where)->select();
        if(!$goods_list) $this->oldAjaxReturn (0, '请选择要提交审核的商品', 0);
        //开启事务
        $check_goods->startTrans();
        foreach($goods_list as $k=>$goods_info){
            if($goods_info['goods_status'] != 20) {
                $errors[] = '商品名称：'.$goods_info['goods_name'].',不是待审核状态，不能被审核通过';
                break;
            }
            $exits_goods = D('GoodsBase')->where(array('goods_id'=>$goods_info['formal_goods_id']))->find();
            $goods_id = $goods_info['formal_goods_id'];
            $goods_base_info = $goods_info;
            $goods_base_info['goods_id'] = $goods_id;
            unset($goods_base_info['formal_goods_id']);
            unset($goods_base_info['edit_goods_id']);
            unset($goods_base_info['admin_id']);
            $goods_base_info['update_time'] = date('Y-m-d H:i:s');
            $goods_base_info['check_time'] = date('Y-m-d H:i:s');
            //审核表状态的修改
            $check_goods_data['goods_status'] = 21;
            $check_goods_data['admin_id'] = session('admin_uid');
            $check_goods_data['update_time'] = date('Y-m-d H:i:s');
            $check_goods_data['check_time'] = date('Y-m-d H:i:s');
            $check_goods_base_bool = $check_goods->where(array('goods_id'=>$goods_info['goods_id']))->data($check_goods_data)->save();
            if($exits_goods){
                 //待上架
                $goods_base_info['goods_status'] = $exits_goods['goods_status'];
                $goods_base_bool = D('GoodsBase')->where(array('goods_id'=>$goods_info['formal_goods_id']))->data($goods_base_info)->save();
            }else{
                 //待上架
                $goods_base_info['goods_status'] = 33;
                $goods_base_bool = D('GoodsBase')->data($goods_base_info)->add();
            }
            $label_relation_arr = array();
            //标签信息
            M('goods_label_relation')->where(array('goods_id'=>$goods_id))->delete();
            $label_relation_list = M('goods_label_relation_check')->where(array('goods_id'=>$goods_info['goods_id']))->select();
            foreach($label_relation_list as $j=>$label_relation){
                unset($label_relation['id']);
               $label_relation['goods_id'] = $goods_id;
               unset($label_relation['admin_id']);
               $label_relation_arr[] = $label_relation;
            }
            $label_relation_bool = true;
            if($label_relation_arr) $label_relation_bool = M('goods_label_relation')->addAll($label_relation_arr);
            //特性
            M('goods_special')->where(array('goods_id'=>$goods_id))->delete();
            $goods_special_arr = array();
            $special_list = M('goods_special_check')->where(array('goods_id'=>$goods_info['goods_id']))->select();
            foreach($special_list as $j=>$special){
                unset($special['id']);
                $special['goods_id'] = $goods_id;
                $goods_special_arr[] = $special;
            }
            $goods_special_bool = true;
            if($goods_special_arr) $goods_special_bool = M('goods_special')->addAll($goods_special_arr);
            //规格
            $relation_goods_bool = true;
            M('relation_goods_info')->where(array('relation_id'=>$goods_id))->delete();
            $relation_goods_info = M('relation_goods_info_check')->where(array('relation_id'=>$goods_info['goods_id']))->find();
            if($relation_goods_info){
                $relation_goods_info['relation_id'] = $goods_id;
                $relation_goods_bool = M('relation_goods_info')->data($relation_goods_info)->add();
            }
            //配置
            M('product_conf_param_info')->where(array('goods_id'=>$goods_id))->delete();
            $conf_param_arr = array();
            $conf_param_list = M('product_conf_param_info_edit')->where(array('goods_id'=>$goods_info['goods_id']))->select();
            foreach($conf_param_list as $j=>$param_info){
                unset($param_info['id']);
                $param_info['goods_id'] = $goods_id;
                $conf_param_arr[] = $param_info;
            }
            $conf_param_bool = true;
            if($conf_param_arr) $conf_param_bool = M('product_conf_param_info')->addAll($conf_param_arr);
            if(!($check_goods_base_bool && $goods_base_bool && $label_relation_bool && $goods_special_bool && $relation_goods_bool && $relation_goods_bool && $conf_param_bool)){
                $errors[] = '【'.$goods_info['goods_name'].'】，审核失败';
            }
            //库存
            $stock_bool = $this->update_goods_stock($goods_base_info);
            if(!$stock_bool) $errors[] = '库存创建失败';
        }
        if(empty($errors)){
            $check_goods->commit();
            $this->oldAjaxReturn(0, '提交审核成功', 1);
        }else{
            $check_goods->rollback();
            $this->oldAjaxReturn(0, implode('; ', $errors), 0);
        }
    }
    
    public function get_check_fail(){
        $goods_ids = I('goods_ids','','trim,htmlspecialchars');
        $this->assign('goods_ids', $goods_ids);
        $content = $this->fetch('fail_info');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    //审核失败
    public function check_fail(){
        $goods_ids = I('goods_ids','','trim,htmlspecialchars');
        $goods_ids = explode(',', trim($goods_ids,','));
        //商品信息
        $where['goods_id'] = array('in',$goods_ids);
        $check_goods = D('GoodsChecked');
        $goods_list = $check_goods->where($where)->select();
        if(!$goods_list) $this->oldAjaxReturn (0, '请选择要提交审核的商品', 0);
        $refuse_reason = I('refuse_reason','','trim,htmlspecialchars');
        if(!$refuse_reason) $this->oldAjaxReturn ('refuse_reason', '请输入拒绝原因', 0);
        //开启事务
        $check_goods->startTrans();
         foreach($goods_list as $k=>$goods_info){
            if($goods_info['goods_status'] != 20) {
                $errors[] = '商品名称：'.$goods_info['goods_name'].',不是待审核状态，不能被审核拒绝';
                break;
            }
         }
         $check_goods_data['admin_id'] = session('admin_uid');
         $check_goods_data['refuse_reason'] = $refuse_reason;
         $check_goods_data['goods_status'] = 22;
         $check_goods_data['update_time'] = date('Y-m-d H:i:s');
         $check_goods_data['check_time'] = date('Y-m-d H:i:s');
         $check_goods_bool = $check_goods->where($where)->data($check_goods_data)->save();
         $edit_goods_data['refuse_reason'] = $refuse_reason;
         $edit_goods_data['update_time'] = date('Y-m-d H:i:s');
         $edit_goods_data['check_time'] = date('Y-m-d H:i:s');
         $edit_goods_data['goods_status'] = 10;
         $edit_goods_ids = getFieldsByKey($goods_list, 'edit_goods_id');
         $edit_goods_bool = D('GoodsEdit')->where(array('goods_id'=>array('in',$edit_goods_ids)))->data($edit_goods_data)->save();
         if(empty($errors) && $check_goods_bool && $edit_goods_bool){
            $check_goods->commit();
            $this->oldAjaxReturn(0, '成功', 1);
        }else{
            $check_goods->rollback();
            $this->oldAjaxReturn(0,  implode('; ', $errors), 0);
        }
    }
    
    
    //获取分类信息
    public function get_goods_category_search($id='0') {
        $info = M('goods_category')->where(array('goods_category_parent_id'=>$id,'goods_category_status'=>array('neq',0)))->getField('goods_category_id,goods_category_name',true);
        $this->ajaxReturn($info);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        $category_id = I('category_id');
        $category = array();
        $where = array('goods_category_parent_id'=>'0','goods_category_status'=>array('neq',0));
        $category[] = M('goods_category')->where($where)->getField('goods_category_id,goods_category_name',true);
        if($category_id) {
            $category_id = explode('_',$category_id);
            for($i=0;$i<count($category_id);$i++) {
                $where['goods_category_parent_id'] = $category_id[$i];
                $temp = M('goods_category')->where($where)->getField('goods_category_id,goods_category_name',true);
                if($temp) $category[] = $temp;
            }
        }
        $shops_list = M('shops_base_info')->where(array('shops_status'=>1))->getField('shops_code,shops_name',true);
        return array(
            'url'=>U('CheckGoodsBase/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'商品名称',
                    'width'=>250,
                    'tip'=>'请输入商品名称'
                ),
                array(
                    'name'=>'shops_code',
                    'show_name'=>'商家',
                    'tip'=>'请选择商家',
                    'select'=>$shops_list,
                    'type'=>'select'
                ),
                array(
                    'name'=>'search_status',
                    'show_name'=>'商品状态',
                    'tip'=>'请选择商品状态',
                    'select'=>array(20=>'待审核',21=>'审核通过',22=>'审核失败'),
                    'type'=>'select'
                ),
            ),
            'other'=>array(
                array(
                    'name'=>'use_storage_money',
                    'show_name'=>'可使用储值卡',
                    'tip'=>'请选择类型',
                    'select'=>array(1=>'否',2=>'是'),
                    'type'=>'select'
                ),
                array(
                    'name'=>'category_id',
                    'show_name'=>'商品分类',
                    'tip'=>'请选择商品分类',
                    'select'=>$category,
                    'url'=>U('EditGoodsBase/get_goods_category_search'), //返回的一维数组  id=>name  参数接受的是id
                    'type'=>'select-ajax'
                ),
            )
        );
    }
    
    private function get_where(){
        $where = array();
        //编辑和拒绝状态
        $search_status = I('search_status',0,'intval');
        if($search_status) $where['goods_status'] = $search_status;
        else $where['goods_status'] = array('in',array(20,21,22));
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['goods_name'] = array('like','%'.$seatch.'%');
        $shops_code = I('shops_code','','trim,htmlspecialchars');
        $goods_id = I('goods_id','','trim,htmlspecialchars');
        if($goods_id) $where['goods_id'] = $goods_id;
        $category_id = I('category_id','','trim,htmlspecialchars');
        if($category_id){
            $category_ids = explode('_', trim($category_id,'_'));
            $category_count = count($category_ids);
            $last_category_id = $category_ids[$category_count - 1];
            $child_category_ids = M('goods_category')->where(array('goods_category_parent_id'=>$last_category_id))->getField('goods_category_id',true);
            if($child_category_ids) {
                $child_category_ids[] = $last_category_id;
                $where['goods_category_id'] = array('in',$child_category_ids);
            }else{
                $where['goods_category_id'] = $last_category_id;
            }
        }
         //商品权限
        $access_shops_codes = $this->check_access_for_goods();
        if(is_array($access_shops_codes)){
            if($shops_code) {
                if(in_array($shops_code, $access_shops_codes)) $where['shops_code'] = $shops_code;
                else $where['shops_code'] = '';
            }else{
                $where['shops_code'] = array('in',$access_shops_codes);
            }
        }else{
            if($shops_code) $where['shops_code'] = $shops_code;
        }
        $use_storage_money = I('use_storage_money',0,'intval');
        if($use_storage_money) $where['use_storage_money'] = $use_storage_money - 1;
        return $where;
    }
    
    //获取分类的所有父分类
    private function getCategoryIds($category_id,$category_ids = array()){
        $category_ids[] = $category_id;
        $curr_category_info = M('goods_category')->where(array('goods_category_id'=>$category_id))->find();
        if($curr_category_info['child_node']) {
            $category_ids = $this->getCategoryIds($curr_category_info['goods_category_parent_id'], $category_ids);
        }
        $category_ids = M('goods_category')->where(array('goods_category_id'=>array('in',$category_ids)))->order('goods_category_level asc')->select();
        $category_ids = getFieldsByKey($category_ids, 'goods_category_id');
        return $category_ids;
    }
    
    //获取配置
    private function getConf($category_type,$goods_id = ''){
        if($goods_id) $checked_param_value_ids = M('product_conf_param_info_edit')->where(array('goods_id'=>$goods_id))->getField('param_value_id',true);
        $where['category_type'] = $category_type;
        $where['status'] = array('neq',0);
        $list = D('ParamCategory')->where($where)->order('category_sort asc,create_time desc')->select();
        foreach($list as $k=>$category){
            $category['parint_id'] = 0;
            $category['show_id'] = $category['param_category_id'];
            $category['level'] = 1;
            $category['show_name'] = $category['category_name'];
            $category['show_sort'] = $category['category_sort'];
            $category['path'] = '0';
            $param_name_list = D('ParamName')->where(array('param_category_id'=>$category['param_category_id']))->order('param_sort asc,create_time desc')->select();
            foreach($param_name_list as $kk=>$param_name){
                $param_value_list = D('ParamValue')->where(array('param_id'=>$param_name['param_id']))->order('create_time asc')->select();
                foreach($param_value_list as $j=>$value_info) if(in_array($value_info['param_value_id'], $checked_param_value_ids)) $param_value_list[$j]['checked'] = 'checked';
                $param_name_list[$kk]['child'] = $param_value_list;
            }
            $list[$k]['child'] = $param_name_list;
        }
        $this->assign('goods_conf_list', $list);
        return $this->fetch('conf');
    }
    
    //创建库存
    private function update_goods_stock($goods_info){
        $mod = D('GoodsStock');
        $stock_info = $mod->where(array('goods_code'=>$goods_info['goods_code']))->find();
        if($stock_info){
            $save_bool = $mod->where(array('goods_code'=>$goods_info['goods_code']))->data(array('goods_name'=>$goods_info['goods_name'],'update_time'=>  date('Y-m-d H:i:s')))->save();
        }else{
            $stock_data = array(
                'stock_id'=>(new \Org\Util\ThinkString())->uuid(),
                'goods_id'=>$goods_info['goods_id'],
                'goods_name'=>$goods_info['goods_name'],
                'goods_code'=>$goods_info['goods_code'],
                'shops_code'=>$goods_info['shops_code'],
                'stock_status'=>1,//有效
                'create_time'=>  date('Y-m-d H:i:s'),
                'update_time'=>  date('Y-m-d H:i:s')
            );
            $save_bool = $mod->data($stock_data)->add();
        }
        return $save_bool;
    }
    //验证管理员操作商品的其他权限
    function check_access_for_goods() {
        if(check_admin_access('show_goods_list_all_check',1,'other')){ //验证是否可以查看所有SKU
            return null;
        }elseif(check_admin_access('show_goods_list_shops_check',1,'other')){ //验证是否可以查看本商家SKU
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
}
