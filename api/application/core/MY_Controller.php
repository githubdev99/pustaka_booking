<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class MY_Controller extends REST_Controller
{
    protected $core = [
        'isProduction' => false
    ];

    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set('Asia/Jakarta');

        $this->core['baseUrlCustomer'] = 'https://siplah.eurekabookhouse.co.id/';
        $this->core['url_cdn'] = 'https://cdn.eurekabookhouse.co.id/';

        $this->core['url_image_product'] = $this->core['url_cdn'] . 'ebh/product/all/';
        $this->core['url_image_mall'] = $this->core['url_cdn'] . 'ebh/mall/';
        $this->core['url_front_image'] = $this->core['baseUrlCustomer'] . 'assets/front/images/';
        $this->core['url_icon_banner_index'] = $this->core['baseUrlCustomer'] . 'assets/front/images/banner-index/icon/';
        $this->core['image_not_found'] = $this->core['baseUrlCustomer'] . 'assets/front/images/placelogo_placeholder.jpg';
        $this->core['image_header_not_found'] = $this->core['baseUrlCustomer'] . 'assets/front/images/banner/banner-mall.jpg';
        $this->core['url_image_confirm_payment'] = $this->core['baseUrlCustomer'] . 'assets/uplod/konfirmasi/';
        $this->core['url_image_complaint'] = $this->core['baseUrlCustomer'] . 'assets/uplod/komplain/';
        $this->core['put_upload_siplah'] = '/home/siplah/sites/siplah.eurekabookhouse.co.id/';
    }

    public function auth()
    {
        $headers = $this->input->request_headers();
        $response = [];

        if (!empty($headers['Authorization'])) {
            $token_decode = $this->token->validate(decrypt_text($headers['Authorization']));
            if ($token_decode['error'] === false) {
                $data_token = $token_decode['output'];

                if (!empty($data_token->role)) {
                    if ($data_token->role == 'customer') {
                        $parsing['db_customer'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_customer',
                            'where' => [
                                'customer_id' => $data_token->id
                            ]
                        ])->row();

                        $parsing['db_customer_school'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_customer_school',
                            'where' => [
                                'id_rajaongkir !=' => '0',
                            ],
                            'like' => [
                                'sekolah_id' => $parsing['db_customer']->sekolah_id,
                            ],
                        ])->row();

                        $customer['id'] = $parsing['db_customer']->customer_id;
                        $customer['nip'] = $parsing['db_customer_school']->nip_kepala_sekolah;
                        $customer['name'] = (!empty($parsing['db_customer']->lastname)) ? $parsing['db_customer']->firstname . ' ' . $parsing['db_customer']->lastname : $parsing['db_customer']->firstname;
                        $customer['firstName'] = $parsing['db_customer']->firstname;
                        $customer['lastName'] = $parsing['db_customer']->lastname;
                        $customer['email'] = $parsing['db_customer']->email;
                        $customer['role'] = $parsing['db_customer']->peran;
                        $customer['position'] = $parsing['db_customer']->jabatan;

                        if (empty($parsing['db_customer_school']->kepala_sekolah)) {
                            $headmaster = (object) [];
                        } else {
                            $headmaster = [
                                'nip' => $parsing['db_customer_school']->nip_kepala_sekolah,
                                'name' => $parsing['db_customer_school']->kepala_sekolah,
                                'email' => $parsing['db_customer_school']->email_kepala_sekolah,
                                'phone' => $parsing['db_customer_school']->hp_kepala_sekolah,
                            ];
                        }

                        if (empty($parsing['db_customer_school']->bendahara_bos)) {
                            $bendahara = (object) [];
                        } else {
                            $bendahara = [
                                'nip' => $parsing['db_customer_school']->nip_bendahara_bos,
                                'name' => $parsing['db_customer_school']->bendahara_bos,
                                'email' => $parsing['db_customer_school']->email_bendahara,
                            ];
                        }

                        $customer['school'] = [
                            'id' => $parsing['db_customer']->sekolah_id,
                            'schoolId' => $parsing['db_customer_school']->school_id,
                            'rajaOngkirId' => $parsing['db_customer_school']->id_rajaongkir,
                            'name' => $parsing['db_customer_school']->nama_sekolah,
                            'email' => $parsing['db_customer_school']->email,
                            'telephone' => $parsing['db_customer_school']->telepon,
                            'npsn' => $parsing['db_customer_school']->npsn,
                            'npwp' => $parsing['db_customer_school']->npwp,
                            'headmaster' => $headmaster,
                            'bendahara' => $bendahara,
                            'location' => [
                                'address' => $parsing['db_customer_school']->alamat_jalan,
                                'village' => $parsing['db_customer_school']->desa,
                                'district' => [
                                    'id' => $parsing['db_customer_school']->kecamatan_id,
                                    'name' => $parsing['db_customer_school']->kecamatan,
                                ],
                                'city' => [
                                    'id' => $parsing['db_customer_school']->kota_id,
                                    'name' => $parsing['db_customer_school']->kota,
                                ],
                                'province' => [
                                    'id' => $parsing['db_customer_school']->provinsi_id,
                                    'name' => $parsing['db_customer_school']->provinsi,
                                ],
                                'postalCode' => $parsing['db_customer_school']->kode_pos,
                                'zone' => $parsing['db_customer_school']->zona,
                                'latitude' => $parsing['db_customer_school']->lintang,
                                'longitude' => $parsing['db_customer_school']->bujur,
                            ],
                        ];

                        $this->core['customer'] = $customer;

                        if (empty($this->core['customer'])) {
                            $response = $this->formatter([
                                'code' => self::HTTP_NOT_FOUND,
                                'message' => 'data not found',
                                'data' => null,
                            ]);
                        }
                    } elseif ($data_token->role == 'seller') {
                        $parsing['db_mall'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_mall',
                            'where' => [
                                'mall_id' => $data_token->id
                            ]
                        ])->row();

                        $seller['id'] = $parsing['db_mall']->mall_id;
                        $seller['rajaOngkirId'] = $parsing['db_mall']->id_rajaongkir;
                        $seller['inActive'] = boolval($parsing['db_mall']->status);
                        $seller['isMustUpdate'] = (empty($parsing['db_mall']->kelurahan) || $parsing['db_mall']->status_approve == '9' || $parsing['db_mall']->blokir == '1' || ($parsing['db_mall']->status_approve != '1' && $parsing['db_mall']->blokir != '0')) ? true : false;

                        $seller['alert'] = [
                            'title' => '',
                            'message' => '',
                        ];
                        if ($seller['isMustUpdate']) {
                            if (empty($parsing['db_mall']->kelurahan)) {
                                $seller['alert'] = [
                                    'title' => 'SEGERA! Update Alamat Toko',
                                    'message' => '<p>Akun Toko telah diblokir sementara oleh tim QC Siplah eureka, <b>untuk melanjutkan proses segera update alamat toko anda!</b></p><p>Periksa kembali daftar produk anda dan pastikan sesuai dengan aturan SIPLah</p>',
                                ];
                            } elseif ($parsing['db_mall']->status_approve == '9') {
                                $seller['alert'] = [
                                    'title' => 'Persetujuan akun anda ditunda',
                                    'message' => '<p>Pengaktifan akun penyedia anda dinyatakan "DITUNDA" oleh tim QC Siplah Eureka</p><p>Periksa kembali syarat legalitas perusahaan</p>',
                                ];
                            } elseif ($parsing['db_mall']->blokir == '1') {
                                $seller['alert'] = [
                                    'title' => 'Akun anda terblokir',
                                    'message' => '<p>Akun penyedia anda telah diblokir tim QC Siplah eureka, namun anda dapat mengelola pesanan anda seperti biasa!</p><p>Periksa kembali daftar produk anda dan pastikan sesuai dengan aturan SIPLah</p>',
                                ];
                            } elseif ($parsing['db_mall']->status_approve != '1' && $parsing['db_mall']->blokir != '0') {
                                $seller['alert'] = [
                                    'title' => 'Belum dikonfirmasi',
                                    'message' => '<p>Akun penyedia anda telah aktif, namun akun ini belum dikonfirmasi oleh Siplah Eureka!</p><p>Lengkapi dokumen legalitas anda untuk mempercepat proses konfirmasi</p>',
                                ];
                            }
                        }

                        $seller['code'] = $parsing['db_mall']->mall_code;
                        $seller['type'] = $parsing['db_mall']->jenis;
                        $seller['name'] = $parsing['db_mall']->name;
                        $seller['email'] = $parsing['db_mall']->email;
                        $seller['slug'] = $parsing['db_mall']->slug;

                        $seller['companyName'] = $parsing['db_mall']->nama_perusahaan;
                        $seller['brandName'] = $parsing['db_mall']->nama_merk;

                        $seller['totalProduct'] = $this->api_model->count_all_data([
                            'where' => [
                                'blokir' => '0',
                                'status' => '1',
                                'disabled' => 'N',
                                'mall_id' => $parsing['db_mall']->mall_id,
                            ],
                            'table' => 'db_product'
                        ]);

                        $seller['business'] = [
                            'type' => $parsing['db_mall']->jenis_usaha,
                            'category' => $parsing['db_mall']->kategori_usaha,
                        ];

                        $parsing['db_mall_to_rek'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_mall_to_rek',
                            'where' => [
                                'mall_id' => $data_token->id
                            ],
                            'limit' => 2
                        ])->result();
                        $seller['account'] = [];
                        foreach ($parsing['db_mall_to_rek'] as $key_db_mall_to_rek) {
                            $account['id'] = $key_db_mall_to_rek->id;
                            $account['bank'] = $key_db_mall_to_rek->nama_bank;
                            $account['branch'] = $key_db_mall_to_rek->cabang;
                            $account['number'] = $key_db_mall_to_rek->nomor_rekening;
                            $account['asName'] = $key_db_mall_to_rek->atas_nama;

                            $seller['account'][] = $account;
                        }

                        $seller['legality'] = [
                            'npwp' => [
                                'id' => $parsing['db_mall']->npwp_id,
                                'image' => "{$this->core['url_image_mall']}{$parsing['db_mall']->npwp}"
                            ],
                            'siup' => [
                                'id' => $parsing['db_mall']->siup_id,
                                'image' => "{$this->core['url_image_mall']}{$parsing['db_mall']->siup}"
                            ]
                        ];

                        $seller['image'] = [
                            'primary' => (!empty($parsing['db_mall']->image) || $parsing['db_mall']->image != '') ? $this->core['url_image_mall'] . $parsing['db_mall']->image : $this->core['image_not_found'],
                            'header' => (!empty($parsing['db_mall']->image_header) || $parsing['db_mall']->image_header != '') ? $this->core['url_image_mall'] . $parsing['db_mall']->image_header : $this->core['image_header_not_found'],
                        ];

                        $seller['pic'] = [
                            'name' => $parsing['db_mall']->nama_pic,
                            'position' => $parsing['db_mall']->jabatan_pic,
                            'email' => $parsing['db_mall']->email_pic,
                            'phone' => $parsing['db_mall']->telp_pic,
                        ];

                        $seller['location'] = [
                            'companyAddress' => $parsing['db_mall']->alamat_perusahaan,
                            'npwpAddress' => $parsing['db_mall']->address_npwp,
                            'address' => $parsing['db_mall']->address,
                            'province' => [
                                'id' => $parsing['db_mall']->province_id,
                                'code' => $parsing['db_mall']->province_kd,
                                'name' => $parsing['db_mall']->province,
                            ],
                            'city' => [
                                'id' => $parsing['db_mall']->city_id,
                                'code' => $parsing['db_mall']->city_kd,
                                'name' => $parsing['db_mall']->city,
                            ],
                            'district' => [
                                'id' => $parsing['db_mall']->zone_id,
                                'code' => $parsing['db_mall']->zone_kd,
                                'name' => $parsing['db_mall']->zone_1,
                            ],
                            'subdistrict' => [
                                'code' => $parsing['db_mall']->kelurahan_kd,
                                'name' => $parsing['db_mall']->kelurahan,
                            ],
                            'postalCode' => trim($parsing['db_mall']->postcode),
                            'country' => $parsing['db_mall']->country,
                            'latitude' => $parsing['db_mall']->lat,
                            'longitude' => $parsing['db_mall']->lon,
                            'zone' => [],
                        ];

                        if (!empty($parsing['db_mall']->zone_1)) {
                            $seller['location']['zone'][] = $parsing['db_mall']->zone_1;
                        }

                        if (!empty($parsing['db_mall']->zone_2)) {
                            $seller['location']['zone'][] = $parsing['db_mall']->zone_2;
                        }

                        $this->core['seller'] = $seller;

                        if (empty($this->core['seller'])) {
                            $response = $this->formatter([
                                'code' => self::HTTP_NOT_FOUND,
                                'message' => 'data not found',
                                'data' => null,
                            ]);
                        }
                    } else {
                        $response = $this->formatter([
                            'code' => self::HTTP_UNAUTHORIZED,
                            'message' => $token_decode['output'],
                            'data' => null,
                        ]);
                    }
                } else {
                    $response = $this->formatter([
                        'code' => self::HTTP_UNAUTHORIZED,
                        'message' => 'token is not match',
                        'data' => null,
                    ]);
                }
            } else {
                $response = $this->formatter([
                    'code' => self::HTTP_UNAUTHORIZED,
                    'message' => $token_decode['output'],
                    'data' => null,
                ]);
            }
        }

        return $response;
    }

    public function formatter($param = [])
    {
        return [
            'result' => array_merge([
                'status' => array_merge([
                    'code' => $param['code'],
                    'message' => $param['message']
                ], (array_key_exists('mailing', $param)) ? [
                    'mailing' => $param['mailing']
                ] : [])
            ], (array_key_exists('data', $param)) ? [
                'data' => $param['data']
            ] : []),
            'status' => self::HTTP_OK
        ];
    }

    public function product_category($param = [])
    {
        $parsing = [];

        $parsing = $this->api_model->select_data([
            'field' => '
            aa.category_id as category_id,
            aa.parent_id as parent_id,
            bb.name as name,
            bb.slug as slug,
            bb.seo,
            Deriv1.Count as Count',
            'table' => 'db_category aa',
            'join' => [
                [
                    'table' => 'db_category_description bb',
                    'on' => 'bb.category_id = aa.category_id',
                    'type' => 'inner'
                ],
                [
                    'table' => '(SELECT parent_id, COUNT(*) AS Count FROM `db_category` GROUP BY parent_id) Deriv1',
                    'on' => 'aa.category_id = Deriv1.parent_id',
                    'type' => 'left outer'
                ]
            ],
            'where' => [
                'aa.parent_id' => ($param['is_parent'] === true) ? '0' : $param['id'],
                'aa.status' => '1'
            ],
            'order_by' => [
                'aa.sort_order' => 'ASC'
            ]
        ])->result();

        return $parsing;
    }

    public function filter_product()
    {
        $filters = [
            [
                'type' => 'dropdown',
                'slug' => 'price',
                'name' => 'Range Dana',
                'items' => [
                    [
                        'name' => '< 10Jt',
                        'slug' => '0-10000000'
                    ],
                    [
                        'name' => '10 - 50Jt',
                        'slug' => '10000000-50000000'
                    ],
                    [
                        'name' => '50 - 200Jt',
                        'slug' => '50000000-200000000'
                    ],
                    [
                        'name' => '> 200Jt',
                        'slug' => '200000000'
                    ],
                ]
            ],
            [
                'type' => 'dropdown',
                'slug' => 'manufacturer',
                'name' => 'Penerbit',
                'items' => []
            ],
            [
                'type' => 'dropdown',
                'slug' => 'province',
                'name' => 'Provinsi',
                'items' => []
            ],
            [
                'type' => 'dropdown',
                'slug' => 'manufacturer',
                'name' => 'Merek',
                'items' => []
            ],
        ];

        $parsing['publisher'] = $this->api_model->select_data([
            'field' => 'count(db_manufacturer.manufacturer_id) as jml,db_manufacturer.manufacturer_id,
                    db_manufacturer.name as name,db_manufacturer.slug as slug',
            'table' => 'db_manufacturer',
            'join' => [
                [
                    'table' => 'db_product',
                    'on' => 'db_product.manufacturer_id=db_manufacturer.manufacturer_id',
                    'type' => 'join'
                ]
            ],
            'where' => [
                'db_product.storage_quantity >' => '0',
                'db_product.blokir' => '0'
            ],
            'like' => [
                'db_manufacturer.name' => 'penerbit'
            ],
            'group_by' => 'db_manufacturer.name',
            'order_by' => [
                'jml' => 'DESC'
            ]
        ])->result();
        if (empty($parsing['publisher'])) {
            $filters[1]['items'] = [];
        } else {
            $filters[1]['items'] = [];
            foreach ($parsing['publisher'] as $key_publisher) {
                $filter_publisher['id'] = $key_publisher->manufacturer_id;
                $filter_publisher['name'] = $key_publisher->name;
                $filter_publisher['slug'] = $key_publisher->slug;

                $filters[1]['items'][] = $filter_publisher;
            }
        }

        $parsing['ro_province'] = $this->api_model->select_data([
            'field' => '*',
            'table' => 'ro_province'
        ])->result();
        if (empty($parsing['ro_province'])) {
            $filters[2]['items'] = [];
        } else {
            $filters[2]['items'] = [];
            foreach ($parsing['ro_province'] as $key_ro_province) {
                $filter_province['id'] = $key_ro_province->province_id;
                $filter_province['name'] = $key_ro_province->province;
                $filter_province['code'] = $key_ro_province->province_kd;

                $filters[2]['items'][] = $filter_province;
            }
        }

        $parsing['brand'] = $this->api_model->select_data([
            'field' => 'count(a.manufacturer_id) as jml,a.manufacturer_id,
                    a.name as name,
                    a.slug as slug',
            'table' => 'db_manufacturer a',
            'join' => [
                [
                    'table' => 'db_product b',
                    'on' => 'b.manufacturer_id=a.manufacturer_id',
                    'type' => 'join'
                ]
            ],
            'where' => [
                'b.storage_quantity >' => '0',
                'b.status' => '1',
                'b.blokir' => '0'
            ],
            'not_like' => [
                'a.name' => 'penerbit'
            ],
            'group_by' => 'a.name',
            'order_by' => [
                'a.name' => 'ASC'
            ]
        ])->result();
        if (empty($parsing['brand'])) {
            $filters[3]['items'] = [];
        } else {
            $filters[3]['items'] = [];
            foreach ($parsing['brand'] as $key_brand) {
                $filter_brand['id'] = $key_brand->manufacturer_id;
                $filter_brand['name'] = $key_brand->name;
                $filter_brand['slug'] = $key_brand->slug;

                $filters[3]['items'][] = $filter_brand;
            }
        }

        return $filters;
    }

    public function sort_product()
    {
        return [
            [
                'slug' => 'name-asc',
                'name' => 'Nama (A-Z)',
            ],
            [
                'slug' => 'name-desc',
                'name' => 'Nama (Z-A)',
            ],
            [
                'slug' => 'price-asc',
                'name' => 'Harga Terendah',
            ],
            [
                'slug' => 'price-desc',
                'name' => 'Harga Tertinggi',
            ],
        ];
    }

    public function filter_product_mall($param = [])
    {
        $filters = [
            [
                'type' => 'dropdown',
                'slug' => 'price',
                'name' => 'Range Dana',
                'items' => [
                    [
                        'name' => '< 10Jt',
                        'slug' => '0-10000000'
                    ],
                    [
                        'name' => '10 - 50Jt',
                        'slug' => '10000000-50000000'
                    ],
                    [
                        'name' => '50 - 200Jt',
                        'slug' => '50000000-200000000'
                    ],
                    [
                        'name' => '> 200Jt',
                        'slug' => '200000000'
                    ],
                ]
            ],
        ];

        $parsing['productCategory'] = $this->api_model->select_data([
            'field' => '
            ptc.product_id,
            ptc.category_id, 
            cd.slug,
            cd.name,
            cat.parent_id',
            'table' => 'db_product_to_category ptc',
            'join' => [
                [
                    'table' => 'db_product pd',
                    'on' => 'pd.product_id=ptc.product_id',
                    'type' => 'inner',
                ],
                [
                    'table' => 'db_category_description cd',
                    'on' => 'cd.category_id=ptc.category_id',
                    'type' => 'inner',
                ],
                [
                    'table' => 'db_category cat',
                    'on' => 'ptc.category_id=cat.category_id',
                    'type' => 'inner',
                ],
            ],
            'where' => [
                'pd.mall_id' => $param['mall_id'],
                'cat.parent_id' => '0',
                'cat.status' => '1',
            ],
            'group_by' => 'cat.category_id',
            'order_by' => [
                'cd.name' => 'ASC'
            ]
        ])->result();
        if (!empty($parsing['productCategory'])) {
            $filters[1] = [
                'type' => 'category',
                'slug' => 'category',
                'name' => 'Kategori Produk',
                'items' => []
            ];
            foreach ($parsing['productCategory'] as $key_productCategory) {
                $productCategory['id'] = $key_productCategory->category_id;
                $productCategory['parentId'] = $key_productCategory->parent_id;
                $productCategory['name'] = $key_productCategory->name;
                $productCategory['slug'] = $key_productCategory->slug;
                $productCategory['children'] = [];

                $parsing['productCategoryChildren'] = $this->api_model->select_data([
                    'field' => '
                    ptc.product_id,
                    ptc.category_id, 
                    cd.slug,
                    cd.name',
                    'table' => 'db_product_to_category ptc',
                    'join' => [
                        [
                            'table' => 'db_product pd',
                            'on' => 'pd.product_id=ptc.product_id',
                            'type' => 'inner',
                        ],
                        [
                            'table' => 'db_category_description cd',
                            'on' => 'cd.category_id=ptc.category_id',
                            'type' => 'inner',
                        ],
                        [
                            'table' => 'db_category cat',
                            'on' => 'ptc.category_id=cat.category_id',
                            'type' => 'inner',
                        ],
                    ],
                    'where' => [
                        'pd.mall_id' => $param['mall_id'],
                        'cat.parent_id' => $key_productCategory->category_id,
                        'cat.status' => '1',
                    ],
                    'group_by' => 'cat.category_id',
                    'order_by' => [
                        'cd.name' => 'ASC'
                    ]
                ])->result();
                if (empty($parsing['productCategoryChildren'])) {
                    $productCategory['children'] = [];
                } else {
                    foreach ($parsing['productCategoryChildren'] as $key_productCategoryChildren) {
                        $productCategoryChildren['id'] = $key_productCategoryChildren->category_id;
                        $productCategoryChildren['name'] = $key_productCategoryChildren->name;
                        $productCategoryChildren['slug'] = $key_productCategoryChildren->slug;
                        $productCategoryChildren['total'] = $this->api_model->select_data([
                            'field' => '
                            COUNT(aa.product_id) as berapa',
                            'table' => 'db_product_to_category aa',
                            'join' => [
                                [
                                    'table' => 'db_product p',
                                    'on' => 'p.product_id=aa.product_id',
                                    'type' => 'inner',
                                ]
                            ],
                            'where' => [
                                'aa.category_id' => $key_productCategoryChildren->category_id,
                                'p.mall_id' => $param['mall_id'],
                                'p.status' => '1',
                            ],
                        ])->row()->berapa;

                        $productCategory['children'][] = $productCategoryChildren;
                    }
                }

                $filters[1]['items'][] = $productCategory;
            }
        }

        return $filters;
    }

    public function sort_product_mall()
    {
        return [
            [
                'slug' => 'date_added-asc',
                'name' => 'Terbaru',
            ],
            [
                'slug' => 'name-asc',
                'name' => 'Nama (A-Z)',
            ],
            [
                'slug' => 'name-desc',
                'name' => 'Nama (Z-A)',
            ],
            [
                'slug' => 'price-asc',
                'name' => 'Harga Terendah',
            ],
            [
                'slug' => 'price-desc',
                'name' => 'Harga Tertinggi',
            ],
        ];
    }

    public function filter_notification()
    {
        $filters = [
            [
                'type' => 'dropdown',
                'slug' => 'type',
                'name' => 'Berdasarkan Jenis',
                'items' => [
                    [
                        'name' => 'Semua',
                        'slug' => 'all'
                    ],
                ]
            ],
        ];

        $parsing['db_notification'] = $this->api_model->select_data([
            'field' => 'jenis',
            'table' => 'db_notification',
            'group_by' => 'jenis',
            'order_by' => [
                'jenis' => 'asc'
            ],
        ])->result();
        foreach ($parsing['db_notification'] as $key_db_notification) {
            $filters[0]['items'][] = [
                'name' => ucwords($key_db_notification->jenis),
                'slug' => $key_db_notification->jenis,
            ];
        }

        return $filters;
    }

    public function get_order_total($id)
    {
        return $this->api_model->count_all_data([
            'table' => 'db_order aa',
            'where' => [
                'aa.order_status_id' => $id,
                'aa.sekolah_id' => $this->core['customer']['school']['id'],
                'aa.invoice_no !=' => 0,
            ],
            'group_by' => 'invoice_no',
            'order_by' => [
                'invoice_no' => 'desc'
            ],
        ]);
    }

    public function filter_order()
    {
        $filters = [
            [
                'title' => 'Pesanan Baru',
                'id' => 0,
                'total' => $this->get_order_total(0),
                'type' => 'primary',
                'inActive' => false,
            ],
            [
                'title' => 'Pesanan Diproses',
                'id' => 2,
                'total' => $this->get_order_total(2),
                'type' => 'primary',
                'inActive' => false,
            ],
            [
                'title' => 'Pesanan Dikirim',
                'id' => 3,
                'total' => $this->get_order_total(3),
                'type' => 'primary',
                'inActive' => false,
            ],
            [
                'title' => 'Pesanan Sampai',
                'id' => 4,
                'total' => $this->get_order_total(4),
                'type' => 'primary',
                'inActive' => false,
            ],
            [
                'title' => 'Pesanan Diterima',
                'id' => 17,
                'total' => $this->get_order_total(17),
                'type' => 'primary',
                'inActive' => false,
            ],
            [
                'title' => 'Pesanan Dibayar',
                'id' => 18,
                'total' => $this->get_order_total(18),
                'type' => 'primary',
                'inActive' => false,
            ],
            [
                'title' => 'Penerimaan Ditolak',
                'id' => 19,
                'total' => $this->get_order_total(19),
                'type' => 'danger',
                'inActive' => false,
            ],
            [
                'title' => 'Pembatalan Dari Sekolah',
                'id' => 7,
                'total' => $this->get_order_total(7),
                'type' => 'danger',
                'inActive' => false,
            ],
            [
                'title' => 'Ditolak Penyedia',
                'id' => 8,
                'total' => $this->get_order_total(8),
                'type' => 'danger',
                'inActive' => false,
            ],
        ];

        return $filters;
    }

    public function filter_nego()
    {
        $filters = [
            [
                'title' => 'Baru',
                'id' => 0,
                'type' => 'primary',
                'inActive' => false,
            ],
            [
                'title' => 'Disetujui',
                'id' => 1,
                'type' => 'primary',
                'inActive' => false,
            ],
            [
                'title' => 'Ditolak',
                'id' => 2,
                'type' => 'primary',
                'inActive' => false,
            ],
            [
                'title' => 'Kadaluarsa',
                'id' => 3,
                'type' => 'primary',
                'inActive' => false,
            ],
        ];

        return $filters;
    }

    public function mailingWithNotif($param = [])
    {
        if (empty($param)) {
            return false;
        } else {
            $mailingWithNotif = send_mail([
                'subject' => $param['subject'],
                'message' => $param['message'],
                'bcc' => $param['to'],
                'dataInvoice' => (!empty($param['dataInvoice'])) ? $param['dataInvoice'] : []
            ]);

            if (!empty($param['userId'])) {
                $parsing['db_customer'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_customer',
                    'where' => [
                        'customer_id' => $param['userId']
                    ]
                ])->row();

                $parsing['db_customer_school'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_customer_school',
                    'where' => [
                        'id_rajaongkir !=' => '0',
                    ],
                    'like' => [
                        'sekolah_id' => $parsing['db_customer']->sekolah_id,
                    ],
                ])->row();

                $dataCustomer = [
                    'user' => (!empty($parsing['db_customer']->lastname)) ? $parsing['db_customer']->firstname . ' ' . $parsing['db_customer']->lastname : $parsing['db_customer']->firstname,
                    'sekolah_id' => $parsing['db_customer']->sekolah_id,
                    'sekolah' => $parsing['db_customer_school']->nama_sekolah,
                ];
            } else {
                $dataCustomer = [];
            }

            if (!empty($param['mallId'])) {
                $parsing['db_mall'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_mall',
                    'where' => [
                        'mall_id' => $param['mallId']
                    ]
                ])->row();

                $dataMall = [
                    'mall' => $parsing['db_mall']->name
                ];
            } else {
                $dataMall = [];
            }

            $this->api_model->send_data([
                'data' => array_merge([
                    'user_id' => (!empty($param['userId'])) ? $param['userId'] : 0,
                    'mall_id' => (!empty($param['mallId'])) ? $param['mallId'] : 0,
                    'judul_notif' => $param['subject'],
                    'id_tautan' => $param['linkId'],
                    'isi_notif' => (!empty($param['textNotif'])) ? $param['textNotif'] : $param['message'],
                    'tgl_added' => date('Y-m-d H:i:s'),
                    'jenis' => $param['type'],
                    'ip' => $this->input->ip_address(),
                    'user_agent' => $this->agent->agent_string(),
                ], $dataCustomer, $dataMall),
                'table' => 'db_notification'
            ]);

            return $mailingWithNotif;
        }
    }

    public function getOrderTotalType($param)
    {
        if (empty($param)) {
            return 0;
        } else {
            $getValue = $this->api_model->select_data([
                'field' => 'value',
                'table' => 'db_order_total',
                'where' => [
                    'order_id' => $param['id'],
                    'code' => $param['code'],
                ],
            ])->row_array()['value'];

            return $getValue;
        }
    }
}
