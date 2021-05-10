<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Returning extends MY_Controller
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
        $title = 'List Peminjaman';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'admin/returning/v_returning',
            'get_script' => 'admin/returning/script_returning'
        ];

        $this->master->template($data);
    }

    public function detail($id)
    {
        $title = 'Detail Peminjaman';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'admin/returning/v_returning_detail',
            'get_script' => 'admin/returning/script_returning_detail',
            'data' => $this->api_model->select_data([
                'field' => '*',
                'table' => 'loaning',
                'where' => [
                    'id' => decrypt_text($id),
                ],
            ])->row_array()
        ];

        $this->master->template($data);
    }

    public function detail_loan($id)
    {
        $title = 'Detail Peminjaman';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'admin/returning/v_returning_detail_loan',
            'get_script' => 'admin/returning/script_returning_detail_loan',
            'data' => $this->api_model->select_data([
                'field' => '*',
                'table' => 'loaning',
                'where' => [
                    'id' => decrypt_text($id),
                ],
            ])->row_array()
        ];

        $this->master->template($data);
    }

    public function action()
    {
        $isError = false;

        if (!$this->input->post()) {
            $output = [
                'isError' => $isError,
                'type' => 'error',
                'message' => 'Permintaan tidak valid',
            ];
        } else {
            if ($this->input->post('submit') == 'process') {
                if (!$isError) {
                    $query = $this->api_model->send_data([
                        'where' => [
                            'id' => decrypt_text($this->input->post('id')),
                        ],
                        'data' => [
                            'return_date' => date('Y-m-d'),
                            'is_return_done' => 1,
                            'penalty_day' => $this->input->post('penalty_day'),
                        ],
                        'table' => 'loaning'
                    ]);

                    if ($query['error']) {
                        $isError = true;
                        $output = [
                            'isError' => $isError,
                            'type' => 'error',
                            'message' => "Pengembalian gagal di proses [{$query['system']}]",
                        ];
                    } else {
                        $output = [
                            'isError' => $isError,
                            'type' => 'success',
                            'message' => 'Pengembalian berhasil di proses',
                            'callback' => base_url() . 'admin/returning'
                        ];
                    }
                }
            }
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }

    public function datatableBooking()
    {
        if (!empty($_REQUEST['draw'])) {
            $draw = $_REQUEST['draw'];
        } else {
            $draw = 0;
        }

        $param['column_search'] = [
            'loaning.loaning_number', 'user.name', 'loaning.loaning_time', 'loaning.return_due_date', 'loaning.created_at'
        ];
        $param['column_order'] = [
            null, 'loaning.loaning_number', 'user.name', 'loaning.loaning_time', 'loaning.return_due_date', 'loaning.created_at', null, null
        ];
        $param['field'] = 'loaning.*, user.name';
        $param['table'] = 'loaning';
        $param['join'] = [
            [
                'table' => 'user',
                'on' => 'user.id = loaning.user_id',
                'type' => 'inner'
            ],
        ];
        $param['order_by'] = [
            'loaning.id' => 'desc'
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

                if (boolval($key->is_return_done)) {
                    $status = '<span class="badge badge-success">Sudah Kembali</span>';
                    $opsi = '
                    <a href="' . base_url() . 'admin/returning/detail_loan/' . encrypt_text($key->id) . '" class="btn btn-info btn-sm" data-toggle="tooltip" title="Detail Data"><i class="fas fa-info"></i></a>';
                } else {
                    $status = '<span class="badge badge-secondary">Belum Kembali</span>';
                    $opsi = '
                    <a href="' . base_url() . 'admin/returning/detail/' . encrypt_text($key->id) . '" class="btn btn-success btn-sm" data-toggle="tooltip" title="Lakukan Pengembalian"><i class="fas fa-save"></i></a>';
                }

                $column[] = $no;
                $column[] = $key->loaning_number;
                $column[] = $key->name;
                $column[] = $key->loaning_time . ' Hari';
                $column[] = $key->return_due_date;
                $column[] = $key->created_at;
                $column[] = $status;
                $column[] = $opsi;

                $data[] = $column;
            }
        }

        $output = [
            'draw' => intval($draw),
            'recordsTotal' => intval($total_data),
            'recordsFiltered' => intval($total_filtered),
            'data' => $data,
        ];

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }

    public function datatableBookingDetail($id)
    {
        if (!empty($_REQUEST['draw'])) {
            $draw = $_REQUEST['draw'];
        } else {
            $draw = 0;
        }

        $param['column_search'] = [
            'book.image', 'book.name', 'category_name', 'book.isbn', 'book.author', 'book.publisher', 'book.publication_year'
        ];
        $param['column_order'] = [
            null, 'book.image', 'book.name', 'category_name', 'book.isbn', 'book.author', 'book.publisher', 'book.publication_year'
        ];
        $param['field'] = 'book.*, category.name as category_name';
        $param['table'] = 'loaning_detail';
        $param['join'] = [
            [
                'table' => 'book',
                'on' => 'book.id = loaning_detail.book_id',
                'type' => 'inner'
            ],
            [
                'table' => 'category',
                'on' => 'category.id = book.category_id',
                'type' => 'inner'
            ],
        ];
        $param['where'] = [
            'loaning_detail.loaning_id' => decrypt_text($id)
        ];
        $param['order_by'] = [
            'book.name' => 'asc'
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

                $image = (!empty($key->image)) ? "{$this->core['imageUpload']}books/{$key->image}" : $this->core['imageNotFound'];

                $column[] = $no;
                $column[] = '<img class="img-thumbnail" alt="images" width="100" src="' . $image . '">';
                $column[] = $key->name;
                $column[] = $key->category_name;
                $column[] = $key->isbn;
                $column[] = $key->author;
                $column[] = $key->publisher;
                $column[] = $key->publication_year;

                $data[] = $column;
            }
        }

        $output = [
            'draw' => intval($draw),
            'recordsTotal' => intval($total_data),
            'recordsFiltered' => intval($total_filtered),
            'data' => $data,
        ];

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
}
