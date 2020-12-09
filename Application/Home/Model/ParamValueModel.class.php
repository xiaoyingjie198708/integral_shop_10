<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;

/**
 *配置参数值模型
 * @author xiao.yingjie
 */
class ParamValueModel extends \Think\Model{
    
    protected $tableName = 'product_conf_param_value';
    protected $patchValidate = true;
    protected $_validate = array(
        array('param_value','require','请输入参数值'),
    );
}
