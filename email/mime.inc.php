<?php

/**
 * 邮件解码
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/3
 * Time: 12:00
 */
class decode_mail
{
    var $from_name;
    var $to_name;
    var $mail_time;
    var $from_mail;
    var $to_mail;
    var $reply_to;
    var $cc_to;
    var $subject;

    // 解码后的邮件头部分的信息：
    var $body;
    // 解码后得到的正文数据，为一个数组。
    var $body_type;// 正文类型
    var $tem_num = 0;
    var $get_content_num = 0;
    var $body_temp = array();
    var $body_code_type;
    var $boundary;
    // 以上是一些方法中用到的一些全局性的临时变量，由于　PHP不能做到良好的封装，所以只能放在这里定义
    var $err_str;// 错误信息
    var $debug = 1; // 调试标记
    var $month_num = array("Jan" => 1, "Feb" => 2, "Mar" => 3, "Apr" => 4, "May" => 5, "Jun" => 6, "Jul" => 7, "Aug" => 8, "Sep" => 9, "Oct" => 10, "Nov" => 11, "Dec" => 12); // 把英文月份转换成数字表示的月份

    // 调用的主方法，$head 与 $body 是两个数组，$content_num 表示的是当正文有多个//部分的时候，只取出指定部分的内容以提高效率，默认为　-1 ,表示解码全部内容，如果解码成功，该 方法返回 true
    function decode($head = null, $body = null, $content_num = -1)
    {

        if (!$head and !$body) {
            $this->err_str = "没有指定邮件的头与内容！!";
            return false;
        }

        if (gettype($head) == "array") {

            $have_decode = true;

            $this->decode_head($head);


        }

        if (gettype($body) == "array") {

            $this->get_content_num = $content_num;

            $this->body_temp = $body;

            $have_decode = true;

            $this->decode_body();

            unset($this->body_temp);

        }

        if (isset($have_decode) && !$have_decode) {

            $this->err_str = "传递的参数不对，用法：new decode_mail(head,body) 两个参数都是数组";
            return false;
        }
    }

    function decode_head($head) // 邮件头内容 的解码，取出邮件头中有意义的内容
    {
        $i = 0;

        $this->from_name = $this->to_name = $this->mail_time = $this->from_mail = $this->to_mail = $this->reply_to = $this->cc_to = $this->subject = "";

        $this->body_type = $this->boundary = $this->body_code_type = "";
        //var_dump($head);
        while (htmlspecialchars($head[$i])) {
            var_dump($i);
            if (strpos($head[$i], "=?"))

                $head[$i] = $this->decode_mime($head[$i]); //如果有编码的内容，则进行解码，解码函数是上文所介绍的　decode_mime()

            $pos = strpos($head[$i], ":");

            $summ = substr($head[$i], 0, $pos);

            $content = substr($head[$i], $pos + 1); //将邮件头信息的标识与内容分开

            if ($this->debug) echo $summ . ":----:" . $content . "";
            // var_dump('sum',$summ);
            switch (strtoupper($summ)) {

                case "FROM": // 发件人地址及姓名（可能没有姓名，只有地址信息）
                    if ($left_tag_pos = strpos($content, "<")) {
                        $mail_lenth = strrpos($content, ">") - $left_tag_pos - 1;
                        $this->from_name = substr($content, 0, $left_tag_pos);
                        $this->from_mail = substr($content, $left_tag_pos + 1, $mail_lenth);
                        if (trim($this->from_name) == "") $this->from_name = $this->from_mail;
                        else {
                            if (@ereg('[/"|/\']([^/\'/"]+)[/\'|/"]', $this->to_name, $reg)) $this->from_name = $reg[1];
                        }

                    } else {
                        $this->from_name = $content;
                        $this->from_mail = $content;
                        //没有发件人的邮件地址
                    }
                    break;
                case "TO": //收件人地址及姓名（可能 没有姓名）
                    if ($left_tag_pos = strpos($content, " < ")) {

                        $mail_lenth = strrpos($content, ">") - $left_tag_pos - 1;

                        $this->to_name = substr($content, 0, $left_tag_pos);

                        $this->to_mail = substr($content, $left_tag_pos + 1, $mail_lenth);

                        if (trim($this->to_name) == "") $this->to_name = $this->to_mail;
                        else {
                            //if (ereg("[/"|/']([^/'/"]+)[/'|/"]",$this->to_name,$reg))
                            if (ereg('[/"|/\']([^/\'/"]+)[/\'|/"]', $this->to_name, $reg)) $this->to_name = $reg[1];
                        }

                    } else {

                        $this->to_name = $content;

                        $this->to_mail = $content;

                        //没有分开收件人的邮件地址

                    }

                    break;

                case "DATE" : //发送日期，为了处理方便，这里返回的是一个 Unix 时间戳，可以用 date("Y-m-d",$this->mail_time)　来得到一般格式的日期

                    $content = trim($content);

                    $day = strtok($content, " ");

                    $day = substr($day, 0, strlen($day) - 1);

                    $date = strtok(" ");

                    $month = $this->month_num[strtok(" ")];

                    $year = strtok(" ");

                    $time = strtok(" ");

                    $time = @split(":", $time);

                    $this->mail_time = mktime($time[0], $time[1], $time[2], $month, $date, $year);

                    break;

                case "SUBJECT":
                    //邮件主题

                    $this->subject = $content;
                    break;

                case "REPLY_TO":
                    // 回复地址(可能没有)

                    if (ereg("<([^>]+)>", $content, $reg))

                        $this->reply_to = $reg[1];

                    else $this->reply_to = $content;

                    break;

                case "CONTENT-TYPE": // 整个邮件的 Content类型， eregi("([^;]*);",$content,$reg);

                    $this->body_type = !empty($reg) ? trim($reg[1]) : '';

                    if (@eregi("multipart", $content)) // 如果是　multipart 类型，取得　分隔符

                    {

                        while (!eregi('boundary = "(.*)"', $head[$i], $reg) and $head[$i])

                            $i++;

                        $this->boundary = $reg[1];

                    } else {//对于一般的正文类型，直接取得其编码方法

                        while (!@eregi("charset=[" | '](.*)[' | "]", $head[$i], $reg))

                            $i++;

                        $this->body_char_set = $reg[1];

                        while (!@eregi("Content-Transfer-Encoding:(.*)", $head[$i], $reg))

                            $i++;

                        $this->body_code_type = trim($reg[1]);

                    }

                    break;
                case "CC":
                    //抄送到。。
                    if (ereg("<([^>]+)>", $content, $reg))
                        $this->cc_to = $reg[1];
                    else
                        $this->cc_to = $content;
                default:
                    echo 'kong';
                    break;
            } // end switch

            $i++;
            //var_dump($i,$this);
        } // end while

        if (trim($this->reply_to) == "")//如果没有指定回复地址，则回复地址为发送人地址

            $this->reply_to = $this->from_mail;

    }

    function decode_body()//正文的解码，其中用到了不少邮件头解码所得来的信息

    {
        echo 'body';
        $i = 0;

        if (!@eregi("multipart", $this->body_type)) //　如果不是复合类型，可以直接解码

        {

            $tem_body = implode($this->body_temp, "rn");

            switch (strtolower($this->body_code_type))// body_code_type ，正文的编码方式，由邮件头信息中取得

            {
                case "base64":

                    $tem_body = base64_decode($tem_body);

                    break;

                case "quoted-printable":

                    $tem_body = quoted_printable_decode($tem_body);

                    break;

            }

            $this->tem_num = 0;

            $this->body = array();

            $this->body[$this->tem_num]['content_id'] = "";

            $this->body[$this->tem_num]['type'] = $this->body_type;

            switch (strtolower($this->body_type)) {

                case "text/html":

                    $this->body[$this->tem_num]['name'] = "超文本正文";

                    break;

                case "text/plain":

                    $this->body[$this->tem_num]['name'] = "文本正文";

                    break;

                default:

                    $this->body[$this->tem_num]['name'] = "未知正文";

            }

            $this->body[$this->tem_num]['size'] = strlen($tem_body);

            $this->body[$this->tem_num]['content'] = $tem_body;

            unset($tem_body);

        } else //　如果是复合类型的

        {

            $this->body = array();

            $this->tem_num = 0;

            $this->decode_mult($this->body_type, $this->boundary, 0);
            //调用复合类型的解码方法

        }

    }

    function decode_mult($type, $boundary, $begin_row)// 该方法用递归的方法实现　复合类型邮件正文的解码，邮件源文件取自于 body_temp 数组，调用时给出该复合类型的类型、分隔符及　在　body_temp 数组中的开始指针

    {
        $i = $begin_row;

        $lines = count($this->body_temp);

        while ($i < $lines) // 这是一个部分的结束标识；

        {

            while (!eregi($boundary, $this->body_temp[$i]))//找到一个开始标识

                $i++;

            if (eregi($boundary . "--", $this->body_temp[$i])) {

                return $i;

            }

            while (!eregi("Content-Type:([^;]*);", $this->body_temp[$i], $reg) and $this->body_temp[$i])

                $i++;

            $sub_type = trim($reg[1]); // 取得这一个部分的 类型是milt or text ....

            if (eregi("multipart", $sub_type))// 该子部分又是有多个部分的；

            {

                while (!eregi('boundary = "([^"]*)"', $this->body_temp[$i], $reg) and $this->body_temp[$i])

                    $i++;

                $sub_boundary = $reg[1];// 子部分的分隔符；

                $i++;

                $last_row = $this->decode_mult($sub_type, $sub_boundary, $i);

                $i = $last_row;

            } else {

                $comm = "";

                while (trim($this->body_temp[$i]) != "") {

                    if (strpos($this->body_temp[$i], " =?"))

                        $this->body_temp[$i] = $this->decode_mime($this->body_temp[$i]);

                    if (eregi("Content - Transfer - Encoding:(.*)", $this->body_temp[$i], $reg))

                        $code_type = strtolower(trim($reg[1])); // 编码方式

                    $comm .= $this->body_temp[$i] . "rn";

                    $i++;

                } // comm 是编码的说明部分

                //if (eregi(‘name=["]([^"]*)["]‘,$comm,$reg))

                $name = $reg[1];

                if (eregi("Content-Disposition:(.*);", $comm, $reg))

                    $disp = $reg[1];

                if (eregi("charset=[" | '](.*)[' | "]", $comm, $reg))

                    $char_set = $reg[1];

                if (eregi("Content-ID:[ ]*<(.*)>", $comm, $reg)) // 图片的标识符。

                    $content_id = $reg[1];


                $this->body[$this->tem_num]['type'] = $sub_type;

                $this->body[$this->tem_num]['content_id'] = $content_id;

                $this->body[$this->tem_num]['char_set'] = $char_set;

                if ($name)

                    $this->body[$this->tem_num]['name'] = $name;

                else

                    switch (strtolower($sub_type)) {

                        case "text/html":

                            $this->body[$this->tem_num]['name'] = "超文本正文";

                            break;

                        case "text/plain":

                            $this->body[$this->tem_num]['name'] = "文本正文";

                            break;

                        default:

                            $this->body[$this->tem_num]['name'] = "未知正文";

                    }

// 下一行开始取回正文

                if ($this->get_content_num == -1 or $this->get_content_num == $this->tem_num) // 判断这个部分是否是需要的。-1 表示全部

                {

                    $content = "";

                    while (!ereg($boundary, $this->body_temp[$i])) {

                        //$content[]=$this->body_temp[$i];

                        $content .= $this->body_temp[$i] . "rn";

                        $i++;

                    }

//$content=implode("rn",$content);
                    switch ($code_type) {

                        case "base64":

                            $content = base64_decode($content);

                            break;

                        case "quoted-printable":

                            $content = str_replace("n", "rn", quoted_printable_decode($content));

                            break;

                    }

                    $this->body[$this->tem_num]['size'] = strlen($content);

                    $this->body[$this->tem_num]['content'] = $content;

                } else {

                    while (!ereg($boundary, $this->body_temp[$i]))

                        $i++;

                }

                $this->tem_num++;

            }

// end else

        } // end while;

    } // end function define


    function decode_mime($string)
    {
        $pos = strpos($string, '=?');
        if (!is_int($pos)) {
            return $string;
        }
        $preceding = substr($string, 0, $pos); // save any preceding text
        $search = substr($string, $pos + 2);
        /* the mime header spec says this is
         the longest a single encoded word can be */
        $d1 = strpos($search, '?');
        if (!is_int($d1)) {
            return $string;
        }
        $charset = substr($string, $pos + 2, $d1); //取出字符集的定义部分
        $search = substr($search, $d1 + 1); //字符集定义以后的部分＝>$search;
        $d2 = strpos($search, '?');
        if (!is_int($d2)) {
            return $string;
        }
        $encoding = substr($search, 0, $d2); ////两个?　之间的部分编码方式　：ｑ或　ｂ　
        $search = substr($search, $d2 + 1);
        $end = strpos($search, '?='); //$d2+1 与 $end 之间是编码了　的内容：=>
        // $endcoded_text;
        if (!is_int($end)) {
            return $string;
        }
        $encoded_text = substr($search, 0, $end);
        $rest = substr($string, (strlen($preceding . $charset . $encoding . $encoded_text) + 6)); //+6 是前面去掉的　=????=　六个字符
        switch ($encoding) {
            case 'Q':
            case 'q':
                //$encoded_text = str_replace('_', '%20', $encoded_text);
                //$encoded_text = str_replace('=', '%', $encoded_text);
                //$decoded = urldecode($encoded_text);
                $decoded = quoted_printable_decode($encoded_text);
                if (strtolower($charset) == 'windows-1251') {
                    $decoded = convert_cyr_string($decoded, 'w', 'k');
                }
                break;
            case 'B':
            case 'b':
                $decoded = base64_decode($encoded_text);
                if (strtolower($charset) == 'windows-1251') {
                    $decoded = convert_cyr_string($decoded, 'w', 'k');
                }
                break;
            default:
                $decoded = '=?' . $charset . '?' . $encoding . '?' . $encoded_text . '?=';
                break;
        }
        return $preceding . $decoded . $this->decode_mime($rest);
    }

    /**
     * 格式化头部信息 $headerinfo get_imap_header 的返回值
     */
    public function get_header_info($mail_header) {
        $sender=$mail_header->from[0];
        $sender_replyto=$mail_header->reply_to[0];
        if(strtolower($sender->mailbox)!='mailer-daemon' && strtolower($sender->mailbox)!='postmaster') {
            $mail_details=array(
                'from'=>strtolower($sender->mailbox).'@'.$sender->host,
                'fromName'=>$this->_decode_mime_str($sender->personal),
                'toOth'=>strtolower($sender_replyto->mailbox).'@'.$sender_replyto->host,
                'toNameOth'=>$this->_decode_mime_str($sender_replyto->personal),
                'subject'=>$this->_decode_mime_str($mail_header->subject),
                'to'=>strtolower($this->_decode_mime_str($mail_header->toaddress))
            );
        }
        return $mail_details;
    }

    private function _decode_mime_str($string, $charset="UTF-8" ) {
        $newString = '';
        $elements=imap_mime_header_decode($string);
        for($i=0;$i<count($elements);$i++) {
            if($elements[$i]->charset == 'default') $elements[$i]->charset = 'iso-8859-1';
            $newString .= iconv($elements[$i]->charset, $charset, $elements[$i]->text);
        }
        return $newString;
    }

}