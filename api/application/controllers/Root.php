<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Root extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        echo '
        <center>
            <h3>Pustaka Booking API</h3>
            <p>Welcome</p>
        </center>
        ';
    }
}
