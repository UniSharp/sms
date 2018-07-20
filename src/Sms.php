<?php

namespace UniSharp\Sms;

use UniSharp\Sms\AptgSmsClient;

class Sms
{
    public function send($phone_number, $content)
    {
        if (empty($phone_number)) {
            \Log::info("[APTG SMS] Failed to send SMS. Phone number is empty.");
        }

        $client = new AptgSmsClient(env('APTG_MDN'), env('APTG_UID'), env('APTG_UPASS'));

        if (env('SMS_IS_DRY_RUN') === false) {
            try {
                $result = $client->send([$phone_number], $content);

                if ($result !== true) {
                    if (is_object($result)) {
                        \Log::error('[APTG SMS] Failed to send SMS. Error code: ' . $result->Code . '. Reason:' . $result->Reason);
                    } else if (empty($result)) {
                        \Log::error('[APTG SMS] Failed to send SMS. Client response is empty.');
                    }
                    $result = false;
                }
            } catch  (\Exception $e) {
                \Log::error('[APTG SMS] Failed to send SMS. Exception: ' . $e);
                $result = false;
            }
        } else {
            \Log::info("[APTG SMS] SMS test succeeded. Phone number: {$phone_number}. Content: {$content}");
            $result = true;
        }

        return $result;
    }
}
