<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendEmail($to, $subj, $template, $data, $from = null)
    {
        $data = is_array($data) ? $data : [];

        Mail::send("emails.$template", $data, function ($message) use ($to, $subj, $from) {
            $message->to($to);
            $message->subject($subj);
            
            if ($from) {
                $message->from($from['address'], $from['name']);
            }
        });
    }
}
