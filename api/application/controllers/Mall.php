<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Mall extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index_get()
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if (!empty($this->get('slug'))) {
                if ($checking === true) {
                    $param['db_mall']['field'] = '*';
                    $param['db_mall']['table'] = 'db_mall';

                    $param['db_mall']['where'] = [
                        'slug' => $this->get('slug'),
                    ];

                    $parsing['db_mall'] = $this->api_model->select_data($param['db_mall'])->row();

                    if (empty($parsing['db_mall'])) {
                        $output = (object) [];
                        $code = self::HTTP_NO_CONTENT;
                    } else {
                        $output = [];
                        $code = self::HTTP_OK;

                        $parsing['rating'] = $this->api_model->select_data([
                            'field' => 'count(mall_id) as jml, SUM(rate) as rate',
                            'table' => 'db_mall_ulasan',
                            'where' => [
                                'mall_id' => $parsing['db_mall']->mall_id,
                            ],
                            'group_by' => 'mall_id'
                        ])->row();

                        $data['id'] = $parsing['db_mall']->mall_id;
                        $data['code'] = $parsing['db_mall']->mall_code;
                        $data['type'] = $parsing['db_mall']->jenis;
                        $data['name'] = $parsing['db_mall']->name;
                        $data['email'] = $parsing['db_mall']->email;
                        $data['slug'] = $parsing['db_mall']->slug;
                        $data['inActive'] = boolval($parsing['db_mall']->status);

                        if (!empty($parsing['rating'])) {
                            $data['totalRating'] = $parsing['rating']->jml;
                            $data['averageRating'] = $parsing['rating']->rate / $data['totalRating'];
                        } else {
                            $data['totalRating'] = 0;
                            $data['averageRating'] = 0;
                        }

                        $data['companyName'] = $parsing['db_mall']->nama_perusahaan;
                        $data['brandName'] = $parsing['db_mall']->nama_merk;

                        $data['totalProduct'] = $this->api_model->count_all_data([
                            'where' => [
                                'blokir' => '0',
                                'status' => '1',
                                'disabled' => 'N',
                                'mall_id' => $parsing['db_mall']->mall_id,
                            ],
                            'table' => 'db_product'
                        ]);

                        $data['business'] = [
                            'type' => $parsing['db_mall']->jenis_usaha,
                            'category' => $parsing['db_mall']->kategori_usaha,
                        ];

                        $data['image'] = [
                            'primary' => (!empty($parsing['db_mall']->image) || $parsing['db_mall']->image != '') ? $this->core['url_image_mall'] . $parsing['db_mall']->image : $this->core['image_not_found'],
                            'header' => (!empty($parsing['db_mall']->image_header) || $parsing['db_mall']->image_header != '') ? $this->core['url_image_mall'] . $parsing['db_mall']->image_header : $this->core['image_header_not_found'],
                        ];

                        $data['pic'] = [
                            'name' => $parsing['db_mall']->nama_pic,
                            'position' => $parsing['db_mall']->jabatan_pic,
                            'email' => $parsing['db_mall']->email_pic,
                            'phone' => $parsing['db_mall']->telp_pic,
                        ];

                        $data['location'] = [
                            'companyAddress' => $parsing['db_mall']->alamat_perusahaan,
                            'npwpAddress' => $parsing['db_mall']->address_npwp,
                            'address' => $parsing['db_mall']->address,
                            'province' => $parsing['db_mall']->province,
                            'city' => $parsing['db_mall']->city,
                            'subdistrict' => $parsing['db_mall']->kelurahan,
                            'postalCode' => trim($parsing['db_mall']->postcode),
                            'country' => $parsing['db_mall']->country,
                            'latitude' => $parsing['db_mall']->lat,
                            'longitude' => $parsing['db_mall']->lon,
                            'zone' => [],
                        ];

                        if (!empty($parsing['db_mall']->zone_1)) {
                            $data['location']['zone'][] = $parsing['db_mall']->zone_1;
                        }

                        if (!empty($parsing['db_mall']->zone_2)) {
                            $data['location']['zone'][] = $parsing['db_mall']->zone_2;
                        }

                        $output = $data;
                    }

                    $response = $this->formatter([
                        'code' => $code,
                        'message' => 'get data success',
                        'data' => $output
                    ]);
                }
            } else {
                if ($this->get('page') == null || $this->get('limit') == null) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'page or limit not found',
                        'data' => [
                            'total' => 0,
                            'items' => [],
                        ]
                    ]);
                } else {
                    if ($this->get('page') < 1 || $this->get('limit') < 1) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => 'value must more than 1',
                            'data' => [
                                'total' => 0,
                                'items' => [],
                            ]
                        ]);
                    }
                }

                if ($checking === true) {
                    $param['db_mall']['field'] = '*';
                    $param['db_mall']['table'] = 'db_mall';

                    $arr_filter_province = (!empty($this->get('filter_province'))) ? [
                        'province_id' => $this->get('filter_province')
                    ] : [];

                    $arr_filter_city = (!empty($this->get('filter_city'))) ? [
                        'city_id' => $this->get('filter_city')
                    ] : [];

                    $arr_filter_district = (!empty($this->get('filter_district'))) ? [
                        'zone_id' => $this->get('filter_district')
                    ] : [];

                    $param['db_mall']['where'] = array_merge([
                        'status_approve' => '1',
                        'blokir' => '0',
                    ], $arr_filter_province, $arr_filter_city, $arr_filter_district);

                    if (!empty($this->get('keyword'))) {
                        $param['db_mall']['like'] = [
                            'name' => $this->get('keyword')
                        ];
                    }

                    $param['db_mall']['order_by'] = [
                        'mall_id' => 'desc'
                    ];
                    $param['db_mall']['limit'] = [
                        $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                    ];
                    $parsing['db_mall'] = $this->api_model->select_data($param['db_mall'])->result();

                    $output = [];
                    if (empty($parsing['db_mall'])) {
                        $data['total'] = 0;
                        $data['items'] = [];
                        $code = self::HTTP_NO_CONTENT;
                    } else {
                        $code = self::HTTP_OK;
                        $total_record = $this->api_model->count_all_data($param['db_mall']);

                        $limit = (int) $this->get('limit');
                        $current_page = (int) $this->get('page');
                        $total_page = ceil($total_record / $limit);

                        $data['page'] = $current_page;
                        $data['limit'] = $limit;
                        $data['total'] = $total_record;
                        $data['pages'] = $total_page;
                        $data['items'] = [];

                        foreach ($parsing['db_mall'] as $key_db_mall) {
                            $parsing['rating'] = $this->api_model->select_data([
                                'field' => 'count(mall_id) as jml, SUM(rate) as rate',
                                'table' => 'db_mall_ulasan',
                                'where' => [
                                    'mall_id' => $key_db_mall->mall_id,
                                ],
                                'group_by' => 'mall_id'
                            ])->row();

                            $items['id'] = $key_db_mall->mall_id;
                            $items['code'] = $key_db_mall->mall_code;
                            $items['type'] = $key_db_mall->jenis;
                            $items['name'] = $key_db_mall->name;
                            $items['email'] = $key_db_mall->email;
                            $items['slug'] = $key_db_mall->slug;
                            $items['inActive'] = boolval($key_db_mall->status);

                            if (!empty($parsing['rating'])) {
                                $items['totalRating'] = $parsing['rating']->jml;
                                $items['averageRating'] = $parsing['rating']->rate / $items['totalRating'];
                            } else {
                                $items['totalRating'] = 0;
                                $items['averageRating'] = 0;
                            }

                            $items['companyName'] = $key_db_mall->nama_perusahaan;
                            $items['brandName'] = $key_db_mall->nama_merk;

                            $items['totalProduct'] = $this->api_model->count_all_data([
                                'where' => [
                                    'blokir' => '0',
                                    'status' => '1',
                                    'disabled' => 'N',
                                    'mall_id' => $key_db_mall->mall_id,
                                ],
                                'table' => 'db_product'
                            ]);

                            $items['business'] = [
                                'type' => $key_db_mall->jenis_usaha,
                                'category' => $key_db_mall->kategori_usaha,
                            ];

                            $items['image'] = [
                                'primary' => (!empty($key_db_mall->image) || $key_db_mall->image != '') ? $this->core['url_image_mall'] . $key_db_mall->image : $this->core['image_not_found'],
                                'header' => (!empty($key_db_mall->image_header) || $key_db_mall->image_header != '') ? $this->core['url_image_mall'] . $key_db_mall->image_header : $this->core['image_header_not_found'],
                            ];

                            $items['pic'] = [
                                'name' => $key_db_mall->nama_pic,
                                'position' => $key_db_mall->jabatan_pic,
                                'email' => $key_db_mall->email_pic,
                                'phone' => $key_db_mall->telp_pic,
                            ];

                            $items['location'] = [
                                'address' => $key_db_mall->alamat_perusahaan,
                                'province' => $key_db_mall->province,
                                'city' => $key_db_mall->city,
                                'zone' => [],
                                'subdistrict' => $key_db_mall->kelurahan,
                                'postalCode' => trim($key_db_mall->postcode),
                                'country' => $key_db_mall->country,
                                'latitude' => $key_db_mall->lat,
                                'longitude' => $key_db_mall->lon,
                            ];

                            if (!empty($key_db_mall->zone_1)) {
                                $items['location']['zone'][] = $key_db_mall->zone_1;
                            }

                            if (!empty($key_db_mall->zone_2)) {
                                $items['location']['zone'][] = $key_db_mall->zone_2;
                            }

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
            }
        }

        $this->response($response['result'], $response['status']);
    }
}
