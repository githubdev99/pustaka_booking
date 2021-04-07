<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'third_party/midtrans/Midtrans.php';

class Midtrans
{
    private $data = [
        'production' => false
    ];

    public function __construct()
    {
        // all key by midtrans jaja.id
        if ($this->data['production'] == false) {
            $this->data['client_key'] = 'SB-Mid-client-5WsXpT-RV8mTehtr';
            $this->data['server_key'] = 'SB-Mid-server-h5mboMANRKZ_j0DfcgGwkNxI';
        } else {
            $this->data['client_key'] = '';
            $this->data['server_key'] = '';
        }
    }
}
