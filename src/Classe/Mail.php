<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail 
{

    private $api_key = '1511acf9587df25e17007c3abd97384e';
    private $api_key_secret =  'a40db7e1db635aaaa2ee71355d67cbef';


    public function send($to_email, $to_name, $subject, $content) 
    {

        $mj = new Client($this->api_key, $this->api_key_secret);
        $mj->setTimeout(3);

        // $body = [
        //     'Messages' => [
        //       [
        //         'From' => [
        //           'Email' => "testdevweb67@gmail.com",
        //           'Name' => "Mikael"
        //         ],
        //         'To' => [
        //           [
        //             'Email' => $to_email,
        //             'Name' => $to_name,
        //           ]
        //         ],
        //         'TemplateID' => 2061021,
        //         'TemplateLanguage' => true,
        //         'Subject' => $subject,
        //         'Variables' => [
        //             'content' => $content,
        //         ]
        //       ]
        //     ]
        //   ];


          $body = [
            'FromEmail' => "testdevweb67@gmail.com",
            'FromName' => "Mailjet Pilot",
            'Subject' => $subject,
            'Variables' => [
                    'content' => $content,
            ],
            'MJ-TemplateID' => '2061021',
            'MJ-TemplateLanguage' => true,
            'Recipients' => [['Email' => $to_email,
            'Vars' => ['content' => $content]
            ]]
        ];


          $response = $mj->post(Resources::$Email, ['body' => $body]);
          $response->success();

    }

}