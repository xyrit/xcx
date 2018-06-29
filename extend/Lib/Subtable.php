<?php
/**
 * 分表
 * Created by PhpStorm.
 * User: Joan
 * Date: 2018/6/20
 * Time: 12:26
 */

namespace Lib;

use think\Db;

define('TABLE_ID', '1807');# 分表起始日期

/**
 * 检查当前表是否存在
 * 存在返回表名，不存在插入返回表名
 * Class Subtable
 * @package Common\Lib
 */
class Subtable
{

    /**
     * 根据条件返回表名
     * @param string $tableName 原始表名
     * @param array $param 条件
     * @param null $tablePrefix 前缀
     * @return string 分表表名
     */
    static public function getSubTableName($tableName = '', $param = array(), $tablePrefix = null)
    {

        if ($tablePrefix === null) $tablePrefix = '';# 默认M方法实例调用不要前缀
        else $tablePrefix = $tablePrefix ?: config("database.prefix");# 非null时采用默认前缀或传递前缀

        if (!empty($param['order_sn'])) $SubTableName = $tablePrefix . $tableName . '_' . substr($param['order_sn'], 2, 6);# 根据单号返分表名
        else if (date("ym", time()) < TABLE_ID) $SubTableName = $tablePrefix . $tableName;# 分表开始日期前返回原有表名
        else $SubTableName = $tablePrefix . $tableName . '_' . date("ym", time());# 返回按月分表
        $tableName = strtolower($tableName);
        self::$tableName($tablePrefix ?: config("database.prefix") . $SubTableName);# 判断是否存在，不存在创建

        return $SubTableName;
    }

    /**
     * 创建表
     * @param $tableName
     * @param $sql
     */
    static public function createTable($tableName = '', $sql = '')
    {
        try {
            $Model = Db::name('');
            $Model->startTrans();
            $check_tables_sql = "show tables like '" . $tableName . "'";
            $check_tables_result = $Model->query($check_tables_sql);

            #  按月生成，没有插入生成
            if (!$check_tables_result) {
                // 创建表时更新自增ID
                if (strstr($tableName, TABLE_ID)) {# 包含常量说明是起始月份
                    $last_table_name = mb_substr($tableName, -20, -5);
                } else {
                    $ym = mb_substr($tableName, -4);# 表的年月序号
                    $ym -= 1;
                    if (mb_substr($ym, -2) == '00') $ym = (mb_substr($ym, 2) - 1) . '12';
                    $last_table_name = mb_substr($tableName, -20, -5) . $ym; # 获得上月该表表ID
                }

                $end_sql = "select max(id)end_id from `" . $last_table_name . "`";# 查询上个月该表的结束ID

                $rs = $Model->query($end_sql);
                $end_id = !empty($rs[0]['end_id']) ? $rs[0]['end_id'] : '0';
                $end_id += 1;
                $sql = str_replace("AUTO_INCREMENT=1", "AUTO_INCREMENT=$end_id", "$sql");# 将自增ID替换为上月该表结束ID+1
                $sql = "CREATE TABLE `" . $tableName . "`" . $sql;

                $createRs = $Model->query($sql);
                if ($rs && $createRs)
                    $Model->commit();
                else
                    $Model->rollback();
            }
        } catch (\Exception $e) {
            file_put_contents('createTable.log', date("Y-m-d H:i:s") . '分表错误:' . $e->getMessage() . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX);
            // echo $e->getMessage();
        }
    }

    /**
     * 支付成功表
     * @param string $tableName
     */
    public static function pay($tableName = '')
    {
        $sql = " (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID自增编号',
  `merchant_id` int(11) NOT NULL COMMENT '商户ID(商户表主键)',
  `customer_id` varchar(45) DEFAULT NULL COMMENT '用户openid,微信可用(会员表主键)',
  `buyers_account` varchar(20) DEFAULT NULL COMMENT '买家支付账号',
  `checker_id` int(11) DEFAULT '0' COMMENT '收银员的ID(用户表主键)',
  `paystyle_id` int(2) NOT NULL COMMENT '支付方式 1是微信 2是支付宝 5是现金支付 3是刷储蓄卡或信用卡',
  `order_id` int(11) DEFAULT '0' COMMENT '双屏收银用到的id(订单表主键)',
  `mode` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0 为台签支付 1为商业扫码支付 2 位商业刷卡支付 3 双屏收银支付 4双屏现金支付,5pos机主扫 6pos机被扫，7pos机现金支付,8pos机其他支付，9pos机刷银行卡，10快速支付,11小程序,12会员充值，13收银APP现金支付，14收银APP余额支付,15小白盒,16台卡余额,17双屏主扫,18双屏余额,19pos余额,20小程序余额,21波普刷卡,22波普扫码,23波普银行卡,24波普余额,25商+宝主扫，26=api接口订单，27=商+宝余额',
  `phone_info` varchar(256) DEFAULT NULL COMMENT '支付人手机信息',
  `price` decimal(10,2) NOT NULL COMMENT '支付金额',
  `price_gold` decimal(10,2) DEFAULT '0.00' COMMENT '奖励金',
  `price_back` decimal(10,2) DEFAULT '0.00' COMMENT '退款金额',
  `cate_id` int(11) DEFAULT NULL COMMENT '支付样式,台签类别',
  `remark` varchar(45) DEFAULT NULL COMMENT '流水号,订单号',
  `wx_remark` varchar(45) DEFAULT '' COMMENT '微信支付订单号',
  `wz_remark` varchar(45) DEFAULT NULL COMMENT '微众订单号',
  `jmt_remark` varchar(45) DEFAULT NULL COMMENT '金木堂订单号',
  `subject` varchar(100) DEFAULT NULL COMMENT '付款描述',
  `cost_rate` decimal(3,2) DEFAULT NULL COMMENT '支付的利率',
  `sort` int(2) NOT NULL DEFAULT '1' COMMENT '排序',
  `bank` tinyint(1) DEFAULT NULL COMMENT '1为微众银行 2为民生银行 3为微信围餐 4招商银行 6济南民生(D0) 7兴业银行 9宿州李灿 10东莞中信 11新大陆 12乐刷',
  `status` varchar(5) NOT NULL DEFAULT '0' COMMENT '支付状态，0未支付,-1支付中，-2失败,1支付成功  2退款成功 3退款失败 4退款中   6预授权',
  `back_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 为未进行退款 1 退款成功  2 退款失败',
  `confirm_status` tinyint(1) DEFAULT '0' COMMENT '作为微信台卡确认订单用途',
  `agent_status` tinyint(1) DEFAULT '0' COMMENT '代理商确认流水',
  `add_time` int(10) unsigned DEFAULT NULL COMMENT '支付订单生成时间',
  `paytime` int(10) unsigned NOT NULL COMMENT '实际支付时间',
  `bill_date` int(11) DEFAULT NULL COMMENT '支付时间按年月日排布',
  `brash` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否删除',
  `new_order_sn` varchar(50) DEFAULT NULL COMMENT '支付订单号,用于退款',
  `no_number` char(12) DEFAULT NULL,
  `transId` varchar(255) DEFAULT NULL COMMENT '民生银行对方订单号用于退款查询',
  `repaid_rate` decimal(10,2) DEFAULT '0.00' COMMENT '垫资费率千分比',
  `poundage` decimal(10,2) DEFAULT '0.00' COMMENT '每比手续费',
  `min_repaid_amount` decimal(10,2) DEFAULT '0.00' COMMENT '最低垫资费',
  `bill_id` int(11) DEFAULT '0' COMMENT '对账单id',
  `remark_mer` varchar(255) DEFAULT NULL COMMENT '接口方订单号',
  `notify_status` tinyint(3) DEFAULT NULL COMMENT '1，回调，2停止回调',
  `bank_mch_id` int(11) DEFAULT NULL COMMENT '接口方id',
  `la_ka_la` tinyint(2) DEFAULT '0' COMMENT '是否是拉卡拉通道  0=否  1=是',
  `authorization` tinyint(2) DEFAULT '0' COMMENT '是否预授权，0=否；1=是',
  `cardtype` varchar(2) DEFAULT '' COMMENT '刷银行卡类型（00借记卡，01贷记卡，02准贷记卡，03预付卡，04其他）',
  `is_ypt` tinyint(1) DEFAULT '0' COMMENT '收款人，0商户，1洋仆淘',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `merchant_id` (`merchant_id`) USING BTREE,
  KEY `remark` (`remark`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='支付订单表'";

        self::createTable($tableName, $sql);
    }

    /**
     * 订单表
     * @param string $tableName
     */
    public static function order($tableName = '')
    {
        $sql = " (
  `order_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单id',
  `order_sn` varchar(20) NOT NULL DEFAULT '' COMMENT '订单编号',
  `mid` int(11) DEFAULT '0' COMMENT '会员id 关联ypt_screen_mem',
  `sup_id` int(11) unsigned DEFAULT '0' COMMENT '代理商uid',
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id 关联ypt_merchants_users',
  `order_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态 1:待付款 2:待发货 3:已发货 4：已收货 5:交易成功 0:交易关闭（订单取消） 7:退换货申请中',
  `shipping_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '发货状态',
  `pay_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '支付状态',
  `consignee` varchar(60) NOT NULL DEFAULT '' COMMENT '收货人',
  `country` varchar(10) DEFAULT '0' COMMENT '国家（不用这个字段要删除）',
  `province` varchar(20) DEFAULT '' COMMENT '省份（不用这个字段要删除）',
  `city` varchar(20) DEFAULT '' COMMENT '城市（不用这个字段要删除）',
  `district` varchar(20) DEFAULT '' COMMENT '县区（不用这个字段要删除）',
  `twon` varchar(20) DEFAULT '' COMMENT '乡镇（不用这个字段要删除）',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `zipcode` varchar(60) NOT NULL DEFAULT '' COMMENT '邮政编码',
  `mobile` varchar(60) NOT NULL DEFAULT '' COMMENT '手机',
  `email` varchar(60) NOT NULL DEFAULT '' COMMENT '邮件',
  `shipping_code` varchar(32) NOT NULL DEFAULT '0' COMMENT '物流code',
  `shipping_name` varchar(120) NOT NULL DEFAULT '' COMMENT '物流名称',
  `pay_code` varchar(32) NOT NULL DEFAULT '' COMMENT '支付code',
  `pay_name` varchar(120) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `invoice_title` varchar(256) DEFAULT '' COMMENT '发票抬头',
  `shipping_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '邮费',
  `user_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '使用余额',
  `coupon_code` varchar(64) DEFAULT '' COMMENT '优惠券code,对应coupon表的usercode字段',
  `coupon_price` decimal(10,2) DEFAULT '0.00' COMMENT '优惠券抵扣',
  `card_code` varchar(45) DEFAULT '' COMMENT '会员卡号,关联screen_memcard_use表card_code',
  `integral` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用积分',
  `discount_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '使用折扣抵用',
  `integral_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '使用积分抵多少钱',
  `order_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际付款金额',
  `total_amount` decimal(10,2) DEFAULT '0.00' COMMENT '订单总价',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下单时间',
  `shipping_time` int(11) DEFAULT '0' COMMENT '最后新发货时间',
  `confirm_time` int(10) DEFAULT '0' COMMENT '收货确认时间',
  `paystyle` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1=微信支付，2=支付宝，5=现金支付，3=刷卡,6=储值余额',
  `tuikuan_price` decimal(10,2) DEFAULT NULL,
  `pay_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `order_prom_type` tinyint(4) DEFAULT '0' COMMENT '0默认1抢购2团购3优惠4预售5虚拟',
  `order_prom_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '活动id',
  `order_benefit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '该订单优惠金额',
  `user_note` varchar(255) NOT NULL DEFAULT '' COMMENT '用户备注',
  `admin_note` varchar(255) DEFAULT '' COMMENT '管理员备注',
  `parent_sn` varchar(100) DEFAULT NULL COMMENT '父单单号',
  `is_distribut` tinyint(1) DEFAULT '0' COMMENT '是否已分成0未分成1已分成',
  `paid_money` decimal(10,2) DEFAULT '0.00' COMMENT '订金',
  `order_goods_num` int(11) DEFAULT '0' COMMENT '该订单商品数量',
  `discount` smallint(3) NOT NULL DEFAULT '100' COMMENT '整单折扣，默认100',
  `type` tinyint(3) DEFAULT '0' COMMENT '0为收银订单，1为小程序，2为点餐订单，3为pos机订单,4为双屏订单,5采购商城订单',
  `dc_no` int(10) DEFAULT NULL COMMENT '点餐小程序：餐桌号 ，关联dc_no表的主键id',
  `dc_db` tinyint(2) DEFAULT '2' COMMENT '是否打包：1打包，2不打包, 3打包外卖',
  `dc_db_price` decimal(10,2) DEFAULT '0.00' COMMENT '打包费',
  `dc_ps_price` decimal(10,2) DEFAULT '0.00' COMMENT '配送费',
  `dc_ch_price` decimal(10,2) DEFAULT '0.00' COMMENT '餐盒费',
  `prepay_id` varchar(255) DEFAULT NULL,
  `transaction` varchar(255) DEFAULT '' COMMENT '交易编号',
  `real_price` decimal(10,2) DEFAULT '0.00' COMMENT '实际支付多少钱',
  `update_time` int(11) DEFAULT '0' COMMENT '订单更新时间',
  `area_id` int(11) DEFAULT '0' COMMENT '地区id',
  `shipping_style` int(2) DEFAULT '0' COMMENT '配送方式（关联express表id）',
  `is_eval` int(2) DEFAULT '0' COMMENT '是否评价 0=未评价，1=评价',
  `staff_id` int(11) NOT NULL DEFAULT '0' COMMENT '推荐职员id  关联用户表主键',
  `is_cancel` tinyint(1) DEFAULT '0' COMMENT '该笔订单是否已核销，1已核销，0未核销',
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_sn` (`order_sn`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42328 DEFAULT CHARSET=utf8 COMMENT='订单表'";

        self::createTable($tableName, $sql);
    }
}