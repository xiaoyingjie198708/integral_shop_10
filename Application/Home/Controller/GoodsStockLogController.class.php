<?php


namespace Home\Controller;

class GoodsStockLogController extends BaseController{
    
    //库存列表
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(D('GoodsStockLog')->where($where)->count(),10);
        $list = D('GoodsStockLog')->where($where)->limit($page->firstRow.','.$page->listRows)->order('create_time desc')->select();
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $shops_names = D('Shops')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $user_ids = getFieldsByKey($list, 'user_id');
        $user_names = M('admin_user')->where(array('id'=>array('in',$user_ids)))->getField('id,username',true);
        foreach($list as $k=>$stock_info){
            $list[$k]['shops_name'] = $shops_names[$stock_info['shops_code']];
            $list[$k]['user_name'] = $user_names[$stock_info['user_id']];
        }
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        $where = array('shops_status'=>array('neq',0));
        $access_shops_codes = $this->check_access_for_stock();
        if(is_array($access_shops_codes)){
            $where['shops_code'] = array('in',$access_shops_codes);
        }
        $shops_list = D('Shops')->where($where)->getField('shops_code,shops_name',true);
        return array(
            'url'=>U('GoodsStockLog/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'商品名称/商品编码',
                    'width'=>200,
                    'tip'=>'请输入商品名称/商品编码'
                ),
                array(
                    'name'=>'shops_code',
                    'show_name'=>'商家',
                    'tip'=>'请选择商家',
                    'select'=>$shops_list,
                    'type'=>'select'
                ),
            )
        );
    }
    
    private function get_where(){
        $where = array();
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['goods_name|goods_code'] = array('like','%'.$seatch.'%');
        $shops_code = I('shops_code','','trim,htmlspecialchars');
        if($shops_code) $where['shops_code'] = $shops_code;
        //商品权限
        $access_shops_codes = $this->check_access_for_stock();
        if(is_array($access_shops_codes)){
            $where['shops_code'] = array('in',$access_shops_codes);
        }
        return $where;
    }
    
    //验证管理员操作商品的其他权限
    function check_access_for_stock() {
        if(check_admin_access('show_goods_stock_log_all_check',1,'other')){ //验证是否可以查看所有SKU
            return null;
        }elseif(check_admin_access('show_goods_stock_log_shops_check',1,'other')){ //验证是否可以查看本商家SKU
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
}
