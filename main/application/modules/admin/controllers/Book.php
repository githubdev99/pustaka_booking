<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Book extends MY_Controller
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
        $title = 'Buku';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'admin/book/v_book',
            'get_script' => 'admin/book/script_book'
        ];

        if (!$this->input->post()) {
            $this->master->template($data);
        } else {
            $isError = false;

            if ($this->input->post('submit') == 'delete') {
                $check['book'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'book',
                    'where' => [
                        'id' => decrypt_text($this->input->post('id')),
                    ],
                ])->row();
                if (empty($check['book'])) {
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
                        'table' => 'book'
                    ]);

                    if ($query['error']) {
                        $isError = true;
                        $output = [
                            'isError' => $isError,
                            'type' => 'error',
                            'message' => "Data gagal di hapus [{$query['system']}]",
                        ];
                    } else {
                        if (!empty($check['book']->image)) {
                            if (file_exists("{$this->core['dirUpload']}books/{$check['book']->image}")) {
                                unlink("{$this->core['dirUpload']}books/{$check['book']->image}");
                            }
                        }

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

    public function add()
    {
        $title = 'Tambah Buku';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'admin/book/v_add',
            'get_script' => 'admin/book/script_add'
        ];

        if (!$this->input->post()) {
            $this->master->template($data);
        } else {
            $isError = false;

            if ($this->input->post('submit') == 'add') {
                $check['book'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'book',
                    'where' => [
                        'name' => $this->input->post('name'),
                    ],
                ])->row();
                if (!empty($check['book'])) {
                    $isError = true;
                    $output = [
                        'isError' => $isError,
                        'type' => 'warning',
                        'message' => 'Data sudah di input',
                    ];
                }

                if (!$isError) {
                    $query = $this->api_model->send_data([
                        'data' => [
                            'category_id' => decrypt_text($this->input->post('category_id')),
                            'name' => $this->input->post('name'),
                            'isbn' => $this->input->post('isbn'),
                            'image' => $this->input->post('image'),
                            'author' => $this->input->post('author'),
                            'publisher' => $this->input->post('publisher'),
                            'publication_year' => $this->input->post('publication_year'),
                            'stock' => $this->input->post('stock'),
                        ],
                        'table' => 'book'
                    ]);

                    if ($query['error']) {
                        if (file_exists("{$this->core['dirUpload']}books/{$this->input->post('image')}")) {
                            unlink("{$this->core['dirUpload']}books/{$this->input->post('image')}");
                        }

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
                            'callback' => base_url() . 'admin/book'
                        ];
                    }
                }
            }

            $this->output->set_content_type('application/json')->set_output(json_encode($output));
        }
    }

    public function edit($id = null)
    {
        $dataDetail = $this->dataDetail($id);

        if (empty($id) || $dataDetail['isError']) {
            $this->alert_popup([
                'name' => 'show_alert',
                'swal' => [
                    'title' => 'Ada kesalahan teknis',
                    'type' => 'error'
                ]
            ]);

            redirect('admin/book', 'refresh');
        } else {
            $title = 'Edit Buku';
            $data = [
                'core' => $this->core($title),
                'get_view' => 'admin/book/v_edit',
                'get_script' => 'admin/book/script_edit',
                'data' => $dataDetail['data']
            ];

            if (!$this->input->post()) {
                $this->master->template($data);
            } else {
                $isError = false;

                if ($this->input->post('submit') == 'edit') {
                    $check['book'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'book',
                        'where' => [
                            'name' => $this->input->post('name'),
                            'id !=' => decrypt_text($id),
                        ],
                    ])->row();
                    if (!empty($check['book'])) {
                        $isError = true;
                        $output = [
                            'isError' => $isError,
                            'type' => 'warning',
                            'message' => 'Data sudah di input',
                        ];
                    }

                    if (!$isError) {
                        if (!empty($this->input->post('image_old')) && ($this->input->post('image_old') == $dataDetail['data']['imageName'])) {
                            $isChanged = false;
                            $image = $dataDetail['data']['imageName'];
                        } else {
                            $isChanged = true;
                            $image = $this->input->post('image');
                        }

                        $query = $this->api_model->send_data([
                            'where' => [
                                'id' => decrypt_text($id)
                            ],
                            'data' => [
                                'category_id' => decrypt_text($this->input->post('category_id')),
                                'name' => $this->input->post('name'),
                                'isbn' => $this->input->post('isbn'),
                                'image' => $image,
                                'author' => $this->input->post('author'),
                                'publisher' => $this->input->post('publisher'),
                                'publication_year' => $this->input->post('publication_year'),
                                'stock' => $this->input->post('stock'),
                            ],
                            'table' => 'book'
                        ]);

                        if ($query['error']) {
                            if (empty($this->input->post('image_old'))) {
                                if (file_exists("{$this->core['dirUpload']}books/{$this->input->post('image')}")) {
                                    unlink("{$this->core['dirUpload']}books/{$this->input->post('image')}");
                                }
                            }

                            $isError = true;
                            $output = [
                                'isError' => $isError,
                                'type' => 'error',
                                'message' => "Data gagal di edit [{$query['system']}]",
                            ];
                        } else {
                            if ($isChanged) {
                                if (file_exists("{$this->core['dirUpload']}books/{$dataDetail['data']['imageName']}")) {
                                    unlink("{$this->core['dirUpload']}books/{$dataDetail['data']['imageName']}");
                                }
                            }

                            $output = [
                                'isError' => $isError,
                                'type' => 'success',
                                'message' => 'Data berhasil di edit',
                                'callback' => base_url() . 'admin/book'
                            ];
                        }
                    }
                }

                $this->output->set_content_type('application/json')->set_output(json_encode($output));
            }
        }
    }

    public function dataDetail($id)
    {
        $isError = false;

        $check['book'] = $this->api_model->select_data([
            'field' => 'book.*, category.name as category_name',
            'table' => 'book',
            'join' => [
                [
                    'table' => 'category',
                    'on' => 'category.id = book.category_id',
                    'type' => 'inner'
                ],
            ],
            'where' => [
                'book.id' => decrypt_text($id),
            ],
        ])->row();
        if (empty($check['book'])) {
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
                    'id' => $check['book']->id,
                    'name' => $check['book']->name,
                    'categoryId' => $check['book']->category_id,
                    'categoryName' => $check['book']->category_name,
                    'author' => $check['book']->author,
                    'publisher' => $check['book']->publisher,
                    'publicationYear' => $check['book']->publication_year,
                    'isbn' => $check['book']->isbn,
                    'stock' => $check['book']->stock,
                    'image' => (!empty($check['book']->image)) ? "{$this->core['imageUpload']}books/{$check['book']->image}" : null,
                    'imageName' => (!empty($check['book']->image)) ? $check['book']->image : null,
                ]
            ];
        }

        return $output;
    }

    public function get_data($id)
    {
        $this->output->set_content_type('application/json')->set_output(json_encode($this->dataDetail($id)));
    }

    public function datatable()
    {
        if (!empty($_REQUEST['draw'])) {
            $draw = $_REQUEST['draw'];
        } else {
            $draw = 0;
        }

        $param['column_search'] = [
            'book.image', 'book.name', 'category_name', 'book.isbn', 'book.author', 'book.publisher', 'book.stock'
        ];
        $param['column_order'] = [
            null, 'book.image', 'book.name', 'category_name', 'book.isbn', 'book.author', 'book.publisher', 'book.stock', null, null, null
        ];
        $param['field'] = '
        book.*,
        category.name as category_name';
        $param['table'] = 'book';
        $param['join'] = [
            [
                'table' => 'category',
                'on' => 'category.id = book.category_id',
                'type' => 'inner'
            ],
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

                $totalBorrow = $this->api_model->count_all_data([
                    'table' => 'loaning_detail',
                    'where' => [
                        'book_id' => $key->id,
                    ],
                    'group_by' => 'book_id'
                ]);
                $totalBook = $this->api_model->count_all_data([
                    'table' => 'booking_detail',
                    'where' => [
                        'book_id' => $key->id,
                    ],
                    'group_by' => 'book_id'
                ]);

                $image = (!empty($key->image)) ? "{$this->core['imageUpload']}books/{$key->image}" : $this->core['imageNotFound'];

                $column[] = $no;
                $column[] = '<img class="img-thumbnail" alt="images" width="100" src="' . $image . '">';
                $column[] = $key->name;
                $column[] = $key->category_name;
                $column[] = $key->isbn;
                $column[] = $key->author;
                $column[] = $key->stock;
                $column[] = $totalBorrow;
                $column[] = $totalBook;
                $column[] = '
                <a href="' . base_url() . 'admin/book/edit/' . encrypt_text($key->id) . '" class="btn btn-success btn-sm" data-toggle="tooltip" title="Edit Data"><i class="fas fa-edit"></i></a>
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
