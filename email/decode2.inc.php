<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/4
 * Time: 11:50
 */

class Decode_Mimemail
{

    /**
     * Mime File
     * @var    string
     */
    var $_input;

    /**
     * header string
     * @var    string
     */
    var $_header;

    /**
     * body string
     * @var    string
     */
    var $_body;

    /**
     * err info
     * @var    string
     */
    var $_error;

    /**
     * whether include body object
     * @var    boolean
     */
    var $_include_bodies;

    /**
     * whether include body object
     * @var    boolean
     */
    var $_decode_bodies;

    /**
     * whether decode headers object
     * @var    boolean
     */
    var $_decode_headers;

    /**
     * crlf variable
     * @var    string
     */
    var $_crlf;

    /**
     * body parts
     * @var object
     */
    var $parts;
    var $mid;
    var $maildir;

    /**
     * Constructor.
     *
     * Sets up the object, initialise the variables, and splits and
     * stores the header and body of the input.
     *
     * @param string The input to decode
     * @access public
     */

    function Decode_Mimemail($input, $mid, $maildir, $crlf = "\n")
    {
        $this->_crlf   = "\n";
        list($header, $body)    = $this->splitBodyHeader($input); //拆分信头和信体两块 
        $this->_input            = $input;
        $this->_header            = $header;
        $this->_body            = $body;
        $this->mid                = $mid;
        $this->maildir            = $maildir;
        $this->_decode_bodies    = false;
        $this->_include_bodies    = true;
    }

    /**
     * Begins the decoding process. If called statically
     * it will create an object and call the decode() method
     * of it.
     *
     * @param array An array of various parameters that determine
     *              various things:
     *              include_bodies - Whether to include the body in the returned
     *                               object.
     *              decode_bodies  - Whether to decode the bodies
     *                               of the parts. (Transfer encoding)
     *              decode_headers - Whether to decode headers
     *              input          - If called statically, this will be treated
     *                               as the input
     * @return object Decoded results
     * @access public
     */

    function decode($params = null)
    {
        // Have we been called statically? 
        // If so, create an object and pass details to that. 
        if (!isset($this) AND isset($params['input']))
        {
            if (isset($params['crlf']))
            {
                $obj = new Decode_Mimemail($params['input'],$params['mid'],$params['maildir'],$params['crlf']);
            }
            else
            {
                $obj = new Decode_Mimemail($params['input'],$params['mid'],$params['maildir']);
            }
            $structure = $obj->decode($params);

            // Called statically but no input 
        }
        elseif (!isset($this))
        {
            return $this->_error="Called statically and no input given";

            // Called via an object 
        }
        else
        {
            $this->_include_bodies = isset($params['include_bodies'])
                ? $params['include_bodies']
                : false;
            $this->_decode_bodies  = isset($params['decode_bodies'])
                ? $params['decode_bodies']
                : false;
            $this->_decode_headers = isset($params['decode_headers'])
                ? $params['decode_headers']
                : false;
            if (is_null($this->_header) || is_null($this->_body)
                || is_null($this->mid) || is_null($this->maildir))
            {
                $structure = false;
            }
            else
            {
                $structure = $this->_decode($this->_header, $this->_body, $this->mid, $this->maildir);
            }
            if($structure === false)
            {
                $structure = $this->_error;
            }
        }
        return $structure;
    }

    /**
     * Performs the decoding. Decodes the body string passed to it
     * If it finds certain content-types it will call itself in a
     * recursive fashion
     *
     * @param string Header section
     * @param string Body section
     * @param string mid mime filename
     * @return object Results of decoding process
     * @access private
     */

    function _decode($headers, $body, $mid, $maildir, $default_ctype = 'text/plain')
    {
        $return = new stdClass;
        if(!is_null($headers))
        {
            $headers = $this->parseHeaders($headers);
        }
        else{
            $this->_error="the mime headers is null.";
            return $this->_error;
        }

        foreach ($headers as $value)
        {
            if (isset($return->headers[$value['name']]) AND !is_array($return->headers[$value['name']]))
            {
                $return->headers[$value['name']]   = array($return->headers[$value['name']]);
                $return->headers[$value['name']][] = $value['value'];
            }
            elseif (isset($return->headers[$value['name']]))
            {
                $return->headers[$value['name']][] = $value['value'];
            }
            else
            {
                $return->headers[$value['name']] = $value['value'];
            }
        }
        reset($headers);
        //rewinds array's internal pointer to the first element and returns the value of the first array element.  
        while (list($key, $value) = each($headers))
        {
            $headers[$key]['name'] = strtolower($headers[$key]['name']);
            switch ($headers[$key]['name'])
            {
                case 'content-type':
                    $content_type = $this->parseHeaderValue($headers[$key]['value']);
                    if (preg_match('/([0-9a-z+.-]+)/([0-9a-z+.-]+)/i', $content_type['value'], $regs))
                    {
                        $return->ctype_primary   = $regs[1];
                        $return->ctype_secondary = $regs[2];
                    }
                    if (isset($content_type['other']))
                    {
                        while (list($p_name, $p_value) = each($content_type['other']))
                        {
                            $return->ctype_parameters[$p_name] = $p_value;
                        }
                    }
                    break;

                case 'content-disposition':
                    $content_disposition = $this->parseHeaderValue($headers[$key]['value']);
                    $return->disposition   = $content_disposition['value'];
                    if (isset($content_disposition['other']))
                    {
                        while (list($p_name, $p_value) = each($content_disposition['other']))
                        {
                            $return->d_parameters[$p_name] = $p_value;
                        }
                    }
                    break;
                case 'content-transfer-encoding':
                    if(!is_null($this->parseHeaderValue($headers[$key]['value'])))
                    {
                        $content_transfer_encoding = $this->parseHeaderValue($headers[$key]['value']);
                    }
                    else{
                        $content_transfer_encoding = "";
                    }
                    break;
            }
        }
        if (isset($content_type))
        {
            $content_type['value'] = strtolower($content_type['value']);
            switch ($content_type['value'])
            {
                case 'text':
                case 'text/plain':
                    if($this->_include_bodies)
                    {
                        if($this->_decode_bodies)
                        {
                            $return->body = isset($content_transfer_encoding['value'])
                                ?$this->decodeBody($body,$content_transfer_encoding['value'])
                                : $body;
                        }
                        else{
                            $return->body = $body;
                        }

                        if(!isset($content_type['other']['charset']))
                        {
                            $content_type['other']['charset']="gb2312";
                        }
                        if($content_type['other']['charset'] != "")
                        {
                            $orim_str = "----- Original Message -----";
                            $orim_startpos = strpos($return->body,$orim_str);
                            if(is_int($orim_startpos))
                            {
                                $return->body = $return->body;
                            }
                            else{
                                $return->body    = str_replace("<","<",$return->body);
                                $return->body    = str_replace(">",">",$return->body);
                                $return->body    = str_replace("\n"," <br> ",$return->body);
                                $return->body    = str_replace(" ","   ",$return->body);
                            }
                        }
                    }
                    $return->body = $this->ConverUrltoLink($return->body);
                    $return->body    = str_replace(" <br> ","<br>",$return->body);
                    $return->body    = str_replace("   "," ",$return->body);
                    if(strtolower($return->ctype_parameters['charset'])=="utf-8")
                    {
                        $return->body=iconv("utf-8", "gb2312", $return->body);
                    }
                    break;

                case 'text/html':
                    if($this->_include_bodies)
                    {
                        if($this->_decode_bodies)
                        {
                            $return->body = isset($content_transfer_encoding['value'])
                                ? $this->decodeBody($body,$content_transfer_encoding['value'])
                                : $body;
                        }
                        else{
                            $return->body = $body;
                        }
                    }
                    $return->body = $this->ConverUrltoLink($return->body);
                    if(strtolower($return->ctype_parameters['charset'])=="utf-8")
                    {
                        $return->body=iconv("utf-8", "gb2312", $return->body);
                    }
                    break;
                case 'multipart/mixed':
                case 'multipart/alternative':
                case 'multipart/digest':
                case 'multipart/parallel':
                case 'multipart/report': // RFC1892 
                case 'multipart/signed': // PGP 
                case 'multipart/related':
                case 'application/x-pkcs7-mime':
                    if(!isset($content_type['other']['boundary']))
                    {
                        $this->_error = 'No boundary found for '.$content_type['value'].' part';
                        return false;
                    }
                    $default_ctype = (strtolower($content_type['value']) === 'multipart/digest')
                        ? 'message/rfc822'
                        : 'text/plain';
                    $parts = $this->boundarySplit($body, $content_type['other']['boundary']);

                    if(!isset($return->attlist))
                    {
                        $return->attlist="";
                    }
                    for ($i = 0; $i < count($parts); $i++)
                    {
                        list($part_header, $part_body) = $this->splitBodyHeader($parts[$i]);
                        if (is_null($part_header) || is_null($part_body))
                        {
                            $part = false;
                        }
                        else
                        {
                            $part = $this->_decode($part_header, $part_body, $mid, $maildir, $default_ctype);
                        }
                        if($part === false)
                        {
                            $part =$this->_error;
                        }
                        if(!is_null($part->ctype_primary) AND !is_null($part->ctype_secondary))
                        {
                            $part_content_type=$part->ctype_primary."/".$part->ctype_secondary;
                        }
                        else{
                            $part_content_type="";
                        }

                        if(isset($part->body))
                        {
                            if(isset($part->headers['content-transfer-encoding']) AND !is_null($part->headers['content-transfer-encoding']))
                            {
                                $part->body    = $this->decodeBody($part->body,$part->headers['content-transfer-encoding']);
                            }
                            else{
                                $part->body    = $part->body;
                            }
                        }
                        /**
                         * if part exists with filename/name,save to disk.
                         */
                        if(!isset($part->body))
                        {
                            $part->body = $this->decodeBody($part_body, "base64");
                        }

                        if((($part->ctype_primary."/".$part->ctype_secondary=="message/rfc822") OR ($part->ctype_parameters['name']!="") OR ($part->headers['content-id']!="") OR (isset($part->d_parameters['filename']) AND isset($part->disposition))) AND isset($part->body))
                        {
                            $att_savename= $mid.".att".$i;    //attachment save name. 
                            $user_cache=$this->maildir;
                            if(!empty($user_cache) AND !empty($att_savename))
                            {
                                $att_file=$user_cache."/".$att_savename;
                            }
                            else
                            {
                                $att_file="";
                                $return->parts[] = $part;
                                break;
                            }
                            $att_filename    = $part->ctype_parameters['name'];
                            if($att_filename=="")
                            {
                                $att_filename = $part->d_parameters['filename']==""
                                    ? $att_filename = "autofile".$i
                                    : $part->d_parameters['filename'];
                                //if the attachment is the type of rfc/822,and filename is null 
                                //rename to autofile with ".eml" 
                                if(($part->ctype_primary."/".$part->ctype_secondary=="message/rfc822") and $att_filename=="autofile".$i)
                                {
                                    $att_filename = $att_filename.".eml";
                                }
                            }
                            $this->createAttfiles($att_file,$part->body);
                            $attfile_size=filesize($att_file);
                            $return->attlist.=$att_filename."|".$attfile_size."|".$att_savename."|".$part_content_type."\n";
                            $logName=$user_cache."/.attlog";
                            $LogContent = $att_savename."\n";
                            $this->CreateLog($logName,$LogContent);
                            $part->body = ""; //released the used memory 
                        }
                        else
                        {
                            if(isset($part->body))
                            {
                                $return->body=$part->body;
                            }
                        }
                        $return->parts[] = $part;
                    }
                    break;
                case 'image/gif':
                case 'image/jpeg':
                    break;
                default:
                    if($this->_include_bodies)
                    {
                        if($this->_decode_bodies)
                        {
                            $return->body = isset($content_transfer_encoding['value'])
                                ?$this->decodeBody($body,$content_transfer_encoding['value'])
                                :$body;
                        }
                        else{
                            $return->body = $body;
                        }
                    }
                    break;
            } // end switch 

        }
        else {
            //process if content-type isn't exist. 
            $ctype = explode('/', $default_ctype);
            $return->ctype_primary   = $ctype[0];
            $return->ctype_secondary = $ctype[1];
            $this->_include_bodies
                ? $return->body = ($this->_decode_bodies
                ? $this->decodeBody($body)
                : $body)
                : null;
            if($this->_include_bodies)
            {
                $orim_str = "----- Original Message -----";
                $orim_startpos = strpos($return->body,$orim_str);
                if(is_int($orim_startpos))
                {
                    $return->body = $return->body;
                }
                else{
                    $return->body    = str_replace("\n"," <br> ",$return->body);
                    $return->body    = str_replace(" ","   ",$return->body);
                }
            }
            $return->body    = $this->ConverUrltoLink($return->body);
            $return->body    = str_replace(" <br> ","<br>",$return->body);
            $return->body    = str_replace("   "," ",$return->body);
            if(strtolower($return->ctype_parameters['charset'])=="utf-8")
            {
                $return->body=iconv("utf-8", "gb2312", $return->body);
            }
        } //end else 
        if(0<strlen($return->attlist))
        {
            $return->attlist  = substr($return->attlist,0,(strlen($return->attlist)-1));
        }
        return $return;
    }

    /**
     * Given a string containing a header and body
     * section, this function will split them (at the first blank line) and return them.
     *
     * @param string Input to split apart
     * @return array Contains header and body section
     * @access private
     */

    function splitBodyHeader($input)
    {
        $pos = strpos($input, $this->_crlf.$this->_crlf);
        if ($pos === false)
        {
            $this->_error = 'Could not split header and body';
            return false;
        }

        $header = substr($input, 0, $pos);
        $body   = substr($input, $pos+(2*strlen($this->_crlf)));
        return array($header, $body);
    }

    /**
     * Parse headers given in $input and return as assoc array.
     *
     * @param string Headers to parse
     * @return array Contains parsed headers
     * @access private
     */

    function parseHeaders($input)
    {
        if ($input !== '')
        {
            // Unfold the input 
            $input   = preg_replace('/' . $this->_crlf . "(\t| )/", ' ', $input);
            $headers = explode($this->_crlf, trim($input));

            foreach ($headers as $value)
            {
                $hdr_name = strtolower(substr($value, 0, $pos = strpos($value, ':')));
                $hdr_value = substr($value, $pos+1);
                $return[] = array(
                    'name'  => $hdr_name,
                    'value' => $this->_decode_headers
                        ? $this->decodeHeader($hdr_value)
                        : $hdr_value
                );
            }
        }
        else
        {
            $return = array();
        }
        return $return;
    }

    /**
     * Function to parse a header value,
     * extract first part, and any secondary
     * parts (after <img src="http://www.pushad.com/Info/images/smilies/wink.gif" border="0" alt=""> This function is not as
     * robust as it could be. Eg. header comments
     * in the wrong place will probably break it.
     *
     * @param string Header value to parse
     * @return array Contains parsed result
     * @access private
     */

    function parseHeaderValue($input)
    {
        if (($pos = strpos($input, ';')) !== false)
        {
            $return['value'] = trim(substr($input, 0, $pos));
            $input = trim(substr($input, $pos+1));
            $T_input = explode(";",$input);
            for($i=0;$i<count($T_input);$i++)
            {
                if (strlen($T_input[$i]) > 0)
                {
                    if(eregi("*([0-9]+)*",$T_input[$i],$regs))
                    {
                        $found=$regs["1"];
                        $T_input[$i] = str_replace("*".$found."*","",$T_input[$i]);
                    }
                    preg_match_all('/(([[:alnum:]]+)={1}"?([^"]*)"?s?;?)+/i', $T_input[$i], $T_matches);
                    for ($j = 0; $j < count($T_matches[2]); $j++)
                    {
                        $return['other'][strtolower($T_matches[2][$j])] = $T_matches[3][$j];
                    }
                }
            }
        }
        else
        {
            $return['value'] = trim($input);
        }
        return $return;
    }

    /**
     * This function splits the input based on the given boundary
     *
     * @param string Input to parse
     * @return array Contains array of resulting mime parts
     * @access private
     */

    function boundarySplit($input, $boundary)
    {
        $parts = array("","");
        $tmp = explode('--'.$boundary, $input);
        for ($i=1; $i<count($tmp)-1; $i++)
        {
            $parts[] = $tmp[$i];
        }
        return $parts;
    }

    /**
     * Given a header, this function will decode it  according to RFC2047.
     * Probably not *exactly*  conformant,
     * but it does pass all the given examples (in RFC2047).
     *
     * @param string Input header value to decode
     * @return string Decoded header value
     * @access private
     */

    function decodeHeader($input)
    {
        if(eregi("?(q|b)?",$input,$regs))
        {
            $found=$regs["1"];
            if($found=="q")
            {
                $input = str_replace("?".$found."?","?Q?",$input);
            }
            if($found=="b")
            {
                $input = str_replace("?".$found."?","?B?",$input);
            }
        }
        // Remove white space between encoded-words 
        $input = preg_replace('/(=?[^?]+?(Q|B)?[^?]*?=)( |' . "\t|" . $this->_crlf . ')+=?/', '1=?', $input);
        // For each encoded-word... 
        if (preg_match_all('/(=?([^?]+)?(Q|B)?([^?]*)?=)/', $input, $matches))
        {
            for ($i=0; $i< count($matches[0]); $i++)
            {
                $matchew  = $matches[0][$i];
                $encoded  = $matches[1][$i];
                $charset  = strtolower($matches[2][$i]);
                $encoding = $matches[3][$i];
                $text     = $matches[4][$i];
                switch ($encoding)
                {
                    case 'B':
                        $text = base64_decode($text);
                        break;

                    case 'Q':
                        $text = quoted_printable_decode($text);
                        break;
                }
                switch($charset)
                {
                    case "big5":
                        $text=iconv("big5", "gb2312",$text);
                        break;
                    case "utf-8":
                        $text=iconv("utf-8", "gb2312",$text);
                        break;
                    case "iso-8859-1":
                        $text=iconv("iso-8859-1", "utf-8",$text);
                        $text=iconv("utf-8", "gb2312",$text);
                        break;
                    default:
                        break;
                }
                $input = str_replace($encoded, $text, $input);
            }
        }
        return $input;
    }

    /**
     * Given a body string and an encoding type,
     * this function will decode and return it.
     *
     * @param  string Input body to decode
     * @param  string Encoding type to use.
     * @return string Decoded body
     * @access private
     */

    function decodeBody($input, $encoding = '7bit')
    {
        $encoding    = strtolower($encoding);
        switch ($encoding)
        {
            case '7bit':
                return $input;
                break;

            case 'quoted-printable':
                return quoted_printable_decode($input);
                break;

            case 'base64':
                return base64_decode($input);
                break;

            default:
                return $input;
        }
    }

    /**
     * parse the url to link
     *
     * @$input string message text
     * @return string $linktext parsed text
     *
     */

    function ConverUrltoLink($messagetext)
    {
        static $urlSearchArray, $urlReplaceArray;
        if (empty($urlSearchArray))
        {
            $urlSearchArray = array(
                "#(^|(?<=[^_a-z0-9-=]\"'/@]|(?<=)]))((https?|ftp|gopher|news|telnet)://|www.)(([(?!/)|[^s[^$!`\"'|{}<>])+)(?![/url|[/img)(?=[,.]*()s|)$|[s[]|$))#siU"
            );

            $urlReplaceArray = array(
                "<a href='\2\4' target='blank'>\2\4</a>"
            );
        }
        $linktext = preg_replace($urlSearchArray, $urlReplaceArray , $messagetext);
        return $linktext;
    }

    /**
     * store the parsed attachment files
     *
     * @$input string filename
     * @return boolean variable true return 1,contrariwise,return 0
     */

    function createAttfiles($filename,$input)
    {
        $content  = $input;
        $filename = $filename;
        $handle   = fopen ($filename,"w");
        if (!is_writable ($filename))
        {
            $this->_error = 'file:'.$filename.'is readonly,please check file mode.';
            return false;
        }
        if (!fwrite ($handle,$content))
        {
            $this->_error = 'create file'.$filename.'error!';
            return false;
        }
        fclose ($handle);
        return true;
    }

    /**
     * append attfile log into the log files
     *
     * @$input string filename
     * @return boolean variable true return 1,contrariwise,return 0
     */

    function CreateLog($LogName,$LogContent)
    {
        $LogName    = $LogName;
        $LogContent = $LogContent;
        if (is_writable($LogName))
        {
            if (!$handle = fopen($LogName, 'a')) {
                $this->_error = "can not open file".$LogName;
                exit;
            }

            if (!fwrite($handle, $LogContent)) {
                $this->_error = "can not write to file".$LogName;
                exit;
            }
            fclose($handle);
        } else {
            $this->_error = "can not write to file".$LogName;
        }
    }

}