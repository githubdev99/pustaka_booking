<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Complaint extends MY_Controller
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
                        $param['db_komplain']['field'] = '
                        aa.*, bb.invoice_no, cc.image, dd.name as nama_produk, ee.name as nama_toko, ff.komplain as nama_komplain';
                        $param['db_komplain']['table'] = 'db_komplain aa';
                        $param['db_komplain']['join'] = [
                            [
                                'table' => 'db_order bb',
                                'on' => 'aa.order_id=bb.order_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_product cc',
                                'on' => 'aa.product_id=cc.product_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_product_description dd',
                                'on' => 'cc.product_id=dd.product_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_mall ee',
                                'on' => 'aa.mall_id=ee.mall_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_komplain_kategori ff',
                                'on' => 'ff.id=aa.id_komplain_kategori',
                                'type' => 'inner'
                            ],
                        ];

                        $param['db_komplain']['where'] = [
                            'aa.customer_id' => $this->core['customer']['id'],
                        ];

                        $param['db_komplain']['group_by'] = 'aa.product_id';
                        $param['db_komplain']['order_by'] = [
                            'aa.id' => 'desc',
                        ];

                        $param['db_komplain']['limit'] = [
                            $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                        ];
                        $parsing['db_komplain'] = $this->api_model->select_data($param['db_komplain'])->result();

                        $output = [];
                        if (empty($parsing['db_komplain'])) {
                            $data['total'] = 0;
                            $data['items'] = [];
                            $code = self::HTTP_NO_CONTENT;
                        } else {
                            $code = self::HTTP_OK;
                            $total_record = $this->api_model->count_all_data($param['db_komplain']);

                            $limit = (int) $this->get('limit');
                            $current_page = (int) $this->get('page');
                            $total_page = ceil($total_record / $limit);

                            $data['page'] = $current_page;
                            $data['limit'] = $limit;
                            $data['total'] = $total_record;
                            $data['pages'] = $total_page;
                            $data['items'] = [];

                            foreach ($parsing['db_komplain'] as $key_db_komplain) {
                                $last_update = explode(' ', $key_db_komplain->last_update);

                                $items['orderId'] = $key_db_komplain->order_id;
                                $items['invoice'] = $key_db_komplain->invoice_no;
                                $items['productName'] = $key_db_komplain->nama_produk;
                                $items['storeName'] = $key_db_komplain->nama_toko;
                                $items['type'] = $key_db_komplain->nama_komplain;
                                $items['date'] = date_indo($last_update[0]) . ' ' . $last_update[1];

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
                } else {
                    if ($checking === true) {
                        $parsing['db_komplain'] = $this->api_model->select_data([
                            'field' => '
                            * ,d.id as komplain_id,
                            bb.name as nama_produk, bb.quantity as qty, b.value,c.value as tkirim, 
                            e.komplain as kategoriKomplain ,
                            d.komplain as komplains',
                            'table' => 'db_order aa',
                            'join' => [
                                [
                                    'table' => 'db_order_product bb',
                                    'on' => 'bb.order_id = aa.order_id',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => "(SELECT value,order_id FROM db_order_total where code='total') b",
                                    'on' => 'b.order_id = aa.order_id',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => "(SELECT value,order_id FROM db_order_total where code='shipping') c",
                                    'on' => 'c.order_id = aa.order_id',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_product cc',
                                    'on' => 'bb.product_id=cc.product_id',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_product_description dd',
                                    'on' => 'cc.product_id=dd.product_id',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_komplain d',
                                    'on' => 'd.order_id = aa.order_id',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_komplain_kategori e',
                                    'on' => 'e.id = d.id_komplain_kategori',
                                    'type' => 'inner'
                                ],
                            ],
                            'where' => [
                                'aa.order_id' => $id,
                            ],
                            'order_by' => [
                                'd.id' => 'desc',
                            ]
                        ])->row();

                        if (empty($parsing['db_komplain'])) {
                            $output = (object) [];
                            $code = self::HTTP_NO_CONTENT;
                        } else {
                            $output = [];
                            $code = self::HTTP_OK;

                            $parsing['complaintMessage'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_order aa',
                                'join' => [
                                    [
                                        'table' => 'db_komplain bb',
                                        'on' => 'bb.order_id = aa.order_id',
                                        'type' => 'inner'
                                    ],
                                    [
                                        'table' => 'db_komplain_detail cc',
                                        'on' => 'cc.id_komplain = bb.id',
                                        'type' => 'inner'
                                    ],
                                ],
                                'where' => [
                                    'aa.order_id' => $id,
                                ],
                            ])->result();

                            $last_update = explode(' ', $parsing['db_komplain']->last_update);

                            $data['orderId'] = $parsing['db_komplain']->order_id;
                            $data['mallId'] = $parsing['db_komplain']->mall_id;
                            $data['complaintId'] = $parsing['db_komplain']->komplain_id;

                            if ($parsing['complaintMessage'][0]->status == '0') {
                                $data['isComplaintResolved'] = false;
                            } else {
                                $data['isComplaintResolved'] = true;
                            }

                            $data['date'] = date_indo($last_update[0]) . ' ' . $last_update[1];
                            $data['product'] = [
                                'id' => $parsing['db_komplain']->product_id,
                                'name' => $parsing['db_komplain']->nama_produk,
                                'image' => (!empty($parsing['db_komplain']->image) || $parsing['db_komplain']->image != '') ? $this->core['url_image_product'] . $parsing['db_komplain']->image : $this->core['image_not_found'],
                                'price' => $parsing['db_komplain']->price,
                                'priceCurrencyFormat' => rupiah($parsing['db_komplain']->price),
                                'qty' => $parsing['db_komplain']->qty,
                                'shippingCost' => $parsing['db_komplain']->tkirim,
                                'shippingCostCurrencyFormat' => rupiah($parsing['db_komplain']->tkirim),
                                'total' => $parsing['db_komplain']->value,
                                'totalCurrencyFormat' => rupiah($parsing['db_komplain']->value),
                            ];
                            $data['complaint'] = [
                                'type' => $parsing['db_komplain']->kategoriKomplain,
                                'image' => (!empty($parsing['db_komplain']->gambar) || $parsing['db_komplain']->gambar != '') ? $this->core['url_image_complaint'] . $parsing['db_komplain']->gambar : $this->core['image_not_found'],
                                'main' => $parsing['db_komplain']->komplains,
                                'message' => [],
                            ];

                            foreach ($parsing['complaintMessage'] as $key_complaintMessage) {
                                $createdAt = explode(' ', $key_complaintMessage->created_time);

                                if ($key_complaintMessage->from == 'mall') {
                                    $name = 'Toko';
                                } elseif ($key_complaintMessage->from == 'admin') {
                                    $name = 'Admin';
                                } else {
                                    $name = 'User';
                                }

                                $complaintMessage['from'] = $key_complaintMessage->from;
                                $complaintMessage['name'] = $name;
                                $complaintMessage['message'] = $key_complaintMessage->message;
                                $complaintMessage['createdAt'] = date_indo($createdAt[0]) . ' ' . $createdAt[1];

                                $data['complaint']['message'][] = $complaintMessage;
                            }

                            $output = $data;
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
                    $check['db_komplain'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_komplain',
                        'where' => [
                            'id' => $this->post('complaintId'),
                        ]
                    ])->row_array();

                    if (empty($check['db_komplain'])) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_NOT_FOUND,
                            'message' => 'data not found',
                        ]);
                    }
                }
            }

            if ($checking === true) {
                $parsing['getMall'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_mall',
                    'where' => [
                        'mall_id' => $check['db_komplain']['mall_id']
                    ]
                ])->row_array();
                $parsing['getOrder'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_order',
                    'where' => [
                        'order_id' => $check['db_komplain']['order_id']
                    ]
                ])->row_array();

                $query = $this->api_model->send_data([
                    'data' => [
                        'id_komplain' => $this->post('complaintId'),
                        'message' => $this->post('message'),
                        'from' => 'user',
                        'created_time' => date('Y-m-d H:i:s'),
                    ],
                    'table' => 'db_komplain_detail'
                ]);

                if ($query['error'] === true) {
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => "add data failed [{$query['system']}]",
                    ]);
                } else {
                    $mailing = $this->mailingWithNotif([
                        'subject' => 'Komplain Pembeli',
                        'message' => "Pembeli telah mengirimkan komplain untuk kode pesanan {$parsing['getOrder']['invoice_no']} dengan isi komplain '{$this->post('message')}'",
                        'to' => [
                            $parsing['getMall']['email']
                        ],
                        'textNotif' => 'Seorang pembeli mengirimkan komplain untuk pesanan dari toko anda',
                        'mallId' => $check['db_komplain']['mall_id'],
                        'linkId' => $this->post('complaintId'),
                        'type' => 'komplain',
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
