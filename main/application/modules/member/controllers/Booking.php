<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Booking extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->auth([
            'session' => 'member',
            'login' => false
        ]);
    }

    public function getTotal()
    {
        $output = $this->api_model->count_all_data([
            'where' => [
                'user_id' => $this->core['user']['id']
            ],
            'table' => 'temp',
        ]);

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
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
            if ($this->input->post('submit') == 'add_booking') {
                $check['temp'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'temp',
                    'where' => [
                        'user_id' => $this->core['user']['id'],
                        'book_id' => decrypt_text($this->input->post('id')),
                    ],
                ])->row();
                if (!empty($check['temp'])) {
                    $isError = true;
                    $output = [
                        'isError' => $isError,
                        'type' => 'warning',
                        'message' => 'Buku telah ada di data booking kamu',
                    ];
                }

                if (!$isError) {
                    $query = $this->api_model->send_data([
                        'data' => [
                            'user_id' => $this->core['user']['id'],
                            'book_id' => decrypt_text($this->input->post('id')),
                            'created_at' => date('Y-m-d H:i:s'),
                        ],
                        'table' => 'temp'
                    ]);

                    if ($query['error']) {
                        $isError = true;
                        $output = [
                            'isError' => $isError,
                            'type' => 'error',
                            'message' => "Buku gagal di booking [{$query['system']}]",
                        ];
                    } else {
                        $output = [
                            'isError' => $isError,
                            'type' => 'success',
                            'message' => 'Buku berhasil di booking',
                            'callback' => base_url() . 'member/booking/temp'
                        ];
                    }
                }
            } elseif ($this->input->post('submit') == 'delete_booking') {
                $check['temp'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'temp',
                    'where' => [
                        'id' => decrypt_text($this->input->post('id')),
                        'user_id' => $this->core['user']['id'],
                    ],
                ])->row();
                if (empty($check['temp'])) {
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
                            'user_id' => $this->core['user']['id'],
                        ],
                        'table' => 'temp'
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
            } elseif ($this->input->post('submit') == 'end_booking') {
                if (!$isError) {
                    $parsing['temp'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'temp',
                        'where' => [
                            'user_id' => $this->core['user']['id'],
                        ],
                    ])->result();

                    $this->db->trans_start();

                    $this->api_model->send_data([
                        'data' => [
                            'user_id' => $this->core['user']['id'],
                            'pickup_due_date' => date('Y-m-d H:i:s', strtotime("+2 day")),
                            'created_at' => date('Y-m-d H:i:s'),
                        ],
                        'table' => 'booking'
                    ]);

                    $lastId = $this->db->insert_id();

                    foreach ($parsing['temp'] as $key_temp) {
                        $this->api_model->send_data([
                            'data' => [
                                'booking_id' => $lastId,
                                'book_id' => $key_temp->book_id,
                            ],
                            'table' => 'booking_detail'
                        ]);
                    }

                    $this->db->trans_complete();

                    if ($this->db->trans_status() === false) {
                        $db_error = $this->db->error();
                        $isError = true;
                        $output = [
                            'isError' => $isError,
                            'type' => 'error',
                            'message' => "Buku gagal di booking [Database error! Error Code [{$db_error['code']}] Error: {$db_error['message']}]",
                        ];
                    } else {
                        $this->api_model->send_data([
                            'where' => [
                                'id' => $lastId,
                                'user_id' => $this->core['user']['id'],
                            ],
                            'data' => [
                                'booking_number' => 'ID' . date('ymd') . $lastId,
                            ],
                            'table' => 'booking'
                        ]);

                        $this->api_model->delete_data([
                            'where' => [
                                'user_id' => $this->core['user']['id'],
                            ],
                            'table' => 'temp'
                        ]);

                        $output = [
                            'isError' => $isError,
                            'type' => 'success',
                            'message' => 'Buku berhasil di booking',
                            'callback' => base_url() . 'member/booking'
                        ];
                    }
                }
            }
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }

    public function index()
    {
        $title = 'List Booking';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'member/booking/v_booking',
            'get_script' => 'member/booking/script_booking'
        ];

        $this->master->template($data);
    }

    public function detail()
    {
        $title = 'Detail Booking';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'member/booking/v_booking_detail',
            'get_script' => 'member/booking/script_booking_detail'
        ];

        $this->master->template($data);
    }

    public function temp()
    {
        $title = 'Booking';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'member/booking/v_temp',
            'get_script' => 'member/booking/script_temp'
        ];

        $this->master->template($data);
    }

    public function datatable()
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
            null, 'book.image', 'book.name', 'category_name', 'book.isbn', 'book.author', 'book.publisher', 'book.publication_year', null
        ];
        $param['field'] = 'book.*, category.name as category_name, temp.id as temp_id';
        $param['table'] = 'temp';
        $param['join'] = [
            [
                'table' => 'book',
                'on' => 'book.id = temp.book_id',
                'type' => 'inner'
            ],
            [
                'table' => 'category',
                'on' => 'category.id = book.category_id',
                'type' => 'inner'
            ],
        ];
        $param['where'] = [
            'temp.user_id' => $this->core['user']['id']
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
                $column[] = '
                <button type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Hapus Data" onclick="show_modal({ modal: ' . "'delete_booking'" . ', id: ' . "'" . encrypt_text($key->temp_id) . "'" . ' })"><i class="fas fa-trash-alt"></i></button>
    			';

                $data[] = $column;
            }
        }

        $output = [
            'draw' => intval($draw),
            'recordsTotal' => intval($total_data),
            'recordsFiltered' => intval($total_filtered),
            'data' => $data,
            'total' => count($data)
        ];

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
            'booking.booking_number', 'user.name', 'booking.pickup_due_date', 'booking.created_at'
        ];
        $param['column_order'] = [
            null, 'booking.booking_number', 'user.name', 'booking.pickup_due_date', 'booking.created_at', null, null
        ];
        $param['field'] = 'booking.*, user.name';
        $param['table'] = 'booking';
        $param['join'] = [
            [
                'table' => 'user',
                'on' => 'user.id = booking.user_id',
                'type' => 'inner'
            ],
        ];
        $param['where'] = [
            'booking.user_id' => $this->core['user']['id']
        ];
        $param['order_by'] = [
            'booking.id' => 'desc'
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

                $parsing['loaning'] = $this->api_model->select_data([
                    'field' => 'loaning.*',
                    'table' => 'loaning',
                    'join' => [
                        [
                            'table' => 'booking',
                            'on' => 'booking.id = loaning.booking_id',
                            'type' => 'inner'
                        ],
                    ],
                    'where' => [
                        'loaning.user_id' => $this->core['user']['id'],
                        'loaning.booking_id' => $key->id,
                    ],
                ])->row();
                if (!empty($parsing['loaning'])) {
                    $status = '<span class="badge badge-success">Sudah Pinjam</span>';
                } else {
                    $status = '<span class="badge badge-secondary">Belum Pinjam</span>';
                }

                $column[] = $no;
                $column[] = $key->booking_number;
                $column[] = $key->name;
                $column[] = $key->pickup_due_date;
                $column[] = $key->created_at;
                $column[] = $status;
                $column[] = '
                <a href="' . base_url() . 'member/booking/detail/' . encrypt_text($key->id) . '" class="btn btn-info btn-sm" data-toggle="tooltip" title="Detail Data"><i class="fas fa-info"></i></a>
                ';

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
        $param['table'] = 'booking_detail';
        $param['join'] = [
            [
                'table' => 'book',
                'on' => 'book.id = booking_detail.book_id',
                'type' => 'inner'
            ],
            [
                'table' => 'category',
                'on' => 'category.id = book.category_id',
                'type' => 'inner'
            ],
        ];
        $param['where'] = [
            'booking_detail.booking_id' => decrypt_text($id)
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
