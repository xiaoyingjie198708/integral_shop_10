<?php


namespace Api\Controller;

class CmsSyncController extends BaseController{
    
    public function sync(){
        $this->sync_goods_sale(1, 100);
    }
    
    /**
     * 同步CRM商品
     * @param type $pageIndex
     * @param type $page_size
     * @return boolean
     */
    private function sync_goods_sale($pageIndex=1, $page_size=10){
        $where = $this->get_goods_where();
        $count = M('goods_base_info')->where($where)->count();
        //没有更新
        if(!$count) return false;
        $fields = 'goods_id,goods_name,goods_status,max_integral,goods_type,goods_category_code,relation_goods,ext_code,goods_code,goods_media_price,update_time,integral,goods_price_pc,promotion_content_pc,sale_type,goods_materiel_code';
        $list = M('goods_base_info')->where($where)->field($fields)->limit(($pageIndex -1) * $page_size  . ','.$page_size)->order('update_time asc')->select();
        foreach($list as $k=>$goods_info){
            $result = $this->sync_cms_goods_sale($goods_info);
            //接口错误
            if($result === FALSE) {
                \Org\My\MyLog::write_log('执行时间：'.  date('Y-m-d H:i:s').',商品Code：'.$goods_info['goods_code'].'，同步失败'."\n", 'cms_goods-error.log',date('Y-m-d'));
            }
        }
        $last_date = $list[count($list) - 1]['update_time'];
        $page_count = ceil($count/$page_size); //计算共多少页
	if(($page_count > $pageIndex) && ($pageIndex * $page_size < 600))  {
            $this->sync_goods_info(++$pageIndex, $page_size);
	}
	else{
            $log_content = json_encode(array('update_time'=>$last_date));
            \Org\My\MyLog::write_log($log_content, 'cms_goods-info.log','','w'); 
            return true;
	}
    }
    
    
    /*-----------------------------------------------------------------------受保护方法--------------------------------------------------------------*/
    private function get_goods_where(){
        $where = array();
        $modifytime = \Org\My\MyLog::read_log('update_time', 'cms_goods-info.log');
        if(!$modifytime) $modifytime = '2017-01-01 00:00:00';
        $where['update_time'] = array('gt',$modifytime);
        return $where;
    }
    
    
    /**
    * 同步商品的销售信息到CMS
    * @param type $goods_base_info
    * @return type
    */
   private function sync_cms_goods_sale($goods_base_info){
       $promotion_type = 0;
       $promotion_list = '';
       $detail_addr = C('PC_HOST_URL').'/mall/detail/goods_code/'.$goods_base_info['goods_code'].'.html';
       $mobile_detail_addr = C('MOBILE_HOST_URL').'/mall/detail/goods_code/'.$goods_base_info['goods_code'].'.html';
       $sale_number = 0;
       if($goods_base_info['goods_status'] == 30) $goods_status = 11;
       else $goods_status = 10;
       $sync_goods_id = $goods_base_info['goods_code'];
       //显示的价格
       if($goods_base_info['integral']){
            $show_integral = $goods_base_info['max_integral'];
       } 
       $cms_data = array();
       $cms_data['materiel_code'] = $goods_base_info['goods_materiel_code'];
       $cms_data['goods_media_price'] = $goods_base_info['goods_media_price'];
       $cms_data['goods_sale_price'] = $goods_base_info['goods_price_pc'];
       $cms_data['is_integral'] = $goods_base_info['integral'];
       $cms_data['max_integral'] = $goods_base_info['max_integral'];
       $cms_data['show_price'] = $goods_base_info['goods_price_pc'];
       $cms_data['show_integral'] = $show_integral ? $show_integral : 0;
       $cms_data['promotion_content'] = $goods_base_info['promotion_content_pc'];
       $cms_data['promotion_type'] = $promotion_type;
       $cms_data['promotion_list'] = $promotion_list;
       $cms_data['sale_type'] = $goods_base_info['sale_type'];
       $cms_data['detail_addr'] = $detail_addr;
       $cms_data['sale_number'] = $sale_number;
       $cms_data['goods_status'] = $goods_status;
       $cms_data['update_time'] = $goods_base_info['update_time'];
       $cms_goods_info = M('cms_goods_info')->where(array('sync_goods_id'=>$sync_goods_id))->find();
       //存在并更新
       if($cms_goods_info){
           $save_bool = M('cms_goods_info')->where(array('sync_goods_id'=>$sync_goods_id))->data($cms_data)->save();
       }else{
           $cms_data['sync_goods_id'] = $sync_goods_id;
           $save_bool = M('cms_goods_info')->data($cms_data)->add();
       }
       return $save_bool;
   }
}
