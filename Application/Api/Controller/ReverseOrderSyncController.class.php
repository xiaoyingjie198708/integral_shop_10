<?php


namespace Api\Controller;

class ReverseOrderSyncController extends BaseController{
    
    public function sync(){
        $this->sync_crm_reverse_order(1, 100);
        $this->sync_reverse_order_goods(1, 100);
        $this->sync_reverse_money(1, 100);
    }
    
    /**
     * 资源管理平台同步反向订单到CRM
     * @param type $reverse_order
     */
    private function sync_crm_reverse_order($pageIndex=1, $page_size=10){
        $where = $this->get_reverse_order_where();
        $count = M('reverse_order_base_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('reverse_order_base_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        foreach($list as $k=>$reverse_order){
            $result = $this->post_crm_data('sync_crm_reverse_order',$reverse_order);
            //接口错误
            if($result['code'] != 200) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',反向订单ID：'.$reverse_order['reverse_order_id'].'，同步失败,失败原因：'.$result['message']."\n", 'reverse_order-error.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_crm_reverse_order(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'reverse_order-info.log','','w'); 
            return true;
	}
    }
    
    /**
     * 资源管理平台同步反向订单到CRM
     * @param type $reverse_order
     */
    private function sync_reverse_order_goods($pageIndex=1, $page_size=10){
        $where = $this->get_reverse_order_goods_where();
        $count = M('reverse_order_goods_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('reverse_order_goods_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('create_time asc')->select();
        foreach($list as $k=>$reverse_order_goods){
            $result = $this->post_crm_data('sync_reverse_order_goods',$reverse_order_goods);
            //接口错误
            if($result['code'] != 200) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',反向订单商品ID：'.$reverse_order_goods['id'].'，同步失败,失败原因：'.$result['message']."\n", 'reverse_order_goods-error.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['create_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_reverse_order_goods(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'reverse_order_goods-info.log','','w'); 
            return true;
	}
    }
    
    /**
     * 资源管理平台同步反向订单到CRM
     * @param type $reverse_order
     */
    private function sync_reverse_money($pageIndex=1, $page_size=10){
        $where = $this->get_reverse_money_where();
        $count = M('reverse_money_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('reverse_money_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        $reverse_order_ids = getFieldsByKey($list, 'reverse_order_id');
        $reverse_orders = M('reverse_order_base_info')->where(array('reverse_order_id'=>array('in',$reverse_order_ids)))->getField('reverse_order_id,order_cur_id,member_id',true);
        foreach($list as $k=>$reverse_money){
            $result = $this->post_crm_data('sync_reverse_money',$reverse_money);
            $reverse_orderinfo = $reverse_orders[$reverse_money['reverse_order_id']];
            //状态为已退款
            if($reverse_money['reverse_status'] == 2){
                //积分
                $use_points = $reverse_money['reverse_point'];
                //调用存储过程添加积分
                if($use_points){
                    $data = array();
                    $data['member_id'] = $reverse_orderinfo['member_id'];
                    $data['point'] = intval($use_points);
                    $data['OrderId'] = $reverse_orderinfo['order_cur_id'];
                    $rs = changePoints($data);
                }
                //储值卡
                $use_money = $reverse_money['reverse_storage_money'];
                if($use_money){
                    $data = array();
                    $data['MemberId'] = $reverse_orderinfo['member_id'];
                    $data['OrderId'] = $reverse_orderinfo['order_cur_id'];
                    $data['UseBalance'] = $use_money;
                    $rs = refundTicket($data);
                }
            }
            //接口错误
            if($result['code'] != 200) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',退款ID：'.$reverse_money['id'].'，同步失败,失败原因：'.$result['message']."\n", 'reverse_money-error.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_reverse_order_goods(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'reverse_money-info.log','','w'); 
            return true;
	}
    }
    
    /*-----------------------------------------------------------受保护方法----------------------------------------------------------------*/
     private function get_reverse_order_where(){
        $where = array();
        $modifytime = \Org\My\MyLog::read_log('update_time', 'reverse_order-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_reverse_order_goods_where(){
        $where = array();
        $modifytime = \Org\My\MyLog::read_log('update_time', 'reverse_order_goods-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['create_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_reverse_money_where(){
        $where = array();
        $modifytime = \Org\My\MyLog::read_log('update_time', 'reverse_money-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
}
