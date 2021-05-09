<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->auth([
            'session' => 'admin',
            'login' => false
        ]);
    }

    public function index()
    {
        $title = 'Dashboard';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'admin/dashboard/v_dashboard',
            'get_script' => 'admin/dashboard/script_dashboard'
        ];

        $this->master->template($data);
    }
}
