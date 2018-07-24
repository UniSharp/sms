<?php

namespace UniSharp\Sms;

use UniSharp\Sms\AptgSmsClient;

class Sms
{
    public function send($phone_number, $content)
    {
        $result = false;

        if (empty($phone_number)) {
            \Log::info("[APTG SMS] Failed to send SMS. Phone number is empty.");
            return $result;
        }

        $client = new AptgSmsClient(env('APTG_MDN'), env('APTG_UID'), env('APTG_UPASS'));

        if (env('SMS_IS_DRY_RUN') === false) {
            try {
                $response = $client->send([$phone_number], $content);

                if (!$response->isSuccessful()) {
                    if (is_object($response)) {
                        \Log::error('[APTG SMS] Failed to send SMS. Error code: ' . $response->code() . '. Reason: ' . $response->reason());
                    }
                }

                $result = true;
            } catch  (\Exception $e) {
                \Log::error('[APTG SMS] Failed to send SMS. Exception: ' . $e);
            }
        } else {
            \Log::info("[APTG SMS] SMS test succeeded. Phone number: {$phone_number}. Content: {$content}");
            $result = true;
        }

        return $result;
    }
}
