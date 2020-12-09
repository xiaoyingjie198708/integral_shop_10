<?php


namespace Home\Controller;

/**
 * 商家结算
 *
 * @author xiao.yingjie
 */
class SettlementController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(M('member_use_code')->where($where)->count(),10);
        $list = M('member_use_code')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $order_ids = getFieldsByKey($list, 'order_id');
        $order_cur_ids = D('Order')->where(array('order_id'=>array('in',$order_ids)))->getField('order_id,order_cur_id,member_id',true);
        $order_goods_ids = getFieldsByKey($list, 'order_goods_id');
        $order_goods_names = M('order_goods_info')->where(array('order_goods_id'=>array('in',$order_goods_ids)))->getField('order_goods_id,goods_name,goods_price,integral,max_integral',true);
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_lists = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_name,goods_price_pc,integral,max_integral',true);
        $member_ids = getFieldsByKey($list, 'member_id');
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        foreach($list as $k=>$code_info){
            if($code_info['order_goods_id']){
                $order_info = $order_cur_ids[$code_info['order_id']];
                $order_goods = $order_goods_names[$code_info['order_goods_id']];
            }else{
                 $order_goods = $goods_lists[$code_info['goods_code']];
                 $order_goods['goods_price'] = $order_goods['goods_price_pc'];
                 $order_info['order_cur_id'] = $code_info['order_id'];
            }
            if($order_goods['integral']){
                if($order_goods['goods_price'] && ($order_goods['max_integral'] >0))  $goods_price = '￥'.$order_goods['goods_price'] .' + ' .$order_goods['max_integral'].'积分';
                if(!$order_goods['goods_price'] && $order_goods['max_integral']) $goods_price = $order_goods['max_integral'].'积分';
                if(($order_goods['goods_price']>0) && !$order_goods['max_integral']) $goods_price = '￥'.$order_goods['goods_price'];
            }else{
                $goods_price = '￥'.$order_goods['goods_price'];
            }
            $list[$k]['order_cur_id'] = $order_info['order_cur_id'];
            $list[$k]['goods_name'] = $order_goods['goods_name'];
            $list[$k]['goods_price'] = $goods_price;
            $list[$k]['member_account'] = $member_accounts[$code_info['member_id']];
        }
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    //修改消费码的状态
    public function update_status(){
        $use_id = I('use_id','','trim,htmlspecialchars');
//        $where['code_type'] = 1;
        $where['use_id'] = $use_id;
        $info = M('member_use_code')->where($where)->find();
        if($info['status'] != 10) $this->oldAjaxReturn (0, '不是未使用状态，不能消费', 0);
        $save_bool = M('member_use_code')->where($where)->data(array('status'=>20,'update_time'=>date('Y-m-d H:i:s')))->save();
        if($save_bool) $this->oldAjaxReturn (0, '消费成功', 1);
        else $this->oldAjaxReturn (0, '消费失败', 0);
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('Settlement/index'),
            'main'=>array(
                array(
                    'name'=>'seatch',
                    'show_name'=>'消费码',
                    'width'=>250,
                    'tip'=>'请输入消费码'
                ),
            ),
        );
    }
    
    private function get_where(){
        $where = array();
        $seatch = I('seatch','','trim,htmlspecialchars');
        if($seatch) $where['use_code'] = $seatch;
        else $where['use_code'] = '';
        //消费码
//        $where['code_type'] = 1;
        return $where;
    }
    
}
