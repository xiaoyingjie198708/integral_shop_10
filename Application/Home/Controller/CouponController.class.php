<?php


namespace Home\Controller;

/**
 *优惠券管理
 * @author xiao.yingjie
 */
class CouponController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('Coupon')->where($where)->count(),10);
        $list = D('Coupon')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    //启用
    public function coupon_enable(){
        $coupon_codes = I('coupon_codes','','trim,htmlspecialchars');
        $coupon_codes = explode(',', trim($coupon_codes, ','));
        $where['coupon_code'] = array('in',$coupon_codes);
        $update_bool = D('Coupon')->where($where)->data(array('coupon_status'=>30,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($update_bool) $this->oldAjaxReturn (0, '启用成功', 1);
        else $this->oldAjaxReturn (0, '启用失败', 0);
    }
    
    //停用
    public function coupon_stop(){
        $coupon_codes = I('coupon_codes','','trim,htmlspecialchars');
        $coupon_codes = explode(',', trim($coupon_codes, ','));
        $where['coupon_code'] = array('in',$coupon_codes);
        $update_bool = D('Coupon')->where($where)->data(array('coupon_status'=>31,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($update_bool) $this->oldAjaxReturn (0, '停用成功', 1);
        else $this->oldAjaxReturn (0, '停用失败', 0);
    }
    
    //查看详情
    public function info(){
        $info = D('Coupon')->where($this->get_where())->find();
        $this->assign('info', $info);
        $this->display();
    }
    
   //维护关联关系
    public function relation_info(){
        $coupon_code = I('coupon_code','','trim,htmlspecialchars');
        $coupon_info = D('Coupon')->where(array('coupon_code'=>$coupon_code))->find();
        $this->assign('info', $coupon_info);
        //关联商家
        if($coupon_info['coupon_use_type'] == 2){
             $relation_list = M('coupon_relation_info')->where(array('coupon_id'=>$coupon_info['coupon_id']))->select();
            $relation_ids = getFieldsByKey($relation_list, 'relation_id');
            $shops_list = D('Shops')->where(array('shops_id'=>array('in',$relation_ids)))->getField('shops_id,shops_name',true);
            foreach($relation_list as $k=>$relation){
                $relation_list[$k]['shops_name'] = $shops_list[$relation['relation_id']];
            }
        }
        //关联分类
        if($coupon_info['coupon_use_type'] == 3){
            $coupon_id = $coupon_info['coupon_id'];
            $coupon_relation_ids = M('coupon_relation_info')->where(array('coupon_id'=>$coupon_id))->getField('relation_id',true);
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
        //排除商品
        if($coupon_info['coupon_use_type'] == 4){
            $mod =  M('coupon_relation_info as exclude,'.C('DB_PREFIX').'goods_base_info as goods');
            $where['exclude.coupon_id'] = $coupon_info['coupon_id'];
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
            $mod =  M('coupon_relation_exclude_goods as exclude,'.C('DB_PREFIX').'goods_base_info as goods');
            $where['exclude.coupon_id'] = $coupon_info['coupon_id'];
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
    
    //修改
    public function edit(){
        $coupon_code = I('coupon_code','','trim,htmlspecialchars');
        $update_bool = D('CouponEdit')->where(array('coupon_code'=>$coupon_code))->data(array('coupon_status'=>10,'update_time'=>date('Y-m-d H:i:s')))->save();
        if(!$update_bool) $this->oldAjaxReturn (0, '修改请求失败', 0);
        $this->oldAjaxReturn(0, '修改请求成功', 1);
    }
    
    public function import(){
        $uploadPath = './Public/temp_excel/';
        $upload = new \Org\My\UploadFile();// 实例化上传类
        $upload->maxSize  = 104857600;// 设置附件上传大小 默认20M
        $upload->allowExts  = array('xlsx','xls');// 设置附件上传类型
        $upload->savePath = $uploadPath;// 设置附件上传目录
        if(!$upload->upload()) { // 上传错误提示错误信息
            exit(json_encode(array('data'=>0,'info'=>$upload->getErrorMsg(),'status'=>0)));
        }else{
            $info = $upload->getUploadFileInfo();
            $info = $info[0];
            $filePath = $info['savepath'].$info['savename'];
            $excel = new \Org\My\Excel();
            $fields = array('mobile','coupon_code');
            $coupon_users = $excel->importExcel($filePath,$fields);
            unlink($filePath);
            if(!$coupon_users)  exit(json_encode(array('data'=>0,'info'=>'没有数据或者数据格式有错误,请删除Excel的所有公式','status'=>0)));
            M('coupon_user_info')->startTrans();
            $coupon_codes = getFieldsByKey($coupon_users, 'coupon_code');
            $coupon_list = M('coupon_base_info')->where(array('coupon_code'=>array('in',$coupon_codes)))->getField('coupon_code,coupon_id,coupon_value,is_pc,is_wap,is_app,coupon_status,valid_start_time,valid_end_time',true);
            $mobiles = getFieldsByKey($coupon_users, 'mobile');
            $user_list = M('user_member_base_info')->where(array('member_account'=>array('in',$mobiles)))->getField('member_account,member_id,member_code',true);
            $coupon_user_data = array();
            foreach($coupon_users as $k=>$coupon_user){
                $coupon_info = $coupon_list[$coupon_user['coupon_code']];
                $user_info = $user_list[$coupon_user['mobile']];
                if(!$coupon_info) $error[] = '优惠券：'.$coupon_user['coupon_code'].',不存在';
                if(!$user_info) $error[] = '会员手机号码：'.$coupon_user['mobile'].',不存在';
                if($coupon_info['coupon_status'] != 30) $error[] = '优惠券:'.$coupon_user['coupon_code'].'不是启用状态,不能发放';
                $coupon_user_data[] = array(
                    'member_id'=>$user_info['member_id'],
                    'coupon_id'=>$coupon_info['coupon_id'],
                    'coupon_code'=>$coupon_info['coupon_code'],
                    'coupon_value'=>$coupon_info['coupon_value'],
                    'is_pc'=>$coupon_info['is_pc'],
                    'is_wap'=>$coupon_info['is_wap'],
                    'is_app'=>$coupon_info['is_app'],
                    'valid_start_time'=>$coupon_info['valid_start_time'],
                    'valid_end_time'=>$coupon_info['valid_end_time'],
                    'coupon_status'=>10,
                    'create_time'=>  date('Y-m-d H:i:s'),
                    'update_time'=>  date('Y-m-d H:i:s'),
                    'coupon_user_id'=>(new \Org\Util\ThinkString())->uuid()
                );
            }
            if(empty($error)){
                $add_bool = M('coupon_user_info')->addAll($coupon_user_data);
                if($add_bool === FALSE) $error[] = '保存错误';
            }
            if(empty($error)){
                M('coupon_user_info')->commit();
                 exit(json_encode(array('data'=>0,'info'=>'导入数据成功','status'=>1)));
            }else{
                M('coupon_user_info')->rollback();
                 exit(json_encode(array('data'=>0,'info'=>implode('  ;',$error),'status'=>0)));
            }
        }
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('Coupon/index'),
            'button'=>array(
                '<a class="btn btn-success" href="/Public/uploads/优惠券发放模板.xlsx" onclick="ajax_loading(false);"><i class="icon-download icon-white"></i> 下载模板</a>',
                '<button class="btn btn-success" id="import" data-url="'.U('GamesCode/import_games_code').'" type="button"><i class="icon-upload icon-white"></i> 发放优惠券</button>'
            ),
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
        $where['coupon_status'] = array('in',array(21,30,31));
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['coupon_name|coupon_code'] = array('like','%'.$seatch.'%');
        $coupon_code = I('coupon_code','','trim,htmlspecialchars');
        if($coupon_code) $where['coupon_code'] = $coupon_code;
        return $where;
    }
}
