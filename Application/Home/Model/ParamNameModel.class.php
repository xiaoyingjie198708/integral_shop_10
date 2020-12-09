<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;

/**
 * 配置参数名模型
 * @author xiao.yingjie
 */
class ParamNameModel extends \Think\Model{
    
    protected $tableName = 'product_conf_param_name';
    protected $patchValidate = true;
    protected $_validate = array(
        array('param_name','require','请输入配置参数名称'),
    );
}
