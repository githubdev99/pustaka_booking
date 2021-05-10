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

    public function datatableUser()
    {
        if (!empty($_REQUEST['draw'])) {
            $draw = $_REQUEST['draw'];
        } else {
            $draw = 0;
        }

        $param['column_search'] = [
            'email', 'name', 'created_at'
        ];
        $param['column_order'] = [
            null, 'email', 'name', 'created_at'
        ];
        $param['field'] = '*';
        $param['table'] = 'user';
        $param['where'] = [
            'role_id' => 2
        ];
        $param['order_by'] = [
            'created_at' => 'desc'
        ];

        $data_parsing = $this->api_model->get_datatable($param);
        $total_filtered = $this->api_model->get_total_filtered($param);
        $total_data = $this->api_model->get_total_data($param);

        $data = [];
        if (!empty($data_parsing)) {
            $no = $_REQUEST['start'];
            foreach ($data_parsing as $key) {
                $no++;
                $column = [];

                $column[] = $no;
                $column[] = $key->email;
                $column[] = $key->name;
                $column[] = $key->created_at;

                $data[] = $column;
            }
        }

        $output = [
            'draw' => intval($draw),
            'recordsTotal' => intval($total_data),
            'recordsFiltered' => intval($total_filtered),
            'data' => $data
        ];

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
}
