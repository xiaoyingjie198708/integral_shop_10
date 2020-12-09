<?php
namespace Home\Controller;
class BaseController extends CommonController {
    /**
    +----------------------------------------------------------
    * 构造函数
    +----------------------------------------------------------
    */
    function __construct(){
        parent::__construct();
        if(!$this->isLogin()) {
            if(IS_AJAX) $this->oldAjaxReturn(0,'登录超时',0);
            else $this->redirect('Index/index');
        } //如果没有登录，请求是ajax的返回登录超时，其他请求重定向到登录页面
        $user_password = M('admin_user')->where(array('id'=>session('admin_uid')))->getField('password');
        if($this->password(C('DEFAULT_ADMIN_PASSWORD')) == $user_password && CONTROLLER_NAME != 'Self')
            $this->redirect('Self/update_password',array('update'=>1));
        if(!check_admin_access(CONTROLLER_NAME.'/'.ACTION_NAME)){
            if(IS_AJAX) $this->oldAjaxReturn(0,'无权操作',0);
            else exit('无权操作');
        }
        if(!IS_AJAX) {
            if(ACTION_NAME == 'index') session(CONTROLLER_NAME.'/index',null);
            $this->getMenu();
            //未读事件
            $access_shops_code = $this->check_access_for_shops();
            $code_where['is_warn'] = 1;//已发警告
            $code_where['is_visit'] = 1;//未回访
            if($access_shops_code) $code_where['shops_code'] = array('in',$access_shops_code);
            $noread_count = M('member_use_code')->where($code_where)->count();
            $this->assign('noread_count', $noread_count);
        }
    }
    //获取后台菜单
    private function getMenu() {
        $memu_id = I('menu',0,'intval');
        $menu = M('admin_menu')->order('sort asc')->select();
        foreach($menu as $k=>$v) {
            if($v['url'] && !check_admin_access($v['url'])) unset($menu[$k]); //验证权限，不通过不显示菜单
        }
        $menu = \Org\My\Tree::getTree($menu);
        foreach($menu as $k=>$v) {
            if(!$v['son'] || $v['lv'] != 1) unset($menu[$k]);
        }
        $menu_info = M('admin_menu')->where(array('url'=>CONTROLLER_NAME.'/'.ACTION_NAME))->select();
        if($menu_info) {
            $menu_path = explode('-',implode('-',getFieldsByKey($menu_info,'path')));
            $menu_path = array_merge($menu_path,getFieldsByKey($menu_info,'id'));
            $choose_menu = M('admin_menu')->where(array('id'=>array('in',array_unique($menu_path))))->order('lv asc')->select();
            $curr_menu_path = explode('-', $menu_info[0]['path']);
            if(!$memu_id) $memu_id = $curr_menu_path[1];
            $this->assign('choose_menu',$choose_menu);
        }
        $left_memu_list = array();
        if(!$memu_id) $memu_id = $menu[array_keys($menu)[0]]['id'];
        foreach($menu as $k=>$menu_info){
            if($memu_id){
                if($memu_id == $menu_info['id']) $left_memu_list = $menu_info['son'];
            }else{
                if($k == 0) {
                    $memu_id = $menu_info['id'];
                    $left_memu_list = $menu_info['son'];
                }
            }
        }
        $this->assign('root_memu_id', $memu_id);
        $this->assign('root_menu_list', $menu);
        $this->assign('menu_list',$left_memu_list);
    }
    
    //选择商品公共函数
    public function choose_goods() {
        $where = $this->goods_list_where_common();
        $goods_model = D('GoodsBase');
        $count = $goods_model->where($where)->count();
        $page = new \Think\Page($count,5);
        $goods_list = $goods_model->where($where)->limit($page->firstRow.','.$page->listRows)->order('goods_sort asc,update_time desc')->select();
        $where = array('goods_category_parent_id'=>'0','goods_category_status'=>1,'goods_category_level'=>1);
        $category_list = M('goods_category')->where($where)->order('create_time asc')->select();
        $category_id = I('category_id','','trim,htmlspecialchars');
        if($category_id) {
            $category_ids = $this->get_select_category_ids($category_id,array());
            $category_list_other = array();
            for($i=0;$i<count($category_ids);$i++) {
                $where = array('goods_category_parent_id'=>$category_ids[$i],'goods_category_status'=>1);
                $category_list_other_temp = M('goods_category')->where($where)->order('create_time asc')->select();
                if($category_list_other_temp) {
                    $category_info_other_temp = array();
                    for($j=0;$j<count($category_list_other_temp);$j++) {
                        if(in_array($category_list_other_temp[$j]['goods_category_id'],$category_ids)) {
                            $category_info_other_temp = $category_list_other_temp[$j];
                            $category_list_other_temp[$j]['checked'] = 'checked';
                            break;
                        }
                    }
                    $category_list_other[] = array('info'=>$category_info_other_temp,'list'=>$category_list_other_temp);
                }
            }
            for($i=0;$i<count($category_list);$i++) {
                if(in_array($category_list[$i]['goods_category_id'],$category_ids)) {
                    $category_parent_info = $category_list[$i];
                    $category_list[$i]['checked'] = 'checked';
                    break;
                }
            }
            $this->assign('category_parent_info',$category_parent_info);
            $this->assign('category_list_other',$category_list_other);
        }
        $this->assign('page',$page->show());
        $this->assign('category_list',$category_list);
        $this->assign('goods_list',$goods_list);
        $this->assign('count',$count);
        $content = $this->fetch('index');
        $this->oldAjaxReturn(0,$content,1);
    }
    
    //关联商家
    public function choose_shops(){
        $where = $this->get_shops_list_where_common();
        $count = M('shops_base_info')->where($where)->count();
        $page = new \Think\Page($count,5);
        $shops_list = M('shops_base_info')->where($where)->limit($page->firstRow.','.$page->listRows)->order('create_time desc')->select();
        $this->assign('page', $page->show());
        $this->assign('shops_list', $shops_list);
        $this->assign('count', $count);
        $content = $this->fetch('shops_info');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    //获取商家名称
    public function get_shops_names(){
        $shops_ids = I('shops_id','','trim,htmlspecialchars');
        $shops_ids = trim($shops_ids,',');
        $where['shops_id'] = array('in',$shops_ids);
        $shops_list = M('shops_base_info')->where($where)->select();
        $this->oldAjaxReturn($shops_list, 0, 1);
    }
    
    //获取商品名称
    public function get_goods_names(){
        $goods_id = I('goods_id','','trim,htmlspecialchars');
        $goods_id = trim($goods_id,',');
        $where['goods_id'] = array('in',$goods_id);
        $goods_list = D('GoodsBase')->where($where)->select();
        $shops_codes = getFieldsByKey($goods_list, 'shops_code');
        $shops_names = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        foreach($goods_list as $k=>$goods_info){
            $goods_list[$k]['shops_name'] = $shops_names[$goods_info['shops_code']];
        }
        $this->oldAjaxReturn($goods_list, 0, 1);
    }
    
    //选择优惠券
    public function choose_coupon(){
        $where = $this->coupon_list_where_common();
        $count = M('coupon_base_info')->where($where)->count();
        $page = new \Think\Page($count,5);
        $coupon_list = M('coupon_base_info')->where($where)->limit($page->firstRow.','.$page->listRows)->order('create_time desc')->select();
        $this->assign('page', $page->show());
        $this->assign('coupon_list', $coupon_list);
        $this->assign('count', $count);
        $content = $this->fetch('coupon_info');
        $this->oldAjaxReturn(0, $content, 1);
    }
    
    //获取优惠券名称
    public function get_coupon_names(){
        $coupon_ids = I('coupon_id','','trim,htmlspecialchars');
        $coupon_ids = trim($coupon_ids,',');
        $where['coupon_status'] = array('in','21,30,31');
        $where['coupon_id'] = array('in',$coupon_ids);
        $coupon_list = M('coupon_base_info')->where($where)->select();
        foreach($coupon_list as $k=>$info) $coupon_list[$k]['coupon_type'] = id2name('coupon_use_type', $info['coupon_use_type']);
        $this->oldAjaxReturn($coupon_list, 0, 1);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    //选择商品公共函数 - 获取商品分类
    public function get_goods_category_common($id) {
        if(!$id) $this->oldAjaxReturn(0,0,0);
        $where = array('goods_category_parent_id'=>$id,'goods_category_status'=>1);
        $child_category = M('goods_category')->where($where)->order('create_time asc')->select();
        $this->oldAjaxReturn($child_category,0,1);
    }
    
    //选择商品公共函数 - 商品条件组合
    private function goods_list_where_common() {
        //接收参数
        $category_id = I('category_id','','trim,htmlspecialchars');
        if($category_id){
            $category_ids = $this->get_select_child_category_ids($category_id);
            $where['goods_category_id'] = array('in',$category_ids);
        }
        $search = I('search','','trim,htmlspecialchars');
        if($search) $where['goods_code|goods_name'] = array('like','%'.$search.'%');
        $where['goods_status'] = array('neq',0);
        return $where;
    }
    
    private function get_select_category_ids($category_id,$category_ids = array()){
        $category_ids[] = $category_id;
        $curr_category_info = M('goods_category')->where(array('goods_category_id'=>$category_id))->find();
        if($curr_category_info['child_node']) {
            $category_ids = $this->get_select_category_ids ($curr_category_info['goods_category_parent_id'], $category_ids);
        }
        $category_ids = M('goods_category')->where(array('goods_category_id'=>array('in',$category_ids)))->order('goods_category_level asc')->field('goods_category_level,goods_category_id')->select();
        $category_ids = getFieldsByKey($category_ids, 'goods_category_id');
        return $category_ids;
    }
    
    private function get_select_child_category_ids($category_id){
         $child_category_list = M('goods_category')->where(array('goods_category_parent_id'=>$category_id))->select();
         $child_category_ids = array();
         foreach($child_category_list as $k=>$child_category){
             $child_category_ids[] = $child_category['goods_category_id'];
             $child_category_arr = M('goods_category')->where(array('goods_category_parent_id'=>$child_category['goods_category_id']))->select();
             foreach($child_category_arr as $j=>$child_category2){
                 $child_category_ids[] = $child_category2['goods_category_id'];
             }
         }
         $child_category_ids[] = $category_id;
         return $child_category_ids;
    }
    
    //商家插件的查询条件
    private function get_shops_list_where_common(){
        $search = I('search','','trim,htmlspecialchars');
        if($search) $where['shops_code|shops_name'] = array('like','%'.$search.'%');
        $where['shops_status'] = 1;
        return $where;
    }
    
    //选择优惠券公共函数--组合条件
    private function coupon_list_where_common(){
        $coupon_category_type = I('coupon_category',0,'intval');
        if($coupon_category_type) $where['coupon_category_type'] = $coupon_category_type;
        $serach = I('search','','trim,htmlspecialchars');
        if($serach) $where['coupon_name'] = array('like','%'.$serach.'%');
        return $where;
    }
    
    //验证管理员操作商品的其他权限
    private function check_access_for_shops() {
        if(check_admin_access('show_use_code_all',1,'other')){ //验证是否可以查看所有
            return null;
        }elseif(check_admin_access('show_use_code_shops',1,'other')){ //验证是否可以查看本商家
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
}