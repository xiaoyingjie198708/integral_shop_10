<?php


namespace Home\Controller;

/**
 *会员管理列表
 * @author xiao.yingjie
 */
class MemberController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(M('user_member_base_info')->where($where)->count(),10);
        $list = M('user_member_base_info')->where($where)->limit($page->firstRow.','.$page->listRows)->order('update_time desc')->select();
        $this->search($this->get_search());
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }
    
    //会员详情
    public function info(){
        $member_id = I('member_id','','trim,htmlspecialchars');
        $where['member_id'] = $member_id;
        $info = M('user_member_base_info')->where($where)->find();
        $this->assign('info', $info);
        //优惠券
        $coupon_list = M('coupon_user_info')->where($where)->select();
        if($coupon_list){
            $coupon_ids = getFieldsByKey($coupon_list, 'coupon_id');
            $coupon_names = D('Coupon')->where(array('coupon_id'=>array('in',$coupon_ids)))->getField('coupon_id,coupon_name',true);
            foreach($coupon_list as $k=>$coupon_info) $coupon_list[$k]['coupon_name'] = $coupon_names[$coupon_info['coupon_id']];
        }
        $this->assign('coupon_list', $coupon_list);
        //消费码
        $code_list = M('member_use_code')->where($where)->order('update_time desc')->select();
        if($code_list){
            $order_ids = getFieldsByKey($code_list, 'order_id');
            $order_cur_ids = D('Order')->where(array('order_id'=>array('in',$order_ids)))->getField('order_id,order_cur_id',true);
            $goods_codes = getFieldsByKey($code_list, 'goods_code');
            $order_goods_names = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_name',true);
            foreach($code_list as $k=>$code_info){
                $code_list[$k]['order_cur_id'] = $order_cur_ids[$code_info['order_id']];
                $code_list[$k]['goods_name'] = $order_goods_names[$code_info['goods_code']];
            }
        }
        $this->assign('code_list', $code_list);
        $this->assign('member_label', array(1=>'会员基本信息',2=>'会员优惠券',3=>'会员消费码'));
        $this->display();
    }
    /*--------------------------------------------------受保护方法---------------------------------------------*/
    private function get_search(){
        return array(
            'url'=>U('Member/index'),
            'main'=>array(
                array(
                    'name'=>'search',
                    'show_name'=>'会员账号',
                    'width'=>250,
                    'tip'=>'请输入会员账号'
                )
            ),
//            'other'=>array(
//                array(
//                    'name' => array('create_start', 'create_end'),
//                    'show_name' => '预约时间',
//                    'tip' => array('请选择开始时间', '请选择结束时间'),
//                    'type' => 'date'
//                )
//            )
        );
    }
    
    private function get_where(){
        $where = array();
        $search = I('search','','trim,htmlspecialchars');
        if($search) $where['member_account'] = array('like','%'.$search.'%');
        return $where;
    }
}
