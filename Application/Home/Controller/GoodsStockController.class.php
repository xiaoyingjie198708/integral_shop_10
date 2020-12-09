<?php


namespace Home\Controller;

class GoodsStockController extends BaseController{
    
    //库存列表
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(M('goods_stock_info as s,tm_goods_base_info as g')->where('s.goods_code=g.goods_code')->where($where)->count(),10);
        $list = M('goods_stock_info as s,tm_goods_base_info as g')->where('s.goods_code=g.goods_code')->where($where)->field('s.*')->limit($page->firstRow.','.$page->listRows)->order('s.create_time desc')->select();
        $shops_codes = getFieldsByKey($list, 'shops_code');
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $shops_names = D('Shops')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name',true);
        $goods_statuss = D('GoodsBase')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_status',true);
        foreach($list as $k=>$stock_info){
            $stock_info['shops_name'] = $shops_names[$stock_info['shops_code']];
            $stock_info['goods_status'] = $goods_statuss[$stock_info['goods_code']];
            $list[$k] = $stock_info;
        }
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    //得到修改页面
    public function get_update_stock(){
        $stock_id = I('stock_id','','trim,htmlspecialchars');
        $info = D('GoodsStock')->where(array('stock_id'=>$stock_id))->find();
        $info['shops_name'] = D('Shops')->where(array('shops_code'=>$info['shops_code']))->getField('shops_name');
        $this->assign('info', $info);
        $contant = $this->fetch('update_stock');
        $this->oldAjaxReturn(0, $contant, 1);
    }
    //修改库存
    public function update_stock(){
        $stock_id = I('stock_id','','trim,htmlspecialchars');
        $stock_type = I('stock_type',1,'intval');
        $stock_number = I('stock_number',0,'intval');
        if($stock_number < 1) $this->oldAjaxReturn ('stock_number', '操作库存数请大于0', 0);
        $mod = D('GoodsStock');
        $where['stock_id'] = $stock_id;
        $info = $mod->where($where)->find();
        //增加
        if($stock_type == 1){
           $data['total_stocks'] = $info['total_stocks'] + $stock_number;
           $data['cur_sale_stocks'] = $info['cur_sale_stocks'] + $stock_number;
        }else{//减少
            if($info['cur_sale_stocks'] - $stock_number < 0) $this->oldAjaxReturn ('stock_number', '请重新输入操作库存数量，当前库存不够减', 0);
           $data['total_stocks'] = $info['total_stocks'] - $stock_number;
           $data['cur_sale_stocks'] = $info['cur_sale_stocks'] - $stock_number;
        }
        $data['update_time'] = date('Y-m-d H:i:s');
        $log_data = array(
            'log_id'=>(new \Org\Util\ThinkString())->uuid(),
            'stock_id'=>$info['stock_id'],
            'goods_id'=>$info['goods_id'],
            'goods_name'=>$info['goods_name'],
            'goods_code'=>$info['goods_code'],
            'create_time'=>  date('Y-m-d H:i:s'),
            'user_id'=>  session('admin_uid'),
            'handle_number'=>$stock_number,
            'stock_type'=>$stock_type,
            'shops_code'=>$info['shops_code'],
        );
        //开启事务
        $mod->startTrans();
        $save_bool = $mod->where($where)->data($data)->save();
        $log_bool = M('goods_stock_log')->data($log_data)->add();
        if($save_bool && $log_bool) {
            $mod->commit();
            $this->oldAjaxReturn(0, '操作成功', 1);
        }else{
            $mod->rollback();
            $this->oldAjaxReturn(0, '操作失败', 0);
        }
    }
    
    //修改状态
    public function change_stock_status(){
        $stock_id = I('stock_id','','trim');
        $stock_status = I('stock_status',0,'intval');
        $update_bool = D('GoodsStock')->where(array('stock_id'=>$stock_id))->data(array('stock_status'=>$stock_status,'update_time'=>date('Y-m-d H:i:s')))->save();
        if($update_bool) $this->oldAjaxReturn (0, '更新成功', 1);
        else $this->oldAjaxReturn (0, '更新失败', 0);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        $where = array('shops_status'=>array('neq',0));
        $access_shops_codes = $this->check_access_for_stock();
        if(is_array($access_shops_codes)){
            $where['shops_code'] = array('in',$access_shops_codes);
        }
        $shops_list = M('shops_base_info')->where($where)->getField('shops_code,shops_name',true);
        return array(
            'url'=>U('GoodsStock/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'商品名称/商品编码',
                    'width'=>200,
                    'tip'=>'请输入商品名称/商品编码'
                ),
                array(
                    'name'=>'seatch_stock_status',
                    'show_name'=>'状态',
                    'tip'=>'请选择状态',
                    'select'=>array(1=>'停用',2=>'启用'),
                    'type'=>'select'
                ),
                array(
                    'name'=>'seatch_goods_status',
                    'show_name'=>'商品状态',
                    'tip'=>'请选择状态',
                    'select'=>array(21=>'审核通过',30=>'上架',31=>'下架',33=>'待上架',34=>'待下架',35=>'预上架'),
                    'type'=>'select'
                ),
                array(
                    'name'=>'seatch_shops_code',
                    'show_name'=>'商家',
                    'tip'=>'请选择商家',
                    'select'=>$shops_list,
                    'type'=>'select'
                )
            ),
        );
    }
    
    private function get_where(){
        $where = array();
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['s.goods_name|s.goods_code'] = array('like','%'.$seatch.'%');
        $stock_status = I('seatch_stock_status',0,'intval');
        //库存状态
        if($stock_status) $where['s.stock_status'] = $stock_status - 1;
        $seatch_goods_status = I('seatch_goods_status',0,'intval');
        if($seatch_goods_status) $where['g.goods_status'] = $seatch_goods_status;
        $seatch_shops_code = I('seatch_shops_code','','trim,htmlspecialchars');
         //商品权限
        $access_shops_codes = $this->check_access_for_stock();
        if(is_array($access_shops_codes)){
            if($seatch_shops_code){
                if(in_array($seatch_shops_code, $access_shops_codes)) $where['s.shops_code'] = $seatch_shops_code;
                else $where['s.shops_code'] = '';
            }else $where['s.shops_code'] = array('in',$access_shops_codes); 
        }elseif($seatch_shops_code){
            $where['s.shops_code'] = $seatch_shops_code;
        }
        return $where;
    }
    
    //验证管理员操作商品的其他权限
    function check_access_for_stock() {
        if(check_admin_access('show_goods_stock_all_check',1,'other')){ //验证是否可以查看所有SKU
            return null;
        }elseif(check_admin_access('show_goods_stock_shops_check',1,'other')){ //验证是否可以查看本商家SKU
            $shops_ids = M('admin_user_relation')->where(array('uid'=>session('admin_uid'),'type'=>1))->getField('relation_id',true);
            $shops_codes = M('shops_base_info')->where(array('shops_id'=>array('in',$shops_ids)))->getField('shops_code',true);
            return $shops_codes ? $shops_codes : array();
        }
    }
}
