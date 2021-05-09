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
                        if (!empty($check['image'])) {
                            if (file_exists($this->core['imageUpload'] . 'books/' . $check['image'])) {
                                unlink($this->core['imageUpload'] . 'books/' . $check['image']);
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

    public function form($id = null)
    {
        if (empty($id)) {
            $title = 'Tambah Buku';
            $data = [
                'core' => $this->core($title),
                'get_view' => 'admin/book/v_add',
                'get_script' => 'admin/book/script_add'
            ];
        } else {
        }

        if (!$this->input->post()) {
            $this->master->template($data);
        } else {
            $isError = false;

            if ($this->input->post('submit') == 'add') {
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
                        if (!empty($check['image'])) {
                            if (file_exists($this->core['imageUpload'] . 'books/' . $check['image'])) {
                                unlink($this->core['imageUpload'] . 'books/' . $check['image']);
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

    public function get_data($id)
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
                    'title' => $check['book']->title,
                    'categoryId' => $check['book']->category_id,
                    'categoryName' => $check['book']->category_name,
                    'author' => $check['book']->author,
                    'publisher' => $check['book']->publisher,
                    'yearPublish' => $check['book']->year_publish,
                    'isbn' => $check['book']->isbn,
                    'stock' => $check['book']->stock,
                    'image' => (!empty($check['book']->image)) ? "{$this->core['imageUpload']}books/{$check['book']->image}" : $this->core['imageNotFound'],
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
            'book.image', 'book.title', 'category_name', 'book.isbn', 'book.author', 'book.publisher', 'book.stock', 'totalBorrow', 'totalBook'
        ];
        $param['column_order'] = [
            null, 'book.image', 'book.title', 'category_name', 'book.isbn', 'book.author', 'book.publisher', 'book.stock', 'totalBorrow', 'totalBook', null
        ];
        $param['field'] = 'book.*, category.name as category_name';
        $param['table'] = 'book';
        $param['join'] = [
            [
                'table' => 'category',
                'on' => 'category.id = book.category_id',
                'type' => 'inner'
            ],
        ];
        $param['order_by'] = [
            'book.title' => 'asc'
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
                $column[] = $key->title;
                $column[] = $key->category_name;
                $column[] = $key->isbn;
                $column[] = $key->author;
                $column[] = $key->stock;
                $column[] = $key->stock;
                $column[] = $key->stock;
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
