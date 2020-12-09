<?php


namespace Home\Controller;

/**
 *活动管理
 * @author xiao.yingjie
 */
class ActivityController extends BaseController{
    
    public function index(){
        $where = $this->get_where();
        $page = new \Org\My\Page(M('activity')->where($where)->count(), 10);
        $list = M('activity')->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('update_time desc')->select();
        foreach($list as $k=>$info){
            $list[$k]['activity_url'] = C('MOBILE_HOST_URL').'Activity/hindex/id/'.$info['activity_id'].'.html';
        }
        $this->search($this->get_search());
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->display();
    }
    
    public function add(){
        if(IS_AJAX){
            $mod = D('Activity');
            if(!$mod->create()) {
                $error = each($mod->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $mod->activity_id = (new \Org\Util\ThinkString())->uuid();
                $mod->create_time = date('Y-m-d H:i:s');
                $mod->update_time = date('Y-m-d H:i:s');
//                if($mod->activity_type == 1 && !$mod->yl_url) $this->oldAjaxReturn ('yl_url', '请输入永乐注册URL', 0);
                if($mod->activity_end_date < $mod->activity_start_date) $this->oldAjaxReturn ('activity_end_date', '选择的活动结束时间必须大于开始时间', 0);
                $add_bool = $mod->add();
                if($add_bool) $this->oldAjaxReturn (0, '添加成功', 1);
                else $this->oldAjaxReturn (0, '添加失败', 0);
            }
        }
        $this->display();
    }
    
    //获取问题列表
    public function getQuestionList() {
        $search = I('search','','htmlspecialchars');
        $question = D('ActivityQuestion');
        $where = array();
        if($search) $where['title'] = array('like','%'.$search.'%');
        $Page = new \Think\Page($question->where($where)->count(),10);
        $data = $question->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        $data = $this->formart($data);
        $search_list = array(
            'url'=>U('Activity/getQuestionList'),
			'ajax'=>'true',
			'method'=>'get',
			'callback'=>'ajax_search',			
            'main'=>array(
                array(
                    'show_name'=>'问题',
                    'name'=>'search',
                    'tip'=>'问题'
                )
            )
        );
        $this->search($search_list);		
        $this->assign('data',$data);
        $this->assign('page',$Page->show());
        $content = $this->fetch('question_page');
        $this->oldAjaxReturn(0,$content,1);
    }
    
    public function update(){
        $mod = D('Activity');
        $activity_id = I('activity_id','','trim,htmlspecialchars');
        if(IS_AJAX){
            if(!$mod->create()) {
                $error = each($mod->getError());
                $this->oldAjaxReturn($error['key'],$error['value'],0);
            }else{
                $mod->update_time = date('Y-m-d H:i:s');
//                if($mod->activity_type == 1 && !$mod->yl_url) $this->oldAjaxReturn ('yl_url', '请输入永乐注册URL', 0);
                if($mod->activity_end_date < $mod->activity_start_date) $this->oldAjaxReturn ('activity_end_date', '选择的活动结束时间必须大于开始时间', 0);
                $add_bool = $mod->where(array('activity_id'=>$activity_id))->save();
                if($add_bool) $this->oldAjaxReturn (0, '修改成功', 1);
                else $this->oldAjaxReturn (0, '修改失败', 0);
            }
        }
        $info = $mod->where(array('activity_id'=>$activity_id))->find();
        $question_list = M('activity_question')->where(array('id'=>array('in',$info['input_pream'])))->select();
        $this->assign('info', $info);
        $this->assign('question_list', $question_list);
        $this->display();
    }
    
    public function change_status(){
        $id = I('id','','trim,htmlspecialchars');
        $status = I('status',0,'intval');
        $update_bool = D('Activity')->where(array('activity_id'=>$id))->data(array('activity_status'=>$status,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($update_bool === FALSE) $this->oldAjaxReturn (0, '修改失败', 0);
        else $this->oldAjaxReturn (0, '修改成功', 1);
    }
    
    public function del(){
        $id = I('id','','trim,htmlspecialchars');
        $update_bool = D('Activity')->where(array('activity_id'=>$id))->data(array('activity_status'=>0,'update_time'=>  date('Y-m-d H:i:s')))->save();
        if($update_bool === FALSE) $this->oldAjaxReturn (0, '删除失败', 0);
        else $this->oldAjaxReturn (0, '删除成功', 1);
    }
    /*----------------------------------------------------受保护方法----------------------------------------------------------*/
    private function get_where(){
        $where = array();
        $where['activity_status'] = array('gt',0);
        $search = I('search','','trim,htmlspecialchars');
        if($search) $where['activity_name'] = array('like','%'.$search.'%');
        return $where;
    }
    
    private function get_search(){
        return array(
            'url' => U('Activity/index'),
            'main' => array(
                array(
                    'name' => 'search',
                    'show_name' => '活动名称',
                    'tip' => '请输入活动名称'
                ),
            ),
        );
    }
    
    //格式化
    private function formart($data) {
        if(!is_array($data)) return false;
        $type_arr = array('文本');
        for($i=0;$i<count($data);$i++) {
            $data[$i]['answer'] = M('question_answer')->where(array('question_id'=>$data[$i]['id']))->getField('content',true);
            $data[$i]['answer_type'] = $type_arr[($data[$i]['answer_type']-1)];
        }
        return $data;
    }
}
