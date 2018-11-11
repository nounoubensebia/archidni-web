<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 23/07/2018
 * Time: 08:52
 */

namespace App\Http\Controllers\FirebaseNotifications;


class NotificationsUtils
{
    //private static $FCM_KEY = "AAAAiQaXMyM:APA91bEHkh_Sc354KGQo33hfxRFiJiz_3Ty30jQQGae07TSXUQ4DnM9VdnF1CpntoII6nmj_XlAD3_mdG4P0nkfx6ZqsatuUyri5So4uGGCvcEo-1HBsIcdWS-RjiSCX-NCSn9PVsjZE";
    public static function send_notification ($message,$title,$body,$icon)
    {

        $data = array("data"=>$message);
        $FCM_KEY = "AAAAVdHfRmw:APA91bGRUOyvsPiLfPRn_lTHFp9qY9fNhmZV2vnE2kGZB2HYq8PGgzduLcTz8C3H1TviaBTzTBvM7dZv3vq8RbBN9vERcpJQ22Cxvz0xgJNXOmgw8j3-d02-DPP3pZ7AGHwNYBtFtzmMljNE1YMEdLt6tAuL2aj8NQ";

        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'to' => '/topics/all-devices-v2',
            'data' => $data,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => $icon,
                "sound"=> "default"
    ]
        );

        $headers = array(
            'Authorization:key = '.$FCM_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
    }

    public static function send_line_disturbance_notification($line_id)
    {
        $ligne = Ligne::get_ligne_by_id($line_id);
        $message['message']="des perturbations ont été signalées sur la ligne : ".$ligne['nom'];
        $message ['line_id']=1;
        NotificationUtils::send_notification($message);
    }
}