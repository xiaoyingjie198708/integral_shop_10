<?php


namespace Home\Controller;

/**
 * 用户详情
 *
 * @author xiao.yingjie
 */
class MemberDetailController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $order_goods_list = M('order_goods_info b')->join('tm_order_base_info a on a.order_id=b.order_id','LEFT')->where($where)->field('a.member_id,b.goods_code,b.goods_number')->select();
        $temp_list = $member_ids =  array();
        foreach($order_goods_list as $k=>$info){
            $price_num = $temp_list[$info['member_id']];
            $temp_num = 0;
            if($info['goods_code'] == '1090'){//500
                $temp_num = 500 * $info['goods_number'];
            }
            if($info['goods_code'] == '1093' || $info['goods_code'] == '1079'){//200
                $temp_num = 200 * $info['goods_number'];
            }
            if(!$price_num) $price_num = $temp_num;
            else $price_num += $temp_num;
            $temp_list[$info['member_id']] = $price_num;
            $member_ids[] = $info['member_id'];
        }
        unset($order_goods_list);
        $member_ids = array_unique($member_ids);
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        foreach($temp_list as $member_id=>$price_num){
            $show_info['member_id'] = $member_id;
            $show_info['member_account'] = $member_accounts[$member_id];
            $show_info['price_num'] = $price_num;
            $show_list[] = $show_info;
        }
        $this->search($this->get_search());
        $this->assign('list', $show_list);
        $this->display();
    }
    
    public function info(){
        $where = $this->get_where();
        $order_goods_list = M('order_goods_info b')->join('tm_order_base_info a on a.order_id=b.order_id','LEFT')->where($where)->field('a.create_time,a.order_cur_id,a.member_id,b.goods_code,b.goods_name,b.goods_number,b.order_goods_status')->select();
        foreach($order_goods_list as $k=>$info){
            $temp_num = 0;
            if($info['goods_code'] == '1090'){//500
                $temp_num = 500 * $info['goods_number'];
            }
            if($info['goods_code'] == '1093' || $info['goods_code'] == '1079'){//200
                $temp_num = 200 * $info['goods_number'];
            }
            $info['price_num'] = $temp_num;
            $order_goods_list[$k] = $info;
        }
        $this->assign('list', $order_goods_list);
        $this->display();
    }
    
    public function export(){
        $limit = 50000;
        $export_model = new \Org\My\Export('order_base_info');
        $export_model->limit = $limit;
        $where = $this->get_where();
        $order_goods_list = M('order_goods_info b')->join('tm_order_base_info a on a.order_id=b.order_id','LEFT')->where($where)->field('a.member_id,b.goods_code,b.goods_number')->select();
        $temp_list = $member_ids =  array();
        foreach($order_goods_list as $k=>$info){
            $price_num = $temp_list[$info['member_id']];
            $temp_num = 0;
            if($info['goods_code'] == '1090'){//500
                $temp_num = 500 * $info['goods_number'];
            }
            if($info['goods_code'] == '1093' || $info['goods_code'] == '1079'){//200
                $temp_num = 200 * $info['goods_number'];
            }
            if(!$price_num) $price_num = $temp_num;
            else $price_num += $temp_num;
            $temp_list[$info['member_id']] = $price_num;
            $member_ids[] = $info['member_id'];
        }
        unset($order_goods_list);
        $member_ids = array_unique($member_ids);
        $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        foreach($temp_list as $member_id=>$price_num){
            $show_info['member_id'] = $member_id;
            $show_info['member_account'] = $member_accounts[$member_id];
            $show_info['price_num'] = $price_num;
            $show_list[] = $show_info;
        }
        $count = count($order_goods_list);
        $export_model->setCount($count);
        //导出的模版
        $export_fields = array('member_account'=>'用户账号','price_num'=>'储值卡金额');
        //导出的数据
        $export_model->title = '用户储值卡数据';
        $export_model->execl_fields = $export_fields;
        $export_model->setData($show_list);
        $export_page = $export_model->export();
        exit($export_page);
    }

    private function get_where(){
        $goods_arr = array('1090','1093','1079');
        $where['b.goods_code'] = array('in',$goods_arr);
        $where['b.order_goods_status'] = array('in',array(4,7,8));
        $search = I('search','','trim,htmlspecialchars');
        if($search) {
             $member_ids = M('user_member_base_info')->where(array('member_account'=>array('like','%'.$search.'%')))->getField('member_id',true);
             $where['a.member_id'] = array('in',$member_ids);
        }
        return $where;
    }
    
    protected function get_search(){
        return array(
            'url' => U('MemberDetail/index'),
            'button'=>array(
                '<a class="btn btn-success"  id="explode_order" data-url="'.U('MemberDetail/export').'"><i class="icon-download icon-white"></i> 导出</a>',
            ),
            'main' => array(
                array(
                    'name' => 'search',
                    'show_name' => '会员账号',
                    'tip' => '请输入会员账号'
                ),
            )
        );
    }
    
}
