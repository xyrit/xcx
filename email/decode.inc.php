<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/4
 * Time: 11:34
 */

class mime_decode
{

    var $content = array();

    function mime_encode_headers($string)
    {
        if ($string == "") return;
        if (!@eregi("^([[:print:]]*)$", $string))
            $string = "=?iso-8859-1?q?" . str_replace("+", "_", str_replace("%", "=", urlencode($string))) . "?=";
        return $string;
    }

    function decode_mime_string($string)
    {
        if (($pos = strpos($string, "=?")) === false) return $string;
        while (!($pos === false)) {
            $newresult .= substr($string, 0, $pos);
            $string = substr($string, $pos + 2, strlen($string));
            $intpos = strpos($string, "?");
            $charset = substr($string, 0, $intpos);
            $enctype = strtolower(substr($string, $intpos + 1, 1));
            $string = substr($string, $intpos + 3, strlen($string));
            $endpos = strpos($string, "?=");
            $mystring = substr($string, 0, $endpos);
            $string = substr($string, $endpos + 2, strlen($string));
            if ($enctype == "q") {
                $mystring = str_replace("_", " ", $mystring);
                $mystring = $this->decode_qp($mystring);
            } else if ($enctype == "b")
                $mystring = base64_decode($mystring);
            $newresult .= $mystring;
            $pos = strpos($string, "=?");
        }
        return $newresult . $string;
    }

    function decode_header($header)
    {
        $headers = explode("\r\n", $header);
        $decodedheaders = array();
        for ($i = 0; $i < count($headers); $i++) {
            $thisheader = $headers[$i];
            if (strpos($thisheader, ": ") === false) {
                $decodedheaders[$lasthead] .= " $thisheader";
            } else {
                $dbpoint = strpos($thisheader, ": ");
                $headname = strtolower(substr($thisheader, 0, $dbpoint));
                $headvalue = trim(substr($thisheader, $dbpoint + 1));
                if ($decodedheaders[$headname] != "") $decodedheaders[$headname] .= "; $headvalue";
                else $decodedheaders[$headname] = $headvalue;
                $lasthead = $headname;
            }
        }
        return $decodedheaders;
    }


    function fetch_structure($email)
    {
        $aremail = array();
        $separador = "\r\n\r\n";
        $header = trim(substr($email, 0, strpos($email, $separador)));
        $bodypos = strlen($header) + strlen($separador);
        $body = substr($email, $bodypos, strlen($email) - $bodypos);
        $aremail["header"] = $header;
        $aremail["body"] = $body;
        return $aremail;
    }

    function get_names($strmail)
    {
        $arfrom = array();
        $strmail = stripslashes(ereg_replace("\t", "", ereg_replace("\n", "", ereg_replace("\r", "", $strmail))));
        if (trim($strmail) == "") return $arfrom;

        $armail = array();
        $counter = 0;
        $inthechar = 0;
        $chartosplit = ",;";
        $protectchar = "\"";
        $temp = "";
        $lt = "<";
        $gt = ">";
        $closed = 1;

        for ($i = 0; $i < strlen($strmail); $i++) {
            $thischar = $strmail[$i];
            if ($thischar == $lt && $closed) $closed = 0;
            if ($thischar == $gt && !$closed) $closed = 1;
            if ($thischar == $protectchar) $inthechar = ($inthechar) ? 0 : 1;
            if (!(strpos($chartosplit, $thischar) === false) && !$inthechar && $closed) {
                $armail[] = $temp;
                $temp = "";
            } else
                $temp .= $thischar;
        }

        if (trim($temp) != "")
            $armail[] = trim($temp);

        for ($i = 0; $i < count($armail); $i++) {
            $thispart = trim(eregi_replace("^\"(.*)\"$", "\\1", trim($armail[$i])));
            if ($thispart != "") {
                if (eregi("(.*)<(.*)>", $thispart, $regs)) {
                    $email = trim($regs[2]);
                    $name = trim($regs[1]);
                } else {
                    if (eregi("([-a-z0-9_$+.]+@[-a-z0-9_.]+[-a-z0-9_]+)((.*))", $thispart, $regs)) {
                        $email = $regs[1];
                        $name = $regs[2];
                    } else
                        $email = $thispart;
                }
                $email = eregi_replace("^\<(.*)\>$", "\\1", $email);
                $name = eregi_replace("^\"(.*)\"$", "\\1", trim($name));
                $name = eregi_replace("^\((.*)\)$", "\\1", $name);
                if ($name == "") $name = $email;
                if ($email == "") $email = $name;
                $arfrom[$i]["name"] = $this->decode_mime_string($name);
                $arfrom[$i]["mail"] = $email;
                unset($name);
                unset($email);
            }
        }
        return $arfrom;
    }

    function build_alternative_body($ctype, $body)
    {
        global $mime_show_html;
        $boundary = $this->get_boundary($ctype);
        $part = $this->split_parts($boundary, $body);
        $thispart = ($mime_show_html) ? $part[1] : $part[0];
        $email = $this->fetch_structure($thispart);
        $header = $email["header"];
        $body = $email["body"];
        $headers = $this->decode_header($header);
        $body = $this->compile_body($body, $headers["content-transfer-encoding"]);
        return $body;
    }

    function build_related_body($ctype, $body)
    {
        global $mime_show_html, $sid, $lid, $ix, $folder;
        $rtype = trim(substr($ctype, strpos($ctype, "type=") + 5, strlen($ctype)));

        if (strpos($rtype, ";") != 0)
            $rtype = substr($rtype, 0, strpos($rtype, ";"));
        if (substr($rtype, 0, 1) == "\"" && substr($rtype, -1) == "\"")
            $rtype = substr($rtype, 1, strlen($rtype) - 2);

        $boundary = $this->get_boundary($ctype);
        $part = $this->split_parts($boundary, $body);

        for ($i = 0; $i < count($part); $i++) {
            $email = $this->fetch_structure($part[$i]);
            $header = $email["header"];
            $body = $email["body"];
            $headers = $this->decode_header($header);
            $ctype = $headers["content-type"];
            $cid = $headers["content-id"];
            $actype = split(";", $headers["content-type"]);
            $types = split("/", $actype[0]);
            $rctype = strtolower($actype[0]);
            if ($rctype == "multipart/alternative")
                $msgbody = $this->build_alternative_body($ctype, $body);
            elseif ($rctype == "text/plain" && strpos($headers["content-disposition"], "name") === false) {
                $body = $this->compile_body($body, $headers["content-transfer-encoding"]);
                $msgbody = $this->build_text_body($body);
            } elseif ($rctype == "text/html" && strpos($headers["content-disposition"], "name") === false) {
                $body = $this->compile_body($body, $headers["content-transfer-encoding"]);
                if (!$mime_show_html) $body = $this->build_text_body(strip_tags($body));
                $msgbody = $body;
            } else {
                $thisattach = $this->build_attach($header, $body, $boundary, $i);
                if ($cid != "") {
                    if (substr($cid, 0, 1) == "<" && substr($cid, -1) == ">")
                        $cid = substr($cid, 1, strlen($cid) - 2);
                    $cid = "cid:$cid";
                    $thisfile = "download.php?sid=$sid&lid=$lid&folder=" . urlencode($folder) . "&ix=" . $ix . "&bound=" . base64_encode($thisattach["boundary"]) . "&part=" . $thisattach["part"] . "&filename=" . urlencode($thisattach["name"]);
                    $msgbody = str_replace($cid, $thisfile, $msgbody);
                }
            }
        }
        return $msgbody;
    }

    function linesize($message = "", $length = 70)
    {
        $line = explode("\r\n", $message);
        unset($message);
        for ($i = 0; $i < count($line); $i++) {
            $line_part = explode(" ", trim($line[$i]));
            unset($buf);
            for ($e = 0; $e < count($line_part); $e++) {
                $buf_o = $buf;
                if ($e == 0) $buf .= $line_part[$e];
                else $buf .= " " . $line_part[$e];
                if (strlen($buf) > $length and $buf_o != "") {
                    $message .= "$buf_o\r\n";
                    $buf = $line_part[$e];
                }
            }
            $message .= "$buf\r\n";
        }
        return ($message);
    }
}