<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/4
 * Time: 9:36
 */

header("Content-type: text/html; charset=utf-8");

class mail {
    private $server='';
    private $username='';
    private $password='';
    private $marubox='';
    private $email='';
    public function __construct ($username,$password,$email_address,$mail_server,$server_type,$port,$ssl=false) {
        if($server_type == 'imap') {
            if($port=='') $port='143';
            $str_connect = '{'.$mail_server.'/imap:'.$port.'}INBOX';
        }else{
            if($port=='') $port='110';
            $str_connect = '{'.$mail_server.':'.$port. '/pop3'.($ssl ? "/ssl" : "").'}INBOX';
        }
        $this->server    = $str_connect;
        $this->username    = $username;
        $this->password    = $password;
        $this->email    = $email_address;
    }
    public function connect() {
        $this->marubox = imap_open($this->server,$this->username,$this->password,0);
        if(!$this->marubox) {
            echo "Error: Connecting to mail server<br/>";
            echo $this->server;
            exit;
        }
        return $this->marubox;
    }
    /**
     * 获取邮件总数
     */
    public function get_mail_total() {
        if(!$this->marubox) return false;
        $tmp = imap_num_msg($this->marubox);
        return is_numeric($tmp) ? $tmp : false;
    }
    /**
     * 获取新进邮件总数
     */
    public function get_new_mail_total() {
        if(!$this->marubox) return false;
        $tmp = imap_num_recent($this->marubox);
        return is_numeric($tmp) ? $tmp : false;
    }
    /**
     * 标记邮件成已读
     */
    public function mark_mail_read($mid) {
        return imap_setflag_full($this->marubox, $mid, '\\Seen');
    }
    /**
     * 标记邮件成未读
     */
    public function mark_mail_un_read($mid) {
        return imap_clearflag_full($this->marubox, $mid, '\\Seen');
    }
    /**
     * 获取邮件的头部
     */
    public function get_imap_header($mid) {
        return imap_headerinfo($this->marubox,$mid);
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
    /**
     * 判断是否阅读了邮件 $headerinfo get_imap_header 的返回值
     */
    public function is_unread($headerinfo) {
        if (($headerinfo->Unseen == 'U') || ($headerinfo->Recent == 'N')) return true;
        return false;
    }
    /**
     * 删除邮件
     */
    public function delete_mail($mid) {
        if(!$this->marubox) return false;
        return imap_delete($this->marubox, $mid, 0);
    }
    /**
     * 获取附件
     */
    public function get_attach($mid,$path) {
        if(!$this->marubox) return false;
        $struckture = imap_fetchstructure($this->marubox,$mid);
        $ar="";
        if($struckture->parts) {
            foreach($struckture->parts as $key => $value) {
                $enc=$struckture->parts[$key]->encoding;
                if($struckture->parts[$key]->ifdparameters) {
                    $name=$struckture->parts[$key]->dparameters[0]->value;
                    $message = imap_fetchbody($this->marubox,$mid,$key+1);
                    switch ($enc) {
                        case 0:
                            $message = imap_8bit($message);
                            break;
                        case 1:
                            $message = imap_8bit ($message);
                            break;
                        case 2:
                            $message = imap_binary ($message);
                            break;
                        case 3:
                            $message = imap_base64 ($message);
                            break;
                        case 4:
                            $message = quoted_printable_decode($message);
                            break;
                        case 5:
                            $message = $message;
                            break;
                    }
                    $fp=fopen($path.$name,"w");
                    fwrite($fp,$message);
                    fclose($fp);
                    $ar=$ar.$name.",";
                }
                // Support for embedded attachments starts here
                if(!empty($struckture->parts[$key]->parts)) {
                    foreach($struckture->parts[$key]->parts as $keyb => $valueb) {
                        $enc=$struckture->parts[$key]->parts[$keyb]->encoding;
                        if($struckture->parts[$key]->parts[$keyb]->ifdparameters) {
                            $name=$struckture->parts[$key]->parts[$keyb]->dparameters[0]->value;
                            $partnro = ($key+1).".".($keyb+1);
                            $message = imap_fetchbody($this->marubox,$mid,$partnro);
                            switch ($enc) {
                                case 0:
                                    $message = imap_8bit($message);
                                    break;
                                case 1:
                                    $message = imap_8bit ($message);
                                    break;
                                case 2:
                                    $message = imap_binary ($message);
                                    break;
                                case 3:
                                    $message = imap_base64 ($message);
                                    break;
                                case 4:
                                    $message = quoted_printable_decode($message);
                                    break;
                                case 5:
                                    $message = $message;
                                    break;
                            }
                            $fp=fopen($path.$name,"w");
                            fwrite($fp,$message);
                            fclose($fp);
                            $ar=$ar.$name.",";
                        }
                    }
                }
            }
        }
        $ar=substr($ar,0,(strlen($ar)-1));
        return $ar;
    }
    /**
     * 读取邮件主体
     */
    public function get_body($mid) {
        if(!$this->marubox) return false;
        $body = $this->_get_part($this->marubox, $mid, "TEXT/HTML");
        if ($body == "") $body = $this->_get_part($this->marubox, $mid, "TEXT/PLAIN");
        if ($body == "") return "";
        return $this->_auto_iconv($body);
    }
    private function _get_mime_type(&$structure) {
        $primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");

        if($structure->subtype) {
            return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }
    private function _get_part($stream, $msg_number, $mime_type, $structure = false, $part_number = false) {
        if(!$structure)  $structure = imap_fetchstructure($stream, $msg_number);
        if($structure) {
            if($mime_type == $this->_get_mime_type($structure))
            {
                if(!$part_number)
                {
                    $part_number = "1";
                }
                $text = imap_fetchbody($stream, $msg_number, $part_number);
                //file_put_contents('D:/project/www/b/'.$msg_number.'.txt', $text);
                if($structure->encoding == 3)
                {
                    return imap_base64($text);
                }
                else if($structure->encoding == 4)
                {
                    return imap_qprint($text);
                }
                else
                {
                    return $text;
                }
            }
            if($structure->type == 1) /* multipart */
            {
                while(list($index, $sub_structure) = each($structure->parts))
                {
                    $prefix = false;
                    if($part_number)
                    {
                        $prefix = $part_number . '.';
                    }
                    $data = $this->_get_part($stream, $msg_number, $mime_type, $sub_structure, $prefix . ($index + 1));
                    if($data)
                    {
                        return $data;
                    }
                }
            }
        }
        return false;
    }
    /**
     * 关闭 IMAP 流
     */
    public function close_mailbox() {
        if(!$this->marubox) return false;
        imap_close($this->marubox,CL_EXPUNGE);
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
    /**
     * 对象销毁前关闭邮箱
     */
    public function __destruct() {
        $this->close_mailbox();
    }
}

//User name off the mail box
$username = 'rao-5782474';
//Password of mailbox
$password = 'Rao@19890802';
//Email address of that mailbox some time the uname and email address are identical
$email_address = 'rao-5782474@163.com';
//Ip or name of the POP or IMAP mail server
$mail_server = 'imap.163.com';
//if this server is imap or pop default is pop
$server_type = 'imap';
//Server port for pop or imap Default is 110 for pop and 143 for imap
$port = 143;

$mail = new mail($username,$password,$email_address,$mail_server,$server_type,$port);
$rs=$mail->connect();
var_dump($rs);
$mail_total = $mail->get_mail_total();
var_dump($mail_total);
for ($i=$mail_total; $i>0; $i--) {
    //附件读取这块，我没搞懂，如果哪位能修好，记得通知我。
    $str = $mail->get_attach($i,"./");
    $arr = explode(",",$str);
    foreach($arr as $key=>$value) echo ($value == "") ? "" : "Atteched File :: " . $value . "<br>";
    echo "<br>------------------------------------------------------------------------------------------<br>";
    exit;
}