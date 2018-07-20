<?php

namespace UniSharp\Sms;

class AptgSmsClient
{
    const XMLPACKETSTR = '<soap-env:Envelope xmlns:soap-env=\'http://schemas.xmlsoap.org/soap/envelope/\'>
<soap-env:Header/>
<soap-env:Body>
<Request>
<MDN>%s</MDN>
<UID>%s</UID>
<UPASS>%s</UPASS>
<Subject>亞太電信簡訊發送平台</Subject>
<AutoSplit>Y</AutoSplit>
<!--<Retry>Y</Retry>
<StopDateTime>201006021230</StopDateTime>--> <Message>%s</Message> <MDNList>
%s
</MDNList> </Request>
</soap-env:Body> </soap-env:Envelope>';
    const MSISDN_TAG_PRE = '<MSISDN>';
    const MSISDN_TAG_POST = '</MSISDN>';
    const DOMAIN = 'xsms.aptg.com.tw';

    private $MDN;
    private $UID;
    private $UPASS;
    private $content;
    private $receivers;

    public function __construct($MDN, $UID, $UPASS)
    {
        $this->MDN = $MDN;
        $this->UID = $UID;
        $this->UPASS = $UPASS;
    }

    private function toString()
    {
        echo "Attributes:{\n";
        echo "MDN:" . $this->MDN . ", UID:" . $this->UID . ", UPASS:" .
        $this->UPASS . ", content:" . $this->content . "\n";
        foreach ($this->receivers as $rec) {
            echo "Rec: $rec\n";
        }
        echo "}";
    }

    private function formatXmlPacket()
    {
        $xmlrec = "";
        foreach ($this->receivers as $rec) {
            $xmlrec .= self::MSISDN_TAG_PRE . $rec . self::MSISDN_TAG_POST;
        }
        return sprintf(self::XMLPACKETSTR, $this->MDN, $this->UID, $this->UPASS, $this->content, $xmlrec);
    }

    private function sendReq()
    {
        $url = 'ssl://' . self::DOMAIN . ':443';

        $contextOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
            ),
        );
        $context = stream_context_create($contextOptions);
        $fp = stream_socket_client($url, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

        if (!$fp) {
            throw new Exception("Could not open connection");
            // echo 'Could not open connection.';
        } else {
            $out = $this->formatHttpReq();
            // echo "\nRequest------\n" . $out . "\nRequest End-----\n";
            fwrite($fp, $out);
            $theOutput = "";
            while (!feof($fp)) {
                $theOutput .= fgets($fp, 128);
            }
            fclose($fp);
            return $theOutput;
        }
    }

    private function formatHttpReq()
    {
        $xmlpacket = $this->formatXmlPacket();
        $contentlength = strlen($xmlpacket);
        $out = "POST /XSMSAP/api/APIRTFastRequest HTTP/1.1\r\n";
        $out .= "Host: 210.200.64.111\r\n";
        $out .= "Connection: close\r\n";
        $out .= "Content-type: text/xml;charset=utf-8\r\n";
        $out .= "Content-length: $contentlength\r\n\r\n";
        $out .= "$xmlpacket";
        return $out;
    }

    public function send($array_of_receivers, $sms_content)
    {
        $this->receivers = $array_of_receivers;
        $this->content = $sms_content;
        // $this->toString();
        $out = $this->sendReq();
        $respObj = $this->getRespObj($out);
        return $this->checkRespStatus($respObj);
    }

    private function getRespObj($resp)
    {
        // echo "Response-------\n" . $resp . "Response End--------\n";
        $xmlStartIdx = strrpos($resp, "<env:Envelope");
        $xmlEndIdx = strripos($resp, "Envelope>");
        $respXmlBody = str_ireplace(['env:'], '', substr($resp, $xmlStartIdx, $xmlEndIdx - $xmlStartIdx + 9));
        $xmlObj = simplexml_load_string($respXmlBody);
        return $xmlObj;
    }

    private function checkRespStatus($respObj)
    {
        // print_r($respObj);
        // print_r($respObj->Body->Response);
        // // echo "\nResponse Code:" . $respObj->Body->Response->Code . "\n";
        if ((string) $respObj->Body->Response->Code === "0") {
            return true;
        } else {
            return false;
        }
    }
}
