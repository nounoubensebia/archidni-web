<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    //
    public function testEmail ()
    {
        $data = array('name'=>"test");
        Mail::send('mail',$data,function ($message)
        {
           $message->to('nounoubensebia96@gmail.com')
               ->subject('test')
               ->from('test@archidni.smartsolutions.network');
        });
        echo "Mail sent";
    }
}
