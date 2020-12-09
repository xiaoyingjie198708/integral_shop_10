<?php

namespace Home\Controller;

/**
 * 商品编辑管理
 *
 * @author xiao.yingjie
 */
class EditGoodsBaseController extends BaseController{
    
    private $goods_label = array('商品分类','基本信息','特性信息','销售信息','绑定标签');
    
    private $goods_label_info = array('基本信息','特性信息','销售信息','绑定标签');
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('GoodsEdit')->where($where)->count(),10);
        $list = D('GoodsEdit')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_list = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,source,goods_status',true);
        foreach($list as $k=>$info) {
            $info['shops_name'] = $shops_names[$info['shops_code']];
            //展示价格
            $info['show_price'] = '￥'.  price_format($info['goods_price_pc']);
            if($info['integral'] && $info['goods_price_pc'] && $info['max_integral'])  $info['show_price'] = '￥'.$info['goods_price_pc'] .' + ' .$info['max_integral'].'积分';
            if($info['integral'] && !$info['goods_price_pc'] && $info['max_integral']) $info['show_price'] = $info['max_integral'].'积分';
            if($info['integral'] && $info['goods_price_pc'] && !$info['max_integral']) $info['show_price'] = '￥'.$info['goods_price_pc'];
            $goods_info = $goods_list[$info['goods_code']];
            if($goods_info && $goods_info['source'] > 1){
                $info['tip'] = true;
                $info['tip_msg'] = '正式商品，在线状态为：'.id2name('goods_status', $goods_info['goods_status']).',请谨慎操作！';
            }
            $list[$k] = $info;
        }
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    public function add(){
        if(IS_AJAX){
            $edit_goods = D('GoodsEdit');
            if(!$edit_goods->create()) {
                $error = each($edit_goods->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $edit_goods->startTrans();
                $goods_id = (new \Org\Util\ThinkString())->uuid();
                $edit_goods->goods_id = $goods_id;
                $edit_goods->formal_goods_id = $goods_id;
                $goods_code = $this->get_goods_code();
                $edit_goods->goods_code = $goods_code;
                //物料编码
                $edit_goods->goods_materiel_code = 'CH'.$goods_code;
                $edit_goods->goods_category_code = M('goods_category')->where(array('goods_category_id'=>$edit_goods->goods_category_id))->getField('goods_category_code');
                $edit_goods->goods_type_code = '';
                $edit_goods->goods_type_id = '';
                $edit_goods->year_count = I('year_count',-1,'intval');
                $edit_goods->total_count = I('total_count',-1,'intval');
                if($edit_goods->pre_up_time && $edit_goods->pre_up_time < date('Y-m-d H:i:s')) $this->oldAjaxReturn ('pre_up_time', '预约上架时间必须大于当前时间', 0);
                //不是固定积分，积分转换
                if(!$edit_goods->integral && ($edit_goods->max_integral > $edit_goods->goods_price_pc)) $this->oldAjaxReturn ('max_integral', '最大抵扣积分金额不能大于总销售价格', 0);
                if(!$edit_goods->integral && $edit_goods->max_integral) {
                    $is_zs = $edit_goods->max_integral % C('INTEGRAL_RATE')['rmb'];
                    if($is_zs) $this->oldAjaxReturn ('max_integral', '积分抵扣金额请输入'.C('INTEGRAL_RATE')['rmb'].'的倍数', 0);
                    $edit_goods->max_integral = ($edit_goods->max_integral / C('INTEGRAL_RATE')['rmb']) * C('INTEGRAL_RATE')['point'];
                }
                if($edit_goods->year_count > -1){
                    //if($edit_goods->limit_start_time < date('Y-m-d H:i:s')) $this->oldAjaxReturn ('limit_start_time', '限制开始时间必须大于当前时间', 0);
                    if(!$edit_goods->limit_end_time && !$edit_goods->limit_start_time) $this->oldAjaxReturn ('limit_start_time', '请选择限制开始时间或者限制结束时间', 0);
                    if($edit_goods->limit_end_time < $edit_goods->limit_start_time) $this->oldAjaxReturn ('limit_start_time', '限制开始时间必须小于结束时间', 0);
                }
//                if(($edit_goods->limit_end_time && $edit_goods->limit_end_time != '0000-00-00 00:00:00') || ($edit_goods->limit_start_time && $edit_goods->limit_start_time != '0000-00-00 00:00:00')) $this->oldAjaxReturn ('year_count', '请输入限制兑换次数', 0);
                if(($edit_goods->limit_end_time && $edit_goods->limit_end_time != '0000-00-00 00:00:00') || ($edit_goods->limit_start_time && $edit_goods->limit_start_time != '0000-00-00 00:00:00')) {
                    if($edit_goods->year_count == -1){
                        $this->oldAjaxReturn ('year_count', '请输入限制兑换次数', 0);
                    }
               }
               //初始化库存
                $goods_stock = I('goods_stock',0,'intval');
                if($goods_stock) $this->init_goods_stock ($edit_goods, $goods_stock);
                //判断是否重名
                $where['goods_name'] = $edit_goods->goods_name;
                $where['goods_category_id'] = $edit_goods->goods_category_id;
                $where['brand_id'] = $edit_goods->brand_id;
                $same_name_count = $edit_goods->where($where)->count();
                if($same_name_count) $this->oldAjaxReturn ('goods_name', '同一个分类和品牌下已经存在相同的商品名称，请重新输入', 0);
                $product_pic_arr = I('product_pic_arr','','trim,htmlspecialchars');
                $edit_goods->product_pic = implode(',', $product_pic_arr);
                $product_default_pic_arr = I('product_default_pic_arr','','trim,htmlspecialchars');
                $edit_goods->product_default_pic = implode(',', $product_default_pic_arr);
                $edit_goods->create_time = date('Y-m-d H:i:s');
                $edit_goods->update_time = date("Y-m-d H:i:s");
                if(!$edit_goods->expect_up_time) unset($edit_goods->expect_up_time);
                if(!$edit_goods->pre_up_time) unset($edit_goods->pre_up_time);
                if(!$edit_goods->expect_down_time) $edit_goods->expect_down_time = '0000-00-00 00:00:00';
                if(!$edit_goods->timeout_time) $edit_goods->timeout_time = '0000-00-00 00:00:00';
                if(!$edit_goods->limit_start_time) $edit_goods->limit_start_time = '0000-00-00 00:00:00';
                if(!$edit_goods->limit_end_time) $edit_goods->limit_end_time = '0000-00-00 00:00:00';
                //大礼包商品
                if($edit_goods->goods_type == 2){
                    $relation_good_ids = I('relation_good_ids','','trim,htmlspecialchars');
                    if(!$relation_good_ids) $this->oldAjaxReturn ('goods_type', '请添加大礼包的商品', 0);
                    $relation_goods_list = D('GoodsBase')->where(array('goods_id'=>array('in',$relation_good_ids)))->select();
                    $relation_total_price = 0.00;$relation_total_point = 0;
                    foreach($relation_goods_list as $k=>$relation_goods){
                        $relation_goods2 = array('goods_code'=>$relation_goods['goods_code'],'goods_name'=>$relation_goods['goods_name'],'number'=>1);
                        $relation_goods_price = I('goods_price_'.$relation_goods['goods_id'],0.00,'float');
                        $relation_goods2['goods_price'] = $relation_goods_price;
                        $relation_goods2['max_integral'] = I('goods_point_'.$relation_goods['goods_id'],0,'intval');
                        $relation_total_price += $relation_goods2['goods_price'];
                        $relation_total_point += $relation_goods2['max_integral'];
                        $relation_goods_arr[] = $relation_goods2;
                    }
                    if($edit_goods->goods_price_pc != $relation_total_price) $this->oldAjaxReturn ('goods_price_pc', '大礼包的价格必须等于所有商品的和', 0);
                    if($edit_goods->max_integral != $relation_total_point) $this->oldAjaxReturn ('max_integral', '大礼包的积分必须等于所有商品的和', 0);
                    $edit_goods->relation_goods = json_encode($relation_goods_arr);
                }
                //添加特性
                $special_bool = $this->add_goods_special($goods_id);
                $goods_bool = $edit_goods->add();
                //关联标签
                $goods_label_ids = I('goods_label_ids','','trim,htmlspecialchars');
                foreach($goods_label_ids as $k=>$label_id){
                    $label_relation_data[] = array(
                        'label_id'=>$label_id,
                        'goods_id'=>$goods_id,
                        'create_time'=>  date('Y-m-d H:i:s'),
                        'update_time'=>  date('Y-m-d H:i:s'),
                    );
                }
                $label_bool = true;
                if($label_relation_data) $label_bool = M('goods_label_relation_edit')->addAll($label_relation_data);
                if($goods_bool && $special_bool && $label_bool){
                    $edit_goods->commit();
                    $this->oldAjaxReturn($goods_code, '添加成功', 1);
                }else{
                    $edit_goods->rollback();
                    $this->oldAjaxReturn(0, '添加失败', 0);
                }
            }
        }
        $this->assign('goods_label', $this->goods_label);
        $category_list = M('goods_category')->where(array('goods_category_status'=>1,'goods_category_level'=>1))->order('create_time asc')->select();
        $this->assign('category_list', $category_list);
        $shops_list = M('shops_base_info')->where(array('shops_status'=>array('neq',0)))->order('create_time asc')->select();
        $this->assign('shops_list', $shops_list);
        $brand_list = M('goods_brand')->where(array('brand_status'=>1))->select();
        $this->assign('brand_list', $brand_list);
        //标签信息
        $this->relation_label();
        $this->display();
    }
    
    public function edit(){
        $info = D('GoodsEdit')->where($this->get_where())->find();
        //商品分类
        $category_list = array();
        if($info['goods_category_id']){
            $category_ids = $this->getCategoryIds($info['goods_category_id']);
            $serial_path = '0_'.implode('_', $category_ids);
            $info['serial_path'] = $serial_path;
            //上架的商品分类
            $ids = $category_ids;
            $p_ids = M('goods_category')->where(array('goods_category_id'=>array('in',$ids)))->order('goods_category_level asc')->select();
            $p_ids = getFieldsByKey($p_ids, 'goods_category_parent_id');
            $where = array('goods_category_parent_id'=>array('in',$p_ids),'goods_category_status'=>array('neq',0));
            $child_category = M('goods_category')->where($where)->order('create_time asc')->select();
            for($i=0;$i<count($child_category);$i++) {
                if(in_array($child_category[$i]['goods_category_id'],$ids)) $child_category[$i]['checked'] = 'checked';
                $child_category[$i]['goods_category_level'] = $child_category[$i]['goods_category_level'] > 0 ? $child_category[$i]['goods_category_level'] : 1;
                $category_list[$child_category[$i]['goods_category_level']-1][] = $child_category[$i];
            }
        }else{
            $child_category = M('goods_category')->where(array('goods_category_level'=>1,'goods_category_status'=>array('neq',0)))->select();
            for($i=0;$i<count($child_category);$i++) {
                $child_category[$i]['goods_category_level'] = $child_category[$i]['goods_category_level'] > 0 ? $child_category[$i]['goods_category_level'] : 1;
                $category_list[$child_category[$i]['goods_category_level']-1][] = $child_category[$i];
            }
        }
        $this->assign('category_list',$category_list);
        $content = $this->fetch('category_list');
        $this->assign('content', $content);
        $shops_list = M('shops_base_info')->where(array('shops_status'=>array('neq',0)))->order('create_time asc')->select();
        $this->assign('shops_list', $shops_list);
        //上架的类型
        $type_list = M('goods_type')->where(array('type_status'=>1))->order('create_time asc')->select();
        $this->assign('type_list', $type_list);
        //品牌
        $brand_list = M('goods_brand')->where(array('brand_status'=>1))->select();
        $this->assign('brand_list', $brand_list);
        //图片信息
        if($info['product_default_pic']) $goods_img_list = explode(',', $info['product_default_pic']);
        if($info['product_pic']) $win_img_list = explode(',', trim($info['product_pic'], ','));
        $this->assign('goods_img_list', $goods_img_list);
        $this->assign('win_img_list', $win_img_list);
        //特性信息
        $special_list = M('goods_special_edit')->where(array('goods_id'=>$info['goods_id']))->select();
        foreach($special_list as $k=>$special_info){
            $special_info['special_content_pc'] = htmlspecialchars_decode(urldecode($special_info['special_content_pc']));
            $special_info['special_content_mobile'] = htmlspecialchars_decode(urldecode($special_info['special_content_mobile']));
            $special_list[$k] = $special_info;
        }
        $this->assign('special_list', $special_list);
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
        $this->assign('goods_label', $this->goods_label);
        //积分
        if(!$info['integral'] && $info['max_integral']) $info['max_integral'] = ($info['max_integral']/C('INTEGRAL_RATE')['point']) * C('INTEGRAL_RATE')['rmb'];
        //库存
        $stock_info = M('goods_stock_info')->where(array('goods_code'=>$info['goods_code']))->find();
        $this->assign('stock_info', $stock_info);
        $this->relation_label($info['goods_id']);
        //TMS
        if($info['source'] == 2){
            $category_ids = $this->getCategoryIds($info['goods_category_id']);
            $category_names = M('goods_category')->where(array('goods_category_id'=>array('in',$category_ids)))->order('goods_category_level asc')->field('goods_category_level,goods_category_name')->select();
            $category_names = getFieldsByKey($category_names, 'goods_category_name');
            $info['category_name'] = implode('->', $category_names);
            $this->assign('info', $info);
             $this->display('third_edit');
        }else{
            $this->assign('info', $info);
             $this->display();
        }
    }
    
    public function update(){
        $goods_id = I('goods_id','','trim,htmlspecialchars');
        $goods_code = I('goods_code','','trim,htmlspecialchars');
        $edit_goods = D('GoodsEdit');
        if(!$edit_goods->create()) {
            $error = each($edit_goods->getError());
            $this->oldAjaxReturn($error['key'],$error['value'],0);
        }else{
            $goods_source = I('goods_source',0,'intval');
            //本系统创建的商品
            $special_bool = true;
            if($goods_source == 1){
                //判断是否重名
                $where['goods_name'] = $edit_goods->goods_name;
                $where['goods_category_id'] = $edit_goods->goods_category_id;
                $where['goods_type_id'] = $edit_goods->goods_type_id;
                $where['brand_id'] = $edit_goods->brand_id;
                $where['goods_id'] = array('neq',$goods_id);
                $same_name_count = $edit_goods->where($where)->count();
                if($same_name_count) $this->oldAjaxReturn ('goods_name', '同一个分类和品牌下已经存在相同的商品名称，请重新输入', 0);
                $product_pic_arr = I('product_pic_arr','','trim,htmlspecialchars');
                $edit_goods->product_pic = implode(',', $product_pic_arr);
                $product_default_pic_arr = I('product_default_pic_arr','','trim,htmlspecialchars');
                $edit_goods->product_default_pic = implode(',', $product_default_pic_arr);
                //添加特性
                $special_del_bool = M('goods_special_edit')->where(array('goods_id'=>$goods_id))->delete();
                $special_bool = $this->add_goods_special($goods_id);
            }
            $edit_goods->year_count = I('year_count',-1,'intval');
            $edit_goods->total_count = I('total_count',-1,'intval');
            //不是固定积分，积分转换
            if(!$edit_goods->integral && ($edit_goods->max_integral > $edit_goods->goods_price_pc)) $this->oldAjaxReturn ('max_integral', '最大抵扣积分金额不能大于总销售价格', 0);
            if(!$edit_goods->integral && $edit_goods->max_integral) {
                $is_zs = $edit_goods->max_integral % C('INTEGRAL_RATE')['rmb'];
                if($is_zs) $this->oldAjaxReturn ('max_integral', '积分抵扣金额请输入'.C('INTEGRAL_RATE')['rmb'].'的倍数', 0);
                $edit_goods->max_integral = ($edit_goods->max_integral / C('INTEGRAL_RATE')['rmb']) * C('INTEGRAL_RATE')['point'];
            }
            if($edit_goods->year_count > -1){
                //if($edit_goods->limit_start_time < date('Y-m-d H:i:s')) $this->oldAjaxReturn ('limit_start_time', '限制开始时间必须大于当前时间', 0);
                if(!$edit_goods->limit_end_time && !$edit_goods->limit_start_time) $this->oldAjaxReturn ('limit_start_time', '请选择限制开始时间或者限制结束时间', 0);
                if($edit_goods->limit_end_time < $edit_goods->limit_start_time) $this->oldAjaxReturn ('limit_start_time', '限制开始时间必须小于结束时间', 0);
            }
           if(($edit_goods->limit_end_time && $edit_goods->limit_end_time != '0000-00-00 00:00:00') || ($edit_goods->limit_start_time && $edit_goods->limit_start_time != '0000-00-00 00:00:00')) {
                if($edit_goods->year_count == -1){
                    $this->oldAjaxReturn ('year_count', '请输入限制兑换次数', 0);
                }
           }
            //初始化库存
            $goods_stock = I('goods_stock',0,'intval');
            if($goods_stock) $this->init_goods_stock ($edit_goods, $goods_stock);
            //编辑状态
            $edit_goods->goods_status = 10;
            $edit_goods->update_time = date("Y-m-d H:i:s");
            //大礼包商品
            if($edit_goods->goods_type == 2){
                $relation_good_ids = I('relation_good_ids','','trim,htmlspecialchars');
                if(!$relation_good_ids) $this->oldAjaxReturn ('goods_type', '请添加大礼包的商品', 0);
                $relation_goods_list = D('GoodsBase')->where(array('goods_id'=>array('in',$relation_good_ids)))->select();
                $relation_total_price = 0.00;$relation_total_point = 0;
                foreach($relation_goods_list as $k=>$relation_goods){
                    $relation_goods2 = array('goods_code'=>$relation_goods['goods_code'],'goods_name'=>$relation_goods['goods_name'],'number'=>1);
                    $relation_goods_price = I('goods_price_'.$relation_goods['goods_id'],0.00,'float');
                    $relation_goods2['goods_price'] = $relation_goods_price;
                    $relation_goods2['max_integral'] = I('goods_point_'.$relation_goods['goods_id'],0,'intval');
                    $relation_total_price += $relation_goods2['goods_price'];
                    $relation_total_point += $relation_goods2['max_integral'];
                    $relation_goods_arr[] = $relation_goods2;
                }
                if($edit_goods->goods_price_pc != $relation_total_price) $this->oldAjaxReturn ('goods_price_pc', '大礼包的价格必须等于所有商品的和', 0);
                if($edit_goods->max_integral != $relation_total_point) $this->oldAjaxReturn ('max_integral', '大礼包的积分必须等于所有商品的和', 0);
                $edit_goods->relation_goods = json_encode($relation_goods_arr);
            }
            if(!$edit_goods->expect_up_time) unset($edit_goods->expect_up_time);
            if(!$edit_goods->pre_up_time) unset($edit_goods->pre_up_time);
            if(!$edit_goods->expect_down_time) $edit_goods->expect_down_time = '0000-00-00 00:00:00';
            if(!$edit_goods->limit_start_time) $edit_goods->limit_start_time = '0000-00-00 00:00:00';
            if(!$edit_goods->limit_end_time) $edit_goods->limit_end_time = '0000-00-00 00:00:00';
            $edit_goods->startTrans();
            $goods_bool = $edit_goods->where($this->get_where())->save();
//            var_dump($edit_goods);
            //关联标签
            $goods_label_ids = I('goods_label_ids','','trim,htmlspecialchars');
            foreach($goods_label_ids as $k=>$label_id){
                $label_relation_data[] = array(
                    'label_id'=>$label_id,
                    'goods_id'=>$goods_id,
                    'create_time'=>  date('Y-m-d H:i:s'),
                    'update_time'=>  date('Y-m-d H:i:s'),
                );
            }
            M('goods_label_relation_edit')->where(array('goods_id'=>$goods_id))->delete();
            $label_bool = true;
            if($label_relation_data) $label_bool = M('goods_label_relation_edit')->addAll($label_relation_data);
            if(($goods_bool !== FALSE) && $special_bool && ($special_del_bool !== FALSE) && $label_bool){
                $edit_goods->commit();
                $this->oldAjaxReturn($goods_code, '修改成功', 1);
            }else{
                var_dump(($goods_bool !== FALSE) , $special_bool , ($special_del_bool !== FALSE) , $label_bool);
                $edit_goods->rollback();
                $this->oldAjaxReturn(0, '修改失败', 0);
            }
        }
    }
    
    public function info(){
        $info = D('GoodsEdit')->where($this->get_where())->find();
        $category_ids = $this->getCategoryIds($info['goods_category_id']);
        $category_names = M('goods_category')->where(array('goods_category_id'=>array('in',$category_ids)))->order('goods_category_level asc')->field('goods_category_level,goods_category_name')->select();
        $category_names = getFieldsByKey($category_names, 'goods_category_name');
        $info['category_name'] = implode('->', $category_names);
        $info['shops_name'] = M('shops_base_info')->where(array('shops_code'=>$info['shops_code']))->getField('shops_name');
        $info['type_name'] = M('goods_type')->where(array('type_id'=>$info['goods_type_id']))->getField('type_name');
        $info['brand_name'] = D('Brand')->where(array('brand_id'=>$info['brand_id']))->getField('brand_name');
        $label_list = M('goods_label_relation_edit as relation,'.C('DB_PREFIX').'goods_label as label')->where('relation.label_id = label.label_id')->where(array('relation.goods_id'=>$info['goods_id']))->getField('label_name',true);
        if($info['product_default_pic']) $goods_img_list = explode(',', $info['product_default_pic']);
        if($info['product_pic']) $win_img_list = explode(',', trim($info['product_pic'],','));
        $special_list = M('goods_special_edit')->where(array('goods_id'=>$info['goods_id']))->order('create_time asc')->select();
        foreach($special_list as $k=>$special_info){
            $special_info['special_content_pc'] = htmlspecialchars_decode(urldecode($special_info['special_content_pc']));
            $special_info['special_content_mobile'] = htmlspecialchars_decode(urldecode($special_info['special_content_mobile']));
            $special_list[$k] = $special_info;
        }
        $relation_content = M('relation_goods_info_edit')->where(array('relation_id'=>$info['goods_id']))->getField('goods_sku_content');
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
        $checked_label_list = M('goods_label_relation_edit relation,tm_goods_label as label')->where('relation.label_id = label.label_id')->where(array('relation.goods_id'=>$info['goods_id']))->field('label.label_id,label.label_name,label.label_parent_id')->order('label.create_time')->select();
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
    
    public function category_list(){
        $id = I('id');
        $ids = I('ids');
        $p_ids = I('p_ids');
        $ids = explode('_',trim($ids,'_'));
        $p_ids = explode('_',trim($p_ids,'_'));
        $p_ids[] = $id;
        $category_list = array();
        //上架的商品分类
        $where = array('goods_category_parent_id'=>array('in',$p_ids),'goods_category_status'=>1);
        $child_category = M('goods_category')->where($where)->order('create_time asc')->select();
        for($i=0;$i<count($child_category);$i++) {
            if(in_array($child_category[$i]['goods_category_id'],$ids)) $child_category[$i]['checked'] = 'checked';
            $child_category[$i]['goods_category_level'] = $child_category[$i]['goods_category_level'] > 0 ? $child_category[$i]['goods_category_level'] : 1;
            $category_list[$child_category[$i]['goods_category_level']-1][] = $child_category[$i];
        }
        $this->assign('category_list',$category_list);
        $content = $this->fetch('category_list');
        $this->ajaxReturn($content);
    }
    
    public function get_conf_page(){
        $type_id = I('type_id','','trim,htmlspecialchars');
        $category_type = M('goods_type')->where(array('type_id'=>$type_id))->getField('physics_type_id');
        $content = $this->getConf($category_type);
        $this->oldAjaxReturn($content, 0, 1);
    }
    
    //提交审核
    public function submit_check(){
        $goods_edit = D('GoodsEdit');
        $goods_codes = I('goods_codes','','trim,htmlspecialchars');
        $goods_codes = explode(',', trim($goods_codes,','));
        //商品信息
        $where['goods_code'] = array('in',$goods_codes);
        $where['goods_status'] = array('in','10,22');
        $goods_list = $goods_edit->where($where)->select();
        if(!$goods_list) $this->oldAjaxReturn (0, '请选择要提交审核的商品', 0);
        $check_goods_arr = array();
        $check_label_relation_arr = array();
        $check_goods_special_arr = array();
        $check_relation_goods_arr = array();
        $check_conf_param_arr = array();
        foreach($goods_list as $k=>$goods_info){
            $edit_goods_id = $goods_info['goods_id'];
            $goods_id = (new \Org\Util\ThinkString())->uuid();
            $goods_info['edit_goods_id'] = $edit_goods_id;
            $goods_info['goods_id'] = $goods_id;
            //待审核
            unset($goods_info['check_time']);
            $goods_info['refuse_reason'] = '';
            $goods_info['goods_status'] = 20;
            $goods_info['update_time'] = date('Y-m-d H:i:s');
            $check_goods_arr[] = $goods_info;
            //标签信息
            $label_relation_list = M('goods_label_relation_edit')->where(array('goods_id'=>$edit_goods_id))->select();
            foreach($label_relation_list as $j=>$label_relation){
                unset($label_relation['id']);
               $label_relation['goods_id'] = $goods_id;
               $check_label_relation_arr[] = $label_relation;
            }
            //特性
            $special_list = M('goods_special_edit')->where(array('goods_id'=>$edit_goods_id))->select();
            foreach($special_list as $j=>$special){
                unset($special['id']);
                $special['goods_id'] = $goods_id;
                $check_goods_special_arr[] = $special;
            }
            //规格
            $relation_goods_info = M('relation_goods_info_edit')->where(array('relation_id'=>$edit_goods_id))->find();
            if($relation_goods_info){
                $relation_goods_info['id'] = (new \Org\Util\ThinkString())->uuid();
                $relation_goods_info['relation_id'] = $goods_id;
                $check_relation_goods_arr[] = $relation_goods_info;
            }
            //配置
            $conf_param_list = M('product_conf_param_info_edit')->where(array('goods_id'=>$edit_goods_id))->select();
            foreach($conf_param_list as $j=>$param_info){
                unset($param_info['id']);
                $param_info['goods_id'] = $goods_id;
                $check_conf_param_arr[] = $param_info;
            }
        }
        //开启事务
        $goods_edit->startTrans();
        $edit_goods_bool = $goods_edit->where($where)->data(array('goods_status'=>20,'update_time'=>  date('Y-m-d H:i:s')))->save();
        $check_goods_bool = D('GoodsChecked')->addAll($check_goods_arr);
        $check_label_relation_bool = true;
        if($check_label_relation_arr) $check_label_relation_bool = M('goods_label_relation_check')->addAll($check_label_relation_arr);
        $check_special_bool = true;
        if($check_goods_special_arr) $check_special_bool = M('goods_special_check')->addAll($check_goods_special_arr);
        $relation_goods_bool = true;
        if($check_relation_goods_arr) $relation_goods_bool = M('relation_goods_info_check')->addAll($check_relation_goods_arr);
        $param_conf_bool = true;
        if($check_conf_param_arr) $param_conf_bool = M('product_conf_param_info_check')->addAll($check_conf_param_arr);
        if($edit_goods_bool && $check_goods_bool && $check_label_relation_bool && $check_special_bool && $relation_goods_bool && $param_conf_bool){
            $goods_edit->commit();
            $this->oldAjaxReturn(0, '提交审核成功', 1);
        }else{
            $goods_edit->rollback();
            $this->oldAjaxReturn(0, '提交审核失败', 0);
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
            'url'=>U('EditGoodsBase/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'商品名称',
                    'width'=>250,
                    'tip'=>'请输入商品名称'
                ),
                array(
                    'name'=>'seatch_shops_code',
                    'show_name'=>'商家',
                    'tip'=>'请选择商家',
                    'select'=>$shops_list,
                    'type'=>'select'
                ),
            ),
            'other'=>array(
                array(
                    'name'=>'serach_use_storage_money',
                    'show_name'=>'可使用储值卡',
                    'tip'=>'请选择类型',
                    'select'=>array(1=>'否',2=>'是'),
                    'type'=>'select'
                ),
                array(
                    'name'=>'seatch_category_id',
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
        $where['goods_status'] = array('in',array(10,22));
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['goods_name'] = array('like','%'.$seatch.'%');
         $goods_code = I('goods_code','','trim,htmlspecialchars');
        if($goods_code) $where['goods_code'] = $goods_code;
        $shops_code = I('seatch_shops_code','','trim,htmlspecialchars');
        if($shops_code) $where['shops_code'] = $shops_code;
        $category_id = I('seatch_category_id','','trim,htmlspecialchars');
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
        $use_storage_money = I('serach_use_storage_money',0,'intval');
        if($use_storage_money) $where['use_storage_money'] = $use_storage_money - 1;
        return $where;
    }
    
    private function get_goods_code(){
        $goods_code = M('goods_code')->data(array('code_status'=>1,'create_time'=>date('Y-m-d H:i:s')))->add();
        return $goods_code;
    }
    
    //添加特性
    private function add_goods_special($goods_id){
        $goods_specials = I('goods_specials');
        $special_save_data = array();
        $special_bool = true;
        if($goods_specials && is_array($goods_specials)) {
            for($i=0;$i<count($goods_specials);$i++) {
                $special_data = explode('&',htmlspecialchars_decode($goods_specials[$i]));
                for($j=0;$j<count($special_data);$j++) {
                    $temp = explode('=',$special_data[$j]);
                    $special_save_data[$i][$temp[0]] = urldecode($temp[1]);
                }
                unset($special_save_data[$i]['id']);
                unset($special_save_data[$i]['goods_id']);
                unset($special_save_data[$i]['special_platform']);
                $special_save_data[$i]['create_time'] = date('Y-m-d H:i:s');
                $special_save_data[$i]['update_time'] = date('Y-m-d H:i:s');
                $special_save_data[$i]['goods_id'] = $goods_id;
                M('goods_special_edit')->data($special_save_data[$i])->add();
            }
        }
        return $special_bool;
    }
    
    //获取分类的所有父分类
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
                foreach($param_value_list as $j=>$value_info) if(in_array($value_info['param_value_id'], $checked_param_value_ids)) $param_value_list[$j]['selected'] = 'selected';
                $param_name_list[$kk]['child'] = $param_value_list;
            }
            $list[$k]['child'] = $param_name_list;
        }
        $this->assign('goods_conf_list', $list);
        return $this->fetch('conf');
    }
    
    //验证管理员操作商品的其他权限
    private function check_access_for_goods() {
        if(check_admin_access('show_goods_list_all_edit',1,'other')){ //验证是否可以查看所有SKU
            return null;
        }elseif(check_admin_access('show_goods_list_shops_edit',1,'other')){ //验证是否可以查看本商家SKU
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
    
    //初始化库存
    protected function init_goods_stock($obj,$goods_stock){
        $exist_stock = M('goods_stock_info')->where(array('goods_id'=>$obj->goods_code))->count();
        if($exist_stock) $this->oldAjaxReturn('goods_stock', '库存已经存在，请前往库存管理操作库存', 0);
        $stock_id = (new \Org\Util\ThinkString())->uuid();
        $data['stock_id'] = $stock_id;
        $data['goods_id'] = $obj->goods_id;
        $data['goods_name'] = $obj->goods_name;
        $data['goods_code'] = $obj->goods_code;
        $data['shops_code'] = $obj->shops_code;
        $data['stock_status'] = 1;
        $data['total_stocks'] = $goods_stock;
        $data['cur_sale_stocks'] = $goods_stock;
        $data['occupy_stocks'] = 0;
        $data['freeze_stocks'] = 0;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');
        $add_bool = M('goods_stock_info')->data($data)->add();
        //库存日志
        $log_data['log_id'] = (new \Org\Util\ThinkString())->uuid();
        $log_data['stock_id'] = $stock_id;
        $log_data['goods_id'] = $obj->goods_id;
        $log_data['goods_name'] = $obj->goods_name;
        $log_data['goods_code'] = $obj->goods_code;
        $log_data['user_id'] = session('admin_uid');
        $log_data['handle_number'] = $goods_stock;
        $log_data['stock_type'] = 1;
        $log_data['create_time'] = date('Y-m-d H:i:s');
        $add_log_bool = M('goods_stock_log')->data($log_data)->add();
        return $add_bool && $add_log_bool;
    }
    
    private function relation_label($goods_id=''){
        if($goods_id) $label_ids = M('goods_label_relation_edit')->where(array('goods_id'=>$goods_id))->getField('label_id',true);
        $label_list = M('goods_label')->where(array('label_level'=>1,'label_status'=>array('neq',0)))->order('create_time asc')->select();
        foreach($label_list as $k=>$label_info){
            $child_list = M('goods_label')->where(array('label_parent_id'=>$label_info['label_id'],'label_status'=>array('neq',0)))->order('create_time asc')->select();
            foreach($child_list as $j=>$child_label){
                if(in_array($child_label['label_id'], $label_ids)) $child_list[$j]['checked'] = 'checked';
            }
            $label_info['child'] = $child_list;
            $label_list[$k] = $label_info;
        }
        $this->assign('label_list', $label_list);
        $content = $this->fetch('label_conf');
        $this->assign('goods_label_content', $content);
    }
}
