<?php

namespace UniSharp\Sms;

class AptgResponse
{
    private $xml;

    public function __construct($response)
    {
        $xmlStartIdx = strrpos($response, "<env:Envelope");
        $xmlEndIdx = strripos($response, "Envelope>") - $xmlStartIdx + strlen("Envelope>");
        $respXmlBody = str_ireplace(['env:'], '', substr($response, $xmlStartIdx, $xmlEndIdx));
        $this->xml = new \SimpleXMLElement($respXmlBody);
    }

    public function isSuccessful()
    {
        return $this->code() === "0";
    }

    public function __toString()
    {
        return json_encode($this->xml);
    }

    public function code()
    {
        return (string) $this->xml->Body->Response->Code;
    }

    public function reason()
    {
        return (string) $this->xml->Body->Response->Reason;
    }
}
