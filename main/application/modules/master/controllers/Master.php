<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Master extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function template($data)
    {
        $this->load->view('template', $data);
    }

    public function uploadBook()
    {
        if (isset($_FILES['image']['name'])) {
            $isError = false;

            $configUpload['file_name'] = 'book-' . date('YmdHis') . '-' . seo($this->input->post('name'));
            $configUpload['upload_path'] = "./assets/upload/books/";
            $configUpload['allowed_types'] = 'gif|jpg|jpeg|png';
            $configUpload['max_size']  = '500';

            $this->upload->initialize($configUpload);

            if ($this->upload->do_upload('image')) {
                $output = [
                    'isError' => $isError,
                    'data' => $this->upload->data()['file_name']
                ];
            } else {
                $isError = true;

                if (empty($this->upload->data()['file_type'])) {
                    $output = [
                        'isError' => $isError,
                        'type' => 'warning',
                        'message' => "File tidak terdefinisi atau ukuran file melebihi maksimal",
                        'data' => $this->upload->data()
                    ];
                } else {
                    $output = [
                        'isError' => $isError,
                        'type' => 'error',
                        'message' => "Gagal upload data",
                    ];
                }
            }
        } else {
            $output = [
                'isError' => true,
                'type' => 'warning',
                'message' => "File tidak terdefinisi",
            ];
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($output));
    }
}
