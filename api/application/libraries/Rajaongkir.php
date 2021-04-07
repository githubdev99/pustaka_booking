<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'third_party/midtrans/Midtrans.php';

class Rajaongkir
{
    private $data = [
        'apikey' => 'bc273bc79dd9157e991bfeb3aba12c99',
        'url' => 'https://pro.rajaongkir.com/api/',
    ];

    public function __construct()
    {
    }

    public function cost($param)
    {
        $response = json_decode(shoot_api([
            'url' => $this->data['url'] . 'cost',
            'method' => 'POST',
            'header' => [
                "content-type: application/x-www-form-urlencoded",
                "key: {$this->data['apikey']}"
            ],
            'data' => "origin={$param['origin']}&originType=city&destination={$param['destination']}&destinationType=subdistrict&weight={$param['weight']}&courier={$param['courier']}&service=REG"
        ]), true);

        if (empty($response)) {
            return 0;
        } else {
            return $response['rajaongkir']['results'][0]['costs'][1]['cost'][0]['value'];
        }
    }
}
