<?php


namespace Home\Controller;

/**
 * 商品管理
 *
 * @author xiao.yingjie
 */
class GoodsBaseController extends BaseController{
    
   private $goods_label_info = array('基本信息','特性信息','销售信息','绑定标签');
   
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('GoodsBase')->where($where)->count(),10);
        $list = D('GoodsBase')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
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
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->search($this->get_search());
        $this->display();
    }
    
    public function info(){
        $info = D('GoodsBase')->where($this->get_where())->find();
        $category_ids = $this->getCategoryIds($info['goods_category_id']);
        $category_names = M('goods_category')->where(array('goods_category_id'=>array('in',$category_ids)))->order('goods_category_level asc')->field('goods_category_level,goods_category_name')->select();
        $category_names = getFieldsByKey($category_names, 'goods_category_name');
        $info['category_name'] = implode('->', $category_names);
        $info['shops_name'] = M('shops_base_info')->where(array('shops_code'=>$info['shops_code']))->getField('shops_name');
        $info['type_name'] = M('goods_type')->where(array('type_id'=>$info['goods_type_id']))->getField('type_name');
        $info['brand_name'] = D('Brand')->where(array('brand_id'=>$info['brand_id']))->getField('brand_name');
        $label_list = M('goods_label_relation_check as relation,'.C('DB_PREFIX').'goods_label as label')->where('relation.label_id = label.label_id')->where(array('relation.goods_id'=>$info['goods_id']))->getField('label_name',true);
        $goods_img_list = explode(',', $info['product_default_pic']);
        if($info['product_pic']) $win_img_list = explode(',', trim($info['product_pic'],','));
        $special_list = M('goods_special')->where(array('goods_id'=>$info['goods_id']))->order('create_time asc')->select();
        foreach($special_list as $k=>$special_info){
            $special_info['special_content_pc'] = htmlspecialchars_decode(urldecode($special_info['special_content_pc']));
            $special_info['special_content_mobile'] = htmlspecialchars_decode(urldecode($special_info['special_content_mobile']));
            $special_list[$k] = $special_info;
        }
        $relation_content = M('relation_goods_info')->where(array('relation_id'=>$info['goods_id']))->getField('goods_sku_content');
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
        $checked_label_list = M('goods_label_relation relation,tm_goods_label as label')->where('relation.label_id = label.label_id')->where(array('relation.goods_id'=>$info['goods_id']))->field('label.label_id,label.label_name,label.label_parent_id')->order('label.create_time')->select();
        $label_parent_ids = getFieldsByKey($checked_label_list, 'label_parent_id');
        $parent_label_list = M('goods_label')->where(array('label_id'=>array('in',$label_parent_ids)))->field('label_id,label_name')->order('create_time asc')->select();
        $temp_label_list = array();
        foreach($parent_label_list as $k=>$parent_label){
            foreach($checked_label_list as $j=>$label_info){
                if($label_info['label_parent_id'] = $parent_label['label_id']){
                    $label_info['parent_name'] == $parent_label['label_name'];
                    $temp_label_list[] = $label_info;
                }
            }
        }
        $this->assign('checked_label_list', $temp_label_list);
        $this->display();
    }
    
    //修改
    public function edit(){
        $goods_code = I('goods_code','','trim,htmlspecialchars');
        $update_bool = D('GoodsEdit')->where(array('goods_code'=>$goods_code))->data(array('goods_status'=>10,'update_time'=>date('Y-m-d H:i:s')))->save();
        if(!$update_bool) $this->oldAjaxReturn (0, '修改请求失败', 0);
        $this->oldAjaxReturn(0, '修改请求成功', 1);
    }
    
    //上架商品
    public function goods_shelves(){
        $goods_codes = I('goods_codes','','trim,htmlspecialchars');
        $goods_codes = explode(',', trim($goods_codes,','));
        $update_bool = D('GoodsBase')->where(array('goods_code'=>array('in',$goods_codes)))->data(array('goods_status'=>30,'update_time'=>date('Y-m-d H:i:s')))->save();
        //同步商品到CRM
        $goods_list = D('GoodsBase')->where(array('goods_code'=>array('in',$goods_codes)))->select();
        $goods_ids= getFieldsByKey($goods_list, 'goods_id');
        $cur_sale_stocks = M('goods_stock_info')->where(array('goods_id'=>array('in',$goods_ids)))->getField('goods_id,cur_sale_stocks',true);
        foreach($goods_list as $k=>$goods_info){
            if(!$cur_sale_stocks[$goods_info['goods_id']]) $warns[] = '商品: '.$goods_info['goods_name'].',没有可用库存，请维护库存';
        }
        if(empty($warns)) $msg = '上架成功';
        else $msg = implode (' ;', $warns);
        if(!$update_bool) $this->oldAjaxReturn (0, '上架失败', 0);
        $this->oldAjaxReturn(0, $msg, 1);
    }
    
    //下架商品
    public function rack_goods(){
        $goods_codes = I('goods_codes','','trim,htmlspecialchars');
        $goods_codes = explode(',', trim($goods_codes,','));
        $update_bool = D('GoodsBase')->where(array('goods_code'=>array('in',$goods_codes)))->data(array('goods_status'=>31,'update_time'=>date('Y-m-d H:i:s')))->save();
         //同步商品到CRM
        $goods_list = D('GoodsBase')->where(array('goods_code'=>array('in',$goods_codes)))->select();
        foreach($goods_list as $k=>$goods_info){
        }
        if(!$update_bool) $this->oldAjaxReturn (0, '下架失败', 0);
        $this->oldAjaxReturn(0, '下架成功', 1);
    }
    
    //预上架
    public function goods_expect_up(){
        $goods_codes = I('goods_codes','','trim,htmlspecialchars');
        $goods_codes = explode(',', trim($goods_codes,','));
        $update_bool = D('GoodsBase')->where(array('goods_code'=>array('in',$goods_codes)))->data(array('goods_status'=>35,'update_time'=>date('Y-m-d H:i:s')))->save();
        //同步商品到CRM
        $goods_list = D('GoodsBase')->where(array('goods_code'=>array('in',$goods_codes)))->select();
        $goods_ids= getFieldsByKey($goods_list, 'goods_id');
        $cur_sale_stocks = M('goods_stock_info')->where(array('goods_id'=>array('in',$goods_ids)))->getField('goods_id,cur_sale_stocks',true);
        foreach($goods_list as $k=>$goods_info){
            if(!$cur_sale_stocks[$goods_info['goods_id']]) $warns[] = '商品: '.$goods_info['goods_name'].',没有可用库存，请维护库存';
        }
        if(empty($warns)) $msg = '预上架成功';
        else $msg = implode (' ;', $warns);
        if(!$update_bool) $this->oldAjaxReturn (0, '预上架失败', 0);
        $this->oldAjaxReturn(0, $msg, 1);
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
            'url'=>U('GoodsBase/index'),
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
                    'select'=>array(21=>'审核通过',30=>'上架',31=>'下架',33=>'待上架',34=>'待下架',35=>'预上架'),
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
        else $where['goods_status'] = array('in',array(21,30,31,33,34,35));
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['goods_name'] = array('like','%'.$seatch.'%');
        $shops_code = I('shops_code','','trim,htmlspecialchars');
        $goods_code = I('goods_code','','trim,htmlspecialchars');
        if($goods_code) $where['goods_code'] = $goods_code;
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
    
    private function getCategoryIds($category_id,$category_ids = array()){
        $category_ids[] = $category_id;
        $curr_category_info = M('goods_category')->where(array('goods_category_id'=>$category_id))->find();
        if($curr_category_info['child_node']) {
            $category_ids = $this->getCategoryIds ($curr_category_info['goods_category_parent_id'], $category_ids);
        }
        $category_ids = M('goods_category')->where(array('goods_category_id'=>array('in',$category_ids)))->order('goods_category_level asc')->field('goods_category_id,goods_category_level')->select();
        $category_ids = getFieldsByKey($category_ids, 'goods_category_id');
        return $category_ids;
    }
    
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
    
    //验证管理员操作商品的其他权限
    function check_access_for_goods() {
        if(check_admin_access('show_goods_list_all',1,'other')){ //验证是否可以查看所有SKU
            return null;
        }elseif(check_admin_access('show_goods_list_shops',1,'other')){ //验证是否可以查看本商家SKU
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
}
