<?php


namespace Api\Controller;

/**
 * 短信服务
 *
 * @author xiao.yingjie
 */
class SendSMSController extends BaseController{
    
    public function sync(){
        $this->member_use_code(1, 100);
        $this->member_appointment(1,100);
    }
    
    public function send_timming_sms(){
        $this->member_use_code2(1,100);
    }
    /**
     * 短信提醒，提货码和消费码
     */
    private function member_use_code($pageIndex=1, $page_size=10){
        $params = array(1=>'消费',2=>'消费');
        $where = $this->get_use_code_where();
        $count = M('member_use_code')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('member_use_code')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        //会员信息
        $member_ids = getFieldsByKey($list, 'member_id');
        $member_arr = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        //商品信息
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_arr = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,service_type,shops_code,goods_name',true);
        //店铺信息
        $shops_codes = getFieldsByKey($goods_arr, 'shops_code');
        $shops_arr = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name,shop_tel',true);
        foreach($list as $k=>$info){
            $member_phone = $member_arr[$info['member_id']];
            $goods_info = $goods_arr[$info['goods_code']];
            $shops_info = $shops_arr[$goods_info['shops_code']];
            //删除的消费码，不发送任何短信
            if($info['status'] == 0)                continue;
            //未使用
            if($info['status'] == 10){
                $valid_time = date('Y年m月d日',$info['valid_time']);
                //预约服务商品
                if($goods_info['service_type']){
//                    $sms_txt = '您的订单已支付成功，['.$goods_info['goods_name'].']'.$params[$info['code_type']].'码为'.$info['use_code'].',有效期为'.$valid_time.',请提前一天预约'.$params[$info['code_type']].'，到店请出示'.$params[$info['code_type']].'码。预约电话：'.$shops_info['shop_tel'];
                    $sms_txt = '您购买的['.$goods_info['goods_name'].']有效期为'.$valid_time.','.$params[$info['code_type']].'码为'.$info['use_code'].',请尽快消费，过期自动失效,请提前一天预约'.$params[$info['code_type']].'，到店请出示'.$params[$info['code_type']].'码。预约电话：'.$shops_info['shop_tel'];
                }else{
//                    $sms_txt = '您的订单已支付成功，['.$goods_info['goods_name'].']'.$params[$info['code_type']].'码为'.$info['use_code'].',有效期为'.$valid_time.',请到店消费时出示本'.$params[$info['code_type']].'码。';
                      $sms_txt = '您购买的['.$goods_info['goods_name'].']有效期为'.$valid_time.','.$params[$info['code_type']].'码为'.$info['use_code'].',请尽快消费，过期自动失效。';
                }  
            }
            //使用
            if($info['status'] == 20){
                $sms_txt = '您编号为'.$info['use_code'].'的'.$params[$info['code_type']].'码,已于'.$info['update_time'].''.$params[$info['code_type']].'完成，感谢您的惠顾。';
            }
            //冻结
            if($info['status'] == 30){
                $sms_txt = '您的['.$goods_info['goods_name'].']'.$params[$info['code_type']].'码,已于'.$info['update_time'].''.$params[$info['code_type']].'失效，感谢您的惠顾。';
            }
            $send_data = array(
                'phone'=>$member_phone,
                'content'=>$sms_txt
            );
            $result = sendSms($send_data);
            //接口错误
            if(!$result) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',使用码ID：'.$info['use_id']."，发送短信失败\n", 'use_code-error.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->member_use_code(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'use_code-info.log','','w'); 
            return true;
	}
    }
    
    /**
     * 短信提醒，提货码和消费码
     */
    private function member_appointment($pageIndex=1, $page_size=10){
        $where = $this->get_member_app_where();
        $count = M('member_appointment')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('member_appointment')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('create_time asc')->select();
        foreach($list as $k=>$info){
            $sms_txt = '您已成功预约，请于'.$info['app_time'].'到店消费，如需取消或更改预约，请提前致电。';
            $send_data = array(
                'phone'=>$info['member_mobile'],
                'content'=>$sms_txt
            );
            $result = sendSms($send_data);
            //接口错误
            if(!$result) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',使用码ID：'.$info['use_id']."，发送短信失败\n", 'member_app-error.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['create_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->member_appointment(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('create_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'member_app-info.log','','w'); 
            return true;
	}
    }
    
    private function get_use_code_where(){
        $where = array();
        $where['status'] = array('gt',0);
        $where['buy_type'] = 1;
        $modifytime = \Org\My\MyLog::read_log('update_time', 'use_code-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    private function get_member_app_where(){
        $where = array();
        $where['app_status'] = array('gt',0);
        $modifytime = \Org\My\MyLog::read_log('create_time', 'member_app-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['create_time'] = array('gt',$modifytime);
        return $where;
    }
    
    /**
     * 短信提醒，提货码和消费码
     */
    private function member_use_code2($pageIndex=1, $page_size=10){
        $params = array(1=>'消费',2=>'消费');
        $where = $this->get_use_code_where2();
        $count = M('member_use_code')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('member_use_code')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        //会员信息
        $member_ids = getFieldsByKey($list, 'member_id');
        $member_arr = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        //商品信息
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_arr = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,service_type,shops_code,goods_name',true);
        //店铺信息
        $shops_codes = getFieldsByKey($goods_arr, 'shops_code');
        $shops_arr = M('shops_base_info')->where(array('shops_code'=>array('in',$shops_codes)))->getField('shops_code,shops_name,shop_tel',true);
        foreach($list as $k=>$info){
            $member_phone = $member_arr[$info['member_id']];
            $goods_info = $goods_arr[$info['goods_code']];
            $shops_info = $shops_arr[$goods_info['shops_code']];
            //删除的消费码，不发送任何短信
            if($info['status'] == 0)                continue;
            //未使用
            if($info['status'] == 10){
                $valid_time = date('Y年m月d日',$info['valid_time']);
                //预约服务商品
                if($goods_info['service_type']){
//                    $sms_txt = '您的订单已支付成功，['.$goods_info['goods_name'].']'.$params[$info['code_type']].'码为'.$info['use_code'].',有效期为'.$valid_time.',请提前一天预约'.$params[$info['code_type']].'，到店请出示'.$params[$info['code_type']].'码。预约电话：'.$shops_info['shop_tel'];
                    $sms_txt = '您购买的['.$goods_info['goods_name'].']有效期为'.$valid_time.','.$params[$info['code_type']].'码为'.$info['use_code'].',请尽快消费，过期自动失效,请提前一天预约'.$params[$info['code_type']].'，到店请出示'.$params[$info['code_type']].'码。预约电话：'.$shops_info['shop_tel'];
                }else{
//                    $sms_txt = '您的订单已支付成功，['.$goods_info['goods_name'].']'.$params[$info['code_type']].'码为'.$info['use_code'].',有效期为'.$valid_time.',请到店消费时出示本'.$params[$info['code_type']].'码。';
                      $sms_txt = '您购买的['.$goods_info['goods_name'].']有效期为'.$valid_time.','.$params[$info['code_type']].'码为'.$info['use_code'].',请尽快消费，过期自动失效。';
                }  
            }
            //使用
            if($info['status'] == 20){
                $sms_txt = '您编号为'.$info['use_code'].'的'.$params[$info['code_type']].'码,已于'.$info['update_time'].''.$params[$info['code_type']].'完成，感谢您的惠顾。';
            }
            //冻结
            if($info['status'] == 30){
                $sms_txt = '您的['.$goods_info['goods_name'].']'.$params[$info['code_type']].'码,已于'.$info['update_time'].''.$params[$info['code_type']].'失效，感谢您的惠顾。';
            }
            $send_data = array(
                'phone'=>$member_phone,
                'content'=>$sms_txt
            );
            $result = sendSms($send_data);
            //接口错误
            if(!$result) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',使用码ID：'.$info['use_id']."，发送短信失败\n", 'use_code-error2.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->member_use_code2(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'use_code-info2.log','','w'); 
            return true;
	}
    }
    
    private function get_use_code_where2(){
        $where = array();
        $where['status'] = array('gt',0);
        $where['buy_type'] = 2;
        $modifytime = \Org\My\MyLog::read_log('update_time', 'use_code-info2.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
}
