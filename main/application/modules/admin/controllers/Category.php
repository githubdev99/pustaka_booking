<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Category extends MY_Controller
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
        $title = 'Kategori Buku';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'admin/category/v_category',
            'get_script' => 'admin/category/script_category'
        ];

        if (!$this->input->post()) {
            $this->master->template($data);
        } else {
            $isError = false;

            if ($this->input->post('submit') == 'add') {
                $check['category'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'category',
                    'where' => [
                        'name' => $this->input->post('name'),
                    ],
                ])->row();
                if (!empty($check['category'])) {
                    $isError = true;
                    $output = [
                        'isError' => $isError,
                        'type' => 'warning',
                        'message' => 'Nama kategori sudah diinput',
                    ];
                }

                if (!$isError) {
                    $query = $this->api_model->send_data([
                        'data' => [
                            'name' => $this->input->post('name'),
                        ],
                        'table' => 'category'
                    ]);

                    if ($query['error']) {
                        $isError = true;
                        $output = [
                            'isError' => $isError,
                            'type' => 'error',
                            'message' => "Data gagal di input [{$query['system']}]",
                        ];
                    } else {
                        $output = [
                            'isError' => $isError,
                            'type' => 'success',
                            'message' => 'Data berhasil di input',
                        ];
                    }
                }
            } elseif ($this->input->post('submit') == 'edit') {
                $check['category'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'category',
                    'where' => [
                        'name' => $this->input->post('name'),
                        'id !=' => decrypt_text($this->input->post('id'))
                    ],
                ])->row();
                if (!empty($check['category'])) {
                    $isError = true;
                    $output = [
                        'isError' => $isError,
                        'type' => 'warning',
                        'message' => 'Nama kategori sudah diinput',
                    ];
                }

                if (!$isError) {
                    $query = $this->api_model->send_data([
                        'where' => [
                            'id' => decrypt_text($this->input->post('id')),
                        ],
                        'data' => [
                            'name' => $this->input->post('name'),
                        ],
                        'table' => 'category'
                    ]);

                    if ($query['error']) {
                        $isError = true;
                        $output = [
                            'isError' => $isError,
                            'type' => 'error',
                            'message' => "Data gagal di edit [{$query['system']}]",
                        ];
                    } else {
                        $output = [
                            'isError' => $isError,
                            'type' => 'success',
                            'message' => 'Data berhasil di edit',
                        ];
                    }
                }
            } elseif ($this->input->post('submit') == 'delete') {
                $check['category'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'category',
                    'where' => [
                        'id' => decrypt_text($this->input->post('id')),
                    ],
                ])->row();
                if (empty($check['category'])) {
                    $isError = true;
                    $output = [
                        'isError' => $isError,
                        'type' => 'warning',
                        'message' => 'Data tidak ditemukan',
                    ];
                }

                if (!$isError) {
                    $query = $this->api_model->delete_data([
                        'where' => [
                            'id' => decrypt_text($this->input->post('id')),
                        ],
                        'table' => 'category'
                    ]);

                    if ($query['error']) {
                        $isError = true;
                        $output = [
                            'isError' => $isError,
                            'type' => 'error',
                            'message' => "Data gagal di hapus [{$query['system']}]",
                        ];
                    } else {
                        $output = [
                            'isError' => $isError,
                            'type' => 'success',
                            'message' => 'Data berhasil di hapus',
                        ];
                    }
                }
            }

            $this->output->set_content_type('application/json')->set_output(json_encode($output));
        }
    }

    public function get_data($id)
    {
        $isError = false;

        $check['category'] = $this->api_model->select_data([
            'field' => '*',
            'table' => 'category',
            'where' => [
                'id' => decrypt_text($id),
            ],
        ])->row();
        if (empty($check['category'])) {
            $isError = true;
            $output = [
                'isError' => $isError,
                'type' => 'warning',
                'message' => 'Data tidak ditemukan',
            ];
        }

        if (!$isError) {
            $output = [
                'isError' => $isError,
                'data' => [
                    'name' => $check['category']->name
                ]
            ];
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }

    public function datatable()
    {
        if (!empty($_REQUEST['draw'])) {
            $draw = $_REQUEST['draw'];
        } else {
            $draw = 0;
        }

        $param['column_search'] = [
            'name'
        ];
        $param['column_order'] = [
            null, 'name', null
        ];
        $param['field'] = '*';
        $param['table'] = 'category';

        $param['order_by'] = [
            'name' => 'asc'
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
                $column[] = $key->name;
                $column[] = '
                <button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" title="Edit Data" onclick="show_modal({ modal: ' . "'edit'" . ', id: ' . "'" . encrypt_text($key->id) . "'" . ' })"><i class="fas fa-edit"></i></button>
                <button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Hapus Data" onclick="show_modal({ modal: ' . "'delete'" . ', id: ' . "'" . encrypt_text($key->id) . "'" . ' })"><i class="fas fa-trash-alt"></i></button>
				';

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
