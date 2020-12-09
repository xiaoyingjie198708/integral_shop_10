<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;

/**
 * 
 *产品配置参数分类模型
 * @author xiao.yingjie
 */
class ParamCategoryModel extends \Think\Model{
    
    protected $tableName = 'product_conf_param_category';
    protected $patchValidate = true;
    protected $_validate = array(
        array('category_name','require','请输入配置属性分类名称'),
        array('category_type','require','请选择分类类别'),
    );
}
