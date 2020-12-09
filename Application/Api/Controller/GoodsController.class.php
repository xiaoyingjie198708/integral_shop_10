<?php


namespace Api\Controller;

class GoodsController extends BaseController{
    
    //创建或者更新商品
    public function update_goods(){
        if(IS_POST){
            $token = I('token','','trim,htmlspecialchars');
            $base_data =  file_get_contents('php://input');
            if(!$base_data) $this->customAjaxReturn (501, FALSE, '没有任何数据');
            if(!$this->check_token($token, $base_data)) $this->customAjaxReturn (501,FALSE,'token验证失败');
            \Org\My\MyLog::write_log('操作时间：'.  date('Y-m-d H:i:s')."\n商品数据包：".$base_data."\n", 'cms-sync-goods.log',date('Y-m-d'));
            $data = json_decode($base_data, true);
            foreach($data as $k=>$val){
                if($k == 'goods_special'){
                    foreach($val as $j=>$val2){
                        $temp_special = array(
                            'name'=>urldecode($val2['name']),
                            'sort'=>$val2['sort'],
                            'pc_content'=>urldecode($val2['pc_content']),
                            'mobile_content'=>urldecode($val2['mobile_content']),
                        );
                        $val[$j] = $temp_special;
                    }
                    $data[$k] = $val;
                }else{
                    $data[$k] = urldecode($val);
                }
            }
            //检查商品必填项数据的正确性
            if(!$data['category_code']) $this->customAjaxReturn (501,FALSE,'商品分类编码不能为空');
            $category_info = M('goods_category')->where(array('goods_category_code'=>$data['category_code']))->find();
            if(!$category_info) $this->customAjaxReturn (501, FALSE, '商品分类编码：'.$data['category_code'].', 在资源管理平台不存在，请联系管理员添加');
            //判断交换ID是否有效
            if($data['sync_goods_id']){
                
            }
            if(!$data['materiel_code']) $this->customAjaxReturn (501,FALSE,'商品物料编码不能为空');
            if(!$data['brand_name']) $this->customAjaxReturn (501,FALSE,'品牌名称不能为空');
            $brand_info = M('goods_brand')->where(array('brand_name'=>$data['brand_name']))->find();
            //商品品牌不存在
            if(!$brand_info){
                $brand_info['brand_id'] = (new \Org\Util\ThinkString())->uuid();
                $brand_code = M('goods_brand')->order('brand_code desc')->getField('brand_code');
                if($brand_code) $brand_code += 1;
                else $brand_code = 1000;
                $brand_info['brand_code'] = $brand_code;
                $brand_info['brand_name'] = $data['brand_name'];
                $brand_info['brand_initial'] = '';
                $brand_info['brand_recommend'] = 0;
                $brand_info['create_time'] = date('Y-m-d H:i:s');
                $brand_info['update_time'] = date('Y-m-d H:i:s');
                M('goods_brand')->data($brand_info)->add();
            }
            if(!$data['goods_name']) $this->customAjaxReturn (501,FALSE,'商品名称不能为空');
            if(!$data['product_default_pic']) $this->customAjaxReturn (501,FALSE,'商品列表图不能为空');
            if(!$data['product_pic']) $this->customAjaxReturn (501,FALSE,'商品橱窗图不能为空');
            if(!$data['goods_special']) $this->customAjaxReturn (501,FALSE,'商品特性内容不能为空');
            //分号替换成逗号
            $data['product_pic'] = str_replace(';', ',', $data['product_pic']);
            $data['product_pic'] = trim($data['product_pic'],',');
            //组装数据
            $goods_status = 10;
            $goods_data = array(
                'goods_category_id'=>$category_info['goods_category_id'],
                'goods_category_code'=>$category_info['goods_category_code'],
                'brand_id'=>$brand_info['brand_id'],
                'goods_status'=>$goods_status,
                'goods_materiel_code'=>$data['materiel_code'],
                'goods_name'=>$data['goods_name'],
                'goods_brief'=>$data['goods_desc'],
                'goods_unit'=>$data['goods_unit'],
                'search_key_word'=>$data['search_key_word'],
                'product_default_pic'=>$data['product_default_pic'],
                'product_pic'=>$data['product_pic'],
                'update_time'=>$data['update_time'],
            );
            M('goods_base_info_edit')->startTrans();
            if($data['sync_goods_id']){
                $goods_id = M('goods_base_info_edit')->where(array('goods_code'=>$data['sync_goods_id']))->getField('goods_id');
                $goods_code = $data['sync_goods_id'];
                $goods_bool = M('goods_base_info_edit')->where(array('goods_code'=>$data['sync_goods_id']))->data($goods_data)->save();
            }else{
                if($data['goods_type'] != 3) {
                    $goods_data['goods_type'] = $data['goods_type'];
                }
                $goods_id = (new \Org\Util\ThinkString())->uuid();
                $goods_code = $this->get_goods_code();
                $goods_data['goods_id'] = $goods_id;
                $goods_data['formal_goods_id'] = $goods_id;
                $goods_data['goods_code'] = $goods_code;
                $goods_data['source'] = 2;
                $goods_data['create_time'] = $data['update_time'];
                $goods_data['goods_type_id'] = '';
                $goods_data['goods_type_code'] = '';
                $goods_data['goods_price_pc'] = 0.00;
                $goods_data['shops_code'] = '';
                $goods_bool = M('goods_base_info_edit')->data($goods_data)->add();
            }
            //商品特性
            $special_del_bool = M('goods_special_edit')->where(array('goods_id'=>$goods_id))->delete();
            foreach($data['goods_special'] as $k=>$goods_special){
                $goods_special_data[] = array(
                    'goods_id'=>$goods_id,
                    'special_name'=>$goods_special['name'],
                    'special_content_pc'=> urlencode($goods_special['pc_content']),
                    'special_content_mobile'=>urlencode($goods_special['mobile_content']),
                    'special_sort'=>$goods_special['sort'],
                    'create_time'=>$data['update_time'],
                    'update_time'=>$data['update_time']
                );
            }
            $special_bool = true;
           if($goods_special_data) $special_bool = M('goods_special_edit')->addAll($goods_special_data);
           if($goods_bool && $special_del_bool !== FALSE && $special_bool){
               M('goods_base_info_edit')->commit();
               $this->ajaxReturn(array('code'=>200,'success'=>TRUE,'message'=>'添加成功','sync_goods_id'=>$goods_code));
           }else{
               var_dump($goods_bool , $special_del_bool !== FALSE , $special_bool);exit;
               M('goods_base_info_edit')->rollback();
               $this->customAjaxReturn (500,FALSE,'添加失败');
           }
        }
        $this->customAjaxReturn (501,FALSE,'请用POST方法调用');
    }
    
    //更新商品销售信息
    public function update_goods_sale(){
        if(IS_POST){
            $token = I('token','','trim,htmlspecialchars');
            $base_data =  file_get_contents('php://input');
            if(!$base_data) $this->customAjaxReturn (501, FALSE, '没有任何数据');
            if(!$this->check_token($token, $base_data)) $this->customAjaxReturn (501,FALSE,'token验证失败');
            $data = json_decode($base_data, true);
            $page_index = intval($data['page_index']);
            $page_index = $page_index ? $page_index : 1;
            if(!$data['start_time'] && !$data['end_time'] && !$data['sync_goods_id']) $this->customAjaxReturn (501,FALSE,'参数：start_time，end_time，sync_goods_id请至少填写一个');
            if($data['start_time'] && !$data['end_time']) $where['update_time'] = array('egt',$data['start_time']);
            if(!$data['start_time'] && $data['end_time']) $where['update_time'] = array('elt',$data['end_time']);
            if($data['start_time'] && $data['end_time']) $where['update_time'] = array('elt',$data['end_time']);
            if($data['sync_goods_id']) $where['sync_goods_id'] = $data['sync_goods_id'];
            $count = M('cms_goods_info')->where($where)->count();
            $list = M('cms_goods_info')->where($where)->limit((($page_index - 1) * 10 ) . ',10')->select();
            foreach($list as $k=>$goods_info){
                $list[$k]['mobile_detail_url'] = C('MOBILE_HOST_URL').'mall/detail/goods_code/'.$goods_info['sync_goods_id'].'.html';
            }
            $this->ajaxReturn(array('code'=>200,'success'=>TRUE,'message'=>'请求成功','count'=>$count,'page_count'=>  ceil($count/10),'goods_list'=>$list));
        }
        $this->customAjaxReturn (501,FALSE,'请用POST方法调用');
    }
    
    /**
     * CRM同步商品
     */
    public function sync_goods(){
        if(IS_POST){
            $token = I('token','','trim,htmlspecialchars');
            $base_data =  file_get_contents('php://input');
            if(!$base_data) $this->customAjaxReturn (501, FALSE, '没有任何数据');
            if(!$this->check_token($token, $base_data)) $this->customAjaxReturn (501,FALSE,'token验证失败');
            $data = json_decode($base_data, true);
            if(!$data['name']) $this->customAjaxReturn (502,FALSE,'名称不能为空');
            if(!$data['code']) $this->customAjaxReturn (502,FALSE,'编码不能为空');
            if(!$data['type']) $this->customAjaxReturn (502,FALSE,'类型不能为空');
            if(!in_array($data['type'], array(1,2,3))) $this->customAjaxReturn (502,FALSE,'类型错误');
            $goods_type = $data['type'] + 2;
            $goods_data = array(
                'goods_name'=>$data['name'],
                'goods_brief'=>$data['desc'],
                'update_time'=>date('Y-m-d H:i:s')
            );
            M('goods_base_info_edit')->startTrans();
            $exist_info = M('goods_base_info_edit')->where(array('ext_code'=>$data['code']))->find();
            if($exist_info){
                $goods_bool = M('goods_base_info_edit')->where(array('ext_code'=>$data['code']))->data($goods_data)->save();
            }else{
                $goods_code = $this->get_goods_code();
                $goods_data['ext_code'] = $data['code'];
                $goods_data['goods_category_id'] = '';
                $goods_data['goods_category_code'] = '';
                $goods_data['goods_materiel_code'] = 'CH'.$goods_code;;
                $goods_data['brand_id'] = '';
                $goods_data['goods_type_id'] = '';
                $goods_data['goods_type_code'] = '';
                $goods_data['goods_unit'] = 1;
                $goods_data['search_key_word'] = '';
                $goods_data['product_default_pic'] = '';
                $goods_data['product_pic'] = '';
                $goods_data['goods_price_pc'] = 0.00;
                $goods_data['shops_code'] = '';
                $goods_id = (new \Org\Util\ThinkString())->uuid();
                $goods_data['goods_id'] = $goods_id;
                $goods_data['formal_goods_id'] = $goods_id;
                $goods_data['goods_code'] = $goods_code;
                $goods_data['source'] = 3;
                $goods_data['goods_type'] = $goods_type;
                $goods_data['create_time'] = date('Y-m-d H:i:s');
                $goods_bool = M('goods_base_info_edit')->data($goods_data)->add();
            }
            if($goods_bool){
                M('goods_base_info_edit')->commit();
                $this->customAjaxReturn (200, true, '同步成功');
            }
            else {
                M('goods_base_info_edit')->rollback();
                $this->customAjaxReturn (500, FALSE, '同步失败');
            }
        }
        $this->customAjaxReturn (510,FALSE,'请用POST方法调用');
    }
    
    /**
     * 关联商品推荐
     */
    public function relation_goods(){
        if(IS_POST){
            $goods_list = M('yl_relation_goods')->limit('0,4')->select();
            //关联商品
            $relation_goods = array();
            foreach($goods_list as $k=>$goods_info){
                $relation_goods[] = array(
                    'productName'=>$goods_info['goods_name'],
                    'imgUrl'=>$goods_info['goods_img'],
                    'productCode'=>$goods_info['goods_code'],
                    'detailUrl'=>C('PC_HOST_URL').'mall/detail/goods_code/'.$goods_info['goods_code'].'.html',
                    'desc'=>$goods_info['goods_desc'],
                    'mediaPrice'=>$goods_info['goods_media_price'],
                    'price'=>$goods_info['goods_price'],
                    'promotionContent'=>$goods_info['promotion_content'],
                );
            }
            $this->ajaxReturn(array('code'=>200,'success'=>true,'message'=>'请求成功','products'=>$relation_goods));
        }
        $this->customAjaxReturn (501,FALSE,'请用POST方法调用');
    }
    /*---------------------------------------------------受保护方法----------------------------------------------------*/
    private function get_goods_code(){
        $goods_code = M('goods_code')->data(array('code_status'=>1,'create_time'=>date('Y-m-d H:i:s')))->add();
        return $goods_code;
    }
}
