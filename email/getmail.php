<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/6/28
 * Time: 19:30
 */
header("content-type:text/html;charset=utf-8");
//error_reporting(0);

$getmail = new getmail();

$getmail->index();

class getmail
{
    //  不同后缀邮箱对应的邮件服务器
    static $emailComArray = array(
        'qq.com' => array(
            "host" => 'pop.qq.com',
            "port" => 110,
        ),
        '163.com' => array(
            "host" => 'pop.163.com',
            "port" => 110,
        ),
        '126.com' => array(
            "host" => 'pop.163.com',
            "port" => 110,
        ),
        'sohu.com' => array(
            "host" => 'pop3.sohu.com',
            "port" => 110,
        ),
        '21cn.com' => array(
            "host" => 'pop.21cn.com',
            "port" => 110,
        ),
        'tom.com' => array(
            "host" => 'pop.tom.com',
            "port" => 110,
        ),
        'sina.com' => array(
            "host" => 'pop.sina.com',
            "port" => 110,
        ),
        'gmail.com' => array(
            "host" => 'ssl://pop.gmail.com',
            "port" => 995,
        )
    );

    /**
     *获取邮件
     */
    function index()
    {
        ini_set('memory_limit', '3072M');    // 临时设置最大内存占用为3G
        set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期
        include("pop3.inc.php");
        include("mime.inc.php");
        include("decode.inc.php");
        include("decode2.inc.php");

        $user = !empty($_REQUEST['user']) ? $_REQUEST['user'] : "247261497@163.com";# 邮箱用户名
        $pass = !empty($_REQUEST['user']) ? $_REQUEST['pass'] : "123456";# 邮箱密码

        if (!$this->checkEmail($user)) exit('邮箱格式不正确!');
        $maillArray = explode('@', $user);
        $host = self::$emailComArray[$maillArray[1]]['host'];# 获取邮件host

        $rec = new pop3($host, 110, 2);
        $decoder = new decode_mail();
        $decode=new mime_decode();
        $decode2=new Decode_Mimemail();

        if (!$rec->open()) die($rec->err_str);
        if ($rec->debug) echo "open ";
        if (!$rec->login($user, $pass)) die($rec->err_str);
        if ($rec->debug) echo "login";

        if (!$rec->stat()) die($rec->err_str);
        echo "共有" . $rec->messages . "封信件，共" . $rec->size . "字节大小\r\n\r\n";
        if ($rec->messages > 0) {
            if (!$rec->listmail()) die($rec->err_str);
            echo "有以下信件：\r\n\r\n";
            if (1) {
                for ($i = 1; $i <= count($rec->mail_list); $i++) {
                    echo "信件" . $rec->mail_list[$i]['num'] . "大小：" . $rec->mail_list[$i]['size'] . "\r\n";
                }

                $rec->getmail(8);
                echo "\r\n邮件头的内容：\r\n\r\n";
               // var_dump($rec->head);exit;
                for ($i = 0; $i < count($rec->head); $i++) {
                   // echo htmlspecialchars($rec->head[$i]) . "\r\n";

                    echo $decode2->decode($rec->head[$i]). "\r\n";
                }
                exit;
                echo "\r\n邮件正文　：\r\n";
                $str = '';
                for ($i = 0; $i < count($rec->body); $i++) {
                    if ($i > 72) echo base64_decode(htmlspecialchars($rec->body[$i])) . "\r\n";
                    else echo (htmlspecialchars($rec->body[$i])) . "\r\n";
                    // echo base64_decode(htmlspecialchars($rec->body[$i])) . "\r\n";
                    //$str.=base64_decode(htmlspecialchars($rec->body[$i])) . "\r\n";
                    //var_dump(htmlspecialchars($rec->head[$i]));
                }
                echo $str;
            } else {
                for ($i = 1; $i <= count($rec->mail_list); $i++) {
                    echo "信件" . $rec->mail_list[$i]['num'] . ",大小：" . $rec->mail_list[$i]['size'] . "\r\n";
                    // $rs1=$rec->getmail($rec->mail_list[$i]['num']);
                    $rs1 = $rec->getmail(7);
                    //var_dump($rec->head,$rec->body);

                    $rs2 = $decoder->decode($rec->head, $rec->body);
                    var_dump('decoder', $decoder);
                    echo "邮件头的内容：\r\n";
                    echo $decoder->from_name . "(" . $decoder->from_mail . ") 于" . date("Y-m-d H:i:s", $decoder->mail_time) . " 发给" . $decoder->to_name . "(" . $decoder->to_mail . ")";
                    echo "\r\n\r\n抄送：";
                    if ($decoder->cc_to) echo $decoder->cc_to; else echo "无";
                    echo "\r\n\r\n主题：" . $decoder->subject;
                    echo "\r\n\r\n回复到:" . $decoder->reply_to;

                    echo "邮件正文　：\r\n";
                    echo "正文类型：" . $decoder->body_type;
                    echo "\r\n正文各内容：";
                    for ($j = 0; $j < count($decoder->body); $j++) {
                        echo "\r\n\r\n类型：" . $decoder->body[$j]['type'];
                        echo "\r\n\r\n名称：" . $decoder->body[$j]['name'];
                        echo "\r\n\r\n大小:" . $decoder->body[$j]['size'];
                        echo "\r\n\r\ncontent_id:" . $decoder->body[$j]['content_id'];
                        echo "\r\n\r\n正文字符集" . $decoder->body[$j]['char_set'];
                        echo "<pre>";
                        echo "正文内容:" . $decoder->body[$j]['content'];
                        echo "</pre>";
                    }
                    $rec->dele($i);
                }
            }
            $rec->close();
        }
    }

    /**
     * 检查邮箱
     * @param string $email
     * @return bool
     */
    function checkEmail($email = '')
    {
        if (!$email) return false;
        $pattern = "/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
        if (!preg_match($pattern, $email)) {
            return false;
        }
        return true;
    }
}


