-- -----------------------------
-- SentCMS MySQL Data Transfer 
-- 
-- Host     : 127.0.0.1
-- Port     : 
-- Database : blt
-- 
-- Part : #20971520
-- Date : 2016-12-23 15:43:16
-- -----------------------------

SET FOREIGN_KEY_CHECKS = 0;


-- -----------------------------
-- Table structure for `onethink_addons`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_addons`;
CREATE TABLE `onethink_addons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(40) NOT NULL COMMENT '插件名或标识',
  `title` varchar(20) NOT NULL DEFAULT '' COMMENT '中文名',
  `description` text COMMENT '插件描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `config` text COMMENT '配置',
  `author` varchar(40) DEFAULT '' COMMENT '作者',
  `version` varchar(20) DEFAULT '' COMMENT '版本号',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '安装时间',
  `has_adminlist` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否有后台列表',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COMMENT='插件表';

-- -----------------------------
-- Records of `onethink_addons`
-- -----------------------------
INSERT INTO `onethink_addons` VALUES ('2', 'SiteStat', '站点统计信息', '用于增强整站长文本的输入和显示', '1', '{\"title\":\"\\u7cfb\\u7edf\\u4fe1\\u606f\",\"width\":\"1\",\"display\":\"1\",\"status\":\"0\"}', 'thinkphp', '0.1', '1379512015', '0');
INSERT INTO `onethink_addons` VALUES ('3', 'DevTeam', '开发团队信息', '开发团队成员信息', '1', '{\"title\":\"OneThink\\u5f00\\u53d1\\u56e2\\u961f\",\"width\":\"2\",\"display\":\"1\"}', 'thinkphp', '0.1', '1379512022', '0');
INSERT INTO `onethink_addons` VALUES ('4', 'SystemInfo', '系统环境信息', '用于显示一些服务器的信息', '1', '{\"title\":\"\\u7cfb\\u7edf\\u4fe1\\u606f\",\"width\":\"2\",\"display\":\"1\"}', 'thinkphp', '0.1', '1379512036', '0');
INSERT INTO `onethink_addons` VALUES ('5', 'Editor', '前台编辑器', '用于增强整站长文本的输入和显示', '1', '{\"editor_type\":\"2\",\"editor_wysiwyg\":\"1\",\"editor_height\":\"300px\",\"editor_resize_type\":\"1\"}', 'thinkphp', '0.1', '1379830910', '0');
INSERT INTO `onethink_addons` VALUES ('6', 'Attachment', '附件', '用于文档模型上传附件', '1', 'null', 'thinkphp', '0.1', '1379842319', '1');
INSERT INTO `onethink_addons` VALUES ('9', 'SocialComment', '通用社交化评论', '集成了各种社交化评论插件，轻松集成到系统中。', '1', '{\"comment_type\":\"1\",\"comment_uid_youyan\":\"\",\"comment_short_name_duoshuo\":\"\",\"comment_data_list_duoshuo\":\"\"}', 'thinkphp', '0.1', '1380273962', '0');
INSERT INTO `onethink_addons` VALUES ('15', 'EditorForAdmin', '后台编辑器', '用于增强整站长文本的输入和显示', '1', '{\"editor_type\":\"2\",\"editor_wysiwyg\":\"2\",\"editor_height\":\"500px\",\"editor_resize_type\":\"1\"}', 'thinkphp', '0.1', '1383126253', '0');
INSERT INTO `onethink_addons` VALUES ('16', 'UploadImages', '多图上传', '多图上传', '1', 'null', '木梁大囧', '1.2', '1453194926', '0');
INSERT INTO `onethink_addons` VALUES ('20', 'Yjategory', '三级联动', '三级联动', '1', '{\"random\":\"good_category\"}', '尤金251', '0.1', '1453258593', '0');
INSERT INTO `onethink_addons` VALUES ('23', 'Goodattr', '商品属性', '商品属性', '1', '{\"random\":\"1\"}', '尤金251', '0.1', '1453368151', '1');
INSERT INTO `onethink_addons` VALUES ('25', 'shai', '筛选属性', '这是一个临时描述', '0', 'null', '尤金251', '0.1', '1458995931', '0');
INSERT INTO `onethink_addons` VALUES ('26', 'datetime', '时间', '这是一个临时描述', '1', 'null', '尤金251', '0.1', '1458999789', '0');
INSERT INTO `onethink_addons` VALUES ('27', 'UploadFile', '上传图片', '', '1', '', '尤金251', '0.1', '1379512015', '0');

-- -----------------------------
-- Table structure for `onethink_admin`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_admin`;
CREATE TABLE `onethink_admin` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nickname` char(255) NOT NULL DEFAULT '' COMMENT '昵称',
  `sex` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `qq` char(10) NOT NULL DEFAULT '' COMMENT 'qq号',
  `score` mediumint(8) NOT NULL DEFAULT '0' COMMENT '用户积分',
  `login` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `reg_ip` varchar(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `reg_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `last_login_ip` varchar(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '会员状态',
  PRIMARY KEY (`uid`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COMMENT='会员表';

-- -----------------------------
-- Records of `onethink_admin`
-- -----------------------------
INSERT INTO `onethink_admin` VALUES ('1', 'admin111', 'bbad8d72c1fac1d081727158807a8798', 'AAH3_GcJADumc1bG-x5aYXHO', '0', '', '0', '33', '127.0.0.1', '1478744039', '2130706433', '1482298556', '1');
INSERT INTO `onethink_admin` VALUES ('30', '123456', 'e10adc3949ba59abbe56e057f20f883e', 'zwc', '0', '', '0', '5', '127.0.0.1', '1479544279', '2130706433', '1479544700', '1');
INSERT INTO `onethink_admin` VALUES ('29', 'admin1111', 'e10adc3949ba59abbe56e057f20f883e', '总务处', '0', '', '0', '2', '127.0.0.1', '1479522949', '2130706433', '1480593180', '1');

-- -----------------------------
-- Table structure for `onethink_attachment`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_attachment`;
CREATE TABLE `onethink_attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `title` char(30) NOT NULL DEFAULT '' COMMENT '附件显示名',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '附件类型',
  `source` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资源ID',
  `record_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联记录ID',
  `download` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '附件大小',
  `dir` int(12) unsigned NOT NULL DEFAULT '0' COMMENT '上级目录ID',
  `sort` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `idx_record_status` (`record_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='附件表';


-- -----------------------------
-- Table structure for `onethink_attribute`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_attribute`;
CREATE TABLE `onethink_attribute` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '字段名',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '字段注释',
  `field` varchar(100) NOT NULL DEFAULT '' COMMENT '字段定义',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '数据类型',
  `value` varchar(100) NOT NULL DEFAULT '' COMMENT '字段默认值',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `extra` varchar(255) NOT NULL DEFAULT '' COMMENT '参数',
  `model_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模型id',
  `is_must` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否必填',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `validate_rule` varchar(255) NOT NULL DEFAULT '',
  `validate_time` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `error_info` varchar(100) NOT NULL DEFAULT '',
  `validate_type` varchar(25) NOT NULL DEFAULT '',
  `auto_rule` varchar(100) NOT NULL DEFAULT '',
  `auto_time` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `auto_type` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `model_id` (`model_id`)
) ENGINE=InnoDB AUTO_INCREMENT=556 DEFAULT CHARSET=utf8 COMMENT='模型属性表';

-- -----------------------------
-- Records of `onethink_attribute`
-- -----------------------------
INSERT INTO `onethink_attribute` VALUES ('514', 'token', 'token', 'int(10) unsigned', 'string', '', '', '1', '', '65', '0', '0', '1480673681', '1480673681', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('515', 'deviceId', 'deviceId', 'varchar(255)', 'string', '', '', '1', '', '65', '0', '0', '1480673681', '1480673681', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('516', 'member_id', 'member_id', 'int(11)', 'string', '', '', '1', '', '65', '0', '0', '1480673681', '1480673681', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('517', 'starttime', 'starttime', 'int(11)', 'string', '', '', '1', '', '65', '0', '0', '1480673681', '1480673681', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('518', 'status', 'status', 'tinyint(3)', 'string', '', '', '1', '', '65', '0', '0', '1480673681', '1480673681', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('519', 'order_code', 'order_code', 'varchar(255)', 'string', '', '', '1', '', '66', '0', '0', '1481089896', '1481089896', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('520', 'order_price', 'order_price', 'varchar(255)', 'string', '', '', '1', '', '66', '0', '0', '1481089896', '1481089896', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('521', 'xd_time', 'xd_time', 'int(11)', 'string', '', '', '1', '', '66', '0', '0', '1481089896', '1481089896', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('522', 'record_time', 'record_time', 'int(11)', 'string', '', '', '1', '', '66', '0', '0', '1481089896', '1481089896', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('523', 'into_time', 'into_time', 'int(10) NOT NULL', 'datetime', '', '', '1', '', '66', '0', '0', '1482205513', '1481089896', '', '0', '', '', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('524', 'goods_name', 'goods_name', 'varchar(255)', 'string', '', '', '1', '', '66', '0', '0', '1481089896', '1481089896', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('525', 'goods_price', 'goods_price', 'varchar(255)', 'string', '', '', '1', '', '66', '0', '0', '1481089896', '1481089896', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('526', 'goods_picture', 'goods_picture', 'varchar(255)', 'string', '', '', '1', '', '66', '0', '0', '1481089896', '1481089896', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('527', 'platform', 'platform', 'char(50) NOT NULL', 'select', '', '', '1', 'taobao:淘宝\r\njd:京东', '66', '0', '0', '1482205642', '1481089896', '', '0', '', '', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('528', 'order_state', 'order_state', 'int(11)', 'string', '', '', '1', '', '66', '0', '0', '1481089896', '1481089896', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('529', 'member_id', 'member_id', 'int(11)', 'string', '', '', '1', '', '67', '0', '0', '1481089910', '1481089910', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('530', 'balance', 'balance', 'double(20,5)', 'string', '', '', '1', '', '67', '0', '0', '1481089910', '1481089910', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('531', 'add_time', 'add_time', 'int(11)', 'string', '', '', '1', '', '67', '0', '0', '1481089910', '1481089910', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('532', 'type', 'type', 'tinyint(3) unsigned', 'string', '', '', '1', '', '67', '0', '0', '1481089910', '1481089910', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('533', 'member_id', 'member_id', 'int(11)', 'string', '', '', '1', '', '69', '0', '0', '1481851558', '1481851558', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('534', 'earnings', 'earnings', 'varchar(20)', 'string', '', '', '1', '', '69', '0', '0', '1481851558', '1481851558', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('535', 'order_price', 'order_price', 'int(10) UNSIGNED NOT NULL', 'picture', '', '', '1', '', '69', '0', '0', '1481851620', '1481851558', '', '0', '', '', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('536', 'order_id', 'order_id', 'text', 'string', '', '', '1', '', '69', '0', '0', '1481851558', '1481851558', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('537', 'add_time', 'add_time', 'varchar(20)', 'string', '', '', '1', '', '69', '0', '0', '1481851558', '1481851558', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('538', 'add_date', 'add_date', 'int(11)', 'string', '', '', '1', '', '69', '0', '0', '1481851558', '1481851558', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('539', 'rate', 'rate', 'double(8,5)', 'string', '', '', '1', '', '69', '0', '0', '1481851558', '1481851558', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('540', 'post', 'post', 'text', 'string', '', '', '1', '', '70', '0', '0', '1481966511', '1481882355', '', '0', '', '', '', '3', 'function');
INSERT INTO `onethink_attribute` VALUES ('541', 'get', 'get', 'text', 'string', '', '', '1', '', '70', '0', '0', '1481882355', '1481882355', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('542', 'url', 'url', 'varchar(255)', 'string', '', '', '1', '', '70', '0', '0', '1481882355', '1481882355', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('543', 'data', 'data', 'varchar(255)', 'string', '', '', '1', '', '70', '0', '0', '1481882355', '1481882355', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('544', 'ip', 'ip', 'varchar(255)', 'string', '', '', '1', '', '70', '0', '0', '1481882355', '1481882355', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('545', 'add_time', 'add_time', 'int(11)', 'string', '', '', '1', '', '70', '0', '0', '1481882355', '1481882355', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('553', 'member_id', 'member_id', 'int(11)', 'string', '', '', '1', '', '66', '0', '0', '1482200315', '1482200315', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('554', 'auction_infos', 'auction_infos', 'text', 'string', '', '', '1', '', '66', '0', '0', '1482200315', '1482200315', '', '0', '', '', '', '0', '');
INSERT INTO `onethink_attribute` VALUES ('555', 'goods_name', '商品名称', 'varchar(255) NOT NULL', 'string', '', '', '1', '', '74', '0', '0', '1482477332', '1482477332', '', '0', '', '', '', '3', 'function');

-- -----------------------------
-- Table structure for `onethink_auth_extend`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_auth_extend`;
CREATE TABLE `onethink_auth_extend` (
  `group_id` mediumint(10) unsigned NOT NULL COMMENT '用户id',
  `extend_id` mediumint(8) unsigned NOT NULL COMMENT '扩展表中数据的id',
  `type` tinyint(1) unsigned NOT NULL COMMENT '扩展类型标识 1:栏目分类权限;2:模型权限',
  UNIQUE KEY `group_extend_type` (`group_id`,`extend_id`,`type`),
  KEY `uid` (`group_id`),
  KEY `group_id` (`extend_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户组与分类的对应关系表';

-- -----------------------------
-- Records of `onethink_auth_extend`
-- -----------------------------
INSERT INTO `onethink_auth_extend` VALUES ('1', '1', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '1', '2');
INSERT INTO `onethink_auth_extend` VALUES ('1', '2', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '2', '2');
INSERT INTO `onethink_auth_extend` VALUES ('1', '3', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '3', '2');
INSERT INTO `onethink_auth_extend` VALUES ('1', '4', '1');
INSERT INTO `onethink_auth_extend` VALUES ('1', '37', '1');

-- -----------------------------
-- Table structure for `onethink_auth_group`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_auth_group`;
CREATE TABLE `onethink_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组id,自增主键',
  `module` varchar(20) NOT NULL DEFAULT '' COMMENT '用户组所属模块',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '组类型',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) NOT NULL DEFAULT '' COMMENT '描述信息',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户组状态：为1正常，为0禁用,-1为删除',
  `rules` varchar(500) NOT NULL DEFAULT '' COMMENT '用户组拥有的规则id，多个规则 , 隔开',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- -----------------------------
-- Records of `onethink_auth_group`
-- -----------------------------
INSERT INTO `onethink_auth_group` VALUES ('1', 'admin', '1', '默认用户组', '', '1', '1,124,129,131,130,132');
INSERT INTO `onethink_auth_group` VALUES ('2', 'admin', '1', '测试用户', '测试用户', '1', '143,144,145,149,167,170,168,169,150,151,152,68,63,64,65,66,67,86,87,88,89,90,91,92,69,80,81,82,83,84,85,58,59,60,61,62,112,113,115,122,70,71,72,73,74,119,75,96,98,114,120,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,116,117');

-- -----------------------------
-- Table structure for `onethink_auth_group_access`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_auth_group_access`;
CREATE TABLE `onethink_auth_group_access` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `group_id` mediumint(8) unsigned NOT NULL COMMENT '用户组id',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- -----------------------------
-- Records of `onethink_auth_group_access`
-- -----------------------------
INSERT INTO `onethink_auth_group_access` VALUES ('1', '1');
INSERT INTO `onethink_auth_group_access` VALUES ('29', '1');
INSERT INTO `onethink_auth_group_access` VALUES ('30', '1');

-- -----------------------------
-- Table structure for `onethink_auth_rule`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_auth_rule`;
CREATE TABLE `onethink_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
  `module` varchar(20) NOT NULL COMMENT '规则所属module',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1-url;2-主菜单',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '规则唯一英文标识',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则中文描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效(0:无效,1:有效)',
  `condition` varchar(300) NOT NULL DEFAULT '' COMMENT '规则附加条件',
  PRIMARY KEY (`id`),
  KEY `module` (`module`,`status`,`type`)
) ENGINE=MyISAM AUTO_INCREMENT=234 DEFAULT CHARSET=utf8;

-- -----------------------------
-- Records of `onethink_auth_rule`
-- -----------------------------
INSERT INTO `onethink_auth_rule` VALUES ('1', 'admin', '1', 'index/index', '首页', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('2', 'admin', '1', 'Article/index', '文章', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('3', 'admin', '1', 'User/index', '用户', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('4', 'admin', '1', 'Addons/index', '扩展', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('5', 'admin', '1', 'Config/group', '系统', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('16', 'admin', '1', 'Admin/article/clear', '清空', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('17', 'admin', '1', 'Admin/Article/examine', '审核列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('18', 'admin', '1', 'Admin/article/recycle', '回收站', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('19', 'admin', '1', 'Admin/User/addaction', '新增用户行为', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('20', 'admin', '1', 'Admin/User/editaction', '编辑用户行为', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('21', 'admin', '1', 'Admin/User/saveAction', '保存用户行为', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('22', 'admin', '1', 'Admin/User/setStatus', '变更行为状态', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('23', 'admin', '1', 'Admin/User/changeStatus?method=forbidUser', '禁用会员', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('24', 'admin', '1', 'Admin/User/changeStatus?method=resumeUser', '启用会员', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('25', 'admin', '1', 'Admin/User/changeStatus?method=deleteUser', '删除会员', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('26', 'admin', '1', 'User/index', '用户信息', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('27', 'admin', '1', 'Admin/User/action', '用户行为', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('42', 'admin', '1', 'Admin/Addons/create', '创建', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('43', 'admin', '1', 'Admin/Addons/checkForm', '检测创建', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('44', 'admin', '1', 'Admin/Addons/preview', '预览', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('45', 'admin', '1', 'Admin/Addons/build', '快速生成插件', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('46', 'admin', '1', 'Admin/Addons/config', '设置', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('47', 'admin', '1', 'Admin/Addons/disable', '禁用', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('48', 'admin', '1', 'Admin/Addons/enable', '启用', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('49', 'admin', '1', 'Admin/Addons/install', '安装', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('50', 'admin', '1', 'Admin/Addons/uninstall', '卸载', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('51', 'admin', '1', 'Admin/Addons/saveconfig', '更新配置', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('52', 'admin', '1', 'Admin/Addons/adminList', '插件后台列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('53', 'admin', '1', 'Admin/Addons/execute', 'URL方式访问插件', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('54', 'admin', '1', 'Admin/Addons/index', '插件管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('55', 'admin', '1', 'Admin/Addons/hooks', '钩子管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('56', 'admin', '1', 'Admin/model/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('57', 'admin', '1', 'Admin/model/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('58', 'admin', '1', 'Admin/model/setStatus', '改变状态', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('59', 'admin', '1', 'Admin/model/update', '保存数据', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('60', 'admin', '1', 'Admin/Model/index', '模型管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('61', 'admin', '1', 'Admin/Config/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('62', 'admin', '1', 'Admin/Config/del', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('63', 'admin', '1', 'Admin/Config/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('64', 'admin', '1', 'Admin/Config/save', '保存', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('65', 'admin', '1', 'Admin/Config/group', '网站设置', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('66', 'admin', '1', 'Admin/Config/index', '配置管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('67', 'admin', '1', 'Admin/Channel/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('68', 'admin', '1', 'Admin/Channel/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('69', 'admin', '1', 'Admin/Channel/del', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('70', 'admin', '1', 'Admin/Channel/index', '导航管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('71', 'admin', '1', 'Admin/Category/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('72', 'admin', '1', 'Admin/Category/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('73', 'admin', '1', 'Admin/Category/remove', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('74', 'admin', '1', 'Admin/Category/index', '分类管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('75', 'admin', '1', 'Admin/file/upload', '上传控件', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('76', 'admin', '1', 'Admin/file/uploadPicture', '上传图片', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('77', 'admin', '1', 'Admin/file/download', '下载', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('79', 'admin', '1', 'Admin/article/batchOperate', '导入', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('80', 'admin', '1', 'Admin/Database/index?type=export', '备份数据库', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('81', 'admin', '1', 'Admin/Database/index?type=import', '还原数据库', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('82', 'admin', '1', 'Admin/Database/export', '备份', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('83', 'admin', '1', 'Admin/Database/optimize', '优化表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('84', 'admin', '1', 'Admin/Database/repair', '修复表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('86', 'admin', '1', 'Admin/Database/import', '恢复', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('87', 'admin', '1', 'Admin/Database/del', '删除', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('88', 'admin', '1', 'Admin/User/add', '新增用户', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('89', 'admin', '1', 'Admin/Attribute/index', '属性管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('90', 'admin', '1', 'Admin/Attribute/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('91', 'admin', '1', 'Admin/Attribute/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('92', 'admin', '1', 'Admin/Attribute/setStatus', '改变状态', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('93', 'admin', '1', 'Admin/Attribute/update', '保存数据', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('96', 'admin', '1', 'Admin/Category/move', '移动', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('97', 'admin', '1', 'Admin/Category/merge', '合并', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('98', 'admin', '1', 'Admin/Config/menu', '后台菜单管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('99', 'admin', '1', 'Admin/Article/mydocument', '内容', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('100', 'admin', '1', 'Admin/Menu/index', '菜单管理', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('101', 'admin', '1', 'Admin/other', '其他', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('102', 'admin', '1', 'Admin/Menu/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('103', 'admin', '1', 'Admin/Menu/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('104', 'admin', '1', 'Admin/Think/lists?model=article', '文章管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('105', 'admin', '1', 'Admin/Think/lists?model=download', '下载管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('106', 'admin', '1', 'Admin/Think/lists?model=config', '配置管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('107', 'admin', '1', 'Admin/Action/actionlog', '行为日志', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('108', 'admin', '1', 'Admin/User/updatePassword', '修改密码', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('109', 'admin', '1', 'Admin/User/updateNickname', '修改昵称', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('110', 'admin', '1', 'Admin/action/edit', '查看行为日志', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('111', 'admin', '2', 'Admin/article/index', '文档列表', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('112', 'admin', '2', 'Admin/article/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('113', 'admin', '2', 'Admin/article/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('114', 'admin', '2', 'Admin/article/setStatus', '改变状态', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('115', 'admin', '2', 'Admin/article/update', '保存', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('116', 'admin', '2', 'Admin/article/autoSave', '保存草稿', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('117', 'admin', '2', 'Admin/article/move', '移动', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('118', 'admin', '2', 'Admin/article/copy', '复制', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('119', 'admin', '2', 'Admin/article/paste', '粘贴', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('120', 'admin', '2', 'Admin/article/batchOperate', '导入', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('121', 'admin', '2', 'Admin/article/recycle', '回收站', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('122', 'admin', '2', 'Admin/article/permit', '还原', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('123', 'admin', '2', 'Admin/article/clear', '清空', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('124', 'admin', '2', 'Admin/User/add', '新增用户', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('125', 'admin', '2', 'Admin/User/action', '用户行为', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('126', 'admin', '2', 'Admin/User/addAction', '新增用户行为', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('127', 'admin', '2', 'Admin/User/editAction', '编辑用户行为', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('128', 'admin', '2', 'Admin/User/saveAction', '保存用户行为', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('129', 'admin', '2', 'Admin/User/setStatus', '变更行为状态', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('130', 'admin', '2', 'Admin/User/changeStatus?method=forbidUser', '禁用会员', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('131', 'admin', '2', 'Admin/User/changeStatus?method=resumeUser', '启用会员', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('132', 'admin', '2', 'Admin/User/changeStatus?method=deleteUser', '删除会员', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('149', 'admin', '2', 'Admin/Addons/create', '创建', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('150', 'admin', '2', 'Admin/Addons/checkForm', '检测创建', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('151', 'admin', '2', 'Admin/Addons/preview', '预览', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('152', 'admin', '2', 'Admin/Addons/build', '快速生成插件', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('153', 'admin', '2', 'Admin/Addons/config', '设置', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('154', 'admin', '2', 'Admin/Addons/disable', '禁用', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('155', 'admin', '2', 'Admin/Addons/enable', '启用', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('156', 'admin', '2', 'Admin/Addons/install', '安装', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('157', 'admin', '2', 'Admin/Addons/uninstall', '卸载', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('158', 'admin', '2', 'Admin/Addons/saveconfig', '更新配置', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('159', 'admin', '2', 'Admin/Addons/adminList', '插件后台列表', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('160', 'admin', '2', 'Admin/Addons/execute', 'URL方式访问插件', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('161', 'admin', '2', 'Admin/Addons/hooks', '钩子管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('162', 'admin', '2', 'Admin/Model/index', '模型管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('163', 'admin', '2', 'model/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('164', 'admin', '2', 'model/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('165', 'admin', '2', 'model/setStatus', '改变状态', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('166', 'admin', '2', 'Admin/model/update', '保存数据', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('167', 'admin', '2', 'Admin/Attribute/index', '属性管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('168', 'admin', '2', 'Admin/Attribute/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('169', 'admin', '2', 'Admin/Attribute/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('170', 'admin', '2', 'Admin/Attribute/setStatus', '改变状态', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('171', 'admin', '2', 'Admin/Attribute/update', '保存数据', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('172', 'admin', '2', 'Admin/Config/index', '配置管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('173', 'admin', '2', 'Admin/Config/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('174', 'admin', '2', 'Admin/Config/del', '删除', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('175', 'admin', '2', 'Admin/Config/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('176', 'admin', '2', 'Admin/Config/save', '保存', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('177', 'admin', '2', 'Admin/Menu/index', '菜单管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('178', 'admin', '2', 'Admin/Channel/index', '导航管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('179', 'admin', '2', 'Admin/Channel/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('180', 'admin', '2', 'Admin/Channel/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('181', 'admin', '2', 'Admin/Channel/del', '删除', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('182', 'admin', '2', 'Admin/Category/index', '分类管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('183', 'admin', '2', 'Admin/Category/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('184', 'admin', '2', 'Admin/Category/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('185', 'admin', '2', 'Admin/Category/remove', '删除', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('186', 'admin', '2', 'Admin/Category/move', '移动', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('187', 'admin', '2', 'Admin/Category/merge', '合并', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('188', 'admin', '2', 'Admin/Database/index?type=export', '备份数据库', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('189', 'admin', '2', 'Admin/Database/export', '备份', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('190', 'admin', '2', 'Admin/Database/optimize', '优化表', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('191', 'admin', '2', 'Admin/Database/repair', '修复表', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('192', 'admin', '2', 'Admin/Database/index?type=import', '还原数据库', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('193', 'admin', '2', 'Admin/Database/import', '恢复', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('194', 'admin', '2', 'Admin/Database/del', '删除', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('195', 'admin', '2', 'Admin/other', '其他', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('196', 'admin', '2', 'Admin/Menu/add', '新增', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('197', 'admin', '2', 'Admin/Menu/edit', '编辑', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('198', 'admin', '2', 'Admin/Think/lists?model=article', '应用', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('199', 'admin', '2', 'Admin/Think/lists?model=download', '下载管理', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('200', 'admin', '2', 'Admin/Think/lists?model=config', '应用', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('201', 'admin', '2', 'Admin/Action/actionlog', '行为日志', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('202', 'admin', '2', 'Admin/User/updatePassword', '修改密码', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('203', 'admin', '2', 'Admin/User/updateNickname', '修改昵称', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('204', 'admin', '2', 'Admin/action/edit', '查看行为日志', '-1', '');
INSERT INTO `onethink_auth_rule` VALUES ('205', 'admin', '1', 'Admin/think/add', '新增数据', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('206', 'admin', '1', 'Admin/think/edit', '编辑数据', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('207', 'admin', '1', 'Admin/Menu/import', '导入', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('208', 'admin', '1', 'Admin/Model/generate', '生成', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('209', 'admin', '1', 'Admin/Addons/addHook', '新增钩子', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('210', 'admin', '1', 'Admin/Addons/edithook', '编辑钩子', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('211', 'admin', '1', 'Admin/Article/sort', '文档排序', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('212', 'admin', '1', 'Admin/Config/sort', '排序', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('213', 'admin', '1', 'Admin/Menu/sort', '排序', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('214', 'admin', '1', 'Admin/Channel/sort', '排序', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('215', 'admin', '1', 'Admin/Category/operate/type/move', '移动', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('216', 'admin', '1', 'Admin/Category/operate/type/merge', '合并', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('217', 'admin', '1', 'Admin/article/index', '文档列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('218', 'admin', '1', 'Admin/think/lists', '数据列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('219', 'admin', '1', 'Admin/Ad/lists', '广告列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('220', 'admin', '1', 'Admin/Ad/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('221', 'admin', '1', 'Admin/Ad/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('222', 'admin', '1', 'Admin/Nav/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('223', 'admin', '1', 'Admin/Good/lists', '商品列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('224', 'admin', '1', 'Admin/good/add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('225', 'admin', '1', 'Admin/good/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('226', 'admin', '1', 'Admin/Nav/lists', '商品导航', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('227', 'admin', '1', 'Admin/Nav/edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('228', 'admin', '1', 'Admin/Floor/Add', '新增', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('229', 'admin', '1', 'Admin/Floor/Edit', '编辑', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('230', 'admin', '1', 'Admin/Floor/lists', '楼层列表', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('231', 'admin', '2', 'Admin/Ad/lists', '设置', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('232', 'admin', '1', 'good/index', '商品', '1', '');
INSERT INTO `onethink_auth_rule` VALUES ('233', 'admin', '1', 'nav/lists', '导航', '1', '');

-- -----------------------------
-- Table structure for `onethink_code`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_code`;
CREATE TABLE `onethink_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(4) DEFAULT NULL,
  `mobile` varchar(11) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `addtime` int(11) DEFAULT NULL,
  `status` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1015 DEFAULT CHARSET=utf8;

-- -----------------------------
-- Records of `onethink_code`
-- -----------------------------
INSERT INTO `onethink_code` VALUES ('1014', '1191', '18823404165', '', '1481253765', '1');
INSERT INTO `onethink_code` VALUES ('1013', '9300', '18823404165', '', '1481249845', '1');
INSERT INTO `onethink_code` VALUES ('1012', '4018', '18823404165', '', '1479786878', '1');
INSERT INTO `onethink_code` VALUES ('1011', '7756', '18823404165', '', '1479786780', '1');
INSERT INTO `onethink_code` VALUES ('1010', '8537', '18823404165', '', '1479786767', '1');
INSERT INTO `onethink_code` VALUES ('1009', '1864', '18823404165', '', '1479786758', '1');
INSERT INTO `onethink_code` VALUES ('1007', '3458', '18823404165', '', '1478771665', '1');
INSERT INTO `onethink_code` VALUES ('1006', '5757', '18823404165', '', '1478771571', '1');
INSERT INTO `onethink_code` VALUES ('1005', '9905', '13534067943', '', '1478769701', '1');
INSERT INTO `onethink_code` VALUES ('1004', '5718', '18823404165', '', '1478769670', '1');
INSERT INTO `onethink_code` VALUES ('1008', '8639', '18823404165', '', '1479786736', '1');
INSERT INTO `onethink_code` VALUES ('1003', '9999', '18823404165', '', '1978799999', '1');

-- -----------------------------
-- Table structure for `onethink_config`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_config`;
CREATE TABLE `onethink_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '配置名称',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置类型',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '配置说明',
  `group` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置分组',
  `extra` varchar(255) NOT NULL DEFAULT '' COMMENT '配置值',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '配置说明',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `value` text COMMENT '配置值',
  `sort` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `type` (`type`),
  KEY `group` (`group`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;

-- -----------------------------
-- Records of `onethink_config`
-- -----------------------------
INSERT INTO `onethink_config` VALUES ('1', 'search', '0', '', '0', '', '', '0', '0', '0', '', '0');

-- -----------------------------
-- Table structure for `onethink_goods`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_goods`;
CREATE TABLE `onethink_goods` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_name` varchar(255) NOT NULL COMMENT '商品名称',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- -----------------------------
-- Table structure for `onethink_goods_class`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_goods_class`;
CREATE TABLE `onethink_goods_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `pid` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=254 DEFAULT CHARSET=utf8;

-- -----------------------------
-- Records of `onethink_goods_class`
-- -----------------------------
INSERT INTO `onethink_goods_class` VALUES ('189', '3C数码配件', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('190', '电脑硬件', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('191', '女装', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('192', '运动', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('193', '玩具', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('194', '男装', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('195', '床上用品', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('196', '居家布艺', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('197', '女士内衣', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('198', '生活电器', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('199', 'OTC药品', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('200', '彩妆', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('201', '居家日用', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('202', '节庆用品', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('203', '家装主材', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('204', '零食', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('205', '家庭', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('206', '女鞋', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('207', '童装', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('208', '饰品', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('209', '汽车', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('210', '宠物', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('211', '流行男鞋', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('212', '洗护清洁剂', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('213', '厨房', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('214', '办公设备', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('215', '收纳整理', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('216', '餐饮具', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('217', '水产肉类', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('218', '箱包皮具', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('219', 'ZIPPO', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('220', '个人护理', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('221', '童鞋', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('222', '电子词典', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('223', '网络设备', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('224', '尿片', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('225', '美容护肤', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('226', '服饰配件', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('227', '茶', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('228', '美发护发', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('229', '智能设备', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('230', '咖啡', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('231', '影音电器', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('232', '粮油米面', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('233', '书籍', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('234', '手表', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('235', '传统滋补营养品', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('236', '大家电', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('237', '厨房电器', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('238', '住宅家具', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('239', '闪存卡', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('240', '音乐', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('241', '户外', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('242', '孕妇装', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('243', '保健食品', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('244', '家居饰品', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('245', '基础建材', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('246', '隐形眼镜', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('247', '商业', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('248', '酒类', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('249', '自行车', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('250', '运动鞋new', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('251', '特色手工艺', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('252', '运动服', '0', '');
INSERT INTO `onethink_goods_class` VALUES ('253', '电子', '0', '');

-- -----------------------------
-- Table structure for `onethink_hooks`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_hooks`;
CREATE TABLE `onethink_hooks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(40) NOT NULL DEFAULT '' COMMENT '钩子名称',
  `description` text COMMENT '描述',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `addons` varchar(255) NOT NULL DEFAULT '' COMMENT '钩子挂载的插件 ''，''分割',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- -----------------------------
-- Records of `onethink_hooks`
-- -----------------------------
INSERT INTO `onethink_hooks` VALUES ('8', 'adminArticleEdit', '后台内容编辑页编辑器', '1', '1378982734', 'EditorForAdmin', '1');
INSERT INTO `onethink_hooks` VALUES ('17', 'UploadImages', '多图上传', '1', '1453193785', 'UploadImages', '1');
INSERT INTO `onethink_hooks` VALUES ('21', 'Yjcategory', '三级联动', '1', '1453258527', 'Yjategory', '1');
INSERT INTO `onethink_hooks` VALUES ('25', 'datetime', '时间插件', '1', '1453258527', 'datetime', '1');
INSERT INTO `onethink_hooks` VALUES ('22', 'UploadFile', '', '1', '1378982734', 'UploadFile', '1');

-- -----------------------------
-- Table structure for `onethink_log`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_log`;
CREATE TABLE `onethink_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post` text COMMENT 'post',
  `get` text,
  `url` varchar(255) DEFAULT NULL,
  `data` text,
  `ip` varchar(255) DEFAULT '',
  `add_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='日志文件';


-- -----------------------------
-- Table structure for `onethink_member`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_member`;
CREATE TABLE `onethink_member` (
  `member_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '会员id',
  `member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `member_truename` varchar(20) DEFAULT '' COMMENT '真实姓名',
  `member_avatar` varchar(255) DEFAULT '' COMMENT '会员头像',
  `member_sex` tinyint(1) DEFAULT '0' COMMENT '会员性别',
  `member_birthday` varchar(255) DEFAULT '' COMMENT '生日',
  `member_passwd` varchar(32) NOT NULL COMMENT '会员密码',
  `member_paypwd` char(32) DEFAULT '' COMMENT '支付密码',
  `member_email` varchar(100) NOT NULL COMMENT '会员邮箱',
  `member_email_bind` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未绑定1已绑定',
  `member_mobile` varchar(11) DEFAULT '' COMMENT '手机号',
  `member_mobile_bind` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未绑定1已绑定',
  `member_qq` varchar(100) DEFAULT '' COMMENT 'qq',
  `member_login_num` int(11) NOT NULL DEFAULT '1' COMMENT '登录次数',
  `member_reg_time` varchar(10) NOT NULL DEFAULT '' COMMENT '会员注册时间',
  `member_login_time` varchar(10) NOT NULL DEFAULT '' COMMENT '最后一次登录时间',
  `member_reg_ip` varchar(20) DEFAULT '' COMMENT '注册ip',
  `member_login_ip` varchar(20) DEFAULT '' COMMENT '最后一次登录ip',
  `member_qqopenid` varchar(100) DEFAULT '' COMMENT 'qq互联id',
  `weixin_unionid` varchar(50) DEFAULT '' COMMENT '微信用户统一标识',
  `weixin_info` varchar(255) DEFAULT '' COMMENT '微信用户相关信息',
  `member_points` int(11) NOT NULL DEFAULT '0' COMMENT '会员积分',
  `member_state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '会员的开启状态 1为开启 0为关闭',
  `member_snsvisitnum` int(11) NOT NULL DEFAULT '0' COMMENT 'sns空间访问次数',
  `member_areaid` int(11) DEFAULT '0' COMMENT '地区ID',
  `member_cityid` int(11) DEFAULT '0' COMMENT '城市ID',
  `member_provinceid` int(11) DEFAULT '0' COMMENT '省份ID',
  `member_exppoints` int(11) NOT NULL DEFAULT '0' COMMENT '会员经验值',
  `inviter_id` int(11) DEFAULT '0' COMMENT '邀请人ID',
  `member_wxopenid` varchar(100) DEFAULT '' COMMENT '微信互联openid',
  `member_code` char(100) DEFAULT '' COMMENT '邀请码',
  `member_earnings` varchar(20) DEFAULT '0' COMMENT '还剩收益',
  `member_tx_earnings` varchar(255) DEFAULT '0' COMMENT '提取收益多少',
  `member_all_earnings` varchar(255) DEFAULT '0' COMMENT '总共收益',
  `order_price` varchar(20) DEFAULT '0' COMMENT '订单总额（消费总额）',
  `order_nums` int(11) DEFAULT '0' COMMENT '（订单总数）存款笔数',
  `day_rate` double(8,5) DEFAULT '0.00000' COMMENT '日收益率(%)',
  `year_rate` double(8,5) DEFAULT '0.00000' COMMENT '年收益率(%)',
  `member_tbopenid` varchar(255) DEFAULT '' COMMENT '淘宝optionid',
  `is_tb_bind` int(11) DEFAULT '0' COMMENT '1:授权,0:未授权',
  `zfb` varchar(50) DEFAULT '' COMMENT '支付宝',
  `member_nickname` varchar(255) DEFAULT '' COMMENT '昵称',
  PRIMARY KEY (`member_id`),
  KEY `member_name` (`member_name`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='会员表';

-- -----------------------------
-- Records of `onethink_member`
-- -----------------------------
INSERT INTO `onethink_member` VALUES ('33', '18823404165', '周文', '', '1', '', 'fcea920f7412b5da7be0cf42b8c93759', '', '', '0', '18823404165', '0', '', '4', '1479796306', '1481250808', '127.0.0.1', '127.0.0.1', '', '', '', '0', '1', '0', '0', '0', '0', '0', '0', '', '', '-221.81', '334', '2.19', '1058.6', '3', '0.05219', '0', 'AAEZ_GcJADumc1bG-x47CKYz', '1', '18823404165', '');
INSERT INTO `onethink_member` VALUES ('34', '', '', '', '0', '', '', '', '', '0', '', '0', '', '2', '1480660829', '1480661007', '127.0.0.1', '127.0.0.1', '18823404615', '', '', '0', '1', '0', '0', '0', '0', '0', '0', '', '', '0.03689', '0', '0.03689', '17', '0', '0.05717', '0', '', '0', '', '');
INSERT INTO `onethink_member` VALUES ('35', '', '', '', '0', '', '', '', '', '0', '', '0', '', '5', '1480661048', '1480661446', '127.0.0.1', '127.0.0.1', '', '', '', '0', '1', '0', '0', '0', '0', '0', '0', '18823404615', '', '1.275', '0', '1.275', '500', '0', '0.05255', '0', '', '0', '', '');

-- -----------------------------
-- Table structure for `onethink_member_balance`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_member_balance`;
CREATE TABLE `onethink_member_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT '0' COMMENT '用户id',
  `balance` double(20,5) DEFAULT '0.00000' COMMENT '余额变化',
  `add_time` int(11) DEFAULT '0' COMMENT '添加时间',
  `type` tinyint(3) unsigned DEFAULT '0' COMMENT '1:收益记录,2:提现记录',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='用户余额';

-- -----------------------------
-- Records of `onethink_member_balance`
-- -----------------------------
INSERT INTO `onethink_member_balance` VALUES ('1', '0', '0', '0', '1');
INSERT INTO `onethink_member_balance` VALUES ('2', '33', '32', '0', '2');
INSERT INTO `onethink_member_balance` VALUES ('3', '33', '32', '0', '2');
INSERT INTO `onethink_member_balance` VALUES ('4', '33', '32', '0', '2');
INSERT INTO `onethink_member_balance` VALUES ('5', '33', '32', '0', '2');
INSERT INTO `onethink_member_balance` VALUES ('6', '33', '32', '1481081660', '2');
INSERT INTO `onethink_member_balance` VALUES ('7', '33', '99', '1481101454', '2');

-- -----------------------------
-- Table structure for `onethink_member_earnings`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_member_earnings`;
CREATE TABLE `onethink_member_earnings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT '0' COMMENT '用户id',
  `earnings` varchar(20) DEFAULT '0' COMMENT '收益',
  `order_price` int(10) unsigned NOT NULL COMMENT 'order_price',
  `order_id` text COMMENT '收益订单id',
  `add_time` varchar(20) DEFAULT NULL COMMENT '获得收益时间',
  `add_date` int(11) DEFAULT '0',
  `rate` double(8,5) DEFAULT '0.00000' COMMENT '利率',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='收益表';

-- -----------------------------
-- Records of `onethink_member_earnings`
-- -----------------------------
INSERT INTO `onethink_member_earnings` VALUES ('17', '33', '2.19', '1000', '', '1482224896', '161220', '0.05219');
INSERT INTO `onethink_member_earnings` VALUES ('18', '34', '0.03689', '17', '', '1482224896', '161220', '0.05717');
INSERT INTO `onethink_member_earnings` VALUES ('19', '35', '1.275', '500', '', '1482224896', '161220', '0.05255');

-- -----------------------------
-- Table structure for `onethink_member_search_record`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_member_search_record`;
CREATE TABLE `onethink_member_search_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT '0' COMMENT '用户id',
  `keyword` varchar(255) DEFAULT '' COMMENT '记录',
  `search_nums` int(11) DEFAULT '0' COMMENT '搜索次数',
  `is_show` int(11) DEFAULT '0' COMMENT '是否显示',
  `add_time` varchar(20) DEFAULT '',
  `update_time` varchar(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='搜索记录';

-- -----------------------------
-- Records of `onethink_member_search_record`
-- -----------------------------
INSERT INTO `onethink_member_search_record` VALUES ('1', '33', '123456', '7', '0', '1479813337', '1479813498');

-- -----------------------------
-- Table structure for `onethink_member_token`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_member_token`;
CREATE TABLE `onethink_member_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` int(10) unsigned NOT NULL COMMENT 'token',
  `deviceId` varchar(255) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `starttime` int(11) DEFAULT NULL,
  `status` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1744 DEFAULT CHARSET=utf8;

-- -----------------------------
-- Records of `onethink_member_token`
-- -----------------------------
INSERT INTO `onethink_member_token` VALUES ('1738', '4294967295', '331479796788', '33', '1479796788', '0');
INSERT INTO `onethink_member_token` VALUES ('1737', '465', '331479796785', '33', '1479796785', '0');
INSERT INTO `onethink_member_token` VALUES ('1736', '235118900', '331479796306', '33', '1479796306', '0');
INSERT INTO `onethink_member_token` VALUES ('1735', '0', '321479796103', '32', '1479796103', '0');
INSERT INTO `onethink_member_token` VALUES ('1734', '0', '311479796080', '31', '1479796080', '0');
INSERT INTO `onethink_member_token` VALUES ('1733', '750', '271479789629', '27', '1479789629', '0');
INSERT INTO `onethink_member_token` VALUES ('1732', '97', '191479787415', '19', '1479787415', '0');
INSERT INTO `onethink_member_token` VALUES ('1731', '97015', '191479787414', '19', '1479787414', '0');
INSERT INTO `onethink_member_token` VALUES ('1730', '77', '191479787413', '19', '1479787413', '0');
INSERT INTO `onethink_member_token` VALUES ('1729', '65', '191479787354', '19', '1479787354', '0');
INSERT INTO `onethink_member_token` VALUES ('1728', '8414', '191479787352', '19', '1479787352', '0');
INSERT INTO `onethink_member_token` VALUES ('1727', '91396882', '191478849672', '19', '1478849672', '0');
INSERT INTO `onethink_member_token` VALUES ('1726', '39144', '191478849657', '19', '1478849657', '0');
INSERT INTO `onethink_member_token` VALUES ('1725', '15', '191478849655', '19', '1478849655', '0');
INSERT INTO `onethink_member_token` VALUES ('1724', '0', '191478849652', '19', '1478849652', '0');
INSERT INTO `onethink_member_token` VALUES ('1723', '750', '191478849650', '19', '1478849650', '0');
INSERT INTO `onethink_member_token` VALUES ('1722', '254', '191478849637', '19', '1478849637', '0');
INSERT INTO `onethink_member_token` VALUES ('1721', '5', '191478849311', '19', '1478849311', '0');
INSERT INTO `onethink_member_token` VALUES ('1720', '0', '191478849064', '19', '1478849064', '0');
INSERT INTO `onethink_member_token` VALUES ('1719', '9', '191478849042', '33', '1478849042', '0');
INSERT INTO `onethink_member_token` VALUES ('1718', '0', '191478848882', '19', '1478848882', '0');
INSERT INTO `onethink_member_token` VALUES ('1717', '0', '191478844956', '19', '1478844956', '0');
INSERT INTO `onethink_member_token` VALUES ('1739', '7', '341480661007', '34', '1480661007', '0');
INSERT INTO `onethink_member_token` VALUES ('1740', '8', '351480661048', '35', '1480661048', '0');
INSERT INTO `onethink_member_token` VALUES ('1741', '0', '351480661053', '35', '1480661053', '0');
INSERT INTO `onethink_member_token` VALUES ('1742', '63', '351480661324', '35', '1480661324', '0');
INSERT INTO `onethink_member_token` VALUES ('1743', '0', '351480661446', '35', '1480661446', '0');

-- -----------------------------
-- Table structure for `onethink_member_tx_record`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_member_tx_record`;
CREATE TABLE `onethink_member_tx_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT '0',
  `tx_earnings` varchar(25) DEFAULT '0' COMMENT '提现收益',
  `add_time` varchar(25) DEFAULT '' COMMENT '添加时间',
  `state` int(1) DEFAULT '0' COMMENT '0:未发货,1.已发货',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COMMENT='提现记录';

-- -----------------------------
-- Records of `onethink_member_tx_record`
-- -----------------------------
INSERT INTO `onethink_member_tx_record` VALUES ('2', '33', '5', '1480059350', '0');
INSERT INTO `onethink_member_tx_record` VALUES ('4', '33', '0', '', '0');
INSERT INTO `onethink_member_tx_record` VALUES ('5', '33', '0', '', '0');
INSERT INTO `onethink_member_tx_record` VALUES ('22', '33', '32', '1481081044', '0');
INSERT INTO `onethink_member_tx_record` VALUES ('23', '33', '32', '1481081576', '0');
INSERT INTO `onethink_member_tx_record` VALUES ('24', '33', '32', '1481081580', '0');
INSERT INTO `onethink_member_tx_record` VALUES ('25', '33', '32', '1481081606', '0');
INSERT INTO `onethink_member_tx_record` VALUES ('26', '33', '32', '1481081660', '0');
INSERT INTO `onethink_member_tx_record` VALUES ('27', '33', '99', '1481101454', '0');

-- -----------------------------
-- Table structure for `onethink_menu`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_menu`;
CREATE TABLE `onethink_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档ID',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `url` char(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `tip` varchar(255) NOT NULL DEFAULT '' COMMENT '提示',
  `group` varchar(50) DEFAULT '' COMMENT '分组',
  `is_dev` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否仅开发者模式可见',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=139 DEFAULT CHARSET=utf8;

-- -----------------------------
-- Records of `onethink_menu`
-- -----------------------------
INSERT INTO `onethink_menu` VALUES ('136', '数据备份', '3', '0', 'Database/lists', '0', '', '数据管理', '0', '0');
INSERT INTO `onethink_menu` VALUES ('134', '授权列表', '7', '0', 'AuthManager/access', '0', '', '', '0', '0');
INSERT INTO `onethink_menu` VALUES ('135', '用户管理', '6', '0', 'member/lists', '0', '', '用户管理', '0', '0');
INSERT INTO `onethink_menu` VALUES ('129', '收益', '124', '0', 'MemberEarnings/lists', '0', '', '信息管理', '0', '0');
INSERT INTO `onethink_menu` VALUES ('128', '生成模型', '12', '0', 'model/generate', '0', '', '', '0', '0');
INSERT INTO `onethink_menu` VALUES ('132', '用户列表', '124', '0', 'User/lists', '0', '', '信息管理', '0', '0');
INSERT INTO `onethink_menu` VALUES ('131', '添加', '129', '0', 'MemberEarnings/add', '0', '', '', '0', '0');
INSERT INTO `onethink_menu` VALUES ('130', '编辑', '129', '0', 'MemberEarnings/edit', '0', '', '', '0', '0');
INSERT INTO `onethink_menu` VALUES ('124', '信息', '0', '3', 'MemberEarnings/lists', '0', '', '', '0', '0');
INSERT INTO `onethink_menu` VALUES ('17', '属性编辑', '3', '0', 'attribute/edit', '0', '', '', '0', '0');
INSERT INTO `onethink_menu` VALUES ('16', '属性/添加', '3', '0', 'attribute/add', '0', '', '', '0', '0');
INSERT INTO `onethink_menu` VALUES ('15', '属性列表', '3', '0', 'attribute/lists', '0', '', '', '0', '0');
INSERT INTO `onethink_menu` VALUES ('14', '模型编辑', '3', '0', 'model/edit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('13', '模型添加', '3', '0', 'model/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('12', '模型管理', '3', '0', 'model/lists', '0', '', '系统管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('11', '新增用户组', '7', '0', 'AuthManager/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('138', '商品', '0', '0', 'goods/lists', '0', '', '', '0', '0');
INSERT INTO `onethink_menu` VALUES ('9', '用户新增', '6', '0', 'member/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('137', '订单', '124', '0', 'order/lists', '0', '', '订单管理', '0', '0');
INSERT INTO `onethink_menu` VALUES ('7', '权限管理', '6', '0', 'AuthManager/lists', '0', '', '用户管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('6', '管理员', '0', '2', 'Member/lists', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('5', '菜单编辑', '3', '0', 'menu/edit', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('4', '菜单新增', '2', '4', 'menu/add', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('3', '系统', '0', '4', 'model/lists', '0', '', '', '0', '1');
INSERT INTO `onethink_menu` VALUES ('2', '菜单管理', '3', '3', 'menu/lists', '0', '', '系统管理', '0', '1');
INSERT INTO `onethink_menu` VALUES ('1', '首页', '0', '1', 'index/index', '0', '', '', '0', '1');

-- -----------------------------
-- Table structure for `onethink_model`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_model`;
CREATE TABLE `onethink_model` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模型ID',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '模型标识',
  `title` char(30) NOT NULL DEFAULT '' COMMENT '模型名称',
  `extend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '继承的模型',
  `relation` varchar(30) NOT NULL DEFAULT '' COMMENT '继承与被继承模型的关联字段',
  `need_pk` varchar(50) NOT NULL DEFAULT '' COMMENT '新建表时是否需要主键字段',
  `field_sort` text COMMENT '表单字段排序',
  `field_group` varchar(255) NOT NULL DEFAULT '1:基础' COMMENT '字段分组',
  `attribute_list` text COMMENT '属性列表（表的字段）',
  `attribute_alias` varchar(255) NOT NULL DEFAULT '' COMMENT '属性别名定义',
  `template_list` varchar(100) NOT NULL DEFAULT '' COMMENT '列表模板',
  `template_add` varchar(100) NOT NULL DEFAULT '' COMMENT '新增模板',
  `template_edit` varchar(100) NOT NULL DEFAULT '' COMMENT '编辑模板',
  `list_grid` text COMMENT '列表定义',
  `list_row` smallint(2) unsigned NOT NULL DEFAULT '10' COMMENT '列表数据长度',
  `search_key` varchar(500) NOT NULL DEFAULT '' COMMENT '默认搜索字段',
  `search_list` varchar(255) NOT NULL DEFAULT '' COMMENT '高级搜索的字段',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `engine_type` varchar(25) NOT NULL DEFAULT 'MyISAM' COMMENT '数据库引擎',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8 COMMENT='文档模型表';

-- -----------------------------
-- Records of `onethink_model`
-- -----------------------------
INSERT INTO `onethink_model` VALUES ('65', 'member_token', 'token', '0', '', 'id', '', '1:基础', '', '', '', '', '', '', '10', '', '', '1480673681', '0', '0', 'MyISAM');
INSERT INTO `onethink_model` VALUES ('66', 'order', 'order', '0', '', 'order_id', '{\"1\":[\"527\",\"526\",\"528\",\"553\",\"554\",\"525\",\"524\",\"520\",\"521\",\"522\",\"523\",\"519\"]}', '1:基础', '', '', '', '', '', 'order_code:订单编号', '10', 'order_code:订单号\r\ninto_time:存入时间\r\nplatform:平台', '', '1481089896', '1482201025', '1', 'MyISAM');
INSERT INTO `onethink_model` VALUES ('67', 'member_balance', '余额', '0', '', 'id', '', '1:基础', '', '', '', '', '', '', '10', '', '', '1481089910', '0', '0', 'MyISAM');
INSERT INTO `onethink_model` VALUES ('68', 'message', '消息', '0', '', 'id', '', '1:基础', '', '', '', '', '', '', '10', '', '', '1481694753', '1481694753', '1', 'MyISAM');
INSERT INTO `onethink_model` VALUES ('69', 'member_earnings', '收益', '0', '', 'id', '{\"1\":[\"538\",\"539\",\"537\",\"536\",\"534\",\"535\",\"533\"]}', '1:基础', '', '', '', '', '', 'rate:上传图片', '10', '', '', '1481851558', '1481851673', '1', 'MyISAM');
INSERT INTO `onethink_model` VALUES ('70', 'log', '日志', '0', '', 'log_id', '{\"1\":[\"544\",\"545\",\"543\",\"542\",\"541\",\"540\"]}', '1:基础', '', '', '', '', '', '', '10', '', '', '1481882355', '1481967579', '1', 'MyISAM');
INSERT INTO `onethink_model` VALUES ('74', 'goods', '商品', '0', '', 'id', '{\"1\":[\"555\"]}', '1:基础', '', '', '', '', '', 'goods_name:商品名称', '10', '', '', '1482291440', '1482477346', '1', 'MyISAM');
INSERT INTO `onethink_model` VALUES ('75', 'goods_class', '商品分类', '0', '', 'id', '', '1:基础', '', '', '', '', '', '', '10', '', '', '1482393910', '1482393910', '1', 'MyISAM');

-- -----------------------------
-- Table structure for `onethink_order`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_order`;
CREATE TABLE `onethink_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_code` varchar(255) DEFAULT '' COMMENT '订单编号',
  `order_price` varchar(255) DEFAULT '' COMMENT '订单总价格',
  `xd_time` int(11) DEFAULT '0' COMMENT '下单时间',
  `record_time` int(11) DEFAULT '0' COMMENT '记录订单时间',
  `into_time` int(10) NOT NULL COMMENT 'into_time',
  `platform` char(50) NOT NULL COMMENT 'platform',
  `order_state` int(11) DEFAULT '10' COMMENT '5:未付款,10:进行中,20:已存入:0无效',
  `member_id` int(11) DEFAULT '0',
  `auction_infos` text COMMENT '商品信息',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COMMENT='订单表';

-- -----------------------------
-- Records of `onethink_order`
-- -----------------------------
INSERT INTO `onethink_order` VALUES ('26', '2855836280472795', '12.80', '1481942875', '1482119958', '0', 'taobao', '5', '33', '[{\"detail_order_id\":\"2855836280472795\",\"auction_id\":\"AAFQ_GcKADumc1bG-xQJsjzl\",\"real_pay\":\"12.80\",\"auction_pict_url\":\"i3\\/TB1BTAXOFXXXXb_aFXXXXXXXXXX_!!0-item_pic.jpg\",\"auction_title\":\"\\u536b\\u9f99\\u4eb2\\u5634\\u70e7\\u8fa3\\u6761\\u96f6\\u98df\\u5927\\u793c\\u5305\\u9ebb\\u8fa3\\u7d20\\u98df\\u5c0f\\u9762\\u7b4b\\u5c0f\\u5403\\u4f11\\u95f2\\u513f\\u65f6\\u6000\\u65e7400\\u514b\",\"auction_amount\":\"1\"}]');
INSERT INTO `onethink_order` VALUES ('27', '2857447698222795', '11.80', '1481943093', '1482119958', '1482226956', 'taobao', '20', '33', '[{\"detail_order_id\":\"2857447698222795\",\"auction_id\":\"AAFQ_GcKADumc1bG-xQJsjzl\",\"real_pay\":\"11.80\",\"auction_pict_url\":\"i3\\/TB1BTAXOFXXXXb_aFXXXXXXXXXX_!!0-item_pic.jpg\",\"auction_title\":\"\\u536b\\u9f99\\u4eb2\\u5634\\u70e7\\u8fa3\\u6761\\u96f6\\u98df\\u5927\\u793c\\u5305\\u9ebb\\u8fa3\\u7d20\\u98df\\u5c0f\\u9762\\u7b4b\\u5c0f\\u5403\\u4f11\\u95f2\\u513f\\u65f6\\u6000\\u65e7400\\u514b\",\"auction_amount\":\"1\"}]');
INSERT INTO `onethink_order` VALUES ('28', '2857657692192795', '17.00', '1481945244', '1482119958', '1482226956', 'taobao', '20', '33', '[{\"detail_order_id\":\"2857657692192795\",\"auction_id\":\"AAFu_GcKADumc1bG-xSVp1Nr\",\"real_pay\":\"17.00\",\"auction_pict_url\":\"i4\\/197575766\\/TB2Ml89nVXXXXarXpXXXXXXXXXX_!!197575766.jpg\",\"auction_title\":\"\\u9ad8\\u9732\\u6d01360\\u5168\\u9762\\u53e3\\u8154\\u5065\\u5eb7\\u7259\\u9f88\\u7259\\u818f\\/\\u7f8e\\u767d\\u7259\\u9f7f\\u7259\\u818f200g 1\\u652f\\u5305\\u90ae\",\"auction_amount\":\"1\"}]');
INSERT INTO `onethink_order` VALUES ('29', '2858365903642795', '29.90', '1481941793', '1482119958', '0', 'taobao', '5', '33', '[{\"detail_order_id\":\"2858365903642795\",\"auction_id\":\"AAHp_GcKADumc1bG-2JrC_m4\",\"real_pay\":\"29.90\",\"auction_pict_url\":\"i4\\/TB1SvpJKVXXXXX0XFXXXXXXXXXX_!!0-item_pic.jpg\",\"auction_title\":\"\\u3010\\u5929\\u732b\\u8d85\\u5e02\\u3011\\u4e09\\u53ea\\u677e\\u9f20 \\u7ea6\\u8fa3\\u8fa3\\u6761200g\\u4f11\\u95f2\\u9ebb\\u8fa3\\u96f6\\u98df\\u5927\\u5200\\u8089\\u7279\\u4ea7\\u8fa3\\u7247\",\"auction_amount\":\"1\"}]');
INSERT INTO `onethink_order` VALUES ('30', '2855663088892795', '29.80', '1481941014', '1482119958', '1482226956', 'taobao', '20', '33', '[{\"detail_order_id\":\"2855663088892795\",\"auction_id\":\"AAE3_GcKADumc1bG-xRYBGp9\",\"real_pay\":\"29.80\",\"auction_pict_url\":\"i1\\/TB1fhnLOpXXXXacapXXXXXXXXXX_!!0-item_pic.jpg\",\"auction_title\":\"\\u3010\\u5929\\u732b\\u8d85\\u5e02\\u3011\\u536b\\u9f99\\u5c0f\\u9762\\u7b4b390g\\u96f6\\u98df\\u8fa3\\u6761\\u8fa3\\u7247\\u9ebb\\u8fa3\\u7d20\\u98df\\u8c46\\u5e72\\u5236\\u54c1\\u5927\\u5200\\u8089\",\"auction_amount\":\"1\"}]');
INSERT INTO `onethink_order` VALUES ('31', '2855975885342795', '38.80', '1481944544', '1482119958', '0', 'taobao', '5', '33', '[{\"detail_order_id\":\"2855975885342795\",\"auction_id\":\"AAE1_GcKADumc1bG-xRgygwL\",\"real_pay\":\"38.80\",\"auction_pict_url\":\"i2\\/TB1JLjZOpXXXXbSXVXXXXXXXXXX_!!0-item_pic.jpg\",\"auction_title\":\"\\u3010\\u5929\\u732b\\u8d85\\u5e02\\u3011 \\u536b\\u9f99\\u4eb2\\u5634\\u70e7\\u7ea2\\u70e7\\u725b\\u8089400g\\u8fa3\\u6761\\u9762\\u7b4b\\u5927\\u5200\\u8089\\u9ebb\\u8fa3\\u96f6\\u98df\",\"auction_amount\":\"1\"}]');
INSERT INTO `onethink_order` VALUES ('36', '2869605297383897', '19.80', '1482137063', '1482137074', '0', 'taobao', '5', '33', '[{\"detail_order_id\":\"2869605297383897\",\"auction_id\":\"AAE0_GcKADumc1bG-2MKS16-\",\"real_pay\":\"19.80\",\"auction_pict_url\":\"i3\\/2846035192\\/TB234iyeLSM.eBjSZFNXXbgYpXa_!!2846035192.jpg\",\"auction_title\":\"\\u4e9a\\u9e4f\\u7537\\u889c\\u5973\\u4eba\\u889c\\u79cb\\u51ac\\u5b63\\u9ad8\\u5e2e\\u4e2d\\u7b52\\u889c\\u4fdd\\u6696\\u900f\\u6c14\\u8212\\u9002\\u889c\\u7537\\u58eb\\u5973\\u58eb\\u68c9\\u889c\\u889c\\u5b50\",\"auction_amount\":\"1\"}]');
INSERT INTO `onethink_order` VALUES ('37', '2869598495653897', '19.80', '1482136970', '1482142561', '0', 'taobao', '5', '33', '[{\"detail_order_id\":\"2869598495653897\",\"auction_id\":\"AAE0_GcKADumc1bG-2MKS16-\",\"real_pay\":\"19.80\",\"auction_pict_url\":\"i3\\/2846035192\\/TB2grSwX3JlpuFjSspjXXcT.pXa_!!2846035192.jpg\",\"auction_title\":\"\\u4e9a\\u9e4f\\u7537\\u889c\\u5973\\u4eba\\u889c\\u79cb\\u51ac\\u5b63\\u9ad8\\u5e2e\\u4e2d\\u7b52\\u889c\\u4fdd\\u6696\\u900f\\u6c14\\u8212\\u9002\\u889c\\u7537\\u58eb\\u5973\\u58eb\\u68c9\\u889c\\u889c\\u5b50\",\"auction_amount\":\"1\"}]');
INSERT INTO `onethink_order` VALUES ('38', '2867878487433897', '39.00', '1482136192', '1482142561', '0', 'taobao', '5', '33', '[{\"detail_order_id\":\"2867878487433897\",\"auction_id\":\"AAHJ_GcKADumc1bG-2Ia_ViY\",\"real_pay\":\"39.00\",\"auction_pict_url\":\"i4\\/TB1Ixw9OFXXXXbDaXXXXXXXXXXX_!!0-item_pic.jpg\",\"auction_title\":\"\\u5e0c\\u62c9\\u4e3d\\u858f\\u4ec1\\u723d\\u80a4\\u6c34\\u6536\\u7f29\\u6bdb\\u5b54\\u4fdd\\u6e7f\\u8865\\u6c34\\u55b7\\u96fe\\u6ecb\\u6da6\\u63a7\\u6cb9\\u5065\\u5eb7\\u67d4\\u80a4\\u5973\\u7537\\u5316\\u5986\",\"auction_amount\":\"1\"}]');
INSERT INTO `onethink_order` VALUES ('39', '2870679108593897', '19.80', '1482137126', '1482142561', '0', 'taobao', '5', '33', '[{\"detail_order_id\":\"2870679108593897\",\"auction_id\":\"AAE0_GcKADumc1bG-2MKS16-\",\"real_pay\":\"19.80\",\"auction_pict_url\":\"i3\\/2846035192\\/TB234iyeLSM.eBjSZFNXXbgYpXa_!!2846035192.jpg\",\"auction_title\":\"\\u4e9a\\u9e4f\\u7537\\u889c\\u5973\\u4eba\\u889c\\u79cb\\u51ac\\u5b63\\u9ad8\\u5e2e\\u4e2d\\u7b52\\u889c\\u4fdd\\u6696\\u900f\\u6c14\\u8212\\u9002\\u889c\\u7537\\u58eb\\u5973\\u58eb\\u68c9\\u889c\\u889c\\u5b50\",\"auction_amount\":\"1\"}]');
INSERT INTO `onethink_order` VALUES ('40', '2869818311113897', '159.00', '1482136895', '1482142561', '0', 'taobao', '5', '33', '[{\"detail_order_id\":\"2869818311113897\",\"auction_id\":\"AAFV_GcKADumc1bG-2N2-0Dh\",\"real_pay\":\"159.00\",\"auction_pict_url\":\"i3\\/2993967042\\/TB2UnjLaCzC11BjSszhXXbGVFXa_!!2993967042.jpg\",\"auction_title\":\"\\u5c0f\\u7c73\\u624b\\u73af2\\u4e8c\\u4ee3\\u9632\\u6c34\\u667a\\u80fd\\u8fd0\\u52a8\\u84dd\\u7259\\u624b\\u8868\\u7537\\u5973\\u5fc3\\u7387\\u5149\\u611f\\u8bb0\\u6b65\\u8ba1\\u6b65\\u5668\\u8155\\u5e261\",\"auction_amount\":\"1\"}]');
INSERT INTO `onethink_order` VALUES ('41', '2869828517513897', '29.99', '1482137166', '1482142561', '0', 'taobao', '5', '33', '[{\"detail_order_id\":\"2869828517513897\",\"auction_id\":\"AAF-_GcKADumc1bG-2V5JxAJ\",\"real_pay\":\"29.99\",\"auction_pict_url\":\"i2\\/2148177407\\/TB27og7dr5K.eBjy0FnXXaZzVXa_!!2148177407.jpg\",\"auction_title\":\"\\u889c\\u5b50\\u7537\\u58eb\\u68c9\\u889c\\u79cb\\u51ac\\u5b63\\u4f4e\\u5e2e\\u77ed\\u889c\\u8239\\u889c\\u79cb\\u51ac\\u6b3e\\u52a0\\u539a\\u8fd0\\u52a8\\u9632\\u81ed\\u6d45\\u53e3\\u9690\\u5f62\\u889c\\u590f\",\"auction_amount\":\"1\"}]');

-- -----------------------------
-- Table structure for `onethink_picture`
-- -----------------------------
DROP TABLE IF EXISTS `onethink_picture`;
CREATE TABLE `onethink_picture` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id自增',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '图片链接',
  `md5` char(32) NOT NULL DEFAULT '' COMMENT '文件md5',
  `sha1` char(40) NOT NULL DEFAULT '' COMMENT '文件 sha1编码',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `link` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;

-- -----------------------------
-- Records of `onethink_picture`
-- -----------------------------
INSERT INTO `onethink_picture` VALUES ('87', '/public/uploads/file/admin/20161222\\3c71fd94d2729fa460e9b81977e8b30d.xlsx', '', '', '', '1', '1482371707', '', '0');
INSERT INTO `onethink_picture` VALUES ('86', '/public/uploads/file/admin/20161222\\9275752ee7804c686478499710880769.xlsx', '', '', '', '1', '1482371674', '', '0');
INSERT INTO `onethink_picture` VALUES ('85', '/public/uploads/file/admin/20161222\\ecbed23266bde3c0eefe355f6cf14e73.xlsx', '', '', '', '1', '1482371657', '', '0');
INSERT INTO `onethink_picture` VALUES ('84', '/public/uploads/file/admin/20161222\\8944e55446ab0421304f277d6da99c00.xlsx', '', '', '', '1', '1482371628', '', '0');
INSERT INTO `onethink_picture` VALUES ('83', '/public/uploads/file/admin/20161222\\951e553db9f17742c0fede55199fc735.xlsx', '', '', '', '1', '1482371623', '', '0');
