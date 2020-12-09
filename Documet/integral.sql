# MySQL-Front 5.0  (Build 1.0)

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='NO_ENGINE_SUBSTITUTION' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;


# Host: localhost    Database: integral
# ------------------------------------------------------
# Server version 5.6.12-log

DROP DATABASE IF EXISTS `integral`;
CREATE DATABASE `integral` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `integral`;

#
# Table structure for table tm_activity
#

CREATE TABLE `tm_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_name` varchar(128) NOT NULL DEFAULT '' COMMENT '活动名称',
  `activity_url` varchar(512) DEFAULT NULL COMMENT '活动详情跳转URL',
  `activity_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '活动类型，1-新会员活动，2-老会员活动',
  `activity_image` varchar(512) DEFAULT NULL COMMENT '活动图片地址',
  `activity_status` smallint(6) NOT NULL DEFAULT '1' COMMENT '状态，0--无效，1--上架，2--下架',
  `activity_sort` int(3) NOT NULL DEFAULT '0' COMMENT '活动前台展示排序',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `activity_start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '活动开始时间',
  `activity_end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '活动结束时间',
  `activity_introduction` varchar(2048) DEFAULT NULL COMMENT '活动简介',
  `activity_rules` varchar(1024) DEFAULT NULL COMMENT '活动规则',
  `activity_id` varchar(36) NOT NULL DEFAULT '' COMMENT '活动ID',
  `input_pream` varchar(2048) DEFAULT '' COMMENT '填写的问题选项,问题ID的集合',
  `team_id` varchar(36) DEFAULT '' COMMENT 'CRM的团体ID',
  `yl_url` varchar(512) DEFAULT NULL COMMENT '永乐URL',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='活动基本信息表';

#
# Table structure for table tm_activity_question
#

CREATE TABLE `tm_activity_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '问题',
  `answer` text COMMENT '答案 |分割',
  `answer_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '答案类型 1文本',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态 0--删除，1有效，2-无效',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `vereist` tinyint(3) NOT NULL DEFAULT '1' COMMENT '是否必填，1--必填，2--非必填',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '最多选择个数，适用于多选',
  `prv_id` int(11) NOT NULL DEFAULT '0' COMMENT '上一个问题',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='活动问题表';

#
# Table structure for table tm_activity_relation
#

CREATE TABLE `tm_activity_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` varchar(255) NOT NULL DEFAULT '' COMMENT '活动ID',
  `member_id` varchar(36) DEFAULT '' COMMENT '会员ID',
  `anwers` varchar(2048) DEFAULT NULL COMMENT '问题的答案，json串',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '参与的时间',
  `mobile` varchar(20) DEFAULT '' COMMENT '手机号码',
  `name` varchar(512) DEFAULT NULL COMMENT '参与活动的姓名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='活动参与关联表';

#
# Table structure for table tm_admin_group
#

CREATE TABLE `tm_admin_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '分组名称',
  `status` int(1) DEFAULT '1' COMMENT '状态 0 不可用 1可用',
  `rules` text COMMENT '对应规则id 多个逗号分割',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='管理员分组';

#
# Table structure for table tm_admin_group_access
#

CREATE TABLE `tm_admin_group_access` (
  `uid` varchar(36) DEFAULT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户所属分组表';

#
# Table structure for table tm_admin_login_history
#

CREATE TABLE `tm_admin_login_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `login_ip` varchar(16) DEFAULT NULL COMMENT '登录ip',
  `time` int(10) DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=645 DEFAULT CHARSET=utf8 COMMENT='管理员登录明细表';

#
# Table structure for table tm_admin_menu
#

CREATE TABLE `tm_admin_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '菜单名称',
  `url` varchar(255) DEFAULT NULL COMMENT '请求的URL',
  `p_id` int(11) DEFAULT '0' COMMENT '上一级的ID',
  `sort` int(3) DEFAULT '1' COMMENT '排序',
  `lv` int(11) DEFAULT NULL COMMENT '等级ID',
  `path` varchar(100) DEFAULT '0' COMMENT '级别路径',
  `icon` varchar(20) DEFAULT NULL COMMENT '菜单图标',
  `is_show` tinyint(3) NOT NULL DEFAULT '1' COMMENT '是否显示在左边菜单，1--显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=150 DEFAULT CHARSET=utf8 COMMENT='后台菜单';

#
# Table structure for table tm_admin_operation_log
#

CREATE TABLE `tm_admin_operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(36) DEFAULT NULL COMMENT '后台用户登录ID,tm_admin_user表中的ID',
  `user_name` varchar(64) NOT NULL COMMENT '后台登录用户账户，tm_admin_user表中的username',
  `operation` varchar(32) NOT NULL COMMENT '用户操作记录，添加、删除、修改、赠送',
  `operation_title` varchar(32) NOT NULL DEFAULT '' COMMENT '用户操作标题',
  `operation_key` varchar(256) NOT NULL DEFAULT '' COMMENT '用户操作关键字',
  `operation_content` varchar(512) NOT NULL COMMENT '用户操作内容',
  `operation_sql` text COMMENT '原始sql语句',
  `menu_crumbs` varchar(128) NOT NULL COMMENT '后台页面面包屑',
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间，存的是时间戳',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4365 DEFAULT CHARSET=utf8 COMMENT='后台操作log日志';

#
# Table structure for table tm_admin_rule
#

CREATE TABLE `tm_admin_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) DEFAULT '' COMMENT '权限唯一标识',
  `title` varchar(50) DEFAULT '' COMMENT '权限显示名称（标题）相当于权限的描述',
  `type` tinyint(1) DEFAULT '1' COMMENT '权限类型',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '权限状态 0失效 1有效',
  `condition` char(100) DEFAULT '' COMMENT '权限条件（为空存在就验证，不为空表示按条件验证）',
  `p_id` int(11) DEFAULT '0' COMMENT '父级ID',
  `lv` int(11) DEFAULT '1' COMMENT '级别',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=1040 DEFAULT CHARSET=utf8 COMMENT='权限表';

#
# Table structure for table tm_admin_user
#

CREATE TABLE `tm_admin_user` (
  `id` varchar(36) NOT NULL DEFAULT '',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '管理员账号',
  `password` varchar(50) NOT NULL DEFAULT '' COMMENT '密码',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `status` int(1) DEFAULT '1' COMMENT '状态1 有效 2 无效',
  `realname` varchar(20) DEFAULT NULL COMMENT '管理员真实姓名',
  `rules` varchar(255) DEFAULT NULL COMMENT '管理员附加权限',
  `lock_ip` varchar(16) DEFAULT NULL COMMENT '锁定IP',
  `user_code` varchar(255) DEFAULT '' COMMENT '工号',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='后台用户';

#
# Table structure for table tm_admin_user_relation
#

CREATE TABLE `tm_admin_user_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(36) NOT NULL COMMENT '后台用户ID,think_admin_user表的ID',
  `type` int(11) NOT NULL COMMENT '关联类型，1--店铺',
  `relation_id` varchar(36) NOT NULL COMMENT '关联ID，type等于1表示think_commodity_warehourse_base_info表中的ID',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COMMENT='后台用户关系表';

#
# Table structure for table tm_brand
#

CREATE TABLE `tm_brand` (
  `brand_id` varchar(36) NOT NULL COMMENT '品牌ID，openid',
  `brand_name` varchar(128) NOT NULL COMMENT '品牌名称',
  `brand_initial` varchar(1) NOT NULL COMMENT '品牌首字母',
  `brand_pic` varchar(256) DEFAULT NULL COMMENT '品牌Logo图片，102*36',
  `brand_recommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '推荐,0为否,1为是',
  `brand_url` varchar(256) DEFAULT NULL COMMENT '品牌介绍URL',
  `brand_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态，0--无效(删除)，1--上架，2--下架',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `brand_sort` int(11) NOT NULL DEFAULT '0' COMMENT '品牌排序',
  `brand_alias` varchar(256) DEFAULT NULL COMMENT '品牌别名',
  PRIMARY KEY (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='品牌信息';

#
# Table structure for table tm_city
#

CREATE TABLE `tm_city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city_id` varchar(36) NOT NULL DEFAULT '' COMMENT '城市ID',
  `parent_id` varchar(36) DEFAULT NULL COMMENT '城市父ID',
  `city_code` varchar(32) NOT NULL DEFAULT '' COMMENT '城市编码',
  `city_name` varchar(36) NOT NULL DEFAULT '' COMMENT '城市名称',
  `level` tinyint(3) NOT NULL DEFAULT '1' COMMENT '等级',
  `displayorder` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3754 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='城市表';

#
# Table structure for table tm_cms_goods_info
#

CREATE TABLE `tm_cms_goods_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `materiel_code` varchar(36) NOT NULL DEFAULT '' COMMENT '商品物料编码',
  `sync_goods_id` varchar(36) DEFAULT '' COMMENT '数据交换ID,tm_goods_info中的goods_id',
  `goods_media_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '市场价',
  `is_integral` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否固定积分',
  `max_integral` int(11) NOT NULL DEFAULT '0' COMMENT '最大可使用积分数',
  `promotion_content` varchar(1024) DEFAULT NULL COMMENT '促销用语',
  `promotion_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '促销类型，0-无促销，1-赠品',
  `promotion _list` text COMMENT '促销商品，json字符串[{name:促品名称,sort:促品顺序}]',
  `sale_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '销售类型，1—普通销售，2—预售',
  `detail_addr` varchar(512) DEFAULT NULL COMMENT '详情页跳转地址',
  `sale_number` int(11) NOT NULL DEFAULT '0' COMMENT '销售数量',
  `goods_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '商品状态，10-下架，11-上架',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  `goods_sale_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品销售价格',
  `show_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '展示价格',
  `show_integral` int(11) NOT NULL DEFAULT '0' COMMENT '展示积分',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS商品更新信息表';

#
# Table structure for table tm_coupon_base_info
#

CREATE TABLE `tm_coupon_base_info` (
  `coupon_id` varchar(36) NOT NULL COMMENT '优惠券ID,OpenID',
  `coupon_code` varchar(8) NOT NULL COMMENT '优惠券编码，字母和数字的组合，保持唯一性',
  `coupon_name` varchar(64) NOT NULL COMMENT '优惠券名称，同一分类下，名称唯一',
  `coupon_desc` varchar(256) DEFAULT '' COMMENT '优惠券规则描述',
  `is_pc` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'PC端支持，0--不支持，1--支持',
  `is_wap` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'wap端支持，0--不支持，1--支持',
  `is_app` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'APP端支持，0--不支持，1--支持',
  `max_count` int(11) NOT NULL DEFAULT '0' COMMENT '优惠券最大领取张数',
  `coupon_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券面值金额',
  `coupon_use_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '优惠券类型，1--全场通用，2--绑定店铺，3--绑定分类，4--绑定商品',
  `coupon_category_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '优惠券分类，1--普通卷',
  `brand_channel` tinyint(3) NOT NULL DEFAULT '1' COMMENT '推广渠道，1--商城',
  `valid_start_time` datetime NOT NULL COMMENT '有效期开始时间',
  `valid_end_time` datetime NOT NULL COMMENT '有效期结束时间',
  `coupon_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--编辑，20--待审核，21--审核通过，22--审核失败，30--启用，31--停用',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `refuse_reason` varchar(512) DEFAULT '' COMMENT '拒绝原因',
  `check_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  `is_generalize` tinyint(3) DEFAULT '0' COMMENT '是否推广，0--不推广，1--推广',
  `generalize_start_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '推广开始时间',
  `generalize_end_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '推广结束时间',
  PRIMARY KEY (`coupon_id`),
  UNIQUE KEY `coupon_code` (`coupon_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠券基本信息';

#
# Table structure for table tm_coupon_base_info_check
#

CREATE TABLE `tm_coupon_base_info_check` (
  `coupon_id` varchar(36) NOT NULL COMMENT 'OpenID',
  `coupon_code` varchar(8) NOT NULL COMMENT 'tm_coupon_base_info表中的coupon_code',
  `formal_coupon_id` varchar(36) DEFAULT NULL COMMENT 'tm_coupon_base_info表中对应的coupon_id',
  `edit_coupon_id` varchar(36) NOT NULL COMMENT 'tm_coupon_base_info_edit表中对应的coupon_id',
  `coupon_name` varchar(64) NOT NULL COMMENT '优惠券名称，同一分类下，名称唯一',
  `coupon_desc` varchar(256) DEFAULT '' COMMENT '优惠券规则描述',
  `is_pc` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'PC端支持，0--不支持，1--支持',
  `is_wap` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'wap端支持，0--不支持，1--支持',
  `is_app` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'APP端支持，0--不支持，1--支持',
  `max_count` int(11) NOT NULL DEFAULT '0' COMMENT '优惠券最大领取张数',
  `coupon_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券面值金额',
  `coupon_use_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '优惠券类型，1--全场通用，2--绑定店铺，3--绑定分类，4--绑定商品',
  `coupon_category_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '优惠券分类，1--普通卷',
  `brand_channel` tinyint(3) NOT NULL DEFAULT '1' COMMENT '推广渠道，1--商城',
  `valid_start_time` datetime NOT NULL COMMENT '有效期开始时间',
  `valid_end_time` datetime NOT NULL COMMENT '有效期结束时间',
  `admin_id` varchar(36) DEFAULT '' COMMENT '审核人员ID,tm_admin_user表中的id',
  `coupon_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--编辑，20--待审核，21--审核通过，22--审核失败，30--启用，31--停用',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `refuse_reason` varchar(512) DEFAULT '' COMMENT '拒绝原因',
  `check_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  `is_generalize` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否推广，0--不推广，1--推广',
  `generalize_start_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '推广开始时间',
  `generalize_end_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '推广结束时间',
  PRIMARY KEY (`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠券基本信息_审核';

#
# Table structure for table tm_coupon_base_info_edit
#

CREATE TABLE `tm_coupon_base_info_edit` (
  `coupon_id` varchar(36) NOT NULL COMMENT '优惠券ID,OpenID',
  `coupon_code` varchar(8) NOT NULL COMMENT '优惠券编码，字母和数字的组合，保持唯一性',
  `formal_coupon_id` varchar(36) DEFAULT NULL COMMENT 'tm_coupon_base_info表中对应的coupon_id',
  `coupon_name` varchar(64) NOT NULL COMMENT '优惠券名称，同一分类下，名称唯一',
  `coupon_desc` varchar(256) DEFAULT '' COMMENT '优惠券规则描述',
  `is_pc` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'PC端支持，0--不支持，1--支持',
  `is_wap` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'wap端支持，0--不支持，1--支持',
  `is_app` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'APP端支持，0--不支持，1--支持',
  `max_count` int(11) NOT NULL DEFAULT '0' COMMENT '优惠券最大领取张数',
  `coupon_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券面值金额',
  `coupon_use_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '优惠券类型，1--全场通用，2--绑定店铺，3--绑定分类，4--绑定商品',
  `coupon_category_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '优惠券分类，1--普通卷',
  `brand_channel` tinyint(3) NOT NULL DEFAULT '1' COMMENT '推广渠道，1--商城',
  `valid_start_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '有效期开始时间',
  `valid_end_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '有效期结束时间',
  `coupon_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--编辑，20--待审核，21--审核通过，22--审核失败，30--启用，31--停用',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `refuse_reason` varchar(512) DEFAULT '' COMMENT '拒绝原因',
  `check_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  `is_generalize` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否推广，0--不推广，1--推广',
  `generalize_start_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '推广开始时间',
  `generalize_end_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '推广结束时间',
  PRIMARY KEY (`coupon_id`),
  UNIQUE KEY `coupon_code` (`coupon_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠券基本信息_编辑';

#
# Table structure for table tm_coupon_code_base_info
#

CREATE TABLE `tm_coupon_code_base_info` (
  `coupon_code_id` varchar(36) NOT NULL COMMENT '优惠码ID,OpenID',
  `coupon_code_name` varchar(64) NOT NULL COMMENT '优惠码名称，同一分类下，名称唯一',
  `coupon_code_desc` varchar(256) NOT NULL COMMENT '优惠码规则描述',
  `is_pc` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'PC端支持，0--不支持，1--支持',
  `is_wap` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'wap端支持，0--不支持，1--支持',
  `is_app` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'APP端支持，0--不支持，1--支持',
  `max_count` int(11) NOT NULL DEFAULT '0' COMMENT '优惠码最大创建个数',
  `max_use_count` int(11) NOT NULL DEFAULT '0' COMMENT '每个优惠码最大使用次数',
  `coupon_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券面值金额',
  `coupon_use_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '优惠码类型，1--全场通用，2--绑定店铺，3--绑定分类，4--绑定商品',
  `coupon_category_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '优惠码分类，1--普通卷',
  `brand_channel` tinyint(3) NOT NULL DEFAULT '1' COMMENT '推广渠道，1--商城',
  `valid_start_time` datetime NOT NULL COMMENT '有效期开始时间',
  `valid_end_time` datetime NOT NULL COMMENT '有效期结束时间',
  `coupon_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--编辑，20--待审核，21--审核通过，22--审核失败，30--启用，31--停用',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `refuse_reason` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `check_time` datetime DEFAULT NULL COMMENT '审核时间',
  PRIMARY KEY (`coupon_code_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠码基本信息';

#
# Table structure for table tm_coupon_code_base_info_check
#

CREATE TABLE `tm_coupon_code_base_info_check` (
  `coupon_code_id` varchar(36) NOT NULL COMMENT '优惠码ID,OpenID',
  `formal_coupon_code_id` varchar(36) DEFAULT NULL COMMENT 'tm_coupon_code_base_info表中对应的coupon_code_id',
  `edit_coupon_code_id` varchar(36) NOT NULL COMMENT 'tm_coupon_code_base_info_edit表中对应的coupon_code_id',
  `coupon_code_name` varchar(64) NOT NULL COMMENT '优惠码名称，同一分类下，名称唯一',
  `coupon_code_desc` varchar(256) NOT NULL COMMENT '优惠码规则描述',
  `is_pc` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'PC端支持，0--不支持，1--支持',
  `is_wap` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'wap端支持，0--不支持，1--支持',
  `is_app` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'APP端支持，0--不支持，1--支持',
  `max_count` int(11) NOT NULL DEFAULT '0' COMMENT '优惠码最大创建个数',
  `max_use_count` int(11) NOT NULL DEFAULT '0' COMMENT '每个优惠码最大使用次数',
  `coupon_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券面值金额',
  `coupon_use_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '优惠码类型，1--全场通用，2--绑定店铺，3--绑定分类，4--绑定商品',
  `coupon_category_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '优惠码分类，1--普通卷',
  `brand_channel` tinyint(3) NOT NULL DEFAULT '1' COMMENT '推广渠道，1--商城',
  `valid_start_time` datetime NOT NULL COMMENT '有效期开始时间',
  `valid_end_time` datetime NOT NULL COMMENT '有效期结束时间',
  `admin_id` varchar(36) DEFAULT NULL COMMENT '审核人员ID,tm_admin_user表中的id',
  `coupon_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--编辑，20--待审核，21--审核通过，22--审核失败，30--启用，31--停用',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `refuse_reason` varchar(512) DEFAULT NULL COMMENT '拒绝原因',
  `check_time` datetime DEFAULT NULL COMMENT '审核时间',
  PRIMARY KEY (`coupon_code_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠码基本信息_审核';

#
# Table structure for table tm_coupon_code_base_info_edit
#

CREATE TABLE `tm_coupon_code_base_info_edit` (
  `id` varchar(36) NOT NULL COMMENT 'OpenID',
  `coupon_code_id` varchar(36) NOT NULL COMMENT '优惠码ID,若是审核列表中过来的ID，那就是tm_coupon_code_base_info表中的ID',
  `formal_coupon_code_id` varchar(36) DEFAULT NULL COMMENT 'tm_coupon_code_base_info表中对应的coupon_code_id',
  `coupon_code_name` varchar(64) NOT NULL COMMENT '优惠码名称，同一分类下，名称唯一',
  `coupon_code_desc` varchar(256) NOT NULL COMMENT '优惠码规则描述',
  `is_pc` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'PC端支持，0--不支持，1--支持',
  `is_wap` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'wap端支持，0--不支持，1--支持',
  `is_app` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'APP端支持，0--不支持，1--支持',
  `max_count` int(11) NOT NULL DEFAULT '0' COMMENT '优惠码最大创建个数',
  `max_use_count` int(11) NOT NULL DEFAULT '0' COMMENT '每个优惠码最大使用次数',
  `coupon_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券面值金额',
  `coupon_use_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '优惠码类型，1--全场通用，2--绑定店铺，3--绑定分类，4--绑定商品',
  `coupon_category_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '优惠码分类，1--普通卷',
  `brand_channel` tinyint(3) NOT NULL DEFAULT '1' COMMENT '推广渠道，1--商城',
  `valid_start_time` datetime NOT NULL COMMENT '有效期开始时间',
  `valid_end_time` datetime NOT NULL COMMENT '有效期结束时间',
  `coupon_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--编辑，20--待审核，21--审核通过，22--审核失败，30--启用，31--停用',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `refuse_reason` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `check_time` datetime DEFAULT NULL COMMENT '审核时间',
  PRIMARY KEY (`coupon_code_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠码基本信息_编辑';

#
# Table structure for table tm_coupon_code_info
#

CREATE TABLE `tm_coupon_code_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_code_id` varchar(36) NOT NULL COMMENT '优惠码ID,tm_coupon_code_base_info表中的ID',
  `coupon_code` varchar(8) NOT NULL COMMENT '优惠码编码',
  `valid_start_time` datetime NOT NULL COMMENT '有效期开始时间',
  `valid_end_time` datetime NOT NULL COMMENT '有效期结束时间',
  `coupon_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--未使用，20--已使用',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `max_use_count` int(11) DEFAULT '1' COMMENT '每个优惠码最大使用次数',
  `use_count` int(11) DEFAULT '1' COMMENT '已经使用的优惠码次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8 COMMENT='优惠码码表';

#
# Table structure for table tm_coupon_code_operation_log
#

CREATE TABLE `tm_coupon_code_operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(36) NOT NULL COMMENT '后台用户登录ID,tm_admin_user表中的ID',
  `user_name` varchar(64) NOT NULL COMMENT '后台登录用户账户，tm_admin_user表中的username',
  `coupon_id` varchar(36) DEFAULT NULL COMMENT '优惠券ID，tm_coupon_code_base_info表中的goods_id',
  `operation_content` varchar(512) NOT NULL COMMENT '用户操作内容',
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间，存的是时间戳',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='优惠码上下架日志';

#
# Table structure for table tm_coupon_code_relation_exclude_goods
#

CREATE TABLE `tm_coupon_code_relation_exclude_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_code_id` varchar(36) NOT NULL COMMENT '优惠券ID,tm_coupon_code_base_info表中的coupon_id',
  `relation_id` varchar(36) NOT NULL COMMENT '关联关系ID，根据tm_coupon_code_base_info表中的coupon_use_type不同，对应的值说明不同',
  `relation_code` varchar(36) NOT NULL COMMENT '关联关系码，根据tm_coupon_code_base_info表中的coupon_use_type不同，对应的值说明不同',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='优惠码排除商品表';

#
# Table structure for table tm_coupon_code_relation_exclude_goods_check
#

CREATE TABLE `tm_coupon_code_relation_exclude_goods_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_code_id` varchar(36) NOT NULL COMMENT '优惠码ID,tm_coupon_code_base_info_edit表中的coupon_id',
  `relation_id` varchar(36) NOT NULL COMMENT '关联关系ID，根据tm_coupon_code_base_info_edit表中的coupon_use_type不同，对应的值说明不同',
  `relation_code` varchar(36) NOT NULL COMMENT '关联关系码，根据tm_coupon_code_base_info_edit表中的coupon_use_type不同，对应的值说明不同',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='优惠码排除商品表_审核';

#
# Table structure for table tm_coupon_code_relation_exclude_goods_edit
#

CREATE TABLE `tm_coupon_code_relation_exclude_goods_edit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_code_id` varchar(36) NOT NULL COMMENT '优惠券ID,tm_coupon_code_base_info_edit表中的coupon_id',
  `relation_id` varchar(36) NOT NULL COMMENT '关联关系ID，根据tm_coupon_code_base_info_edit表中的coupon_use_type不同，对应的值说明不同',
  `relation_code` varchar(36) NOT NULL COMMENT '关联关系码，根据tm_coupon_code_base_info_edit表中的coupon_use_type不同，对应的值说明不同',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='优惠码排除商品表_编辑';

#
# Table structure for table tm_coupon_code_relation_info
#

CREATE TABLE `tm_coupon_code_relation_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_code_id` varchar(36) NOT NULL COMMENT '优惠券ID,tm_coupon_code_base_info表中的coupon_id',
  `relation_id` varchar(36) NOT NULL COMMENT '关联关系ID，根据tm_coupon_code_base_info表中的coupon_use_type不同，对应的值说明不同',
  `relation_code` varchar(36) NOT NULL COMMENT '关联关系码，根据tm_coupon_code_base_info表中的coupon_use_type不同，对应的值说明不同',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠码绑定信息表';

#
# Table structure for table tm_coupon_code_relation_info_check
#

CREATE TABLE `tm_coupon_code_relation_info_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_code_id` varchar(36) NOT NULL COMMENT '优惠券ID,tm_coupon_code_base_info_check表中的coupon_id',
  `relation_id` varchar(36) NOT NULL COMMENT '关联关系ID，根据tm_coupon_code_base_info_check表中的coupon_use_type不同，对应的值说明不同',
  `relation_code` varchar(36) NOT NULL COMMENT '关联关系码，根据tm_coupon_code_base_info_check表中的coupon_use_type不同，对应的值说明不同',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='优惠码绑定信息表_审核';

#
# Table structure for table tm_coupon_code_relation_info_edit
#

CREATE TABLE `tm_coupon_code_relation_info_edit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_code_id` varchar(36) NOT NULL COMMENT '优惠券ID,tm_coupon_code_base_info_edit表中的coupon_id',
  `relation_id` varchar(36) NOT NULL COMMENT '关联关系ID，根据tm_coupon_code_base_info_edit表中的coupon_use_type不同，对应的值说明不同',
  `relation_code` varchar(36) NOT NULL COMMENT '关联关系码，根据tm_coupon_code_base_info_edit表中的coupon_use_type不同，对应的值说明不同',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='优惠码绑定信息表_编辑';

#
# Table structure for table tm_coupon_operation_log
#

CREATE TABLE `tm_coupon_operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(36) NOT NULL COMMENT '后台用户登录ID,tm_admin_user表中的ID',
  `user_name` varchar(64) NOT NULL COMMENT '后台登录用户账户，tm_admin_user表中的username',
  `coupon_id` varchar(36) DEFAULT NULL COMMENT '优惠券ID，tm_coupon_base_info表中的goods_id',
  `operation_content` varchar(512) NOT NULL COMMENT '用户操作内容',
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间，存的是时间戳',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='优惠券上下架日志';

#
# Table structure for table tm_coupon_relation_exclude_goods
#

CREATE TABLE `tm_coupon_relation_exclude_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` varchar(36) NOT NULL COMMENT '优惠券ID,tm_coupon_base_info表中的coupon_id',
  `coupon_code` varchar(8) NOT NULL COMMENT '优惠券编码，tm_coupon_base_info表中的coupon_code',
  `relation_id` varchar(36) NOT NULL COMMENT '关联关系ID，根据tm_coupon_base_info表中的coupon_use_type不同，对应的值说明不同',
  `relation_code` varchar(36) NOT NULL COMMENT '关联关系码，根据tm_coupon_base_info表中的coupon_use_type不同，对应的值说明不同',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='优惠券排除商品表';

#
# Table structure for table tm_coupon_relation_exclude_goods_check
#

CREATE TABLE `tm_coupon_relation_exclude_goods_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` varchar(36) NOT NULL COMMENT '优惠券ID,tm_coupon_base_info_check表中的coupon_id',
  `coupon_code` varchar(8) NOT NULL COMMENT '优惠券编码，tm_coupon_base_info_check表中的coupon_code',
  `relation_id` varchar(36) NOT NULL COMMENT '关联关系ID，根据tm_coupon_base_info_check表中的coupon_use_type不同，对应的值说明不同',
  `relation_code` varchar(36) NOT NULL COMMENT '关联关系码，根据tm_coupon_base_info_check表中的coupon_use_type不同，对应的值说明不同',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='优惠券排除商品表_审核';

#
# Table structure for table tm_coupon_relation_exclude_goods_edit
#

CREATE TABLE `tm_coupon_relation_exclude_goods_edit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` varchar(36) NOT NULL COMMENT '优惠券ID,tm_coupon_base_info_edit表中的coupon_id',
  `coupon_code` varchar(8) NOT NULL COMMENT '优惠券编码，tm_coupon_base_info_edit表中的coupon_code',
  `relation_id` varchar(36) NOT NULL COMMENT '关联关系ID，根据tm_coupon_base_info_edit表中的coupon_use_type不同，对应的值说明不同',
  `relation_code` varchar(36) NOT NULL COMMENT '关联关系码，根据tm_coupon_base_info_edit表中的coupon_use_type不同，对应的值说明不同',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='优惠券排除商品表_编辑';

#
# Table structure for table tm_coupon_relation_info
#

CREATE TABLE `tm_coupon_relation_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` varchar(36) NOT NULL COMMENT '优惠券ID,tm_coupon_base_info表中的coupon_id',
  `coupon_code` varchar(8) NOT NULL COMMENT '优惠券编码，tm_coupon_base_info表中的coupon_code',
  `relation_id` varchar(36) NOT NULL COMMENT '关联关系ID，根据tm_coupon_base_info表中的coupon_use_type不同，对应的值说明不同',
  `relation_code` varchar(36) NOT NULL COMMENT '关联关系码，根据tm_coupon_base_info表中的coupon_use_type不同，对应的值说明不同',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='优惠券绑定信息表';

#
# Table structure for table tm_coupon_relation_info_check
#

CREATE TABLE `tm_coupon_relation_info_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` varchar(36) NOT NULL COMMENT '优惠券ID,tm_coupon_base_info_check表中的coupon_id',
  `coupon_code` varchar(8) NOT NULL COMMENT '优惠券编码，tm_coupon_base_info_check表中的coupon_code',
  `relation_id` varchar(36) NOT NULL COMMENT '关联关系ID，根据tm_coupon_base_info_check表中的coupon_use_type不同，对应的值说明不同',
  `relation_code` varchar(36) NOT NULL COMMENT '关联关系码，根据tm_coupon_base_info_check表中的coupon_use_type不同，对应的值说明不同',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='优惠券绑定信息表_审核';

#
# Table structure for table tm_coupon_relation_info_edit
#

CREATE TABLE `tm_coupon_relation_info_edit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` varchar(36) NOT NULL COMMENT '优惠券ID,tm_coupon_base_info_edit表中的coupon_id',
  `coupon_code` varchar(8) NOT NULL COMMENT '优惠券编码，tm_coupon_base_info_edit表中的coupon_code',
  `relation_id` varchar(36) NOT NULL COMMENT '关联关系ID，根据tm_coupon_base_info_edit表中的coupon_use_type不同，对应的值说明不同',
  `relation_code` varchar(36) NOT NULL COMMENT '关联关系码，根据tm_coupon_base_info_edit表中的coupon_use_type不同，对应的值说明不同',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='优惠券绑定信息表_编辑';

#
# Table structure for table tm_coupon_user_info
#

CREATE TABLE `tm_coupon_user_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(36) NOT NULL COMMENT '会员ID,tm_user_member_base_info表中的会员ID',
  `coupon_id` varchar(36) NOT NULL COMMENT '优惠券ID,tm_coupon_base_info表中的优惠券ID',
  `coupon_code` varchar(8) NOT NULL COMMENT '优惠券编码，tm_coupon_base_info表中的优惠券编码',
  `coupon_value` float(10,2) NOT NULL COMMENT '优惠券面值金额，tm_coupon_base_info表中的优惠券面值',
  `is_pc` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'PC端支持，tm_coupon_base_info表中的is_pc',
  `is_wap` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'wap端支持，tm_coupon_base_info表中is_wap',
  `is_app` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'APP端支持，tm_coupon_base_info表中is_app',
  `valid_start_time` datetime NOT NULL COMMENT '有效期开始时间,tm_coupon_base_info表中的开始时间',
  `valid_end_time` datetime NOT NULL COMMENT '有效期结束时间,tm_coupon_base_info表中的结束时间',
  `coupon_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--未使用，20--已使用',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `order_goods_id` varchar(36) DEFAULT NULL COMMENT '订单商品ID',
  `coupon_user_id` varchar(36) NOT NULL DEFAULT '' COMMENT '会员优惠券ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8 COMMENT='用户优惠券信息表';

#
# Table structure for table tm_goods_base_info
#

CREATE TABLE `tm_goods_base_info` (
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，openid',
  `goods_code` varchar(10) NOT NULL COMMENT '商品编码，从1000开始',
  `goods_category_id` varchar(36) NOT NULL COMMENT '产品分类，tm_goods_category表中的goods_category_id',
  `goods_category_code` varchar(5) NOT NULL COMMENT '产品分类编码，tm_goods_category表中的goods_category_code',
  `brand_id` varchar(36) NOT NULL COMMENT '品牌ID，tm_brand表中的brand_id',
  `goods_type_id` varchar(36) NOT NULL COMMENT '商品类型ID，tm_goods_type中的type_id',
  `goods_type_code` varchar(5) NOT NULL COMMENT '商品类型编码，tm_goods_type中的type_code',
  `goods_name` varchar(128) NOT NULL COMMENT '商品名称',
  `goods_brief` varchar(256) DEFAULT NULL COMMENT '商品简介',
  `goods_desc` varchar(256) DEFAULT NULL COMMENT '商品描述',
  `goods_materiel_code` varchar(36) NOT NULL COMMENT '商品物料编码',
  `goods_unit` tinyint(4) DEFAULT NULL COMMENT '单位',
  `goods_media_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '媒体价',
  `is_sale_pc` tinyint(1) DEFAULT '1' COMMENT 'PC端是否销售，1--销售，0--不销售',
  `goods_price_pc` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'PC端销售价',
  `promotion_content_pc` varchar(1024) DEFAULT '' COMMENT 'PC端商品促销语',
  `is_sale_wap` tinyint(1) DEFAULT '1' COMMENT 'wap端是否销售，1--销售，0--不销售',
  `goods_price_wap` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'wap端销售价',
  `promotion_content_wap` varchar(1024) DEFAULT '' COMMENT 'wap端商品促销语',
  `is_sale_app` tinyint(1) DEFAULT '1' COMMENT 'app端是否销售，1--销售，0--不销售',
  `goods_price_app` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'app端销售价',
  `promotion_content_app` varchar(1024) DEFAULT '' COMMENT 'app端商品促销语',
  `is_object` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否是实物，0--非实物，1--实物',
  `is_search` tinyint(1) NOT NULL DEFAULT '1' COMMENT '能否被搜索，0--不能被搜索，1--能不搜索',
  `is_detail` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否生产详情页，0--不生成，1--生成',
  `service_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '服务产品类型，0--非服务产品',
  `is_promotion` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否支持退货，0--不支持，1--支持',
  `is_change` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否支持换货，0--不支持，1--支持',
  `is_repair` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支持维修，0--不支持，1-支持',
  `is_check_store` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否验证库存，0--不支持，1-支持',
  `search_key_word` varchar(512) DEFAULT NULL COMMENT '产品搜索关键字，多个用英文分号隔开',
  `product_pic` varchar(2048) DEFAULT NULL COMMENT '产品图片，多个用分号分开,详情页面中展示',
  `product_default_pic` varchar(512) DEFAULT NULL COMMENT '产品橱窗图片，列表页面中展示',
  `shops_code` varchar(8) NOT NULL COMMENT '商铺编码，tm_shops_base_info表中的商铺编码',
  `goods_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--编辑，20--待审核，21--审核通过，22--审核失败，30--上架，31--下架,32--停产,33--待上架，34--待下架',
  `refuse_reason` varchar(512) DEFAULT NULL COMMENT '拒绝原因',
  `pre_up_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '预上下架时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `check_time` datetime NOT NULL COMMENT '审核时间',
  `goods_sort` int(11) DEFAULT NULL COMMENT '商品排序',
  `sale_type` tinyint(3) DEFAULT '10' COMMENT '销售类型，0--无效(删除)，10--普通销售，20--预售，30--团购, 40--众筹，50--新品试用',
  `source` tinyint(3) NOT NULL DEFAULT '1' COMMENT '来源：1--自己创建，2--CMS，3--CRM',
  `goods_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '商品类型，1--普通商品，2--大礼包',
  `relation_goods` varchar(1024) DEFAULT '' COMMENT '当goods_type是2时，是大礼包的具体商品的json集合',
  `goods_cost_price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '结算价',
  `integral` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否固定积分，1--是，0--否',
  `max_integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最大可使用积分数',
  `ext_code` varchar(36) DEFAULT NULL COMMENT '外部扩展码',
  `expect_up_time` datetime DEFAULT NULL COMMENT '预计上架时间',
  `sale_number` int(11) DEFAULT NULL COMMENT '销售数量',
  `use_storage_money` tinyint(3) DEFAULT '0' COMMENT '是否可用储值卡金额,0--不能使用，1--可以使用',
  `is_black` tinyint(3) NOT NULL DEFAULT '0' COMMENT '黑户是否能购买，0-不能，1-能',
  `expect_down_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '预下架时间',
  PRIMARY KEY (`goods_id`),
  UNIQUE KEY `goods_code` (`goods_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品基本信息列表';

#
# Table structure for table tm_goods_base_info_check
#

CREATE TABLE `tm_goods_base_info_check` (
  `goods_id` varchar(36) NOT NULL COMMENT 'openid',
  `goods_code` varchar(10) NOT NULL COMMENT '商品编码，从1000开始',
  `formal_goods_id` varchar(36) DEFAULT NULL COMMENT 'tm_goods_base_info表中对应的goods_id',
  `edit_goods_id` varchar(36) NOT NULL COMMENT 'tm_goods_base_info_edit表中对应的goods_id',
  `goods_category_id` varchar(36) NOT NULL COMMENT '产品分类，tm_goods_category表中的goods_category_id',
  `goods_category_code` varchar(5) NOT NULL COMMENT '产品分类编码，tm_goods_category表中的goods_category_code',
  `brand_id` varchar(36) NOT NULL COMMENT '品牌ID，tm_brand表中的brand_id',
  `goods_type_id` varchar(36) NOT NULL COMMENT '商品类型ID，tm_goods_type中的type_id',
  `goods_type_code` varchar(5) NOT NULL COMMENT '商品类型编码，tm_goods_type中的type_code',
  `goods_name` varchar(128) NOT NULL COMMENT '商品名称',
  `goods_brief` varchar(256) DEFAULT NULL COMMENT '商品简介',
  `goods_desc` varchar(256) DEFAULT NULL COMMENT '商品描述',
  `goods_materiel_code` varchar(36) NOT NULL COMMENT '商品物料编码',
  `goods_unit` tinyint(4) DEFAULT NULL COMMENT '单位',
  `goods_media_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '媒体价',
  `is_sale_pc` tinyint(1) DEFAULT '1' COMMENT 'PC端是否销售，1--销售，0--不销售',
  `goods_price_pc` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'PC端销售价',
  `promotion_content_pc` varchar(1024) DEFAULT '' COMMENT 'PC端商品促销语',
  `is_sale_wap` tinyint(1) DEFAULT '1' COMMENT 'wap端是否销售，1--销售，0--不销售',
  `goods_price_wap` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'wap端销售价',
  `promotion_content_wap` varchar(1024) DEFAULT '' COMMENT 'wap端商品促销语',
  `is_sale_app` tinyint(1) DEFAULT '1' COMMENT 'app端是否销售，1--销售，0--不销售',
  `goods_price_app` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'app端销售价',
  `promotion_content_app` varchar(1024) DEFAULT '' COMMENT 'app端商品促销语',
  `is_object` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否是实物，0--非实物，1--实物',
  `is_search` tinyint(1) NOT NULL DEFAULT '1' COMMENT '能否被搜索，0--不能被搜索，1--能不搜索',
  `is_detail` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否生产详情页，0--不生成，1--生成',
  `service_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '服务产品类型，0--非服务产品',
  `is_promotion` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否支持退货，0--不支持，1--支持',
  `is_change` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否支持换货，0--不支持，1--支持',
  `is_repair` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支持维修，0--不支持，1-支持',
  `is_check_store` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否验证库存，0--不支持，1-支持',
  `search_key_word` varchar(512) DEFAULT NULL COMMENT '产品搜索关键字，多个用英文分号隔开',
  `product_pic` varchar(2048) DEFAULT NULL COMMENT '产品图片，多个用分号分开,详情页面中展示',
  `product_default_pic` varchar(512) DEFAULT NULL COMMENT '产品橱窗图片，列表页面中展示',
  `shops_code` varchar(8) NOT NULL COMMENT '商铺编码，tm_shops_base_info表中的商铺编码',
  `goods_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--编辑，20--待审核，21--审核通过，22--审核失败，30--上架，31--下架,32--停产,33--待上架，34--待下架',
  `refuse_reason` varchar(512) DEFAULT NULL COMMENT '拒绝原因',
  `pre_up_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '预上下架时间',
  `admin_id` varchar(36) DEFAULT '' COMMENT '审核人员ID,tm_admin_user表中的id',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `check_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  `goods_sort` int(11) DEFAULT NULL COMMENT '商品排序',
  `sale_type` tinyint(3) DEFAULT '10' COMMENT '销售类型，0--无效(删除)，10--普通销售，20--预售，30--团购, 40--众筹，50--新品试用',
  `source` varchar(255) NOT NULL DEFAULT '1' COMMENT '来源：1--自己创建，2--CMS，3--CRM',
  `goods_type` tinyint(3) DEFAULT '1' COMMENT '商品类型，1--普通商品，2--大礼包',
  `relation_goods` varchar(1024) DEFAULT '' COMMENT '当goods_type是2时，是大礼包的具体商品的json集合',
  `goods_cost_price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '结算价',
  `integral` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否固定积分，1--是，0--否',
  `max_integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最大可使用积分数',
  `ext_code` varchar(36) DEFAULT NULL COMMENT '外部扩展码',
  `expect_up_time` datetime DEFAULT NULL COMMENT '预计上架时间',
  `sale_number` int(11) DEFAULT NULL COMMENT '销售数量',
  `use_storage_money` tinyint(3) DEFAULT '0' COMMENT '是否可用储值卡金额,0--不能使用，1--可以使用',
  `is_black` tinyint(3) NOT NULL DEFAULT '0' COMMENT '黑户是否能购买，0-不能，1-能',
  `expect_down_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '预下架时间',
  PRIMARY KEY (`goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品基本信息列表_审核状态';

#
# Table structure for table tm_goods_base_info_edit
#

CREATE TABLE `tm_goods_base_info_edit` (
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，openid',
  `goods_code` varchar(10) NOT NULL COMMENT '商品编码，从1000开始',
  `formal_goods_id` varchar(36) DEFAULT NULL COMMENT 'tm_goods_base_info表中对应的formal_goods_id',
  `goods_category_id` varchar(36) NOT NULL COMMENT '产品分类，tm_goods_category表中的goods_category_id',
  `goods_category_code` varchar(5) NOT NULL COMMENT '产品分类编码，tm_goods_category表中的goods_category_code',
  `brand_id` varchar(36) NOT NULL COMMENT '品牌ID，tm_brand表中的brand_id',
  `goods_type_id` varchar(36) NOT NULL COMMENT '商品类型ID，tm_goods_type中的type_id',
  `goods_type_code` varchar(5) NOT NULL COMMENT '商品类型编码，tm_goods_type中的type_code',
  `goods_name` varchar(128) NOT NULL COMMENT '商品名称',
  `goods_brief` varchar(256) DEFAULT NULL COMMENT '商品简介',
  `goods_desc` varchar(256) DEFAULT NULL COMMENT '商品描述',
  `goods_materiel_code` varchar(36) NOT NULL COMMENT '商品物料编码',
  `goods_unit` tinyint(3) DEFAULT '1' COMMENT '单位，枚举常量',
  `goods_media_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '媒体价',
  `is_sale_pc` tinyint(1) DEFAULT '1' COMMENT 'PC端是否销售，1--销售，0--不销售',
  `goods_price_pc` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'PC端销售价',
  `promotion_content_pc` varchar(1024) DEFAULT '' COMMENT 'PC端商品促销语',
  `is_sale_wap` tinyint(1) DEFAULT '1' COMMENT 'wap端是否销售，1--销售，0--不销售',
  `goods_price_wap` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'wap端销售价',
  `promotion_content_wap` varchar(1024) DEFAULT '' COMMENT 'wap端商品促销语',
  `is_sale_app` tinyint(1) DEFAULT '1' COMMENT 'app端是否销售，1--销售，0--不销售',
  `goods_price_app` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'app端销售价',
  `promotion_content_app` varchar(1024) DEFAULT '' COMMENT 'app端商品促销语',
  `is_object` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否是实物，0--非实物，1--实物',
  `is_search` tinyint(1) NOT NULL DEFAULT '1' COMMENT '能否被搜索，0--不能被搜索，1--能不搜索',
  `is_detail` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否生产详情页，0--不生成，1--生成',
  `service_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '服务产品类型，0--非服务产品',
  `is_promotion` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否支持退货，0--不支持，1--支持',
  `is_change` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否支持换货，0--不支持，1--支持',
  `is_repair` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支持维修，0--不支持，1-支持',
  `is_check_store` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否验证库存，0--不支持，1-支持',
  `search_key_word` varchar(512) DEFAULT NULL COMMENT '产品搜索关键字，多个用英文分号隔开',
  `product_pic` varchar(2048) DEFAULT NULL COMMENT '产品图片，多个用分号分开,详情页面中展示',
  `product_default_pic` varchar(512) DEFAULT NULL COMMENT '产品橱窗图片，列表页面中展示',
  `shops_code` varchar(8) NOT NULL COMMENT '商铺编码，tm_shops_base_info表中的商铺编码',
  `goods_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--编辑，20--待审核，21--审核通过，22--审核失败，30--上架，31--下架,32--停产,33--待上架，34--待下架',
  `refuse_reason` varchar(512) DEFAULT NULL COMMENT '拒绝原因',
  `pre_up_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '预上下架时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `check_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  `goods_sort` int(11) DEFAULT NULL COMMENT '商品排序',
  `sale_type` tinyint(3) DEFAULT '10' COMMENT '销售类型，0--无效(删除)，10--普通销售，20--预售，30--团购, 40--众筹，50--新品试用',
  `source` tinyint(3) NOT NULL DEFAULT '1' COMMENT '来源：1--自己创建，2--CMS，3--CRM',
  `goods_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '商品类型，1--普通商品，2--大礼包',
  `relation_goods` varchar(1024) DEFAULT '' COMMENT '当goods_type是2时，是大礼包的具体商品的json集合',
  `goods_cost_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '结算价',
  `integral` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否固定积分，1--是，0--否',
  `max_integral` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最大可使用积分数',
  `ext_code` varchar(36) DEFAULT NULL COMMENT '外部扩展码',
  `expect_up_time` datetime DEFAULT NULL COMMENT '预计上架时间',
  `sale_number` int(11) DEFAULT NULL COMMENT '销售数量',
  `use_storage_money` tinyint(3) DEFAULT '0' COMMENT '是否可用储值卡金额,0--不能使用，1--可以使用',
  `is_black` tinyint(3) NOT NULL DEFAULT '0' COMMENT '黑户是否能购买，0-不能，1-能',
  `expect_down_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '预下架时间',
  PRIMARY KEY (`goods_id`),
  UNIQUE KEY `goods_code` (`goods_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品基本信息列表_编辑状态';

#
# Table structure for table tm_goods_bind_sale_info
#

CREATE TABLE `tm_goods_bind_sale_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bind_goods_id` varchar(36) NOT NULL COMMENT '捆绑商品ID，tm_goods_base_info表中的goods_id',
  `primary_goods_id` varchar(36) NOT NULL COMMENT '主品商品ID，tm_goods_base_info表中的goods_id',
  `bind_goods_name` varchar(128) NOT NULL COMMENT '商品名称',
  `bind_promotion_content` varchar(1024) NOT NULL COMMENT '服务商品简介',
  `bind_pc_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'PC端销售价',
  `bind_wap_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'WAP端销售价',
  `bind_app_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'APP端销售价',
  `bind_status` tinyint(3) NOT NULL DEFAULT '11' COMMENT '状态，0--无效(删除)，10--绑定，11--解绑',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品捆绑销售信息表';

#
# Table structure for table tm_goods_bind_sale_info_check
#

CREATE TABLE `tm_goods_bind_sale_info_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bind_goods_id` varchar(36) NOT NULL COMMENT '捆绑商品ID，tm_goods_base_info表中的goods_id',
  `primary_goods_id` varchar(36) NOT NULL COMMENT '主品商品ID，tm_goods_base_info表中的goods_id',
  `bind_goods_name` varchar(128) NOT NULL COMMENT '商品名称',
  `bind_promotion_content` varchar(1024) NOT NULL COMMENT '服务商品简介',
  `bind_pc_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'PC端销售价',
  `bind_wap_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'WAP端销售价',
  `bind_app_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'APP端销售价',
  `bind_status` tinyint(3) NOT NULL DEFAULT '11' COMMENT '状态，0--无效(删除)，10--绑定，11--解绑',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品捆绑销售信息表_审核状态';

#
# Table structure for table tm_goods_bind_sale_info_edit
#

CREATE TABLE `tm_goods_bind_sale_info_edit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bind_goods_id` varchar(36) NOT NULL COMMENT '捆绑商品ID，tm_goods_base_info表中的goods_id',
  `primary_goods_id` varchar(36) NOT NULL COMMENT '主品商品ID，tm_goods_base_info表中的goods_id',
  `bind_goods_name` varchar(128) NOT NULL COMMENT '商品名称',
  `bind_promotion_content` varchar(1024) NOT NULL COMMENT '服务商品简介',
  `bind_pc_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'PC端销售价',
  `bind_wap_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'WAP端销售价',
  `bind_app_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'APP端销售价',
  `bind_status` tinyint(3) NOT NULL DEFAULT '11' COMMENT '状态，0--无效(删除)，10--绑定，11--解绑',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品捆绑销售信息表_编辑状态';

#
# Table structure for table tm_goods_brand
#

CREATE TABLE `tm_goods_brand` (
  `brand_id` varchar(36) NOT NULL COMMENT '品牌ID，openid',
  `brand_code` varchar(8) NOT NULL COMMENT '品牌编码,不重复的数字',
  `brand_name` varchar(128) NOT NULL COMMENT '品牌名称',
  `brand_initial` varchar(1) NOT NULL COMMENT '品牌首字母',
  `brand_pic` varchar(256) DEFAULT NULL COMMENT '品牌Logo图片，102*36',
  `brand_recommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '推荐,0为否,1为是',
  `brand_url` varchar(256) DEFAULT NULL COMMENT '品牌介绍URL',
  `brand_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态，0--无效(删除)，1--上架，2--下架',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `brand_sort` int(11) NOT NULL DEFAULT '0' COMMENT '品牌排序',
  `brand_alias` varchar(256) DEFAULT NULL COMMENT '品牌别名',
  PRIMARY KEY (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品品牌信息';

#
# Table structure for table tm_goods_category
#

CREATE TABLE `tm_goods_category` (
  `goods_category_id` varchar(36) NOT NULL COMMENT '商品分类ID，openid',
  `goods_category_parent_id` varchar(36) NOT NULL DEFAULT '' COMMENT '父类别ID,NULL--表示顶级分类',
  `goods_category_path` varchar(256) DEFAULT NULL COMMENT '分类面包屑,用中横杆分开',
  `goods_category_level` tinyint(3) NOT NULL DEFAULT '0' COMMENT '分类级别,1--第一级别,2--第二级别,3--第三级别,最多5级',
  `goods_category_code` varchar(8) NOT NULL COMMENT '商品分类编码,不重复的数字',
  `goods_category_name` varchar(128) NOT NULL COMMENT '分类名称',
  `goods_category_desc` varchar(256) DEFAULT NULL COMMENT '分类简介',
  `goods_category_pic` varchar(1024) DEFAULT NULL COMMENT '分类图片，多张用逗号分开，最多3张',
  `goods_category_summary` varchar(64) NOT NULL DEFAULT '' COMMENT '分类简短标语',
  `goods_category_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态，0--无效(删除)，1--上架，2--下架',
  `goods_category_seo_info` varchar(512) NOT NULL DEFAULT '' COMMENT '扩展信息,SEO关键字用,多个用英文分号分开',
  `child_node` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否子节点，0--不是子节点，1--子节点',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`goods_category_id`),
  UNIQUE KEY `goods_category_code` (`goods_category_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品分类';

#
# Table structure for table tm_goods_code
#

CREATE TABLE `tm_goods_code` (
  `goods_code` int(11) NOT NULL AUTO_INCREMENT,
  `code_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态，0--无效，1--有效',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`goods_code`)
) ENGINE=MyISAM AUTO_INCREMENT=1025 DEFAULT CHARSET=utf8 COMMENT='商品编码表';

#
# Table structure for table tm_goods_label
#

CREATE TABLE `tm_goods_label` (
  `label_id` varchar(36) NOT NULL COMMENT '商品标签ID，openid',
  `label_name` varchar(128) NOT NULL COMMENT '产品标签名称，名称必须唯一',
  `label_desc` varchar(256) DEFAULT NULL COMMENT '产品标签简介',
  `label_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态，0--无效(删除)，1--有效',
  `label_sort` int(11) NOT NULL DEFAULT '0' COMMENT '标签排序',
  `label_icon` varchar(256) NOT NULL COMMENT '标签图标',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `label_parent_id` varchar(36) DEFAULT '0' COMMENT '父类ID',
  `label_level` tinyint(3) NOT NULL DEFAULT '1' COMMENT '等级',
  PRIMARY KEY (`label_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品标签信息';

#
# Table structure for table tm_goods_label_relation
#

CREATE TABLE `tm_goods_label_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label_id` varchar(36) NOT NULL COMMENT '商品标签ID，tm_product_label表中的label_id',
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods表中的goods_id',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='商品标签关系表';

#
# Table structure for table tm_goods_label_relation_check
#

CREATE TABLE `tm_goods_label_relation_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label_id` varchar(36) NOT NULL COMMENT '商品标签ID，tm_product_label表中的label_id',
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods表中的goods_id',
  `admin_id` varchar(36) DEFAULT NULL COMMENT '审核人员ID,tm_admin_user表中的id',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='商品标签关系表_审核';

#
# Table structure for table tm_goods_label_relation_edit
#

CREATE TABLE `tm_goods_label_relation_edit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label_id` varchar(36) NOT NULL COMMENT '商品标签ID，tm_product_label表中的label_id',
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods表中的goods_id',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='商品标签关系表_编辑状态';

#
# Table structure for table tm_goods_operation_log
#

CREATE TABLE `tm_goods_operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(36) NOT NULL COMMENT '后台用户登录ID,tm_admin_user表中的ID',
  `user_name` varchar(64) NOT NULL COMMENT '后台登录用户账户，tm_admin_user表中的username',
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods_base_info表中的goods_id',
  `operation_content` varchar(512) NOT NULL COMMENT '用户操作内容',
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间，存的是时间戳',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品上下架日志';

#
# Table structure for table tm_goods_special
#

CREATE TABLE `tm_goods_special` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods表中的goods_id',
  `special_name` varchar(64) NOT NULL DEFAULT '' COMMENT '特性名称',
  `special_content_pc` longtext NOT NULL COMMENT 'PC端特性内容',
  `special_content_mobile` longtext NOT NULL COMMENT '移动端特性内容',
  `special_sort` int(11) NOT NULL DEFAULT '0' COMMENT '配置信息排序',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COMMENT='商品特性信息';

#
# Table structure for table tm_goods_special_check
#

CREATE TABLE `tm_goods_special_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods表中的goods_id',
  `special_name` varchar(64) NOT NULL DEFAULT '' COMMENT '特性名称',
  `special_content_pc` longtext NOT NULL COMMENT 'PC端特性内容',
  `special_content_mobile` longtext NOT NULL COMMENT '移动端特性内容',
  `special_sort` int(11) NOT NULL DEFAULT '0' COMMENT '配置信息排序',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 COMMENT='商品特性信息_审核状态';

#
# Table structure for table tm_goods_special_edit
#

CREATE TABLE `tm_goods_special_edit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods表中的goods_id',
  `special_name` varchar(64) NOT NULL DEFAULT '' COMMENT '特性名称',
  `special_content_pc` longtext NOT NULL COMMENT 'PC端特性内容',
  `special_content_mobile` longtext NOT NULL COMMENT '移动端特性内容',
  `special_sort` int(11) NOT NULL DEFAULT '0' COMMENT '配置信息排序',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=176 DEFAULT CHARSET=utf8 COMMENT='商品特性信息_编辑状态';

#
# Table structure for table tm_goods_stock_info
#

CREATE TABLE `tm_goods_stock_info` (
  `stock_id` varchar(36) NOT NULL COMMENT '商品库存ID，openID',
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods_base_info表中的goods_id',
  `goods_name` varchar(255) DEFAULT NULL COMMENT '商品名称，tm_goods_base_info中的goods_name',
  `goods_code` varchar(10) NOT NULL COMMENT '商品编码，tm_goods_base_info表中的goods_code',
  `total_stocks` int(11) NOT NULL DEFAULT '0' COMMENT '商品总库存数',
  `cur_sale_stocks` int(11) NOT NULL DEFAULT '0' COMMENT '当前销售库存数',
  `occupy_stocks` int(11) NOT NULL DEFAULT '0' COMMENT '当前占用库存数',
  `freeze_stocks` int(11) NOT NULL DEFAULT '0' COMMENT '当前冻结存数',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `shops_code` varchar(36) DEFAULT '' COMMENT '商家code，从商品表带过来',
  `stock_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '库存状态，0--停用，1--启用',
  PRIMARY KEY (`stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品库存信息表';

#
# Table structure for table tm_goods_stock_log
#

CREATE TABLE `tm_goods_stock_log` (
  `log_id` varchar(36) NOT NULL COMMENT '商品库存日志ID，openID',
  `stock_id` varchar(36) NOT NULL COMMENT '商品库存ID，openID',
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods_base_info表中的goods_id',
  `goods_name` varchar(255) DEFAULT NULL COMMENT '商品名称，tm_goods_base_info中的goods_name',
  `goods_code` varchar(10) NOT NULL COMMENT '商品编码，tm_goods_base_info表中的goods_code',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `user_id` varchar(36) NOT NULL COMMENT '管理员ID',
  `handle_number` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作数量',
  `stock_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '操作类型，1--增加，2--减少',
  `shops_code` varchar(36) DEFAULT NULL COMMENT '商家编码',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品库存日志表';

#
# Table structure for table tm_goods_type
#

CREATE TABLE `tm_goods_type` (
  `type_id` varchar(36) NOT NULL COMMENT '产品类型ID，openid',
  `type_name` varchar(128) NOT NULL COMMENT '产品类型名称',
  `type_code` varchar(5) NOT NULL COMMENT '产品类型编码,从10开始',
  `physics_type_id` tinyint(3) NOT NULL COMMENT '物理类型，枚举常量',
  `type_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态，0--无效(删除)，1--上架，2--下架',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `type_code` (`type_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品类型';

#
# Table structure for table tm_goods_type_brand_relation
#

CREATE TABLE `tm_goods_type_brand_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` varchar(36) NOT NULL COMMENT '产品类型ID，tm_goods_type表中的type_id',
  `brand_id` varchar(36) NOT NULL COMMENT '产品品牌ID，tm_goods_brand表中的type_id',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='商品类别所关联的品牌';

#
# Table structure for table tm_goods_type_label_relation
#

CREATE TABLE `tm_goods_type_label_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` varchar(36) NOT NULL COMMENT '产品类型ID，tm_goods_type表中的type_id',
  `label_id` varchar(36) NOT NULL COMMENT '商品标签ID，tm_goods_label表中的label_id',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品类型与商品标签关系表';

#
# Table structure for table tm_goods_use_detail_info
#

CREATE TABLE `tm_goods_use_detail_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_cur_id` varchar(50) DEFAULT NULL COMMENT '订单编号',
  `shops_code` varchar(50) DEFAULT NULL COMMENT '商家编码',
  `order_id` varchar(36) DEFAULT NULL COMMENT '订单编号，guid',
  `order_goods_id` varchar(36) DEFAULT NULL COMMENT '订单商品id,guid',
  `goods_code` varchar(36) NOT NULL DEFAULT '' COMMENT '商品编码',
  `goods_name` varchar(128) NOT NULL DEFAULT '' COMMENT '商品名称',
  `integral` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否固定积分，1--是，0--否',
  `max_integral` int(11) NOT NULL DEFAULT '0' COMMENT '最大可使用积分数',
  `goods_price` float(10,2) DEFAULT NULL COMMENT '销售价',
  `goods_number` int(11) NOT NULL DEFAULT '0' COMMENT '商品数量',
  `use_code` varchar(36) DEFAULT NULL COMMENT '对应的使用码',
  `delivery_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '提货方式，1-快递，2-自提',
  `order_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '订单类型，1-资源管理销售，2-其它平台',
  `pay_cash` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '应付金额',
  `pay_point` int(11) NOT NULL DEFAULT '0' COMMENT '应付积分',
  `real_pay_cash` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '实付金额',
  `real_pay_point` int(11) NOT NULL DEFAULT '0' COMMENT '实付积分',
  `real_pay_storage_money` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '实付储值卡',
  `real_pay_coupon_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '实付优惠券',
  `real_pay_coupon_code_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '实付优惠码',
  `use_coupon` varchar(36) DEFAULT '' COMMENT '使用优惠券',
  `use_coupon_code` varchar(255) DEFAULT NULL COMMENT '使用优惠码',
  `reverse_cash` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
  `reverse_point` int(11) NOT NULL DEFAULT '0' COMMENT '退款积分',
  `reverse_storage_money` int(11) NOT NULL DEFAULT '0' COMMENT '退款储值卡',
  `use_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '使用状态，1-未使用，2-已使用，3-已退货',
  `use_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '消费时间',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `member_id` varchar(36) NOT NULL DEFAULT '' COMMENT '会员编码',
  `pay_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '支付时间',
  `payment_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '支付方式',
  `order_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '订单状态',
  `coupon_category_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '优惠券分类，1--普通券，2-文惠券',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COMMENT='商品使用详情表';

#
# Table structure for table tm_member_appointment
#

CREATE TABLE `tm_member_appointment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_desc` varchar(256) NOT NULL COMMENT '商品内容',
  `goods_num` int(11) NOT NULL COMMENT '商品数量',
  `member_name` varchar(64) NOT NULL COMMENT '客户姓名',
  `member_mobile` varchar(18) NOT NULL COMMENT '客户手机号码',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `app_time` datetime NOT NULL COMMENT '申请时间',
  `app_status` tinyint(3) NOT NULL COMMENT '状态',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `shops_code` varchar(50) DEFAULT NULL COMMENT '商家编码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户申请预约表';

#
# Table structure for table tm_member_use_code
#

CREATE TABLE `tm_member_use_code` (
  `use_id` varchar(36) NOT NULL DEFAULT '',
  `member_id` varchar(36) NOT NULL DEFAULT '' COMMENT '会员ID,tm_user_member_base_info表中的会员ID',
  `order_id` varchar(36) DEFAULT '' COMMENT '订单ID，tm_order_base_info表中的order_id',
  `order_goods_id` varchar(36) DEFAULT '' COMMENT '订单ID，tm_order_goods_info表中的order_goods_id',
  `use_code` varchar(36) NOT NULL DEFAULT '' COMMENT '使用码',
  `code_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '使用码类型，1--消费码，2--提货码',
  `status` tinyint(3) DEFAULT '1' COMMENT '状态，0--无效，1--未使用，2--已使用',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `goods_code` varchar(36) DEFAULT NULL COMMENT '商品编码',
  PRIMARY KEY (`use_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户消费码表';

#
# Table structure for table tm_order_base_info
#

CREATE TABLE `tm_order_base_info` (
  `order_id` varchar(36) NOT NULL COMMENT '订单ID，openID',
  `parent_id` varchar(36) DEFAULT '' COMMENT '父订单ID，tm_order_info表中的id',
  `order_cur_id` varchar(17) DEFAULT '' COMMENT '当前订单ID，时间+8位，数字ID从tm_order_tmpid表中获取',
  `order_external_id` varchar(24) DEFAULT '' COMMENT '订单对外ID，和外部交互时用',
  `member_id` varchar(36) DEFAULT '' COMMENT '会员ID,tm_user_member_base_info表中的会员ID',
  `order_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态，0--无效(删除)，1--待处理',
  `order_internal_status` int(11) NOT NULL DEFAULT '10' COMMENT '内部运营状态，枚举值',
  `pay_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，10--未支付，11--支付中，12--支付完成',
  `order_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '订单类型，1--普通订单，2--闪购订单，3--团购订单，4--众筹订单，5--OTO订单，6--预售订单',
  `order_way` tinyint(3) NOT NULL DEFAULT '1' COMMENT '下单设备，1--PC，2--wap，3--app',
  `total_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单总金额',
  `pay_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单已付金额',
  `pay_total_cash` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单应付现金总金额',
  `name` varchar(64) DEFAULT '' COMMENT '收货人姓名',
  `province` varchar(64) DEFAULT '' COMMENT '省/直辖市',
  `city` varchar(64) DEFAULT '' COMMENT '市/县/区',
  `county` varchar(255) DEFAULT '' COMMENT '县区',
  `address` varchar(126) DEFAULT '' COMMENT '详细地址',
  `mobile` varchar(16) DEFAULT '' COMMENT '收货人手机或者座机',
  `email` varchar(100) DEFAULT '' COMMENT '收货人邮箱',
  `user_remark` varchar(255) DEFAULT '' COMMENT '用户备注',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `pay_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '支付类型，1--在线支付',
  `shops_code` varchar(36) DEFAULT '' COMMENT '商家编码',
  `is_invoice` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否需要发票，0-不需要，1--需要',
  `invoice_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '1--未开，2--已开',
  `cancel_way` tinyint(3) NOT NULL DEFAULT '0' COMMENT '取消方式，1--后台取消，2--前台取消，3--自动取消',
  `delivery_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '收货方式，1--快递，2自提',
  `is_part` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否拆单，0-未拆单，1-拆单',
  `pay_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '支付时间',
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_cur_id` (`order_cur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户订单信息表';

#
# Table structure for table tm_order_comment
#

CREATE TABLE `tm_order_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(36) NOT NULL DEFAULT '' COMMENT '订单ID,tm_order_base_info中的order_id',
  `user_id` varchar(36) NOT NULL DEFAULT '' COMMENT '操作ID,tm_admin_user中的user_id',
  `comment` varchar(1024) DEFAULT NULL COMMENT '备注内容',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='订单运营备注表';

#
# Table structure for table tm_order_express
#

CREATE TABLE `tm_order_express` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(36) NOT NULL DEFAULT '' COMMENT '订单ID,tm_order_base_info中order_id',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '类型，1-整单发，2-商品发',
  `order_goods_id` varchar(255) DEFAULT NULL COMMENT '订单商品ID，tm_order_goods_info中的order_goods_id',
  `shipper_name` varchar(255) DEFAULT '' COMMENT '快递商名称',
  `express_order` varchar(255) DEFAULT NULL COMMENT '快递单号',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '寄件时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='订单快递信息表';

#
# Table structure for table tm_order_goods_discount_info
#

CREATE TABLE `tm_order_goods_discount_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID ',
  `order_goods_id` varchar(36) NOT NULL COMMENT '订单商品ID，tm_order_goods_info表中的order_goods_id',
  `order_id` varchar(36) NOT NULL COMMENT '订单ID，tm_order_base_info表中的order_id',
  `discount_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '折扣类型，1--套餐，2--赠品，3--优惠券，4--优惠码，5-积分',
  `discount_code` varchar(32) NOT NULL DEFAULT '' COMMENT '优惠码或者优惠券编码，如果是积分则是积分数量',
  `discount_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠金额',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='订单商品折扣优惠信息';

#
# Table structure for table tm_order_goods_info
#

CREATE TABLE `tm_order_goods_info` (
  `order_goods_id` varchar(36) NOT NULL DEFAULT '' COMMENT '订单商品ID，openID',
  `order_id` varchar(36) DEFAULT '' COMMENT '订单ID，tm_order_base_info表中的order_id',
  `order_goods_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '订单商品类型，1--单品，2--赠品，3--套餐，4--选件搭售，5--捆绑销售,6--大礼包',
  `is_primary` tinyint(2) NOT NULL DEFAULT '1' COMMENT '是否是主商品，0--非主商品，1--主商品',
  `primary_order_goods_id` varchar(36) NOT NULL DEFAULT '' COMMENT '主订单商品ID，tm_order_goods_info中的order_goods_id,单品时该值为空',
  `goods_code` varchar(10) DEFAULT '' COMMENT '商品编码，tm_goods_base_info表中的goods_code',
  `goods_extern_code` varchar(36) DEFAULT '' COMMENT '商品扩展码，crm的商品ID',
  `goods_category_code` varchar(5) DEFAULT '' COMMENT '商品分类编码，tm_goods_base_info表中的goods_category_code',
  `goods_type_code` varchar(5) DEFAULT '' COMMENT '商品类型编码，tm_goods_base_info表中的type_code',
  `goods_name` varchar(128) DEFAULT '' COMMENT '商品名称，tm_goods_base_info表中商品名称',
  `goods_materiel_code` varchar(36) DEFAULT '' COMMENT '商品物料编码，tm_goods_base_info表中物料编码',
  `goods_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品销售价',
  `promotion_content` varchar(1024) DEFAULT '' COMMENT '商品促销语',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `relation_goods` varchar(1024) DEFAULT '' COMMENT '当order_goods_type是6时，是大礼包的具体商品的json集合',
  `goods_number` int(11) NOT NULL DEFAULT '0' COMMENT '商品数量',
  `shops_code` varchar(36) DEFAULT '' COMMENT '商家编码',
  `order_goods_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '订单商品状态',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `integral` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否使用积分',
  `max_integral` int(11) NOT NULL DEFAULT '0' COMMENT '最大可用积分',
  `product_default_pic` varchar(512) DEFAULT NULL COMMENT '商品默认图片',
  PRIMARY KEY (`order_goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单商品信息';

#
# Table structure for table tm_order_invoice_info
#

CREATE TABLE `tm_order_invoice_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID ',
  `order_id` char(36) NOT NULL COMMENT '订单ID，tm_order_base_info表中的order_id',
  `user_id` varchar(36) DEFAULT '' COMMENT '开票用户ID，后台用户登录ID,tm_admin_user表中的ID,在开票时填写',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '发票方式，1--个人,2--公司',
  `open_way` tinyint(4) NOT NULL DEFAULT '1' COMMENT '发票类型，1--普通发票(纸质),2--普通发票(电子),3--增值税发票',
  `invoice_title` varchar(128) NOT NULL DEFAULT '' COMMENT '发票抬头',
  `content_way` tinyint(4) NOT NULL DEFAULT '1' COMMENT '发票内容方式，1--明细',
  `invoice_content` varchar(1024) DEFAULT '' COMMENT '发票内容方式，在正在开票的时候填写',
  `invoice_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '1--未开，2--已开',
  `invoice_crash` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '开票金额，在开票的时候填写',
  `vat_comapny_name` varchar(128) DEFAULT '' COMMENT '增值税发票公司名称',
  `vat_code` varchar(128) DEFAULT '' COMMENT '纳税人识别号',
  `vat_register_address` varchar(256) DEFAULT '' COMMENT '单位注册地址',
  `vat_register_phone` varchar(128) DEFAULT '' COMMENT '单位注册电话',
  `vat_open_bank` varchar(128) DEFAULT '' COMMENT '开户行',
  `vat_bank_account` varchar(128) DEFAULT '' COMMENT '开户行账户',
  `business_license_img` varchar(256) DEFAULT '' COMMENT '营业执照副本',
  `organization_code_img` varchar(256) DEFAULT '' COMMENT '组织机构副本',
  `open_account_img` varchar(256) DEFAULT '' COMMENT '开户许可证',
  `taxpayer_img` varchar(256) DEFAULT '' COMMENT '纳税人认定',
  `vat_rev_name` varchar(128) DEFAULT '' COMMENT '收票人姓名',
  `vat_rev_phone` varchar(18) DEFAULT '' COMMENT '收票人手机',
  `vat_rev_email` varchar(100) DEFAULT '' COMMENT '收票人邮箱',
  `vat_province` varchar(64) DEFAULT '' COMMENT '省/直辖市',
  `vat_city` varchar(64) DEFAULT '' COMMENT '市/县/区',
  `vat_address` varchar(126) DEFAULT '' COMMENT '地址详细',
  `open_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开票时间',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后一次修改时间',
  `vat_county` varchar(255) DEFAULT '' COMMENT '县区',
  `invoice_code` varchar(255) DEFAULT '' COMMENT '发票号',
  `shipper_name` varchar(255) DEFAULT '' COMMENT '快递商名称',
  `express_order` varchar(255) DEFAULT '' COMMENT '快递单号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单中的发票信息表';

#
# Table structure for table tm_order_loginfo
#

CREATE TABLE `tm_order_loginfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(36) NOT NULL COMMENT '订单编号',
  `comment` varchar(1024) NOT NULL COMMENT '日志内容',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '类型',
  `operate_user_id` varchar(36) NOT NULL COMMENT '管理员ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COMMENT='订单日志表';

#
# Table structure for table tm_order_operate_log
#

CREATE TABLE `tm_order_operate_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID ',
  `order_id` varchar(36) NOT NULL COMMENT '订单ID，openID',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态，1--正常',
  `user_id` varchar(36) NOT NULL COMMENT '后台用户登录ID,tm_admin_user表中的ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单运营备注';

#
# Table structure for table tm_order_pay_discount_info
#

CREATE TABLE `tm_order_pay_discount_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID ',
  `order_id` char(36) NOT NULL COMMENT '订单ID，tm_order_base_info 表中的order_id',
  `discount_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '折扣类型，1--运营修改订单总价，2--优惠码，3--优惠券，4-积分，5-储值卡',
  `discount_code` varchar(32) NOT NULL DEFAULT '' COMMENT '优惠码或者优惠券编码',
  `discount_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠金额',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='订单支付优惠信息';

#
# Table structure for table tm_order_payment_info
#

CREATE TABLE `tm_order_payment_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID ',
  `payment_serial_number` varchar(64) DEFAULT NULL COMMENT '商城支付流水号,tm_order_payment_serial_number_info表中的payment_serial_number',
  `order_id` varchar(36) DEFAULT NULL COMMENT 'tm_order_base_info 表中的order_id',
  `payment_trade_no` varchar(255) DEFAULT NULL COMMENT '支付接口交易号',
  `payment_trade_status` varchar(255) DEFAULT NULL COMMENT '支付接口交易状态',
  `payment_notify_id` varchar(255) DEFAULT NULL COMMENT '支付接口异步通知ID',
  `payment_notify_time` varchar(255) DEFAULT NULL COMMENT '通知时间',
  `payment_buyer_account` varchar(255) DEFAULT NULL COMMENT '购买者账号',
  `payment_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '付款通道，1--支付宝，2--银联,3--货到付款支付，4--打款支付',
  `payment_fee` float NOT NULL DEFAULT '0' COMMENT '支付的现金',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COMMENT='订单支付信息';

#
# Table structure for table tm_order_payment_serial_number_info
#

CREATE TABLE `tm_order_payment_serial_number_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID ',
  `order_id` char(36) NOT NULL COMMENT 'tm_original_order_info 表中的order_id',
  `payment_serial_number` varchar(64) DEFAULT NULL COMMENT '商城支付流水号',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_serial_number` (`payment_serial_number`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='订单支付流水号和订单ID关联信息';

#
# Table structure for table tm_order_tmpid
#

CREATE TABLE `tm_order_tmpid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COMMENT='订单ID数字表';

#
# Table structure for table tm_other_use_code
#

CREATE TABLE `tm_other_use_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `use_code` varchar(100) NOT NULL DEFAULT '' COMMENT '使用码编码',
  `code_pwd` varchar(255) DEFAULT NULL COMMENT '卡密',
  `code_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '编码类型',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态，1--购买，2-激活，3-预约，4-使用',
  `valid_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '有效期截至时间',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `member_id` varchar(36) NOT NULL DEFAULT '' COMMENT '会员ID',
  `shops_code` varchar(36) DEFAULT '' COMMENT '商家编码',
  `goods_code` varchar(36) DEFAULT NULL COMMENT '商品编码',
  `order_id` varchar(36) DEFAULT NULL COMMENT '订单ID',
  `user_name` varchar(255) DEFAULT NULL COMMENT '会员账号',
  `active_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '激活时间',
  `use_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '使用时间',
  `order_goods_id` varchar(36) DEFAULT '' COMMENT '订单ID，tm_order_goods_info表中的order_goods_id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='其它使用码表';

#
# Table structure for table tm_product_conf_param_category
#

CREATE TABLE `tm_product_conf_param_category` (
  `param_category_id` varchar(36) NOT NULL COMMENT '产品配置参数分类ID open id',
  `category_name` varchar(64) NOT NULL DEFAULT '' COMMENT '配置属性分类名称，同一分类类别中，分类名称不能重复',
  `category_sort` int(11) NOT NULL DEFAULT '0' COMMENT '配置属性分类排序,倒序排序',
  `category_type` int(11) NOT NULL DEFAULT '0' COMMENT '分类类别',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态，0--无效(删除)，1--有效',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`param_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品配置参数分类';

#
# Table structure for table tm_product_conf_param_info
#

CREATE TABLE `tm_product_conf_param_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `goods_id` varchar(36) NOT NULL COMMENT '产品ID，tm_goods_base_info表中的goods_id',
  `param_value_id` varchar(36) NOT NULL COMMENT '产品配置参数值ID，tm_product_conf_param_value表中的param_value_id',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品配置参数信息';

#
# Table structure for table tm_product_conf_param_info_check
#

CREATE TABLE `tm_product_conf_param_info_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `goods_id` varchar(36) NOT NULL COMMENT '产品ID，tm_goods_base_info表中的goods_id',
  `param_category_id` varchar(36) NOT NULL COMMENT '产品配置参数分类ID，tm_product_conf_param_category表中的param_category_id',
  `category_name` varchar(128) NOT NULL DEFAULT '' COMMENT '配置属性分类名称，tm_product_conf_param_category表中的category_name',
  `category_sort` int(11) NOT NULL DEFAULT '0' COMMENT '配置属性分类排序,tm_product_conf_param_category表中的category_sort',
  `param_id` varchar(36) NOT NULL COMMENT '产品配置参数id，tm_product_conf_param_name表中的param_id',
  `param_name` varchar(128) NOT NULL COMMENT '配置参数名称，tm_product_conf_param_name表中的param_name',
  `param_sort` int(11) NOT NULL DEFAULT '0' COMMENT '配置参数排序,tm_product_conf_param_name表中的param_sort',
  `param_value_id` varchar(36) NOT NULL COMMENT '产品配置参数值ID，tm_product_conf_param_value表中的param_value_id',
  `param_value` varchar(128) NOT NULL COMMENT '参数值，同一参数名称中，参数值不能重复',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品配置参数信息_审核状态';

#
# Table structure for table tm_product_conf_param_info_edit
#

CREATE TABLE `tm_product_conf_param_info_edit` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `goods_id` varchar(36) NOT NULL COMMENT '产品ID，tm_goods_base_info表中的goods_id',
  `param_category_id` varchar(36) NOT NULL COMMENT '产品配置参数分类ID，tm_product_conf_param_category表中的param_category_id',
  `category_name` varchar(128) NOT NULL DEFAULT '' COMMENT '配置属性分类名称，tm_product_conf_param_category表中的category_name',
  `category_sort` int(11) NOT NULL DEFAULT '0' COMMENT '配置属性分类排序,tm_product_conf_param_category表中的category_sort',
  `param_id` varchar(36) NOT NULL COMMENT '产品配置参数id，tm_product_conf_param_name表中的param_id',
  `param_name` varchar(128) NOT NULL COMMENT '配置参数名称，tm_product_conf_param_name表中的param_name',
  `param_sort` int(11) NOT NULL DEFAULT '0' COMMENT '配置参数排序,tm_product_conf_param_name表中的param_sort',
  `param_value_id` varchar(36) DEFAULT NULL COMMENT '产品配置参数值ID，tm_product_conf_param_value表中的param_value_id，值为空，为输入框',
  `param_value` varchar(128) NOT NULL COMMENT '参数值，同一参数名称中，参数值不能重复',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品配置参数信息_编辑状态';

#
# Table structure for table tm_product_conf_param_name
#

CREATE TABLE `tm_product_conf_param_name` (
  `param_id` varchar(36) NOT NULL COMMENT '商品配置参数id，open id',
  `param_category_id` varchar(36) NOT NULL COMMENT '产品配置参数分类ID，tm_product_conf_param_category表中的param_category_id',
  `param_name` varchar(64) NOT NULL COMMENT '配置参数名称，同一分类别中，参数名称不能重复',
  `param_sort` int(11) NOT NULL DEFAULT '0' COMMENT '配置参数排序，倒序排序',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态，0--无效(删除)，1--有效',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`param_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品配置参数';

#
# Table structure for table tm_product_conf_param_value
#

CREATE TABLE `tm_product_conf_param_value` (
  `param_value_id` varchar(36) NOT NULL COMMENT '产品配置参数值ID',
  `param_category_id` varchar(36) NOT NULL COMMENT '产品配置参数分类ID，tm_product_conf_param_category表中的param_category_id',
  `category_name` varchar(128) NOT NULL DEFAULT '' COMMENT '配置属性分类名称，tm_product_conf_param_category表中的category_name',
  `category_sort` int(11) NOT NULL DEFAULT '0' COMMENT '配置属性分类排序,tm_product_conf_param_category表中的category_sort',
  `param_id` varchar(36) NOT NULL COMMENT '产品配置参数id，tm_product_conf_param_name表中的param_id',
  `param_name` varchar(128) NOT NULL COMMENT '配置参数名称，tm_product_conf_param_name表中的param_name',
  `param_sort` int(11) NOT NULL DEFAULT '0' COMMENT '配置参数排序,tm_product_conf_param_name表中的param_sort',
  `param_value` varchar(128) NOT NULL COMMENT '参数值，同一参数名称中，参数值不能重复',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态，0--无效(删除)，1--有效',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`param_value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='配置参数所对应的值';

#
# Table structure for table tm_product_specification
#

CREATE TABLE `tm_product_specification` (
  `specification_id` varchar(36) NOT NULL COMMENT '规格ID，openid',
  `specification_name` varchar(128) NOT NULL COMMENT '规格名称',
  `type_id` varchar(36) NOT NULL COMMENT '产品类型ID，tm_goods_type表中的type_id',
  `specification_sort` int(11) NOT NULL DEFAULT '0' COMMENT '规格排序，前台展示顺序按照这个展示',
  `specification_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态，0--无效(删除)，1--上架，2--下架',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`specification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品规格参数';

#
# Table structure for table tm_promotion_bundles_activity_info
#

CREATE TABLE `tm_promotion_bundles_activity_info` (
  `activity_id` varchar(36) NOT NULL COMMENT '商品ID，openid',
  `activity_name` varchar(128) NOT NULL COMMENT '活动名称，活动名称唯一',
  `activity_remark` varchar(256) DEFAULT '' COMMENT '活动备注',
  `promotion_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '促销活动类型，1--赠品，2--赠品可选，3--套餐，4--搭售',
  `enable_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用状态，0--未启用，1--启用',
  `start_time` datetime NOT NULL COMMENT '活动开始时间',
  `end_time` datetime NOT NULL COMMENT '活动结束时间',
  `status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--编辑，20--待审核，21--审核通过，22--审核失败',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `is_pc` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'PC端支持，0--不支持，1--支持',
  `is_wap` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'wap端支持，0--不支持，1--支持',
  `is_app` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'APP端支持，0--不支持，1--支持',
  PRIMARY KEY (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='捆绑促销活动信息';

#
# Table structure for table tm_promotion_bundles_activity_info_check
#

CREATE TABLE `tm_promotion_bundles_activity_info_check` (
  `activity_id` varchar(36) NOT NULL COMMENT 'openID',
  `formal_activity_id` varchar(36) DEFAULT NULL COMMENT 'tm_promotion_bundles_activity_info表中对应的activity_id',
  `edit_activity_id` varchar(36) NOT NULL COMMENT 'tm_promotion_bundles_activity_info_edit表中对应的activity_id',
  `activity_name` varchar(128) NOT NULL COMMENT '活动名称，活动名称唯一',
  `activity_remark` varchar(256) DEFAULT '' COMMENT '活动备注',
  `promotion_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '促销活动类型，1--赠品，2--赠品可选，3--套餐，4--搭售',
  `enable_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用状态，0--未启用，1--启用',
  `start_time` datetime NOT NULL COMMENT '活动开始时间',
  `end_time` datetime NOT NULL COMMENT '活动结束时间',
  `status` tinyint(3) NOT NULL DEFAULT '11' COMMENT '状态，0--无效(删除)，10--编辑，20--待审核，21--审核通过，22--审核失败',
  `refuse_reason` varchar(128) DEFAULT '' COMMENT '拒绝原因',
  `admin_id` varchar(36) DEFAULT '' COMMENT '审核人员ID,tm_admin_user表中的id',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `is_pc` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'PC端支持，0--不支持，1--支持',
  `is_wap` tinyint(1) DEFAULT NULL COMMENT 'wap端支持，0--不支持，1--支持',
  `is_app` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'APP端支持，0--不支持，1--支持',
  `check_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  PRIMARY KEY (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='捆绑促销活动信息_审核';

#
# Table structure for table tm_promotion_bundles_activity_info_edit
#

CREATE TABLE `tm_promotion_bundles_activity_info_edit` (
  `activity_id` varchar(36) NOT NULL COMMENT '促销活动ID，tm_promotion_bundles_activity_info表中的activity_id',
  `formal_activity_id` varchar(36) DEFAULT NULL COMMENT 'tm_promotion_bundles_activity_info表中对应的activity_id',
  `activity_name` varchar(128) NOT NULL COMMENT '活动名称，活动名称唯一',
  `activity_remark` varchar(256) DEFAULT '' COMMENT '活动备注',
  `promotion_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '促销活动类型，1--赠品，2--赠品可选，3--套餐，4--搭售',
  `enable_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用状态，0--未启用，1--启用',
  `start_time` datetime NOT NULL COMMENT '活动开始时间',
  `end_time` datetime NOT NULL COMMENT '活动结束时间',
  `status` tinyint(3) NOT NULL DEFAULT '11' COMMENT '状态，0--无效(删除)，10--编辑，20--待审核，21--审核通过，22--审核失败',
  `refuse_reason` varchar(128) DEFAULT '' COMMENT '拒绝原因',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `is_pc` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'PC端支持，0--不支持，1--支持',
  `is_wap` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'wap端支持，0--不支持，1--支持',
  `is_app` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'APP端支持，0--不支持，1--支持',
  PRIMARY KEY (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='捆绑促销活动信息_编辑状态';

#
# Table structure for table tm_promotion_bundles_activity_operation_log
#

CREATE TABLE `tm_promotion_bundles_activity_operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(36) NOT NULL COMMENT '后台用户登录ID,tm_admin_user表中的ID',
  `user_name` varchar(64) NOT NULL COMMENT '后台登录用户账户，tm_admin_user表中的username',
  `activity_id` varchar(36) NOT NULL COMMENT '商品ID，tm_promotion_bundles_activity_info表中的goods_id',
  `operation_content` varchar(512) NOT NULL COMMENT '用户操作内容',
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间，存的是时间戳',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='促销活动上下架日志';

#
# Table structure for table tm_promotion_bundles_goods_info
#

CREATE TABLE `tm_promotion_bundles_goods_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` varchar(36) NOT NULL COMMENT 'tm_promotion_bundles_activity_info 表的activity_id',
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods_base_info表的goods_id',
  `goods_code` varchar(10) NOT NULL COMMENT '商品编码，tm_goods_base_info表的goods_code',
  `type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1--主品，2--捆绑品',
  `promotion_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠金额',
  `count` int(10) NOT NULL DEFAULT '0' COMMENT '数量，主品时，表示需要购买多少才满足条件，主要是赠品及赠品可选才有效，捆绑商品时，表示赠送多少',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COMMENT='捆绑销售商品信息';

#
# Table structure for table tm_promotion_bundles_goods_info_check
#

CREATE TABLE `tm_promotion_bundles_goods_info_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` varchar(36) NOT NULL COMMENT 'tm_promotion_bundles_activity_info 表的activity_id',
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods_base_info表的goods_id',
  `goods_code` varchar(10) NOT NULL COMMENT '商品编码，tm_goods_base_info表的goods_code',
  `type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1--主品，2--捆绑品',
  `promotion_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠金额',
  `count` int(10) NOT NULL DEFAULT '0' COMMENT '数量，主品时，表示需要购买多少才满足条件，主要是赠品及赠品可选才有效，捆绑商品时，表示赠送多少',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='捆绑销售商品信息_审核';

#
# Table structure for table tm_promotion_bundles_goods_info_edit
#

CREATE TABLE `tm_promotion_bundles_goods_info_edit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` varchar(36) NOT NULL COMMENT 'tm_promotion_bundles_activity_info 表的activity_id',
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods_base_info表的goods_id',
  `goods_code` varchar(10) NOT NULL COMMENT '商品编码，tm_goods_base_info表的goods_code',
  `type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1--主品，2--捆绑品',
  `promotion_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠金额',
  `count` int(10) NOT NULL DEFAULT '0' COMMENT '数量，主品时，表示需要购买多少才满足条件，主要是赠品及赠品可选才有效，捆绑商品时，表示赠送多少',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COMMENT='捆绑销售商品信息_编辑状态';

#
# Table structure for table tm_relation_goods_info
#

CREATE TABLE `tm_relation_goods_info` (
  `id` varchar(36) NOT NULL COMMENT '商品ID，openid',
  `relation_id` varchar(36) NOT NULL COMMENT '关联ID，openID，所有关联的商品ID相同',
  `goods_code` varchar(10) NOT NULL COMMENT '商品编码，tm_goods_base_info表中的goods_code',
  `goods_sku_content` varchar(512) NOT NULL COMMENT '商品关联内容，JSON格式保存,规格ID为key,规格值为value',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `goods_code` (`goods_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='关联商品信息列表';

#
# Table structure for table tm_relation_goods_info_check
#

CREATE TABLE `tm_relation_goods_info_check` (
  `id` varchar(36) NOT NULL COMMENT '商品ID，openid',
  `relation_id` varchar(36) NOT NULL COMMENT '关联ID，openID，所有关联的商品ID相同',
  `goods_code` varchar(10) NOT NULL COMMENT '商品编码，tm_goods_base_info表中的goods_code',
  `goods_sku_content` varchar(512) NOT NULL COMMENT '商品关联内容，JSON格式保存,规格ID为key,规格值为value',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='关联商品信息列表_审核状态';

#
# Table structure for table tm_relation_goods_info_edit
#

CREATE TABLE `tm_relation_goods_info_edit` (
  `id` varchar(36) NOT NULL COMMENT '商品ID，openid',
  `relation_id` varchar(36) NOT NULL COMMENT '关联ID，openID，所有关联的商品ID相同',
  `goods_code` varchar(10) NOT NULL COMMENT '商品编码，tm_goods_base_info表中的goods_code',
  `goods_sku_content` varchar(512) NOT NULL COMMENT '商品关联内容，JSON格式保存,规格ID为key,规格值为value',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `goods_code` (`goods_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='关联商品信息列表_编辑状态';

#
# Table structure for table tm_reverse_money_info
#

CREATE TABLE `tm_reverse_money_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reverse_order_id` varchar(36) NOT NULL COMMENT '反向订单ID，openID',
  `forward_order_id` varchar(36) NOT NULL COMMENT '原始订单ID，tm_order_base_info表中的order_id',
  `forward_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '应退金额',
  `reverse_value` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '实退金额',
  `reverse_serial_number` varchar(64) NOT NULL DEFAULT '0' COMMENT '退款流水编号',
  `reverse_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '退款状态，1--待退款，2--已退款，3--取消退款',
  `comment` varchar(1024) DEFAULT NULL COMMENT '备注',
  `poundage` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `forward_point` int(11) DEFAULT '0' COMMENT '应退积分',
  `reverse_point` int(11) DEFAULT '0' COMMENT '实退积分',
  `forward_storage_money` float(10,2) DEFAULT '0.00' COMMENT '应退储值卡金额',
  `reverse_storage_money` float(10,2) DEFAULT '0.00' COMMENT '实退储值卡金额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='反向订单退款信息';

#
# Table structure for table tm_reverse_order_base_info
#

CREATE TABLE `tm_reverse_order_base_info` (
  `reverse_order_id` varchar(36) NOT NULL COMMENT '反向订单ID，openID',
  `forward_order_id` varchar(36) NOT NULL COMMENT '原始订单ID，tm_order_base_info表中的order_id',
  `order_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '订单类型，1--换货订单，2--退货订单',
  `order_cur_id` varchar(17) NOT NULL COMMENT '反向订单ID，时间+8位，数字ID从tm_order_tmpid表中获取',
  `order_external_id` varchar(24) DEFAULT '' COMMENT '反向订单对外ID，和外部交互时用',
  `member_id` varchar(36) NOT NULL COMMENT '会员ID,和tm_order_base_info表中的member_id一致',
  `order_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态，0--无效(删除)，10--待处理，20--审核拒绝，30--审核通过,40--取消',
  `order_way` tinyint(3) NOT NULL DEFAULT '1' COMMENT '申请设备，1--PC，2--wap，3--app',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `forward_order_cur_id` varchar(36) DEFAULT NULL COMMENT 'tm_order_base_info表中的order_cur_id',
  `shops_code` varchar(36) DEFAULT '' COMMENT '商家编号，tm_order_base_info中的shops_code',
  `comment` varchar(1024) DEFAULT NULL COMMENT '备注内容',
  PRIMARY KEY (`reverse_order_id`),
  KEY `新建索引` (`order_cur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户反向订单(退换货)信息表';

#
# Table structure for table tm_reverse_order_comment
#

CREATE TABLE `tm_reverse_order_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(36) NOT NULL DEFAULT '' COMMENT '订单ID,tm_reverse_order_base_info中的reverse_order_id',
  `user_id` varchar(36) NOT NULL DEFAULT '' COMMENT '操作ID,tm_admin_user中的user_id',
  `comment` varchar(1024) DEFAULT NULL COMMENT '备注内容',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='订单运营备注表';

#
# Table structure for table tm_reverse_order_express_info
#

CREATE TABLE `tm_reverse_order_express_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID ',
  `reverse_order_id` varchar(36) NOT NULL COMMENT '订单ID，tm_reverse_order_base_info表中的reverse_order_id',
  `express_company_code` varchar(128) NOT NULL COMMENT '快递公司编码',
  `express_company_name` varchar(128) NOT NULL COMMENT '快递公司名称',
  `express_id` varchar(128) NOT NULL COMMENT '快递单号',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='反向订单快递信息';

#
# Table structure for table tm_reverse_order_goods_info
#

CREATE TABLE `tm_reverse_order_goods_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID ',
  `order_goods_id` varchar(36) NOT NULL COMMENT '反向订单商品ID，tm_order_goods_info表中的order_goods_id',
  `reverse_order_id` varchar(36) NOT NULL COMMENT 'tm_reverse_order_base_info表中reverse_order_id',
  `numbers` int(11) NOT NULL DEFAULT '1' COMMENT '数量',
  `order_goods_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '订单商品类型，1--单品，2--赠品，3--套餐，4--选件搭售，5--捆绑销售',
  `is_primary` tinyint(2) NOT NULL DEFAULT '1' COMMENT '是否是主商品，0--非主商品，1--主商品',
  `primary_order_goods_id` varchar(36) DEFAULT '' COMMENT '主订单商品ID，tm_order_goods_info中的order_goods_id,单品时该值为空',
  `goods_code` varchar(10) DEFAULT '' COMMENT '商品编码，tm_goods_base_info表中的goods_code',
  `goods_category_code` varchar(5) DEFAULT '' COMMENT '商品分类编码，tm_goods_base_info表中的goods_category_code',
  `goods_type_code` varchar(5) DEFAULT '' COMMENT '商品类型编码，tm_goods_base_info表中的type_code',
  `goods_name` varchar(128) DEFAULT '' COMMENT '商品名称，tm_goods_base_info表中商品名称',
  `goods_materiel_code` varchar(36) DEFAULT '' COMMENT '商品物料编码，tm_goods_base_info表中物料编码',
  `goods_price` float(10,2) DEFAULT '0.00' COMMENT '商品销售价',
  `promotion_content` varchar(1024) DEFAULT '' COMMENT '商品促销语',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `forward_order_goods_id` varchar(36) DEFAULT '' COMMENT '原订单商品ID',
  `shops_code` varchar(36) DEFAULT '' COMMENT '商家编码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='反向订单商品信息';

#
# Table structure for table tm_reverse_order_operate_log
#

CREATE TABLE `tm_reverse_order_operate_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID ',
  `reverse_order_id` varchar(36) NOT NULL COMMENT '订单ID，tm_reverse_order_base_info表中的reverse_order_id',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态，1--正常',
  `user_id` varchar(36) NOT NULL COMMENT '后台用户登录ID,tm_admin_user表中的ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单运营备注';

#
# Table structure for table tm_server_goods_bind_info
#

CREATE TABLE `tm_server_goods_bind_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_goods_id` varchar(36) NOT NULL COMMENT '服务商品ID，tm_goods_base_info表中的goods_id',
  `primary_goods_id` varchar(36) NOT NULL COMMENT '主品商品ID，tm_goods_base_info表中的goods_id',
  `server_goods_name` varchar(128) NOT NULL COMMENT '商品名称',
  `server_promotion_content` varchar(1024) NOT NULL COMMENT '服务商品简介',
  `server_price_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'PC端销售价',
  `server_wap_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'PC端销售价',
  `server_app_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'PC端销售价',
  `server_weixin_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT 'PC端销售价',
  `bind_status` tinyint(3) NOT NULL DEFAULT '11' COMMENT '状态，0--无效(删除)，10--绑定，11--解绑',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='服务商品绑定主品信息列表';

#
# Table structure for table tm_shops_base_info
#

CREATE TABLE `tm_shops_base_info` (
  `shops_id` varchar(36) NOT NULL COMMENT '商铺ID，openid',
  `shops_code` varchar(8) NOT NULL COMMENT '商铺编码，从100开始',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `shops_name` varchar(128) NOT NULL DEFAULT '' COMMENT '商铺名称',
  `shops_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '商铺状态，0--删除，1--有效',
  `shop_address` varchar(512) DEFAULT '' COMMENT '商家地址',
  `shop_tel` varchar(512) DEFAULT NULL COMMENT '商家电话',
  `office_hours` varchar(255) DEFAULT NULL COMMENT '营业时间',
  `shops_logo` varchar(255) DEFAULT NULL COMMENT '商家logo',
  `shops_desc` varchar(1024) DEFAULT NULL COMMENT '商家简介',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `alias_name` varchar(128) DEFAULT NULL COMMENT '别名，展示在前台使用',
  PRIMARY KEY (`shops_id`),
  UNIQUE KEY `shops_code` (`shops_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商铺基本信息';

#
# Table structure for table tm_shops_contact_info
#

CREATE TABLE `tm_shops_contact_info` (
  `shops_id` varchar(36) NOT NULL DEFAULT '' COMMENT '商家ID',
  `contact_id` varchar(36) NOT NULL DEFAULT '',
  `contact_name` varchar(255) NOT NULL DEFAULT '' COMMENT '联系人姓名',
  `contact_phone` varchar(14) DEFAULT NULL COMMENT '联系人手机号码',
  `contact_email` varchar(255) DEFAULT NULL COMMENT '联系人邮箱',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商家联系人表';

#
# Table structure for table tm_sms_log
#

CREATE TABLE `tm_sms_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(256) NOT NULL DEFAULT '' COMMENT '发送短信手机号码,多个以,分割',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '短信内容',
  `send_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发送时间',
  `send_log` text NOT NULL COMMENT '发送短信的日志，以序列化字符串形式存放',
  `send_id` varchar(36) NOT NULL DEFAULT '' COMMENT '操作人ID',
  `send_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态，1-发送成功，2-发送失败',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='发送短信日志表';

#
# Table structure for table tm_sms_template_info
#

CREATE TABLE `tm_sms_template_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(255) NOT NULL DEFAULT '' COMMENT '要发送的表名',
  `sms_template` varchar(1024) NOT NULL DEFAULT '' COMMENT '短信模板',
  `params` varchar(255) DEFAULT NULL COMMENT '表触发的条件，多个逗号分隔',
  `placeholder` varchar(512) DEFAULT NULL COMMENT '占位符，多个逗号分隔',
  `last_mark` varchar(255) DEFAULT NULL COMMENT '最后标志，条件',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  `template_name` varchar(512) NOT NULL DEFAULT '' COMMENT '模板名称',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态，1-有效，0-无效',
  `last_mark_value` varchar(255) DEFAULT NULL COMMENT '条件初始化值',
  `phone_field` varchar(255) DEFAULT NULL COMMENT '手机号码字段',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='短信模板表';

#
# Table structure for table tm_stock_operation_log
#

CREATE TABLE `tm_stock_operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(36) NOT NULL COMMENT '后台用户登录ID,tm_admin_user表中的ID',
  `user_name` varchar(64) NOT NULL COMMENT '后台登录用户账户，tm_admin_user表中的username',
  `goods_id` varchar(36) NOT NULL COMMENT '商品ID，tm_goods_base_info表中的goods_id',
  `goods_code` varchar(10) NOT NULL COMMENT '商品编码，tm_goods_base_info表中的goods_code',
  `operation_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '操作类型，10--增加销售库存，11--增加总库存，20--减少销售库存，21--减少总库存',
  `operation_content` varchar(512) NOT NULL COMMENT '用户操作内容',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品库存操作日志表';

#
# Table structure for table tm_temp_order
#

CREATE TABLE `tm_temp_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `temp_order_id` varchar(36) NOT NULL DEFAULT '' COMMENT '临时订单ID,guid',
  `member_id` varchar(36) NOT NULL DEFAULT '' COMMENT '会员编号',
  `delivery_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '发货方式，1-快递，2-自提',
  `addr_id` int(11) NOT NULL DEFAULT '0' COMMENT '地址ID',
  `invoice` varchar(1024) DEFAULT NULL COMMENT '发票信息',
  `coupon_info` varchar(1024) DEFAULT NULL COMMENT '优惠券信息',
  `coupon_code` varchar(1024) DEFAULT NULL COMMENT '优惠码信息',
  `use_integral` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否使用积分',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `use_storage_money` tinyint(3) DEFAULT '0' COMMENT '是否使用储值卡金额,0--不使用，1--使用',
  `storage_password` varchar(10) DEFAULT '' COMMENT '储值卡密码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT='临时订单表';

#
# Table structure for table tm_user_appointment
#

CREATE TABLE `tm_user_appointment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(36) NOT NULL DEFAULT '' COMMENT '会员编码',
  `goods_id` varchar(36) NOT NULL DEFAULT '' COMMENT '商品ID',
  `goods_code` varchar(36) NOT NULL DEFAULT '' COMMENT '商品编码',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态',
  `platform` tinyint(3) NOT NULL DEFAULT '1' COMMENT '平台，1-PC,2-Mobile',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注信息',
  `shops_code` varchar(36) DEFAULT NULL COMMENT '商家编码',
  `goods_number` int(11) NOT NULL DEFAULT '0' COMMENT '商品数量',
  `goods_name` varchar(512) NOT NULL DEFAULT '' COMMENT '商品名称',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='预约商品表';

#
# Table structure for table tm_user_cart
#

CREATE TABLE `tm_user_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(36) NOT NULL DEFAULT '' COMMENT '会员编码',
  `goods_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '商品类型',
  `bind_id` varchar(36) DEFAULT NULL COMMENT '绑定ID',
  `goods_id` varchar(36) DEFAULT NULL COMMENT '商品ID',
  `goods_code` varchar(36) DEFAULT NULL COMMENT '商品编码',
  `goods_name` varchar(512) DEFAULT NULL COMMENT '商品名称',
  `extern_sale_info_id` varchar(36) DEFAULT NULL COMMENT '销售ID',
  `number` int(11) NOT NULL DEFAULT '0' COMMENT '商品数量',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态，1-有效',
  `relation_goods` varchar(2048) DEFAULT NULL COMMENT '关联商品',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `新建索引` (`member_id`,`goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COMMENT='用户购物车';

#
# Table structure for table tm_user_invoice_info
#

CREATE TABLE `tm_user_invoice_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID ',
  `member_id` varchar(36) NOT NULL COMMENT '会员ID,tm_user_member_base_info表中的会员ID',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '发票方式，1--个人,2--公司',
  `open_way` tinyint(4) NOT NULL DEFAULT '1' COMMENT '发票类型，1--普通发票(电子),2--普通发票(纸质),3--增值税专用发票',
  `invoice_title` varchar(128) NOT NULL DEFAULT '' COMMENT '发票title',
  `content_way` tinyint(4) NOT NULL DEFAULT '1' COMMENT '发票内容方式，1--明细',
  `invoice_content` varchar(1024) DEFAULT NULL COMMENT '发票内容方式，在正在开票的时候填写',
  `invoice_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '0--无效，1--有效',
  `invoice_crash` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '开票金额，在开票的时候填写',
  `vat_id` varchar(36) NOT NULL COMMENT '增值税发票ID，tm_vat_invoice_base_info表中的vat_id',
  `vat_rev_name` varchar(128) NOT NULL DEFAULT '' COMMENT '收票人姓名',
  `vat_rev_phone` varchar(18) NOT NULL DEFAULT '' COMMENT '收票人手机',
  `vat_rev_email` varchar(100) DEFAULT NULL COMMENT '收票人邮箱',
  `vat_province` int(11) DEFAULT NULL COMMENT '省/直辖市 ',
  `vat_city` int(11) DEFAULT NULL COMMENT '市/直辖市',
  `vat_county` int(11) DEFAULT '0' COMMENT '区县 think_city表中的ID',
  `vat_address` varchar(126) NOT NULL COMMENT '地址详细',
  `status` tinyint(3) DEFAULT '1' COMMENT '状态 0禁用 1-未审核，2--审核通过，3--审核失败',
  `is_default` tinyint(1) DEFAULT '0' COMMENT '是否默认 0否 1是',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员发票信息表';

#
# Table structure for table tm_user_member_base_info
#

CREATE TABLE `tm_user_member_base_info` (
  `member_id` varchar(36) NOT NULL COMMENT '会员ID,OpenID',
  `member_code` varchar(20) NOT NULL COMMENT '会员Code，8~16位的数字字符串',
  `member_account` varchar(64) NOT NULL COMMENT '会员账号，会员注册时的账号',
  `member_pass` varchar(64) NOT NULL COMMENT '会员密码，MD5加密',
  `username` varchar(100) NOT NULL DEFAULT '' COMMENT '会员用户名',
  `nick_name` varchar(64) NOT NULL DEFAULT '' COMMENT '会员昵称',
  `register_ip` varchar(24) NOT NULL DEFAULT '' COMMENT '注册IP',
  `register_date` int(10) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `last_login_ip` char(24) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `last_login_date` int(10) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `login_count` int(10) NOT NULL DEFAULT '0' COMMENT '总共登录次数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '会员状态，0--冻结，1--未冻结',
  `income_type` int(11) NOT NULL DEFAULT '0' COMMENT '会员来源类型，1--PC端,2--WAP端，3--app端',
  `verified` tinyint(1) DEFAULT '0' COMMENT '激活状态 0未激活 1激活',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `card_number` varchar(50) DEFAULT NULL COMMENT '会员卡号',
  `tier_id` varchar(50) DEFAULT NULL COMMENT '会员等级id',
  `tier_name` varchar(50) DEFAULT NULL COMMENT '会员等级名称',
  `tier_code` varchar(50) DEFAULT NULL COMMENT '等级码表',
  `tier_disCount` varchar(50) DEFAULT NULL COMMENT '会员等级折扣',
  `current_point` int(11) NOT NULL DEFAULT '0' COMMENT '会员当前积分',
  `state_code` int(11) DEFAULT NULL COMMENT '会员状态',
  `validate_state` int(11) DEFAULT NULL COMMENT '会员校验状态',
  `storage_money` float(10,2) DEFAULT NULL COMMENT '储值卡金额',
  `storage_password` varchar(10) DEFAULT NULL COMMENT '储值卡密码',
  `storage_state` int(11) NOT NULL DEFAULT '0' COMMENT '储值卡状态',
  PRIMARY KEY (`member_id`),
  UNIQUE KEY `新建索引` (`member_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员基本信息表';

#
# Table structure for table tm_user_member_cooperation_account
#

CREATE TABLE `tm_user_member_cooperation_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(36) NOT NULL COMMENT '会员账号ID，tm_user_member_base_info表中的member_id',
  `cooperation_account_id` varchar(64) NOT NULL COMMENT '第三方账户ID',
  `account_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '账户类型，1--qq',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态，1--有效，0--无效',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='第三方账户登录信息';

#
# Table structure for table tm_user_member_label_info
#

CREATE TABLE `tm_user_member_label_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(36) NOT NULL COMMENT '会员账号ID，tm_user_member_base_info表中的member_id',
  `label_type` int(3) NOT NULL DEFAULT '0' COMMENT '标签类型',
  `label_content` varchar(128) NOT NULL DEFAULT '0' COMMENT '标签内容',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员标签信息';

#
# Table structure for table tm_user_member_virtual_currency
#

CREATE TABLE `tm_user_member_virtual_currency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(36) NOT NULL COMMENT '会员账号ID，tm_user_member_base_info表中的member_id',
  `currency_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '虚拟货币类型',
  `current_number` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟货币当前可用数量',
  `total_number` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟货币累计获取数量',
  `consume_number` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟货币累计消费数量',
  `invalid_number` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟货币累计失效数量',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员虚拟币信息';

#
# Table structure for table tm_user_rev_address_info
#

CREATE TABLE `tm_user_rev_address_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(36) NOT NULL DEFAULT '' COMMENT '会员ID',
  `user_name` varchar(64) NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `province` varchar(36) NOT NULL DEFAULT '' COMMENT '省份ID',
  `city` varchar(36) NOT NULL DEFAULT '' COMMENT '城市ID',
  `county` varchar(36) NOT NULL DEFAULT '' COMMENT '区县ID',
  `address` varchar(512) DEFAULT NULL COMMENT '详细地址',
  `mobile` varchar(18) DEFAULT NULL COMMENT '手机号码',
  `tel` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态',
  `is_default` varchar(255) NOT NULL DEFAULT '0' COMMENT '是否是默认地址，1--是',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='会员收货地址';

#
# Table structure for table tm_vat_invoice_base_info
#

CREATE TABLE `tm_vat_invoice_base_info` (
  `vat_id` varchar(36) NOT NULL COMMENT '增值税发票ID，openID',
  `vat_comapny_name` varchar(128) NOT NULL COMMENT '增值税发票公司名称',
  `vat_code` varchar(128) NOT NULL COMMENT '纳税人识别号',
  `vat_register_address` varchar(256) NOT NULL COMMENT '单位注册地址',
  `vat_register_phone` varchar(128) NOT NULL COMMENT '单位注册电话',
  `vat_open_bank` varchar(128) NOT NULL COMMENT '开户行',
  `vat_bank_account` varchar(128) NOT NULL COMMENT '开户行账户',
  `business_license_img` varchar(256) NOT NULL DEFAULT '' COMMENT '营业执照副本',
  `organization_code_img` varchar(256) NOT NULL DEFAULT '' COMMENT '组织机构副本',
  `open_account_img` varchar(256) NOT NULL DEFAULT '' COMMENT '开户许可证',
  `taxpayer_img` varchar(256) NOT NULL DEFAULT '' COMMENT '纳税人认定',
  `status` tinyint(3) DEFAULT '1' COMMENT '状态 0禁用 1-未审核，2--审核通过，3--审核失败',
  `update_time` datetime NOT NULL COMMENT '修改时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`vat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='增值税专用发票基础信息表';

#
# Table structure for table tm_yl_relation_goods
#

CREATE TABLE `tm_yl_relation_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` varchar(36) NOT NULL DEFAULT '' COMMENT '商品ID',
  `goods_code` varchar(36) NOT NULL DEFAULT '' COMMENT '商品编码',
  `goods_name` varchar(512) NOT NULL DEFAULT '' COMMENT '商品名称',
  `goods_img` varchar(1024) DEFAULT NULL COMMENT '商品图片地址',
  `goods_media_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '市场价',
  `goods_price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '销售价格',
  `is_integral` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否使用积分',
  `max_integral` int(11) DEFAULT NULL COMMENT '最大可用积分',
  `goods_desc` varchar(1024) DEFAULT NULL COMMENT '商品简介',
  `promotion_content` varchar(1024) DEFAULT NULL COMMENT '促销短语',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='推荐商品表';

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
