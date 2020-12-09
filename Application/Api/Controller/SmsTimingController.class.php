<?php


namespace Api\Controller;

/**
 * 定时短信任务
 * @author xiao.yingjie
 */
class SmsTimingController extends BaseController{
    
    public function index(){
        $this->send_sms_tempate(1,100);
    }
    
    /**
     * 短信模板发送短信
     */
    public function send_sms_tempate($pageIndex=1, $page_size=10){
        $sms_temp_list = M('sms_template_info')->where(array('status'=>1))->select();
        $mod = new \Think\Model();
        foreach($sms_temp_list as $j=>$sms_temp){
            $log_prefix = $sms_temp['table_name'].'_'.$sms_temp['id'];
            $where = $this->get_temp_where($sms_temp);
            $count = $mod->table($sms_temp['table_name'])->where($this->get_first_where($sms_temp))->where($where)->count();
            //没有更新
            if(!$count) continue;
            $list = $mod->table($sms_temp['table_name'])->where($this->get_first_where($sms_temp))->where($where)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order($sms_temp['last_mark'].' asc')->select();
            $member_ids = getFieldsByKey($list, 'member_id');
            $member_accounts = M('user_member_base_info')->where(array('member_id'=>array('in',$member_ids)))->getField('member_id,member_account',true);
            foreach($list as $k=>$info){
                if($info['order_goods_id']) {
                    $order_goods_status = M('order_goods_info')->where(array('order_goods_id'=>$info['order_goods_id']))->getField('order_goods_status');
                    if(in_array($order_goods_status, array(13,14))) continue;
                }
                $phone = $member_accounts[$info['member_id']];
                $sms_txt = $sms_temp['sms_template'];
                if($sms_temp['placeholder']) $placeholders = explode (',', $sms_temp['placeholder']);
                foreach($placeholders as $kk=>$plac){
                    $sms_txt = str_replace($plac, $info[$plac], $sms_txt);
                }
                $send_data = array(
                    'phone'=>$phone,
                    'content'=>$sms_txt
                );
                $result = sendSms($send_data);
                //接口错误
                if($result === FALSE) {
                    \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',短信数据：'.  json_encode($send_data)."，发送短信失败\n", $log_prefix.'-error.log',date('Y-m-d'));
                }
            }
            if($sms_temp['last_mark']){
                $log_content = json_encode(array($sms_temp['last_mark']=>$info[$sms_temp['last_mark']]));
                \Org\My\MyLog::write_log($log_content, $log_prefix.'.log','','w'); 
            }
        }
    }
    
    private function get_temp_where($sms_temp){
        $where = array();
        if($sms_temp['last_mark']){
            $log_name = $sms_temp['table_name'].'_'.$sms_temp['id'].'.log';
            $last_mark = \Org\My\MyLog::read_log($sms_temp['last_mark'], $log_name);
            if(!$last_mark) $last_mark = $sms_temp['last_mark_value'];
            $where[$sms_temp['last_mark']] = array('gt',$last_mark);
        }
        return $where;
    }
    
    private function get_first_where($sms_temp){
        //暂时有mysql注入的风险 TODO
        $where_str = '';
        if($sms_temp['params']){
            $where_str = $sms_temp['params'];
//            $params = explode(',', trim($sms_temp['params'],','));
//            foreach($params as $i=>$temp_param){
//                $param  = explode('=', $temp_param);
//                $where[trim($param[0])] = trim($param[1]);
//            }
            $where_str = str_replace("&lt;", "<", $where_str);
            $where_str = str_replace("&gt;", ">", $where_str);
        }
        return $where_str;
    }
}
