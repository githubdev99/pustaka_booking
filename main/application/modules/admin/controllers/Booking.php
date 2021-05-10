<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Booking extends MY_Controller
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
        $title = 'List Booking';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'admin/booking/v_booking',
            'get_script' => 'admin/booking/script_booking'
        ];

        $this->master->template($data);
    }

    public function detail()
    {
        $title = 'Detail Booking';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'admin/booking/v_booking_detail',
            'get_script' => 'admin/booking/script_booking_detail'
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
                    $parsing['booking'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'booking',
                        'where' => [
                            'id' => decrypt_text($this->input->post('booking_id')),
                        ],
                    ])->row();

                    $parsing['booking_detail'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'booking_detail',
                        'where' => [
                            'booking_id' => decrypt_text($this->input->post('booking_id')),
                        ],
                    ])->result();

                    $this->db->trans_start();

                    $this->api_model->send_data([
                        'data' => [
                            'booking_id' => $parsing['booking']->id,
                            'user_id' => $parsing['booking']->user_id,
                            'loaning_time' => $this->input->post('loaning_time'),
                            'return_due_date' => date('Y-m-d H:i:s', strtotime("+{$this->input->post('loaning_time')} day")),
                            'penalty_price' => $this->input->post('penalty_price'),
                            'created_at' => date('Y-m-d H:i:s'),
                        ],
                        'table' => 'loaning'
                    ]);

                    $lastId = $this->db->insert_id();

                    foreach ($parsing['booking_detail'] as $key_booking_detail) {
                        $this->api_model->send_data([
                            'data' => [
                                'loaning_id' => $lastId,
                                'book_id' => $key_booking_detail->book_id,
                            ],
                            'table' => 'loaning_detail'
                        ]);
                    }

                    $this->db->trans_complete();

                    if ($this->db->trans_status() === false) {
                        $db_error = $this->db->error();
                        $isError = true;
                        $output = [
                            'isError' => $isError,
                            'type' => 'error',
                            'message' => "Peminjaman gagal di proses [Database error! Error Code [{$db_error['code']}] Error: {$db_error['message']}]",
                        ];
                    } else {
                        $output = [
                            'isError' => $isError,
                            'type' => 'success',
                            'message' => 'Peminjaman berhasil di proses',
                            'callback' => base_url() . 'admin/booking'
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
                        'loaning.booking_id' => $key->id,
                    ],
                ])->row();
                if (!empty($parsing['loaning'])) {
                    $status = '<span class="badge badge-success">Sudah Pinjam</span>';
                    $opsi = '<a href="' . base_url() . 'admin/return/detail/' . encrypt_text($key->id) . '" class="btn btn-info btn-sm" data-toggle="tooltip" title="Process Data"><i class="fas fa-save"></i></a>';
                } else {
                    $status = '<span class="badge badge-secondary">Belum Pinjam</span>';
                    $opsi = '<a href="' . base_url() . 'admin/booking/detail/' . encrypt_text($key->id) . '" class="btn btn-success btn-sm" data-toggle="tooltip" title="Process Data"><i class="fas fa-save"></i></a>';
                }

                $column[] = $no;
                $column[] = $key->booking_number;
                $column[] = $key->name;
                $column[] = $key->pickup_due_date;
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
