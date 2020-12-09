<?php
return array(
    'LOAD_EXT_CONFIG' => 'host,db,other,app', //加载扩展配置文件
    'URL_MODEL' => 2,//URL模式 REWRITE模式
    'URL_HTML_SUFFIX'=>'.html', //伪静态后缀
    'URL_CASE_INSENSITIVE' =>true, //忽略大小写
    'REQUEST_VARS_FILTER'=>true, //参数过滤
    'LAYOUT_ON' => true, //开启布局模式
    'SHOW_PAGE_TRACE'=>FALSE, //开启trace信息
    'MODULE_ALLOW_LIST'=>array('Home','Api'),  //可访问模块
    'DEFAULT_MODULE'=>'Home',   //默认模块
    'APP_TITLE' => '国家大剧院资源管理平台系统', //项目标题
    'DEFAULT_ADMIN_GROUP'=>1, //默认超级管理员分组ID
    'DEFAULT_ADMIN_ID'=>1, //默认超级管理员ID
    'DEFAULT_ADMIN_PASSWORD'=>'6650000', //默认管理员密码
    'URL_ROUTER_ON'   => true, //开启路由
    'URL_ROUTE_RULES' => array( //定义路由规则
        'upload' =>array('Upload/index'), //上传
        'crop' =>array('Upload/crop'), //裁剪
        'download'=>array('Upload/download'), //下载
        'downloadmagazine'=>array('Upload/downloadmagazine'), //下载杂志
        'pdf/:id'=>array('Upload/pdf'), //pdf
        'img/:size/:name'=>array('Upload/img'),

    ),
    'INTEGRAL_RATE'=>array('point'=>100,'rmb'=>3),//兑换比例
    'DEFAULT_FILTER'=>'htmlspecialchars',
    'PROXY_IP'=>'192.168.9.250',
    'PROXY_PORT'=>'3128'
);
?>