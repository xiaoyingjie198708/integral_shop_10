<?php


namespace Api\Controller;

class CrmSyncController extends BaseController{
    
    public function sync(){
        $this->sync_goods_info(1, 100);
        $this->sync_coupon_user(1, 100);
        $this->sync_crm_member_use_code(1, 100);
        $this->sync_coupon_info(1,100);
    }
    
    /**
     * 同步CRM商品
     * @param type $pageIndex
     * @param type $page_size
     * @return boolean
     */
    private function sync_goods_info($pageIndex=1, $page_size=10){
        $where = $this->get_goods_where();
        $count = M('goods_base_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $fields = 'goods_id,goods_name,goods_status,max_integral,goods_type,goods_category_code,relation_goods,ext_code,goods_code,create_time,update_time';
        $list = M('goods_base_info')->where($where)->field($fields)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        foreach($list as $k=>$goods_info){
            $result = $this->post_crm_data('sync_goods_info',array($goods_info));
            //接口错误
            if($result['code'] != 200) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',商品Code：'.$goods_info['goods_code'].'，同步失败,失败原因：'.$result['message'].'失败数据：'.  json_encode(array($goods_info))."\n", 'goods-error.log',date('Y-m-d'));
            }else{
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',商品Code：'.$goods_info['goods_code'].'，同步成功'."\n", 'goods-success.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_goods_info(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'goods-info.log','','w'); 
            return true;
	}
    }
    
    /**
     * 同步CRM商品
     * @param type $pageIndex
     * @param type $page_size
     * @return boolean
     */
    private function sync_coupon_user($pageIndex=1, $page_size=10){
        $where = $this->get_coupon_user_where();
        $count = M('coupon_user_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('coupon_user_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        foreach($list as $k=>$coupon_user_info){
            $result = $this->post_crm_data('sync_coupon_user',array($coupon_user_info));
            //接口错误
            if($result['code'] != 200) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',优惠券用户ID：'.$coupon_user_info['coupon_user_id'].'，同步失败,失败原因：'.$result['message']."\n", 'coupon-user-error.log',date('Y-m-d'));
            }else{
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',优惠券用户ID：'.$coupon_user_info['coupon_user_id'].'，同步成功'."\n", 'coupon-user-success.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_coupon_user(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'coupon-user-info.log','','w'); 
            return true;
	}
    }
    
    private function sync_crm_member_use_code($pageIndex=1, $page_size=10){
        $change_code = array(0=>0,10=>1,20=>2);
        $where = $this->get_member_use_code_where();
        $count = M('member_use_code')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('member_use_code')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        foreach($list as $k=>$use_code){
            $use_code['status'] = $change_code[$use_code['status']];
            $result = $this->post_crm_data('sync_crm_member_use_code',$use_code);
            //接口错误
            if($result['code'] != 200) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',消费码：'.$use_code['use_code'].'，同步失败,失败原因：'.$result['message']."\n", 'member_use_code-error.log',date('Y-m-d'));
            }else{
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',消费码：'.$use_code['use_code'].'，同步成功'."\n", 'member_use_code-success.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_crm_member_use_code(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'member_use_code-info.log','','w'); 
            return true;
	}
    }
    
    /**
     * 同步CRM商品
     * @param type $pageIndex
     * @param type $page_size
     * @return boolean
     */
    private function sync_coupon_info($pageIndex=1, $page_size=10){
        $where = $this->get_coupon_info_where();
        $count = M('coupon_base_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('coupon_base_info')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        foreach($list as $k=>$coupon_info){
            $result = $this->post_crm_data('sync_coupon_info',$coupon_info);
            //接口错误
            if($result['code'] != 200) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',优惠券：'.$coupon_info['coupon_code'].'，同步失败,失败原因：'.$result['message'].',失败数据: '.  json_encode($coupon_info)."\n", 'coupon_info-error.log',date('Y-m-d'));
            }else{
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',优惠券：'.$coupon_info['coupon_code'].'，同步成功'."\n", 'coupon_info-success.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_coupon_info(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'coupon_info-info.log','','w'); 
            return true;
	}
    }
    /*-----------------------------------------------------------受保护方法----------------------------------------------------------------*/
    private function get_goods_where(){
        $where = array();
        $modifytime = \Org\My\MyLog::read_log('update_time', 'goods-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_coupon_user_where(){
        $where = array();
        $modifytime = \Org\My\MyLog::read_log('update_time', 'coupon-user-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_member_use_code_where(){
        $where = array();
        $modifytime = \Org\My\MyLog::read_log('update_time', 'member_use_code-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_coupon_info_where(){
        $where = array();
        $modifytime = \Org\My\MyLog::read_log('update_time', 'coupon_info-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
}
