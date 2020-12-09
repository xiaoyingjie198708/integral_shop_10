<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\Controller;

/**
 *
 * @author xiao.yingjie
 */
class TimeoutController extends BaseController{
    
   public function index(){
       $this->exec_sms_warn(1, 100);
       $this->exec_timing_code(1, 100);
   }
   
   /**
    * 
    * @param type $pageIndex
    * @param type $page_size
    */
   protected function exec_timing_code($pageIndex=1, $page_size=10){
        $where = $this->get_vaild_where();
        $count = M('member_use_code')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('member_use_code')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('valid_time asc')->select();
        $member_ids = getFieldsByKey($list, 'member_id');
        $member_arr = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        foreach($list as $k=>$info){
//            $valid_time = date('Y年m月d日',$info['valid_time']);
//            $sms_txt = '您的['.$info['use_code'].']消费码,有效期为'.$valid_time.',已经过期，如有疑问请咨询客服，感谢您的支持。';
//            $send_data = array(
//                'phone'=>$member_arr[$info['member_id']],
//                'content'=>$sms_txt
//            );
//            $result = sendSms($send_data);
            $result =true;
            //接口错误
            if($result === FALSE) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',短信数据：'.  json_encode($send_data)."，发送短信失败\n",'warn-error.log',date('Y-m-d'));
            }else{
                //设置已经过期
                M('member_use_code')->where(array('use_id'=>$info['use_id']))->data(array('is_valid'=>0,'status'=>40,'update_time'=> date('Y-m-d H:i:s')))->save();
            } 
        }
    }
    
    /**
     * 
     * @param type $pageIndex
     * @param type $page_size
     */
    protected function exec_sms_warn($pageIndex=1, $page_size=10){
        $where = $this->get_warn_where();
        $count = M('member_use_code')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $list = M('member_use_code')->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('valid_time asc')->select();
        $member_ids = getFieldsByKey($list, 'member_id');
        $member_arr = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
        $goods_codes = getFieldsByKey($list, 'goods_code');
        $goods_names = M('goods_base_info')->where(array('goods_code'=>array('in',$goods_codes)))->getField('goods_code,goods_name',true);
        foreach($list as $k=>$info){
            $valid_time = date('Y年m月d日',$info['valid_time']);
//            $sms_txt = '您的['.$info['use_code'].']消费码,有效期为'.$valid_time.',即将过期，请前往国家大剧院使用，感谢您的支持。';
            $sms_txt = '您购买的['.$goods_names[$info['goods_code']].']有效期至'.$valid_time.'，消费码为'.$info['use_code'].'，请尽快消费，过期自动失效。';
            $send_data = array(
                'phone'=>$member_arr[$info['member_id']],
                'content'=>$sms_txt
            );
            $result = sendSms($send_data);
            //接口错误
            if($result === FALSE) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',短信数据：'.  json_encode($send_data)."，发送短信失败\n",'warn-error.log',date('Y-m-d'));
            }else{
                M('member_use_code')->where(array('use_id'=>$info['use_id']))->data(array('is_warn'=>1,'update_time'=> date('Y-m-d H:i:s')))->save();
            }
        }
    }
    
    //警告通知
    private function get_warn_where(){
        $brfore_month = strtotime(date("Y-m-d",strtotime("-1 month")));
        //0为不限制
        $where['valid_time'] = array('between',array(100,$brfore_month));
        $where['status'] = 10;//未使用的状态
        $where['is_valid'] = 1;//有效
        $where['is_warn'] = 0;//未发送短信
        $where['valid_type'] = array('neq',3);
        return $where;
    }
    
    //设置无效
    private function get_vaild_where(){
        //有效期小于当前
//        $where['valid_time'] = array('lt',time());
        $where['valid_time'] = array('between',array(100,time()));
        $where['status'] = 10;//未使用的状态
        $where['is_valid'] = 1;//有效
//        $where['is_warn'] = 1;//已发送短信
        return $where;
    }
}
