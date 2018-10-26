<?php

namespace UniSharp\Sms;

use UniSharp\Sms\AptgSmsClient;

class Sms
{
    protected $response;

    private $client;

    public function __construct()
    {
        $this->client = new AptgSmsClient(env('APTG_MDN'), env('APTG_UID'), env('APTG_UPASS'));
    }

    public function send($phone_number, $message)
    {
        $result = false;

        if (empty($phone_number)) {
            \Log::info("[APTG SMS] Failed to send SMS. Phone number is empty.");
            return $result;
        }

        if (env('SMS_IS_DRY_RUN') === false) {
            try {
                $response = $this->client->send([$phone_number], $message);

                $this->response = $response;

                $result = $response->isSuccessful();

                if (!$result) {
                    if (is_object($response)) {
                        \Log::error('[APTG SMS] Failed to send SMS. Error code: ' . $response->code() . '. Reason: ' . $response->reason());
                    }
                }
            } catch (\Exception $e) {
                \Log::error('[APTG SMS] Failed to send SMS. Exception: ' . $e);
            }
        } else {
            \Log::info("[APTG SMS] SMS test succeeded. Phone number: {$phone_number}. Message: {$message}");
            $this->response = new AptgResponse('<env:Envelope></Envelope>');
            $result = true;
        }

        return $result;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function splitMessage(bool $autoSplit = true): self
    {
        $this->client->setAutoSplit($autoSplit);

        return $this;
    }
}
