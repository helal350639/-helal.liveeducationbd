<?php
return [
    // Set true only AFTER existing SourceCtrl::sms_send() has been tested with the real provider.
    'sms_enabled' => env('LEBD_PURCHASE_SMS_ENABLED', false),
];
