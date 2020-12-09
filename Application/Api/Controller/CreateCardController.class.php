<?php


namespace Api\Controller;

/**
 *奔驰用车服务接口
 * @author xiao.yingjie
 */
class CreateCardController extends BaseController{
    
    
    public function update_status(){
        if(IS_POST){
            $token = I('token','','trim,htmlspecialchars');
            $base_data =  file_get_contents('php://input');
            if(!$base_data) $this->customAjaxReturn (501, FALSE, '没有任何数据');
            if(!$this->check_token($token, $base_data)) $this->customAjaxReturn (501,FALSE,'token验证失败');
            $data = json_decode($base_data, true);
            if(!$data['cardNum']) $this->customAjaxReturn (501, FALSE, '卡号不能为空');
            if(!in_array($data['status'], array(1,2,3))) $this->customAjaxReturn (501, FALSE, '状态不合法');
            if($data['validTime'] && (strtotime($data['validTime']) < time())) $this->customAjaxReturn (501, FALSE, '有效期截至日期必须大于当前时间');
            $mod = M('other_use_code');
            $code_info = $mod->where(array('use_code'=>$data['cardNum']))->find();
            if(!$code_info) $this->customAjaxReturn (502, FALSE, '卡号不存在');
            $status = $data['status'] + 1;
            $save_data['status'] = $status;
            if($data['valid_time']) $save_data['valid_time'] = $data['valid_time'];
            if($data['updateTime']) $save_data['update_time'] = $data['updateTime'];
            else $save_data['update_time'] = date('Y-m-d H:i:s');
            $save_bool = $mod->where(array('use_code'=>$data['cardNum']))->data($save_data)->save();
            if($save_bool) $this->customAjaxReturn (200, TRUE, '更新成功');
            else $this->customAjaxReturn (500, FALSE, '更新失败');
        }
        $this->customAjaxReturn(400, FALSE, '只支持POST');
    }
    
    
}
