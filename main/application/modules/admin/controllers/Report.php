<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->auth([
            'session' => 'admin',
            'login' => false
        ]);
    }

    public function print_book()
    {
        $title = 'Laporan Data Buku';

        $data = [
            'core' => $this->core($title),
            'data' => $this->api_model->select_data([
                'field' => 'book.*, category.name as category_name',
                'table' => 'book',
                'join' => [
                    [
                        'table' => 'category',
                        'on' => 'category.id = book.category_id',
                        'type' => 'inner'
                    ],
                ],
                'order_by' => [
                    'book.name' => 'asc'
                ],
            ])->result()
        ];

        $this->load->view('admin/report/report_print_book', $data);
    }

    public function excel_book()
    {
        $title = 'Laporan Data Buku';

        header("Content-type=appalication/vnd.ms-excel");
        header("content-disposition:attachment;filename=laporan_data_buku-" . date('Y-m-d') . ".xls");

        $data = [
            'core' => $this->core($title),
            'data' => $this->api_model->select_data([
                'field' => 'book.*, category.name as category_name',
                'table' => 'book',
                'join' => [
                    [
                        'table' => 'category',
                        'on' => 'category.id = book.category_id',
                        'type' => 'inner'
                    ],
                ],
                'order_by' => [
                    'book.name' => 'asc'
                ],
            ])->result()
        ];

        $this->load->view('admin/report/report_excel_book', $data);
    }
}
