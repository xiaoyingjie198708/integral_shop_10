<?php


namespace Api\Controller;

class OrderSyncController extends BaseController{
    
    //订单同步
    public function sync(){
        $this->sync_order_info(1, 100);
        $this->sync_order_goods(1, 300);
        $this->sync_order_payment(1, 100);
        $this->sync_order_pay_discount(1, 100);
        $this->sync_order_goods_discount(1, 100);
    }
    
    private function sync_order_info($pageIndex=1, $page_size=10){
        $where = $this->get_order_where();
        $count = M('order_base_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('order_base_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        foreach($list as $k=>$order_info){
            //商品详情项
            M('goods_use_detail_info')->where(array('order_id'=>$order_info['order_id']))->data(array('order_status'=>$order_info['order_status']))->save();
            //用户删除的订单不同步
            if($order_info['order_status'] != 20){
                $result = $this->post_crm_data('sync_order_info', array($order_info));
                //接口错误
                if($result['code'] != 200) {
                    \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单ID：'.$order_info['order_cur_id'].'，同步失败,失败原因：'.$result['message']."\n", 'order-error.log',date('Y-m-d'));
                }else{
                    \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单ID：'.$order_info['order_cur_id'].'，同步成功'."\n", 'order-success.log',date('Y-m-d'));
                }
            }
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_order_info(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'order-info.log','','w'); 
            return true;
	}
    }
    
    private function sync_order_goods($pageIndex=1, $page_size=10){
        $where = $this->get_order_goods_where();
        $count = M('order_goods_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('order_goods_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        foreach($list as $k=>$order_goods_info){
            $result = $this->post_crm_data('sync_order_goods',array($order_goods_info));
            //接口错误
            if($result['code'] != 200) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单商品ID：'.$order_goods_info['order_goods_id'].'，同步失败,失败原因：'.$result['message'].', 数据：'.  json_encode(array($order_goods_info))."\n", 'order-goods-error.log',date('Y-m-d'));
            }else{
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单商品ID：'.$order_goods_info['order_goods_id'].'，同步成功'."\n", 'order-goods-success.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_order_goods(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'order-goods-info.log','','w'); 
            return true;
	}
    }
    
    private function sync_order_payment($pageIndex=1, $page_size=10){
        $where = $this->get_order_payment_where();
        $count = M('order_payment_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('order_payment_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('create_time asc')->select();
        foreach($list as $k=>$order_payment){
            $result = $this->post_crm_data('sync_order_payment', array($order_payment));
            //接口错误
            if($result['code'] != 200) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单支付ID：'.$order_payment['id'].'，同步失败,失败原因：'.$result['message']."\n", 'order-payment-error.log',date('Y-m-d'));
            }else{
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单支付ID：'.$order_payment['id'].'，同步成功'."\n", 'order-payment-success.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['create_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_order_payment(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('create_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'order-payment-info.log','','w'); 
            return true;
	}
    }
    
    private function sync_order_pay_discount($pageIndex=1, $page_size=10){
        $where = $this->get_order_pay_discount_where();
        $count = M('order_pay_discount_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('order_pay_discount_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('create_time asc')->select();
        foreach($list as $k=>$order_discount){
            $result = $this->post_crm_data('sync_order_pay_discount', array($order_discount));
            //接口错误
            if($result['code'] != 200) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单优惠ID：'.$order_discount['id'].'，同步失败,失败原因：'.$result['message']."\n", 'order-discount-error.log',date('Y-m-d'));
            }else{
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单优惠ID：'.$order_discount['id'].'，同步成功'."\n", 'order-discount-success.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['create_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_order_pay_discount(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('create_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'order-discount-info.log','','w'); 
            return true;
	}
    }
    
    private function sync_order_goods_discount($pageIndex=1, $page_size=10){
        $change_type = array(2=>1,3=>2,4=>3,5=>4);
        $where = $this->get_order_goods_discount_where();
        $count = M('order_goods_discount_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('order_goods_discount_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('create_time asc')->select();
        foreach($list as $k=>$order_discount){
            $order_discount['discount_type'] = $change_type[$order_discount['discount_type']];
            $result = $this->post_crm_data('sync_order_goods_discount', array($order_discount));
            //接口错误
            if($result['code'] != 200) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单商品优惠ID：'.$order_discount['id'].'，同步失败,失败原因：'.$result['message']."\n", 'order-goods-discount-error.log',date('Y-m-d'));
            }else{
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',订单商品优惠ID：'.$order_discount['id'].'，同步成功'."\n", 'order-goods-discount-success.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['create_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_order_goods_discount(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('create_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'order-goods-discount-info.log','','w'); 
            return true;
	}
    }
    
    private function get_order_where(){
        $where = array();
        $where['order_status'] = array('neq',20);
        $modifytime = \Org\My\MyLog::read_log('update_time', 'order-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_order_goods_where(){
        $where = array();
        $where['order_goods_status'] = array('neq',20);
        $modifytime = \Org\My\MyLog::read_log('update_time', 'order-goods-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_order_payment_where(){
        $where = array();
        $modifytime = \Org\My\MyLog::read_log('create_time', 'order-payment-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['create_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_order_pay_discount_where(){
        $where = array();
        $modifytime = \Org\My\MyLog::read_log('create_time', 'order-discount-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['create_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_order_goods_discount_where(){
        $where = array();
        $modifytime = \Org\My\MyLog::read_log('create_time', 'order-goods-discount-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['create_time'] = array('gt',$modifytime);
        return $where;
    }
}
