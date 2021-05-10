<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Home extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->auth([
            'session' => 'member',
            'login' => false
        ]);
    }

    public function index()
    {
        $title = 'Beranda';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'member/home/v_home',
            'get_script' => 'member/home/script_home',
            'data' => $this->dataBooks()
        ];

        $this->master->template($data);
    }

    public function detail($id)
    {
        $title = 'Detail Buku';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'member/home/v_detail',
            'get_script' => 'member/home/script_detail',
            'data' => $this->dataDetailBook($id)
        ];

        $this->master->template($data);
    }

    public function dataBooks()
    {
        $parsing['book'] = $this->api_model->select_data([
            'field' => 'book.*, category.name as category_name',
            'table' => 'book',
            'join' => [
                [
                    'table' => 'category',
                    'on' => 'category.id = book.category_id',
                    'type' => 'inner'
                ],
            ],
        ])->result();
        $output = [];
        foreach ($parsing['book'] as $key_book) {
            $output[] = [
                'id' => encrypt_text($key_book->id),
                'name' => $key_book->name,
                'categoryId' => $key_book->category_id,
                'categoryName' => $key_book->category_name,
                'author' => $key_book->author,
                'publisher' => $key_book->publisher,
                'publicationYear' => $key_book->publication_year,
                'isbn' => $key_book->isbn,
                'stock' => $key_book->stock,
                'image' => (!empty($key_book->image)) ? "{$this->core['imageUpload']}books/{$key_book->image}" : null,
                'imageName' => (!empty($key_book->image)) ? $key_book->image : null,
            ];
        }

        return $output;
    }

    public function dataDetailBook($id)
    {
        $parsing['book'] = $this->api_model->select_data([
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
                'book.id' => decrypt_text($id)
            ]
        ])->row();
        if (empty($parsing['book'])) {
            $output = [];
        } else {
            $output = [
                'id' => encrypt_text($parsing['book']->id),
                'name' => $parsing['book']->name,
                'categoryId' => $parsing['book']->category_id,
                'categoryName' => $parsing['book']->category_name,
                'author' => $parsing['book']->author,
                'publisher' => $parsing['book']->publisher,
                'publicationYear' => $parsing['book']->publication_year,
                'isbn' => $parsing['book']->isbn,
                'stock' => $parsing['book']->stock,
                'image' => (!empty($parsing['book']->image)) ? "{$this->core['imageUpload']}books/{$parsing['book']->image}" : null,
                'imageName' => (!empty($parsing['book']->image)) ? $parsing['book']->image : null,
            ];
        }

        return $output;
    }
}
