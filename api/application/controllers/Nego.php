<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Nego extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index_get($id = null)
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if (empty($this->core['customer'])) {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_UNAUTHORIZED,
                    'message' => 'unauthorized',
                    'data' => [],
                ]);
            } else {
                if (empty($id)) {
                    $filters = $this->filter_nego();

                    if ($this->get('status') == null) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => 'parameter not found',
                            'data' => [],
                        ]);
                    } else {
                        if ($this->get('page') == null || $this->get('limit') == null) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_BAD_REQUEST,
                                'message' => 'page or limit not found',
                                'data' => [
                                    'total' => 0,
                                    'listNegotiation' => [],
                                    'filters' => $filters,
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
                                        'listNegotiation' => [],
                                        'filters' => $filters,
                                    ]
                                ]);
                            }
                        }
                    }

                    if ($checking === true) {
                        $param['db_nego']['field'] = '
                        aa.id_nego,
                        bb.price,
                        bb.image,
                        cc.seo as slug,
                        cc.name,
                        dd.name as mall_name,
                        aa.tgl_added,
                        aa.status,
                        max(gg.harga_tambahan) as harga_tambahan,
                        gg.harga as harga_nego,
                        gg.qty, 
                        gg.top';
                        $param['db_nego']['table'] = 'db_nego aa';
                        $param['db_nego']['join'] = [
                            [
                                'table' => 'db_product bb',
                                'on' => 'bb.product_id=aa.id_product',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_product_description cc',
                                'on' => 'cc.product_id=aa.id_product',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_mall dd',
                                'on' => 'dd.mall_id=aa.id_mall',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_nego_detail gg',
                                'on' => 'gg.id_nego=aa.id_nego',
                                'type' => 'inner'
                            ],
                        ];

                        $param['db_nego']['where'] = [
                            'aa.id_customer' => $this->core['customer']['id'],
                            'aa.status' => $this->get('status'),
                        ];

                        $param['db_nego']['group_by'] = 'aa.id_nego';
                        $param['db_nego']['order_by'] = [
                            'aa.tgl_added' => 'desc',
                        ];

                        $param['db_nego']['limit'] = [
                            $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                        ];
                        $parsing['db_nego'] = $this->api_model->select_data($param['db_nego'])->result();

                        $output = [];
                        if (empty($parsing['db_nego'])) {
                            $data['total'] = 0;
                            $data['listNegotiation'] = [];
                            $code = self::HTTP_NO_CONTENT;
                        } else {
                            $code = self::HTTP_OK;
                            $total_record = $this->api_model->count_all_data($param['db_nego']);

                            $limit = (int) $this->get('limit');
                            $current_page = (int) $this->get('page');
                            $total_page = ceil($total_record / $limit);

                            $data['page'] = $current_page;
                            $data['limit'] = $limit;
                            $data['total'] = $total_record;
                            $data['pages'] = $total_page;
                            $data['listNegotiation'] = [];

                            foreach ($parsing['db_nego'] as $key_db_nego) {
                                $tgl_added = explode(' ', $key_db_nego->tgl_added);

                                $listNegotiation['id'] = $key_db_nego->id_nego;
                                $listNegotiation['image'] = $this->core['url_image_product'] . $key_db_nego->image;
                                $listNegotiation['name'] = $key_db_nego->name;
                                $listNegotiation['slug'] = $key_db_nego->slug;
                                $listNegotiation['qty'] = $key_db_nego->qty;
                                $listNegotiation['date'] = date_indo($tgl_added[0]) . ' ' . $tgl_added[1];
                                $listNegotiation['price'] = $key_db_nego->price;
                                $listNegotiation['priceCurrencyFormat'] = rupiah($listNegotiation['price']);
                                $listNegotiation['priceNego'] = $key_db_nego->harga_nego;
                                $listNegotiation['priceNegoCurrencyFormat'] = rupiah($listNegotiation['priceNego']);

                                foreach ($filters as $key_filters) {
                                    if ($key_filters['id'] == $this->get('status')) {
                                        $listNegotiation['status'] = $key_filters['title'];
                                    }
                                }

                                $data['listNegotiation'][] = $listNegotiation;
                            }
                        }

                        foreach ($filters as $key_filters) {
                            $data['filters'][] = [
                                'title' => $key_filters['title'],
                                'id' => $key_filters['id'],
                                'type' => $key_filters['type'],
                                'inActive' => ($this->get('status') == $key_filters['id']) ? true : false,
                            ];
                        }

                        $output = $data;

                        $response = $this->formatter([
                            'code' => $code,
                            'message' => 'get data success',
                            'data' => $output
                        ]);
                    }
                } else {
                    if ($checking === true) {
                        $parsing['db_nego'] = $this->api_model->select_data([
                            'field' => '
                            aa.id_nego,
                            bb.price,
                            bb.image,
                            cc.seo as slug,
                            cc.name,
                            dd.mall_id,
                            dd.name as mall,
                            dd.slug as mall_slug,
                            dd.city as mall_city,
                            dd.province as mall_province,
                            aa.tgl_added,
                            aa.status,
                            max(gg.harga_tambahan) as harga_tambahan,
                            gg.harga as harga_nego,
                            gg.qty, 
                            gg.top,
                            gg.kurir,
                            gg.pembungkus,
                            gg.pembungkus_fee,
                            gg.asuransi,
                            gg.asuransi_fee,
                            aa.id_product,
                            aa.selesai',
                            'table' => 'db_nego aa',
                            'join' => [
                                [
                                    'table' => 'db_product bb',
                                    'on' => 'bb.product_id=aa.id_product',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_product_description cc',
                                    'on' => 'cc.product_id=aa.id_product',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_mall dd',
                                    'on' => 'dd.mall_id=aa.id_mall',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_nego_detail gg',
                                    'on' => 'gg.id_nego=aa.id_nego',
                                    'type' => 'inner'
                                ],
                            ],
                            'where' => [
                                'aa.id_customer' => $this->core['customer']['id'],
                                'aa.id_nego' => $id,
                            ],
                        ])->row();

                        if (empty($parsing['db_nego'])) {
                            $output = (object) [];
                            $code = self::HTTP_NO_CONTENT;
                        } else {
                            $code = self::HTTP_OK;
                            $output = [];

                            $tgl_added = explode(' ', $parsing['db_nego']->tgl_added);

                            if ($parsing['db_nego']->selesai == '1') {
                                $isNegoDone = true;
                            } else {
                                if ($parsing['db_nego']->status == '1') {
                                    $isNegoDone = true;
                                } else {
                                    $isNegoDone = true;
                                }
                            }

                            $listNegotiation['id'] = $parsing['db_nego']->id_nego;
                            $listNegotiation['isNegoReject'] = ($parsing['db_nego']->status == '2') ? true : false;
                            $listNegotiation['isNegoDone'] = $isNegoDone;
                            $listNegotiation['productId'] = $parsing['db_nego']->id_product;
                            $listNegotiation['date'] = date_indo($tgl_added[0]) . ' ' . $tgl_added[1];
                            $listNegotiation['image'] = $this->core['url_image_product'] . $parsing['db_nego']->image;
                            $listNegotiation['name'] = $parsing['db_nego']->name;
                            $listNegotiation['slug'] = $parsing['db_nego']->slug;

                            $listNegotiation['mall'] = [
                                'id' => $parsing['db_nego']->mall_id,
                                'name' => $parsing['db_nego']->mall,
                                'slug' => $parsing['db_nego']->mall_slug,
                                'city' => $parsing['db_nego']->mall_city,
                                'province' => $parsing['db_nego']->mall_province
                            ];

                            $listNegotiation['initialUnitPrice'] = $parsing['db_nego']->price;
                            $listNegotiation['initialUnitPriceCurrencyFormat'] = rupiah($listNegotiation['initialUnitPrice']);
                            $listNegotiation['negoUnitPrice'] = $parsing['db_nego']->harga_nego;
                            $listNegotiation['negoUnitPriceCurrencyFormat'] = rupiah($listNegotiation['negoUnitPrice']);
                            $listNegotiation['qty'] = $parsing['db_nego']->qty;
                            $listNegotiation['totalPrice'] = $listNegotiation['negoUnitPrice'] * $listNegotiation['qty'];
                            $listNegotiation['totalPriceCurrencyFormat'] = rupiah($listNegotiation['totalPrice']);
                            $listNegotiation['termOfPayment'] = $parsing['db_nego']->top . ' hari';
                            $listNegotiation['courier'] = strtoupper($parsing['db_nego']->kurir);
                            $listNegotiation['wrapping'] = $parsing['db_nego']->pembungkus;
                            $listNegotiation['packagingCost'] = $parsing['db_nego']->pembungkus_fee;
                            $listNegotiation['packagingCostCurrencyFormat'] = rupiah($listNegotiation['packagingCost']);
                            $listNegotiation['assurance'] = $parsing['db_nego']->asuransi . ' (' . rupiah($parsing['db_nego']->asuransi_fee) . ')';

                            $parsing['db_nego_detail'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_nego_detail',
                                'where' => [
                                    'id_nego' => $parsing['db_nego']->id_nego,
                                ],
                            ])->result();
                            $listNegotiation['history'] = [];
                            foreach ($parsing['db_nego_detail'] as $key_db_nego_detail) {
                                $history_tgl_added = explode(' ', $key_db_nego_detail->tgl_added);

                                $history['negoUnitPrice'] = $key_db_nego_detail->harga;
                                $history['negoUnitPriceCurrencyFormat'] = rupiah($history['negoUnitPrice']);
                                $history['qty'] = $key_db_nego_detail->qty;
                                $history['total'] = $history['negoUnitPrice'] * $history['qty'];
                                $history['totalCurrencyFormat'] = rupiah($history['total']);
                                $history['date'] = date_indo($history_tgl_added[0]) . ' ' . $history_tgl_added[1];
                                $history['priceResponse'] = $key_db_nego_detail->harga_tambahan;
                                $history['priceResponseCurrencyFormat'] = rupiah($history['priceResponse']);

                                $listNegotiation['history'][] = $history;
                            }

                            $listNegotiation['isResponded'] = (!empty($history['priceResponse'])) ? true : false;

                            $output = $listNegotiation;
                        }

                        $response = $this->formatter([
                            'code' => $code,
                            'message' => 'get data success',
                            'data' => $output
                        ]);
                    }
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function index_post()
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if (empty($this->core['customer'])) {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_UNAUTHORIZED,
                    'message' => 'unauthorized',
                ]);
            } else {
                if (!$this->post()) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                    ]);
                } else {
                    $check['db_product'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_product',
                        'where' => [
                            'product_id' => $this->post('productId'),
                        ]
                    ])->row();

                    if (empty($check['db_product'])) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_NOT_FOUND,
                            'message' => 'product not found',
                        ]);
                    }
                }
            }

            if ($checking === true) {
                $parsing['getProductDesc'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_product_description',
                    'where' => [
                        'product_id' => $this->post('productId'),
                    ]
                ])->row_array();

                $parsing['getMall'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_mall',
                    'where' => [
                        'mall_id' => $this->post('mallId'),
                    ]
                ])->row_array();

                $this->db->trans_start();

                $this->api_model->send_data([
                    'data' => [
                        'id_customer' => $this->core['customer']['id'],
                        'id_product' => $this->post('productId'),
                        'id_mall' => $this->post('mallId'),
                        'tgl_added' => date('Y-m-d H:i:s'),
                        'status' => 0,
                    ],
                    'table' => 'db_nego'
                ]);

                $lastId = $this->db->insert_id();

                $this->api_model->send_data([
                    'data' => [
                        'id_nego' => $lastId,
                        'qty' => $this->post('qty'),
                        'harga' => $this->post('priceNego'),
                        'top' => $this->post('termOfPayment'),
                        'kurir' => $this->post('courier'),
                        'pembungkus' => $this->post('wrapping'),
                        'asuransi' => $this->post('insurance'),
                        'spek' => $this->post('otherSpec'),
                        'tgl_added' => date('Y-m-d H:i:s'),
                        'siapa' => '0'
                    ],
                    'table' => 'db_nego_detail'
                ]);

                $this->db->trans_complete();

                if ($this->db->trans_status() === false) {
                    $db_error = $this->db->error();
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => "add data failed [Database error! Error Code [{$db_error['code']}] Error: {$db_error['message']}]",
                    ]);
                } else {
                    $messageOtherSpec = (!empty($this->post('otherSpec'))) ? ", spesifikasi {$this->post('otherSpec')}" : "";

                    $mailing = $this->mailingWithNotif([
                        'subject' => 'SIPLah - Permintaan Negosiasi',
                        'message' => "Permintaan negosiasi atas barang {$parsing['getProductDesc']['name']}, dengan kuantitas {$this->post('qty')}, harga " . rupiah($this->post('priceNego')) . "{$messageOtherSpec} telah diajukan kepada seller {$parsing['getMall']['name']}",
                        'to' => [
                            $this->core['customer']['email'],
                            $parsing['getMall']['email']
                        ],
                        'userId' => $this->core['customer']['id'],
                        'mallId' => $this->post('mallId'),
                        'linkId' => $lastId,
                        'type' => 'nego',
                    ]);

                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => "add data success",
                        'mailing' => $mailing,
                    ]);
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function respond_post()
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if (empty($this->core['customer'])) {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_UNAUTHORIZED,
                    'message' => 'unauthorized',
                ]);
            } else {
                if (!$this->post()) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                    ]);
                } else {
                    $check['db_nego'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_nego',
                        'where' => [
                            'id_nego' => $this->post('negoId'),
                        ]
                    ])->row();

                    if (empty($check['db_nego'])) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_NOT_FOUND,
                            'message' => 'data not found',
                        ]);
                    }
                }
            }

            if ($checking === true) {
                $parsing['db_nego'] = $this->api_model->select_data([
                    'field' => '
                    aa.id_nego,
                    bb.price,
                    bb.product_id,
                    bb.image,
                    cc.seo,
                    cc.name,dd.name as mall_name,
                    dd.email as mall_email,
                    dd.image as mall_image,
                    aa.id_mall,
                    aa.tgl_added,
                    aa.status,
                    aa.selesai,
                    dd.zone_2,
                    dd.city,
                    dd.province,
                    ee.firstname,
                    ee.avatar,
                    ff.nama_sekolah,
                    ff.kota,
                    gg.*',
                    'table' => 'db_nego aa',
                    'join' => [
                        [
                            'table' => 'db_product bb',
                            'on' => 'bb.product_id=aa.id_product',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_product_description cc',
                            'on' => 'cc.product_id=aa.id_product',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_mall dd',
                            'on' => 'dd.mall_id=aa.id_mall',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_customer ee',
                            'on' => 'ee.customer_id=aa.id_customer',
                            'type' => 'left'
                        ],
                        [
                            'table' => 'db_customer_school ff',
                            'on' => 'ff.sekolah_id=ee.sekolah_id',
                            'type' => 'left'
                        ],
                        [
                            'table' => 'db_nego_detail gg',
                            'on' => 'gg.id_nego=aa.id_nego',
                            'type' => 'inner'
                        ],
                    ],
                    'where' => [
                        'aa.id_nego' => $this->post('negoId'),
                    ]
                ])->row_array();

                $query = $this->api_model->send_data([
                    'data' => [
                        'id_nego' => $this->post('negoId'),
                        'qty' => $parsing['db_nego']['qty'],
                        'harga' => $this->post('priceNego'),
                        'top' => $parsing['db_nego']['top'],
                        'kurir' => $parsing['db_nego']['kurir'],
                        'pembungkus' => $parsing['db_nego']['pembungkus'],
                        'asuransi' => $parsing['db_nego']['asuransi'],
                        'spek' => $this->post('otherSpec'),
                        'tgl_added' => date('Y-m-d H:i:s'),
                        'siapa' => '0'
                    ],
                    'table' => 'db_nego_detail'
                ]);

                if ($query['error'] === true) {
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => "add data failed [{$query['system']}]",
                    ]);
                } else {
                    $messageOtherSpec = (!empty($this->post('otherSpec'))) ? ", spesifikasi {$this->post('otherSpec')}" : "";

                    $mailing = $this->mailingWithNotif([
                        'subject' => 'SIPLah - Permintaan Negosiasi',
                        'message' => "Permintaan negosiasi atas barang {$parsing['db_nego']['name']}, dengan kuantitas {$this->post('qty')}, harga " . rupiah($this->post('priceNego')) . "{$messageOtherSpec} telah diajukan kepada seller {$parsing['db_nego']['mall_name']}",
                        'to' => [
                            $this->core['customer']['email'],
                            $parsing['db_nego']['mall_email']
                        ],
                        'userId' => $this->core['customer']['id'],
                        'mallId' => $parsing['db_nego']['mall_id'],
                        'linkId' => $this->post('negoId'),
                        'type' => 'nego',
                    ]);

                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => "add data success",
                        'mailing' => $mailing,
                    ]);
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }
}
