<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Master extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function province_get()
    {
        $checking = true;

        if ($checking === true) {
            $param['ro_province']['field'] = '*';
            $param['ro_province']['table'] = 'ro_province';
            $param['ro_province']['order_by'] = [
                'province' => 'asc'
            ];
            $parsing['ro_province'] = $this->api_model->select_data($param['ro_province'])->result();

            $output = [];
            if (empty($parsing['ro_province'])) {
                $data['items'] = [];
                $code = self::HTTP_NO_CONTENT;
            } else {
                $code = self::HTTP_OK;
                $total_record = $this->api_model->count_all_data($param['ro_province']);

                $data['total'] = $total_record;
                $data['items'] = [];

                foreach ($parsing['ro_province'] as $key_ro_province) {
                    $items['id'] = $key_ro_province->province_id;
                    $items['name'] = $key_ro_province->province;
                    $items['code'] = $key_ro_province->province_kd;

                    $data['items'][] = $items;
                }
            }

            $output = $data;

            $response = $this->formatter([
                'code' => $code,
                'message' => 'get data success',
                'data' => $output
            ]);
        }

        $this->response($response['result'], $response['status']);
    }

    public function city_get($id = null)
    {
        $checking = true;

        if (empty($id)) {
            $checking = false;
            $response = $this->formatter([
                'code' => self::HTTP_BAD_REQUEST,
                'message' => 'parameter not found',
                'data' => [
                    'total' => 0,
                    'items' => [],
                ]
            ]);
        }

        if ($checking === true) {
            $param['ro_city']['field'] = '*';
            $param['ro_city']['table'] = 'ro_city';
            $param['ro_city']['where'] = [
                'province_id' => $id
            ];
            $param['ro_city']['order_by'] = [
                'city_name' => 'asc'
            ];
            $parsing['ro_city'] = $this->api_model->select_data($param['ro_city'])->result();

            $output = [];
            if (empty($parsing['ro_city'])) {
                $data['total'] = 0;
                $data['items'] = [];
                $code = self::HTTP_NO_CONTENT;
            } else {
                $code = self::HTTP_OK;
                $total_record = $this->api_model->count_all_data($param['ro_city']);

                $data['total'] = $total_record;
                $data['items'] = [];

                $items['province'] = [
                    'id' => $parsing['ro_city'][0]->province_id,
                    'name' => $parsing['ro_city'][0]->province,
                    'code' => $parsing['ro_city'][0]->province_kd,
                ];

                $items['city'] = [];

                foreach ($parsing['ro_city'] as $key_ro_city) {
                    $city['id'] = $key_ro_city->city_id;
                    $city['name'] = $key_ro_city->city_name;
                    $city['code'] = $key_ro_city->city_kd;

                    $items['city'][] = $city;
                }

                $data['items'] = $items;
            }

            $output = $data;

            $response = $this->formatter([
                'code' => $code,
                'message' => 'get data success',
                'data' => $output
            ]);
        }

        $this->response($response['result'], $response['status']);
    }

    public function district_get($id = null)
    {
        $checking = true;

        if (empty($id)) {
            $checking = false;
            $response = $this->formatter([
                'code' => self::HTTP_BAD_REQUEST,
                'message' => 'parameter not found',
                'data' => [
                    'total' => 0,
                    'items' => [],
                ]
            ]);
        }

        if ($checking === true) {
            $param['ro_subdistrict']['field'] = '*';
            $param['ro_subdistrict']['table'] = 'ro_subdistrict';
            $param['ro_subdistrict']['where'] = [
                'city_id' => $id
            ];
            $param['ro_subdistrict']['order_by'] = [
                'subdistrict' => 'asc'
            ];
            $parsing['ro_subdistrict'] = $this->api_model->select_data($param['ro_subdistrict'])->result();

            $output = [];
            if (empty($parsing['ro_subdistrict'])) {
                $data['total'] = 0;
                $data['items'] = [];
                $code = self::HTTP_NO_CONTENT;
            } else {
                $code = self::HTTP_OK;
                $total_record = $this->api_model->count_all_data($param['ro_subdistrict']);

                $data['total'] = $total_record;
                $data['items'] = [];

                $items['province'] = [
                    'id' => $parsing['ro_subdistrict'][0]->province_id,
                    'name' => $parsing['ro_subdistrict'][0]->province,
                    'code' => $parsing['ro_subdistrict'][0]->province_kd,
                ];

                $items['city'] = [
                    'id' => $parsing['ro_subdistrict'][0]->city_id,
                    'name' => $parsing['ro_subdistrict'][0]->city,
                    'code' => $parsing['ro_subdistrict'][0]->city_kd,
                ];

                $items['district'] = [];

                foreach ($parsing['ro_subdistrict'] as $key_ro_subdistrict) {
                    $district['id'] = $key_ro_subdistrict->subdistrict_id;
                    $district['name'] = $key_ro_subdistrict->subdistrict;
                    $district['code'] = $key_ro_subdistrict->subdistrict_kd;

                    $items['district'][] = $district;
                }

                $data['items'] = $items;
            }

            $output = $data;

            $response = $this->formatter([
                'code' => $code,
                'message' => 'get data success',
                'data' => $output
            ]);
        }

        $this->response($response['result'], $response['status']);
    }

    public function subdistrict_get($id = null)
    {
        $checking = true;

        if (empty($id)) {
            $checking = false;
            $response = $this->formatter([
                'code' => self::HTTP_BAD_REQUEST,
                'message' => 'parameter not found',
                'data' => [
                    'total' => 0,
                    'items' => [],
                ]
            ]);
        }

        if ($checking === true) {
            $param['ro_wilayah']['field'] = '*';
            $param['ro_wilayah']['table'] = 'ro_wilayah';
            $param['ro_wilayah']['where'] = [
                'kd_kec' => $id
            ];
            $param['ro_wilayah']['order_by'] = [
                'kelurahan_desa' => 'asc'
            ];
            $parsing['ro_wilayah'] = $this->api_model->select_data($param['ro_wilayah'])->result();

            $output = [];
            if (empty($parsing['ro_wilayah'])) {
                $data['total'] = 0;
                $data['items'] = [];
                $code = self::HTTP_NO_CONTENT;
            } else {
                $code = self::HTTP_OK;
                $total_record = $this->api_model->count_all_data($param['ro_wilayah']);

                $data['total'] = $total_record;
                $data['items'] = [];

                $items['province'] = $parsing['ro_wilayah'][0]->propinsi;
                $items['city'] = $parsing['ro_wilayah'][0]->kabupaten_kota;
                $items['district'] = $parsing['ro_wilayah'][0]->kecamatan;
                $items['subdistrict'] = [];

                foreach ($parsing['ro_wilayah'] as $key_ro_wilayah) {
                    $subdistrict['id'] = $key_ro_wilayah->kelurahan_id;
                    $subdistrict['name'] = $key_ro_wilayah->kelurahan_desa;
                    $subdistrict['code'] = $key_ro_wilayah->kd_kelurahan_desa;

                    $items['subdistrict'][] = $subdistrict;
                }

                $data['items'] = $items;
            }

            $output = $data;

            $response = $this->formatter([
                'code' => $code,
                'message' => 'get data success',
                'data' => $output
            ]);
        }

        $this->response($response['result'], $response['status']);
    }

    public function upload_post()
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if (!empty($this->core['customer']) || !empty($this->core['seller'])) {
                if (!$this->post()) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                    ]);
                } else {
                    if (empty($this->post('for'))) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => 'type not found',
                            'data' => [],
                        ]);
                    } else {
                        if (!in_array($this->post('for'), [
                            'complaint', 'confirmPayment'
                        ])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_BAD_REQUEST,
                                'message' => 'type not found, valid type is complaint, confirmPayment',
                                'data' => [],
                            ]);
                        }
                    }
                }
            } else {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_UNAUTHORIZED,
                    'message' => 'unauthorized',
                ]);
            }

            if ($checking === true) {
                if ($this->post('for') == 'complaint') {
                    $file_name = date('YmdHis') . '_complaint_' . seo($this->core['customer']['school']['name']);
                    $upload_path = "assets/uplod/komplain/";
                    $allowed_types = 'gif|jpg|jpeg|png';
                    $max_size = '500';
                } elseif ($this->post('for') == 'confirmPayment') {
                    $file_name = date('YmdHis') . '_confirm-payment_' . seo($this->core['customer']['school']['name']);
                    $upload_path = "assets/uplod/konfirmasi/";
                    $allowed_types = 'gif|jpg|png|jpeg|pdf';
                    $max_size = '0';

                    $configUpload['overwrite'] = FALSE;
                    $configUpload['maintain_ratio'] = FALSE;
                    $configUpload['create_thumb'] = FALSE;
                    $configUpload['quality'] = '80%';
                    $configUpload['width'] = '800';
                    $configUpload['height'] = '600';
                }

                $configUpload['file_name'] = $file_name;
                $configUpload['upload_path'] = "{$this->core['put_upload_siplah']}{$upload_path}";
                $configUpload['allowed_types'] = $allowed_types;
                $configUpload['max_size']  = $max_size;

                $this->upload->initialize($configUpload);

                if ($this->upload->do_upload('file')) {
                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => 'upload data success',
                        'data' => $this->upload->data()['file_name']
                    ]);
                } else {
                    if (empty($this->upload->data()['file_type'])) {
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => 'file not declared or the uploaded file exceeds the maximum allowed size'
                        ]);
                    } else {
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => 'upload data failed'
                        ]);
                    }
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }
}
