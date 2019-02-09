<?php
/**
 * Created by PhpStorm.
 * User: nouno
 * Date: 09/02/2019
 * Time: 20:17
 */

namespace App\Http\Controllers\Emails;


class MailSender
{
    public function sendVerificationCode ($user)
    {
        $random = rand(6);
        $to_email = $user->email;
        $subject = 'Code de vérification';
        $message = 'Veuillez utiliser le code suivant : '.$random;
        $headers = 'test@archidni.smartsolutions.network';
        mail($to_email,$subject,$message,$headers);
        return $random;
    }
}