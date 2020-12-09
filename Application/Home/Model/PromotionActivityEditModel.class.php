<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;

/**
 * 捆绑促销活动编辑模型
 *
 * @author xiao.yingjie
 */
class PromotionActivityEditModel extends \Think\Model{
    
    protected $tableName = 'promotion_bundles_activity_info_edit';
    protected $patchValidate = true;
    protected $_validate = array(
        array('activity_name','require','请输入活动名称'),
    );
}
