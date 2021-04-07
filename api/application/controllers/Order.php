<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Order extends MY_Controller
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
                    $filters = $this->filter_order();

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
                                    'orders' => [],
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
                                        'orders' => [],
                                        'filters' => $filters,
                                    ]
                                ]);
                            }
                        }
                    }

                    if ($checking === true) {
                        $param['db_order']['field'] = '
                        aa.invoice_no, 
                        aa.date_added,
                        aa.order_id,
                        SUM(aa.total) as total,
                        aa.payment_method,
                        aa.order_status_id,
                        CONCAT(c.firstname, " ", c.lastname) as pemesan,
                        c.jabatan,
                        tot.value as total,
                        db_order_status.name as status,
                        aa.mall_id,
                        aa.mall_name,
                        aa.mall_province,
                        aa.mall_city';
                        $param['db_order']['table'] = 'db_order aa';
                        $param['db_order']['join'] = [
                            [
                                'table' => 'db_customer c',
                                'on' => 'c.customer_id=aa.customer_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_order_total tot',
                                'on' => 'tot.order_id=aa.order_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_order_status',
                                'on' => 'db_order_status.order_status_id=aa.order_status_id',
                                'type' => 'inner'
                            ],
                        ];

                        $param['db_order']['where'] = [
                            'aa.order_status_id' => $this->get('status'),
                            'aa.sekolah_id' => $this->core['customer']['school']['id'],
                            'tot.code' => 'total',
                        ];

                        $param['db_order']['group_by'] = 'aa.invoice_no';
                        $param['db_order']['order_by'] = [
                            'aa.date_added' => 'desc',
                        ];

                        $param['db_order']['limit'] = [
                            $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                        ];
                        $parsing['db_order'] = $this->api_model->select_data($param['db_order'])->result();

                        $output = [];
                        if (empty($parsing['db_order'])) {
                            $data['total'] = 0;
                            $data['orders'] = [];
                            $code = self::HTTP_NO_CONTENT;
                        } else {
                            $code = self::HTTP_OK;
                            $total_record = $this->api_model->count_all_data($param['db_order']);

                            $limit = (int) $this->get('limit');
                            $current_page = (int) $this->get('page');
                            $total_page = ceil($total_record / $limit);

                            $data['page'] = $current_page;
                            $data['limit'] = $limit;
                            $data['total'] = $total_record;
                            $data['pages'] = $total_page;
                            $data['orders'] = [];

                            foreach ($parsing['db_order'] as $key_db_order) {
                                $date_added = explode(' ', $key_db_order->date_added);

                                $orders['id'] = $key_db_order->order_id;
                                $orders['invoice'] = $key_db_order->invoice_no;
                                $orders['orderDate'] = date_indo($date_added[0]) . ' ' . $date_added[1];
                                $orders['customerName'] = $key_db_order->pemesan;
                                $orders['total'] = $key_db_order->total;
                                $orders['totalCurrencyFormat'] = rupiah($orders['total']);
                                $orders['status'] = $key_db_order->status;

                                $parsing['db_mall'] = $this->api_model->select_data([
                                    'field' => '*',
                                    'table' => 'db_mall',
                                    'where' => [
                                        'mall_id' => $key_db_order->mall_id,
                                    ],
                                ])->row();
                                $orders['mall'] = [
                                    'id' => $key_db_order->mall_id,
                                    'name' => $key_db_order->mall_name,
                                    'slug' => $parsing['db_mall']->slug,
                                    'image' => (!empty($parsing['db_mall']->image) || $parsing['db_mall']->image != '') ? $this->core['url_image_mall'] . $parsing['db_mall']->image : $this->core['image_not_found'],
                                    'location' => $key_db_order->mall_city . ', ' . $key_db_order->mall_province,
                                ];

                                $orders['totalProduct'] = $this->api_model->count_all_data([
                                    'table' => 'db_order_product',
                                    'where' => [
                                        'order_id' => $key_db_order->order_id,
                                    ],
                                ]);

                                $parsing['db_order_product'] = $this->api_model->select_data([
                                    'field' => '
                                    aa.name,
                                    aa.quantity,
                                    aa.price,
                                    aa.order_id,
                                    bb.image,
                                    bb.product_id',
                                    'table' => 'db_order_product aa',
                                    'join' => [
                                        [
                                            'table' => 'db_product bb',
                                            'on' => 'bb.product_id=aa.product_id',
                                            'type' => 'left'
                                        ],
                                    ],
                                    'where' => [
                                        'aa.order_id' => $key_db_order->order_id,
                                    ],
                                    'limit' => 1,
                                ])->result();
                                $orders['items'] = [];
                                foreach ($parsing['db_order_product'] as $key_db_order_product) {
                                    $items['name'] = $key_db_order_product->name;
                                    $items['image'] = (!empty($key_db_order_product->image) || $key_db_order_product->image != '') ? $this->core['url_image_product'] . $key_db_order_product->image : $this->core['image_not_found'];
                                    $items['qty'] = $key_db_order_product->quantity;
                                    $items['price'] = $key_db_order_product->price;
                                    $items['priceCurrencyFormat'] = rupiah($items['price']);
                                    $items['totalPrice'] = $items['price'] * $items['qty'];
                                    $items['totalPriceCurrencyFormat'] = rupiah($items['totalPrice']);

                                    $orders['items'][] = $items;
                                }

                                $data['orders'][] = $orders;
                            }
                        }

                        foreach ($filters as $key_filters) {
                            $data['filters'][] = [
                                'title' => $key_filters['title'],
                                'id' => $key_filters['id'],
                                'total' => $key_filters['total'],
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
                        $parsing['db_order'] = $this->api_model->select_data([
                            'field' => '
                            aa.order_id,
                            aa.invoice_no,
                            aa.invoice_prefix,
                            aa.mall_id,
                            aa.customer_id,
                            aa.shipping_zone,
                            aa.payment_method,
                            aa.payment_va,
                            aa.berattotal,
                            aa.subtotal,
                            aa.ongkoskirim,
                            aa.subtotal_final,
                            aa.awb,
                            aa.shipping_id AS alamat_id,
                            aa.shipping_code,
                            aa.date_added,
                            aa.comment,
                            aa.order_status_id,
                            aa.mall_name,
                            aa.shipping_company,
                            aa.shipping_address_1,
                            aa.shipping_address_2,
                            aa.telephone,
                            aa.shipping_province,
                            aa.shipping_postcode,
                            aa.shipping_city,
                            aa.shipping_firstname,
                            aa.shipping_lastname,
                            aa.total,
                            aa.withdraw_status,
                            aa.email,
                            aa.konfirm_bayar,
                            aa.payment_tempo,
                            aa.denda_date,
                            aa.denda_bayar,
                            aa.denda_hari,
                            aa.mall_zone,
                            aa.mall_city,
                            aa.mall_province,
                            aa.mall_postcode,
                            aa.mall_address,
                            aa.date_added as tgl_order,
                            cc.nama_pic as mall_pic,
                            cc.telp_pic as mall_phone',
                            'table' => 'db_order aa',
                            'join' => [
                                [
                                    'table' => 'db_mall cc',
                                    'on' => 'cc.mall_id=aa.mall_id',
                                    'type' => 'left'
                                ],
                            ],
                            'where' => [
                                'aa.order_id' => $id,
                                'aa.sekolah_id' => $this->core['customer']['school']['id'],
                            ],
                        ])->row();

                        if (empty($parsing['db_order'])) {
                            $output = (object) [];
                            $code = self::HTTP_NO_CONTENT;
                        } else {
                            $output = [];
                            $code = self::HTTP_OK;

                            $parsing['getBast'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_order_bast',
                                'where' => [
                                    'order_id' => $parsing['db_order']->order_id
                                ]
                            ])->row_array();

                            $date_added = explode(' ', $parsing['db_order']->date_added);

                            $orders['id'] = $parsing['db_order']->order_id;
                            $orders['isCancelOrder'] = ($parsing['db_order']->order_status_id == '0') ? true : false;
                            $orders['isConfirmPayment'] = ($parsing['db_order']->order_status_id == '17' || $parsing['db_order']->order_status_id == '18') ? true : false;

                            if (empty($parsing['getBast'])) {
                                $isEbast = false;
                            } else {
                                $isEbast = ($parsing['db_order']->order_status_id == '3' || $parsing['db_order']->order_status_id == '4' || $parsing['db_order']->order_status_id == '5' || $parsing['db_order']->order_status_id == '17' || $parsing['db_order']->order_status_id == '18' || $parsing['db_order']->order_status_id == '20') ? true : false;
                            }

                            $orders['isEbast'] = $isEbast;
                            $orders['invoice'] = $parsing['db_order']->invoice_no;
                            $orders['orderDate'] = date_indo($date_added[0]) . ' ' . $date_added[1];

                            if ($parsing['db_order']->order_status_id == '17') {
                                $nextStep = 'Wajib upload bukti pembayaran dibawah ini untuk konfirmasi pembayaran';
                            } elseif ($parsing['db_order']->order_status_id == '0') {
                                $nextStep = 'Tunggu hingga pesanan diproses penyedia';
                            } elseif ($parsing['db_order']->order_status_id == '2') {
                                $nextStep = 'Pesanan sedang diproses penyedia';
                            } elseif ($parsing['db_order']->order_status_id == '3') {
                                $nextStep = 'Pesanan sedang dalam perjalanan';
                            } elseif ($parsing['db_order']->order_status_id == '4' || $parsing['db_order']->order_status_id == '5') {
                                if (empty($parsing['getBast'])) {
                                    $nextStep = 'Data <b>eBAST</b> tidak ditemukan<br>Silahkan hubungi penyedia untuk mengirim ulang eBAST';
                                } else {
                                    $nextStep = 'Wajib isi <b>eBAST</b> dibawah ini sebagai bukti tanda terima';
                                }
                            } elseif ($parsing['db_order']->order_status_id == '17') {
                                $nextStep = 'Pesanan telah ditagihkan, lakukan pembayaran lalu segera konfirmasi pembayaran';
                            } elseif ($parsing['db_order']->order_status_id == '18') {
                                $nextStep = 'Pesanan telah dibayarkan, menuju ke halaman pembayaran';
                            } else {
                                $nextStep = 'Aksi belum tersedia';
                            }

                            $orders['nextStep'] = $nextStep;

                            $parsing['db_order_history'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_order_history aa',
                                'join' => [
                                    [
                                        'table' => 'db_order_status bb',
                                        'on' => 'bb.order_status_id=aa.order_status_id',
                                        'type' => 'inner'
                                    ],
                                ],
                                'where' => [
                                    'aa.order_id' => $parsing['db_order']->order_id,
                                ],
                                'order_by' => [
                                    'aa.order_history_id' => 'desc'
                                ],
                            ])->result();
                            foreach ($parsing['db_order_history'] as $key_db_order_history) {
                                $createdAt = explode(' ', $key_db_order_history->date_added);
                                $orders['orderHistory'][] = [
                                    'title' => $key_db_order_history->name,
                                    'text' => $key_db_order_history->comment,
                                    'createdAt' => date_indo($createdAt[0]) . ' ' . $createdAt[1],
                                ];
                            }

                            $parsing['db_bank'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_bank',
                                'where' => [
                                    'slug' => $parsing['db_order']->payment_method,
                                ],
                            ])->row();
                            $orders['billingDetail'] = [
                                'paymentMethod' => ($parsing['db_bank']->bank == 'Virtual Account Mandiri') ? 'Virtual Account' : 'Bank Transfer',
                                'paymentDue' => $parsing['db_order']->payment_tempo . ' hari',
                                'bank' => $parsing['db_bank']->bank,
                                'virtualAccountNumber' => (!empty($parsing['db_order']->payment_va)) ? $parsing['db_order']->payment_va : $parsing['db_bank']->no_rek,
                                'asName' => 'PT. Eureka Bookhouse',
                                'shipping' => strtoupper($parsing['db_order']->shipping_code),
                                'total' => $parsing['db_order']->total,
                                'totalCurrencyFormat' => rupiah($parsing['db_order']->total),
                            ];

                            $orders['shippingAddress'] = [
                                'to' => $parsing['db_order']->shipping_company,
                                'pic' => $parsing['db_order']->shipping_firstname . ' ' . $parsing['db_order']->shipping_lastname,
                                'address' => $parsing['db_order']->shipping_address_2,
                                'addressDetail' => $parsing['db_order']->shipping_zone . ', ' . $parsing['db_order']->shipping_city . ', ' . $parsing['db_order']->shipping_province . ', ' . $parsing['db_order']->shipping_postcode,
                                'phone' => $parsing['db_order']->telephone,
                            ];

                            $parsing['db_mall'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_mall',
                                'where' => [
                                    'mall_id' => $parsing['db_order']->mall_id,
                                ],
                            ])->row();
                            $orders['mall'] = [
                                'id' => $parsing['db_order']->mall_id,
                                'name' => $parsing['db_order']->mall_name,
                                'slug' => $parsing['db_mall']->slug,
                                'image' => (!empty($parsing['db_mall']->image) || $parsing['db_mall']->image != '') ? $this->core['url_image_mall'] . $parsing['db_mall']->image : $this->core['image_not_found'],
                                'address' => $parsing['db_order']->mall_address,
                                'location' => $parsing['db_order']->mall_city . ', ' . $parsing['db_order']->mall_province . ', ' . $parsing['db_order']->mall_postcode,
                                'pic' => $parsing['db_order']->mall_pic,
                                'phone' => (!empty($parsing['db_order']->mall_phone)) ? $parsing['db_order']->mall_phone : null,
                            ];

                            $parsing['db_order_status'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_order_status',
                                'where' => [
                                    'order_status_id' => $parsing['db_order']->order_status_id,
                                ],
                            ])->row();
                            $orders['status'] = [
                                'name' => $parsing['db_order_status']->name,
                                'detail' => $parsing['db_order_status']->komentar,
                                'orderStatus' => $parsing['db_order_status']->status_transaksi,
                            ];

                            $parsing['db_order_pembayaran'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_order_pembayaran aa',
                                'join' => [
                                    [
                                        'table' => 'db_status_pembayaran bb',
                                        'on' => 'bb.id=aa.id_status_pembayaran',
                                        'type' => 'inner'
                                    ],
                                ],
                                'where' => [
                                    'aa.order_id' => $parsing['db_order']->order_id,
                                ],
                            ])->row();
                            if ($orders['isConfirmPayment'] === false || empty($parsing['db_order_pembayaran'])) {
                                $orders['confirmPayment'] = (object) [];
                            } else {
                                $orders['confirmPayment'] = [
                                    'status' => $parsing['db_order_pembayaran']->singkat,
                                    'invoice' => $parsing['db_order_pembayaran']->invoice_no,
                                    'method' => $parsing['db_order_pembayaran']->metode,
                                    'accountNumber' => $parsing['db_order_pembayaran']->no_rek_pembeli,
                                    'asName' => $parsing['db_order_pembayaran']->an_rek_pembeli,
                                    'date' => $parsing['db_order_pembayaran']->date_created,
                                    'memo' => $parsing['db_order_pembayaran']->memo,
                                    'datePayment' => $parsing['db_order_pembayaran']->tgl_pembayaran,
                                    'total' => $parsing['db_order_pembayaran']->total_pembayaran,
                                    'totalCurrencyFormat' => rupiah($parsing['db_order_pembayaran']->total_pembayaran),
                                    'adminFee' => $parsing['db_order_pembayaran']->biaya_admin,
                                    'adminFeeCurrencyFormat' => rupiah($parsing['db_order_pembayaran']->biaya_admin),
                                    'ppn' => $parsing['db_order_pembayaran']->ppn,
                                    'image' => $this->core['url_image_confirm_payment'] . $parsing['db_order_pembayaran']->img_upload,
                                ];
                            }

                            if (!$orders['isEbast']) {
                                $orders['eBast'] = (object) [];
                            } else {
                                $parsing['db_order_bast'] = $this->api_model->select_data([
                                    'field' => '
                                    aa.*,bb.invoice_no,bb.shipping_firstname,bb.shipping_company,bb.shipping_address_2,bb.shipping_zone,bb.shipping_kecamatan,bb.shipping_city,bb.shipping_province,bb.shipping_postcode,
                                    bb.shipping_lastname,cc.name as mall_name,
                                    bb.mall_address,bb.mall_zone,bb.mall_city,bb.mall_province,bb.mall_postcode,bb.telephone,
                                    dd.img_upload as img_struk,
                                    cc.nama_pic,
                                    cc.jabatan_pic,
                                    dd.*',
                                    'table' => 'db_order_bast aa',
                                    'join' => [
                                        [
                                            'table' => 'db_order bb',
                                            'on' => 'bb.order_id=aa.order_id',
                                            'type' => 'inner'
                                        ],
                                        [
                                            'table' => 'db_mall cc',
                                            'on' => 'cc.mall_id=bb.mall_id',
                                            'type' => 'inner'
                                        ],
                                        [
                                            'table' => 'db_order_pembayaran dd',
                                            'on' => 'dd.order_id=aa.order_id',
                                            'type' => 'left'
                                        ],
                                    ],
                                    'where' => [
                                        'aa.order_id' => $parsing['db_order']->order_id,
                                    ],
                                    'order_by' => [
                                        'dd.id' => 'desc'
                                    ],
                                ])->row();

                                $parsing['firstItem'] = $this->api_model->select_data([
                                    'field' => '
                                    aa.*, bb.name as nama_produk, bb.quantity as qty, b.value,c.value as tkirim',
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
                                            'table' => "db_product cc",
                                            'on' => 'bb.product_id=cc.product_id',
                                            'type' => 'inner'
                                        ],
                                        [
                                            'table' => "db_product_description dd",
                                            'on' => 'cc.product_id=dd.product_id',
                                            'type' => 'inner'
                                        ],
                                    ],
                                    'where' => [
                                        'aa.order_id' => $parsing['db_order']->order_id,
                                    ],
                                ])->row();

                                $eBast_tgl_buat = explode(' ', $parsing['db_order_bast']->tgl_buat);
                                $eBast_business_type = (!empty($parsing['db_order_bast']->jenis_usaha)) ? "({$parsing['db_order_bast']->jenis_usaha})" : "";
                                $orders['eBast'] = [
                                    'date' => day_indo(date('D', strtotime($eBast_tgl_buat[0]))) . ', ' . date_indo($eBast_tgl_buat[0]),
                                    'bastNumber' => $parsing['db_order_bast']->bast_no,
                                    'firstParty' => [
                                        'name' => $parsing['db_order_bast']->nama_pic,
                                        'position' => $parsing['db_order_bast']->jabatan_pic,
                                        'companyName' => $parsing['db_order_bast']->mall_name . $eBast_business_type,
                                        'address' => $parsing['db_order_bast']->mall_address . ', ' . $parsing['db_order_bast']->mall_zone . ', ' . $parsing['db_order_bast']->mall_city . ', ' . $parsing['db_order_bast']->mall_province . ', ' . $parsing['db_order_bast']->mall_postcode,
                                    ],
                                    'secondParty' => [
                                        'name' => $parsing['db_order_bast']->shipping_firstname,
                                        'position' => 'Bendahara BOS',
                                        'schoolName' => $parsing['db_order_bast']->shipping_company,
                                        'address' => $parsing['db_order_bast']->shipping_address_2 . ', ' . $parsing['db_order_bast']->shipping_zone . ', ' . $parsing['db_order_bast']->shipping_kecamatan . ', ' . $parsing['db_order_bast']->shipping_city . ', ' . $parsing['db_order_bast']->shipping_province . ', ' . $parsing['db_order_bast']->shipping_postcode,
                                        'phone' => (!empty($parsing['db_order_bast']->telephone)) ? $parsing['db_order_bast']->telephone : null,
                                    ],
                                    'shippingPriceCurrencyFormat' => rupiah($this->getOrderTotalType([
                                        'id' => $parsing['db_order']->order_id,
                                        'code' => 'shipping'
                                    ])),
                                    'totalPriceCurrencyFormat' => rupiah($this->getOrderTotalType([
                                        'id' => $parsing['db_order']->order_id,
                                        'code' => 'total'
                                    ])),
                                    'complaintCategory' => [
                                        [
                                            'value' => '1',
                                            'name' => 'Kualitas Produk'
                                        ],
                                        [
                                            'value' => '2',
                                            'name' => 'Pembungkus'
                                        ],
                                        [
                                            'value' => '3',
                                            'name' => 'Pengiriman'
                                        ],
                                        [
                                            'value' => '4',
                                            'name' => 'Harga Produk'
                                        ],
                                        [
                                            'value' => '5',
                                            'name' => 'Respon Penjual'
                                        ],
                                    ]
                                ];
                            }

                            $parsing['db_order_product'] = $this->api_model->select_data([
                                'field' => '
                                aa.order_product_id,
                                aa.order_id,
                                aa.product_id,
                                aa.mall_id,
                                aa.name,
                                aa.model,
                                aa.quantity,
                                aa.id_nego,
                                aa.id_banding,
                                aa.price, 
                                aa.total,
                                aa.tax,
                                aa.reward,
                                aa.qty_terima_baik,
                                aa.qty_terima_buruk,
                                bb.price1,
                                bb.price2,
                                bb.price3,
                                bb.price4,
                                bb.price5,
                                bb.grosir_price1,
                                bb.grosir_price2,
                                bb.grosir_price3,
                                bb.grosir_price4,
                                bb.grosir_min1,
                                bb.grosir_min2,
                                bb.grosir_min3,
                                bb.grosir_min4,bb.ppn,
                                bb.image,
                                cc.seo,
                                aa.note_terima',
                                'table' => 'db_order_product aa',
                                'join' => [
                                    [
                                        'table' => 'db_product bb',
                                        'on' => 'bb.product_id=aa.product_id',
                                        'type' => 'inner'
                                    ],
                                    [
                                        'table' => 'db_product_description cc',
                                        'on' => 'cc.product_id=bb.product_id',
                                        'type' => 'inner'
                                    ],
                                ],
                                'where' => [
                                    'aa.order_id' => $parsing['db_order']->order_id,
                                ],
                            ])->result();
                            $orders['items'] = [];
                            foreach ($parsing['db_order_product'] as $key_db_order_product) {
                                $items['id'] = $key_db_order_product->order_product_id;
                                $items['productId'] = $key_db_order_product->product_id;
                                $items['name'] = $key_db_order_product->name;
                                $items['image'] = (!empty($key_db_order_product->image) || $key_db_order_product->image != '') ? $this->core['url_image_product'] . $key_db_order_product->image : $this->core['image_not_found'];
                                $items['qty'] = $key_db_order_product->quantity;
                                $items['amountGoodCondition'] = ($parsing['getBast']['setuju_terima'] == '1') ? $key_db_order_product->qty_terima_baik : null;
                                $items['amountBadCondition'] = ($parsing['getBast']['setuju_terima'] == '1') ? $key_db_order_product->qty_terima_buruk : null;
                                $items['price'] = $key_db_order_product->price;
                                $items['priceCurrencyFormat'] = rupiah($items['price']);
                                $items['totalPrice'] = $items['price'] * $items['qty'];
                                $items['totalPriceCurrencyFormat'] = rupiah($items['totalPrice']);
                                $items['note'] = (!empty($key_db_order_product->note_terima)) ? $key_db_order_product->note_terima : null;

                                $orders['items'][] = $items;
                            }

                            $parsing['db_order_total'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_order_total',
                                'where' => [
                                    'order_id' => $parsing['db_order']->order_id,
                                ],
                                'order_by' => [
                                    'sort_order' => 'asc'
                                ],
                            ])->result();
                            foreach ($parsing['db_order_total'] as $key_db_order_total) {
                                if ($key_db_order_total->title != 'Biaya Tambahan') {
                                    $orders['descriptionTotal'][] = [
                                        $key_db_order_total->title, rupiah($key_db_order_total->value)
                                    ];
                                }
                            }

                            $orders['paymentMethodSelected'] = (object) [];

                            $orders['paymentMethod'] = [
                                [
                                    'group' => 'Virtual Account',
                                    'items' => [
                                        [
                                            'value' => 'bank_mandiri_va',
                                            'name' => 'Virtual Account Mandiri',
                                            'isSelected' => ($parsing['db_order']->payment_method == 'bank_mandiri_va') ? true : false,
                                        ],
                                    ]
                                ]
                            ];

                            if ($parsing['db_order']->mall_name == 'EUREKA TRIAL') {
                                $orders['paymentMethod'][0]['items'][] = [
                                    'value' => 'bank_bri_va',
                                    'name' => 'Virtual Account BRI (BRIVA)',
                                    'isSelected' => ($parsing['db_order']->payment_method == 'bank_bri_va') ? true : false,
                                ];
                            }

                            if ($parsing['db_order']->payment_method == 'bank_mandiri_va') {
                                $orders['paymentMethodSelected'] = [
                                    'label' => 'Virtual Account Mandiri',
                                    'value' => 'bank_mandiri_va'
                                ];
                            } elseif ($parsing['db_order']->payment_method == 'bank_bri_va') {
                                $orders['paymentMethodSelected'] = [
                                    'label' => 'Virtual Account BRI (BRIVA)',
                                    'value' => 'bank_bri_va'
                                ];
                            }

                            $parsing['db_bank'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_bank',
                                'where' => [
                                    'status' => '1',
                                ]
                            ])->result();
                            $orders['paymentMethod'][] = [
                                'group' => 'Bank Transfer',
                                'items' => []
                            ];
                            foreach ($parsing['db_bank'] as $key_db_bank) {
                                if ($key_db_bank->slug != 'bank_mandiri_va') {
                                    $orders['paymentMethod'][1]['items'][] = [
                                        'value' => $key_db_bank->slug,
                                        'name' => $key_db_bank->bank,
                                        'isSelected' => ($parsing['db_order']->payment_method == $key_db_bank->slug) ? true : false,
                                    ];

                                    if ($parsing['db_order']->payment_method == $key_db_bank->slug) {
                                        $orders['paymentMethodSelected'] = [
                                            'label' => $key_db_bank->bank,
                                            'value' => $key_db_bank->slug
                                        ];
                                    }
                                }
                            }

                            $output = $orders;
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

    public function cancel_put()
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
                if (!$this->put()) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                    ]);
                } else {
                    $check['db_order'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_order',
                        'where' => [
                            'order_id' => $this->put('orderId'),
                        ]
                    ])->row();

                    if (empty($check['db_order'])) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_NOT_FOUND,
                            'message' => 'data not found',
                        ]);
                    }
                }
            }

            if ($checking === true) {
                $query = $this->api_model->send_data([
                    'where' => [
                        'order_id' => $this->put('orderId'),
                    ],
                    'data' => [
                        'order_status_id' => '7'
                    ],
                    'table' => 'db_order'
                ]);

                if ($query['error'] === true) {
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => "edit data failed [{$query['system']}]",
                    ]);
                } else {
                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => "edit data success",
                    ]);
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function accept_put()
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
                if (!$this->put()) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                    ]);
                } else {
                    $check['db_order'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_order',
                        'where' => [
                            'order_id' => $this->put('orderId')
                        ]
                    ])->row_array();
                    if (empty($check['db_order'])) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_NOT_FOUND,
                            'message' => 'data not found',
                        ]);
                    } else {
                        if ($check['db_order']['order_status_id'] == '17' || $check['db_order']['order_status_id'] == '19') {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_CONFLICT,
                                'message' => 'order has answered',
                            ]);
                        } else {
                            $getOrder = json_decode(shoot_api([
                                'url' => base_url() . "order/{$this->put('orderId')}",
                                'method' => 'GET',
                                'header' => [
                                    "Authorization: {$this->input->request_headers()['Authorization']}"
                                ],
                            ]), true);
                            if ($getOrder['status']['code'] !== 200) {
                                $checking = false;
                                $response = $this->formatter([
                                    'code' => $getOrder['status']['code'],
                                    'message' => $getOrder['status']['message']
                                ]);
                            } else {
                                $eBast = $this->put('eBast');
                                $iEbast = 0;
                                foreach ($getOrder['data']['items'] as $key_getOrder) {
                                    $amount = $eBast[$iEbast]['amountGoodCondition'] + $eBast[$iEbast]['amountBadCondition'];
                                    if ($key_getOrder['qty'] != $amount) {
                                        $checking = false;
                                        $response = $this->formatter([
                                            'code' => self::HTTP_BAD_REQUEST,
                                            'message' => 'amount is not same',
                                        ]);
                                    }

                                    $iEbast++;
                                }
                            }
                        }
                    }
                }
            }

            if ($checking === true) {
                $parsing['getMall'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_mall',
                    'where' => [
                        'mall_id' => $check['db_order']['mall_id']
                    ]
                ])->row_array();

                $this->db->trans_start();

                $iEbast = 0;
                foreach ($getOrder['data']['items'] as $key_getOrder) {
                    $this->api_model->send_data([
                        'where' => [
                            'order_id' => $this->put('orderId'),
                            'order_product_id' => $key_getOrder['id'],
                        ],
                        'data' => [
                            'qty_terima_baik' => $eBast[$iEbast]['amountGoodCondition'],
                            'qty_terima_buruk' => $eBast[$iEbast]['amountBadCondition'],
                            'note_terima' => $eBast[$iEbast]['note'],
                        ],
                        'table' => 'db_order_product'
                    ]);

                    $iEbast++;
                }

                $this->api_model->send_data([
                    'where' => [
                        'order_id' => $this->put('orderId')
                    ],
                    'data' => [
                        'order_status_id' => 17
                    ],
                    'table' => 'db_order'
                ]);

                $this->api_model->send_data([
                    'where' => [
                        'order_id' => $this->put('orderId')
                    ],
                    'data' => [
                        'setuju_terima' => 1
                    ],
                    'table' => 'db_order_bast'
                ]);

                $this->api_model->send_data([
                    'data' => [
                        'order_id' => $this->put('orderId'),
                        'order_status_id' => 5,
                        'notify' => 0,
                        'comment' => 'Pembeli telah menerima pemesanan dengan baik',
                        'date_added' => date('Y-m-d H:i:s'),
                    ],
                    'table' => 'db_order_history'
                ]);

                $this->db->trans_complete();

                if ($this->db->trans_status() === false) {
                    $db_error = $this->db->error();
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => "edit data failed [Database error! Error Code [{$db_error['code']}] Error: {$db_error['message']}]",
                    ]);
                } else {
                    $mailing = $this->mailingWithNotif([
                        'subject' => 'Pesanan diterima oleh pembeli',
                        'message' => "Pesanan dengan kode pesanan {$check['db_order']['invoice_no']} telah diterima oleh pembeli",
                        'to' => [
                            $parsing['getMall']['email']
                        ],
                        'mallId' => $check['db_order']['mall_id'],
                        'linkId' => $this->put('orderId'),
                        'type' => 'order',
                        'dataInvoice' => [
                            'invoiceNumber' => $getOrder['data']['invoice'],
                            'items' => $getOrder['data']['items'],
                            'descriptionTotal' => $getOrder['data']['descriptionTotal'],
                        ]
                    ]);

                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => "edit data success",
                        'mailing' => $mailing,
                    ]);
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function penalty_put()
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
                if (!$this->put()) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                    ]);
                } else {
                    $check['db_order'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_order',
                        'where' => [
                            'order_id' => $this->put('orderId')
                        ]
                    ])->row_array();
                    if (empty($check['db_order'])) {
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
                        'mall_id' => $check['db_order']['mall_id']
                    ]
                ])->row_array();
                $parsing['getTotalPenalty'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_order_total',
                    'where' => [
                        'order_id' => $this->put('orderId'),
                        'code' => 'denda',
                    ]
                ])->row_array();

                $parsing['getAllTotal'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_order_total',
                    'where' => [
                        'order_id' => $this->put('orderId'),
                        'code !=' => 'total',
                    ]
                ])->result();
                $getAllTotal = [];
                foreach ($parsing['getAllTotal'] as $key_getAllTotal) {
                    $getAllTotal[] = $key_getAllTotal->value;
                }

                $this->db->trans_start();

                $penaltyDay = $this->put('penaltyDay');
                $penaltyPrice = $this->put('penaltyPrice');
                $totalPenalty = $penaltyPrice * $penaltyDay;

                $this->api_model->send_data([
                    'where' => [
                        'order_id' => $this->put('orderId')
                    ],
                    'data' => [
                        'denda_hari' => $penaltyDay,
                        'denda_bayar' => $totalPenalty,
                        'denda_date' => date('Y-m-d H:i:s'),
                    ],
                    'table' => 'db_order'
                ]);

                $this->api_model->send_data([
                    'where' => [
                        'order_id' => $this->put('orderId'),
                        'code' => 'total',
                    ],
                    'data' => [
                        'value' => (array_sum($getAllTotal) + $totalPenalty) - $parsing['getTotalPenalty']['value'],
                    ],
                    'table' => 'db_order_total'
                ]);

                if (!empty($parsing['getTotalPenalty'])) {
                    $this->api_model->send_data([
                        'where' => [
                            'order_total_id' => $parsing['getTotalPenalty']['order_total_id']
                        ],
                        'data' => [
                            'value' => $totalPenalty,
                        ],
                        'table' => 'db_order_total'
                    ]);
                } else {
                    $this->api_model->send_data([
                        'data' => [
                            'order_id' => $this->put('orderId'),
                            'code' => 'denda',
                            'title' => 'Denda',
                            'value' => $totalPenalty,
                            'sort_order' => '5',
                        ],
                        'table' => 'db_order_total'
                    ]);
                }

                $this->db->trans_complete();

                if ($this->db->trans_status() === false) {
                    $db_error = $this->db->error();
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => "edit data failed [Database error! Error Code [{$db_error['code']}] Error: {$db_error['message']}]",
                    ]);
                } else {
                    $getOrder = json_decode(shoot_api([
                        'url' => base_url() . "order/{$this->put('orderId')}",
                        'method' => 'GET',
                        'header' => [
                            "Authorization: {$this->input->request_headers()['Authorization']}"
                        ],
                    ]), true);
                    if ($getOrder['status']['code'] !== 200) {
                        $mailing = false;
                    } else {
                        $mailing = $this->mailingWithNotif([
                            'subject' => 'Pembeli mengajukan denda',
                            'message' => "Pembeli telah mengajukan denda untuk kode pesanan {$check['db_order']['invoice_no']} dikarenakan keterlambatan pengiriman barang",
                            'to' => [
                                $parsing['getMall']['email']
                            ],
                            'mallId' => $check['db_order']['mall_id'],
                            'linkId' => $this->put('orderId'),
                            'type' => 'order',
                            'dataInvoice' => [
                                'invoiceNumber' => $getOrder['data']['invoice'],
                                'items' => $getOrder['data']['items'],
                                'descriptionTotal' => $getOrder['data']['descriptionTotal'],
                            ]
                        ]);
                    }

                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => "edit data success",
                        'mailing' => $mailing,
                    ]);
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function refuse_put()
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
                if (!$this->put()) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                    ]);
                } else {
                    $check['db_order'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_order',
                        'where' => [
                            'order_id' => $this->put('orderId')
                        ]
                    ])->row_array();
                    if (empty($check['db_order'])) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_NOT_FOUND,
                            'message' => 'data not found',
                        ]);
                    } else {
                        if ($check['db_order']['order_status_id'] == '17' || $check['db_order']['order_status_id'] == '19') {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_CONFLICT,
                                'message' => 'order has answered',
                            ]);
                        } else {
                            if (empty($this->put('fileName'))) {
                                $checking = false;
                                $response = $this->formatter([
                                    'code' => self::HTTP_BAD_REQUEST,
                                    'message' => 'file not declared',
                                ]);
                            } else {
                                $getOrder = json_decode(shoot_api([
                                    'url' => base_url() . "order/{$this->put('orderId')}",
                                    'method' => 'GET',
                                    'header' => [
                                        "Authorization: {$this->input->request_headers()['Authorization']}"
                                    ],
                                ]), true);
                                if ($getOrder['status']['code'] !== 200) {
                                    $checking = false;
                                    $response = $this->formatter([
                                        'code' => $getOrder['status']['code'],
                                        'message' => $getOrder['status']['message']
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            if ($checking === true) {
                $parsing['getMall'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_mall',
                    'where' => [
                        'mall_id' => $check['db_order']['mall_id']
                    ]
                ])->row_array();

                $this->db->trans_start();

                $this->api_model->send_data([
                    'where' => [
                        'order_id' => $this->put('orderId')
                    ],
                    'data' => [
                        'order_status_id' => 19
                    ],
                    'table' => 'db_order'
                ]);

                $this->api_model->send_data([
                    'data' => [
                        'order_id' => $this->put('orderId'),
                        'order_status_id' => 19,
                        'notify' => 0,
                        'comment' => 'Pembeli menolak pesanan yang anda kirimkan',
                        'date_added' => date('Y-m-d H:i:s'),
                    ],
                    'table' => 'db_order_history'
                ]);

                $this->api_model->send_data([
                    'data' => [
                        'order_id' => $this->put('orderId'),
                        'product_id' => $getOrder['data']['items'][0]['productId'],
                        'customer_id' => $this->core['customer']['id'],
                        'id_komplain_kategori' => $this->put('category'),
                        'komplain' => $this->put('reason'),
                        'mall_id' => $check['db_order']['mall_id'],
                        'gambar' => $this->put('fileName'),
                        'status' => '0',
                        'last_update' => date('Y-m-d H:i:s'),
                    ],
                    'table' => 'db_komplain'
                ]);

                $lastIdComplaint = $this->db->insert_id();

                $this->db->trans_complete();

                if ($this->db->trans_status() === false) {
                    $db_error = $this->db->error();
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => "edit data failed [Database error! Error Code [{$db_error['code']}] Error: {$db_error['message']}]",
                    ]);
                } else {
                    $mailing = $this->mailingWithNotif([
                        'subject' => 'Komplain Pembeli',
                        'message' => "Pembeli telah mengirimkan komplain untuk kode pesanan {$check['db_order']['invoice_no']} dengan isi komplain '{$this->put('reason')}'",
                        'to' => [
                            $parsing['getMall']['email']
                        ],
                        'textNotif' => 'Seorang pembeli mengirimkan komplain untuk pesanan dari toko anda',
                        'mallId' => $check['db_order']['mall_id'],
                        'linkId' => $lastIdComplaint,
                        'type' => 'komplain',
                    ]);

                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => "edit data success",
                        'mailing' => $mailing,
                    ]);
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function confirmPayment_put()
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
                if (!$this->put()) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                    ]);
                } else {
                    $check['db_order'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_order',
                        'where' => [
                            'order_id' => $this->put('orderId')
                        ]
                    ])->row_array();
                    if (empty($check['db_order'])) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_NOT_FOUND,
                            'message' => 'data not found',
                        ]);
                    } else {
                        $getOrder = json_decode(shoot_api([
                            'url' => base_url() . "order/{$this->put('orderId')}",
                            'method' => 'GET',
                            'header' => [
                                "Authorization: {$this->input->request_headers()['Authorization']}"
                            ],
                        ]), true);
                        if ($getOrder['status']['code'] !== 200) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => $getOrder['status']['code'],
                                'message' => $getOrder['status']['message']
                            ]);
                        }
                    }
                }
            }

            if ($checking === true) {
                $parsing['getMall'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_mall',
                    'where' => [
                        'mall_id' => $check['db_order']['mall_id']
                    ]
                ])->row_array();

                $parsing['db_order_pembayaran'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_order_pembayaran',
                    'where' => [
                        'order_id' => $this->put('orderId'),
                    ],
                ])->row();

                $parsing['db_bank'] = $this->api_model->select_data([
                    'field' => '*',
                    'table' => 'db_bank',
                    'where' => [
                        'status' => '1',
                        'slug' => $this->put('bank')
                    ]
                ])->row_array();

                $this->db->trans_start();

                if (!empty($parsing['db_order_pembayaran'])) {
                    if (!empty($parsing['db_order_pembayaran']->img_upload)) {
                        if (file_exists($this->core['baseUrlCustomer'] . 'assets/uplod/konfirmasi/' . $parsing['db_order_pembayaran']->img_upload)) {
                            unlink($this->core['baseUrlCustomer'] . 'assets/uplod/konfirmasi/' . $parsing['db_order_pembayaran']->img_upload);
                        }
                    }

                    $this->api_model->send_data([
                        'where' => [
                            'order_id' => $this->put('orderId')
                        ],
                        'data' => [
                            'metode' => $this->put('bank'),
                            'no_rek_pembeli' => $this->put('accountNumber'),
                            'an_rek_pembeli' => $this->put('accountAsName'),
                            'memo' => $this->put('memo'),
                            'tgl_konfirmasi' => date('Y-m-d H:i:s'),
                            'tgl_pembayaran' => date('Y-m-d', strtotime($this->put('date'))),
                            'total_pembayaran' => $getOrder['data']['billingDetail']['total'],
                            'img_upload' => $this->put('fileName')
                        ],
                        'table' => 'db_order_pembayaran'
                    ]);
                } else {
                    $this->api_model->send_data([
                        'data' => [
                            'invoice_no' => $check['db_order']['invoice_no'],
                            'order_id' => $this->put('orderId'),
                            'mall_id' => $check['db_order']['mall_id'],
                            'customer_id' => $this->core['customer']['id'],
                            'metode' => $this->put('bank'),
                            'no_rek_pembeli' => $this->put('accountNumber'),
                            'an_rek_pembeli' => $this->put('accountAsName'),
                            'memo' => $this->put('memo'),
                            'tgl_konfirmasi' => date('Y-m-d H:i:s'),
                            'tgl_pembayaran' => date('Y-m-d', strtotime($this->put('date'))),
                            'total_pembayaran' => $getOrder['data']['billingDetail']['total'],
                            'id_status_pembayaran' => '1',
                            'img_upload' => $this->put('fileName')
                        ],
                        'table' => 'db_order_pembayaran'
                    ]);
                }

                $this->api_model->send_data([
                    'where' => [
                        'order_id' => $this->put('orderId')
                    ],
                    'data' => [
                        'payment_method' => $this->put('bank'),
                        'order_status_id' => 18
                    ],
                    'table' => 'db_order'
                ]);

                $this->db->trans_complete();

                if ($this->db->trans_status() === false) {
                    if (empty($parsing['db_order_pembayaran'])) {
                        if (file_exists($this->core['baseUrlCustomer'] . 'assets/uplod/konfirmasi/' . $this->put('fileName'))) {
                            unlink($this->core['baseUrlCustomer'] . 'assets/uplod/konfirmasi/' . $this->put('fileName'));
                        }
                    }

                    $db_error = $this->db->error();
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => "edit data failed [Database error! Error Code [{$db_error['code']}] Error: {$db_error['message']}]",
                    ]);
                } else {
                    $mailing = $this->mailingWithNotif([
                        'subject' => 'SIPLah - Konfirmasi Pembayaran',
                        'message' => "Pesanan dengan nomor invoice {$check['db_order']['invoice_no']} telah dibayar oleh pembeli, pembayaran pada tanggal {$this->put('date')} dengan metode pembayaran {$parsing['db_bank']['bank']} rekening atas nama {$this->put('accountAsName')}",
                        'to' => [
                            $parsing['getMall']['email']
                        ],
                        'mallId' => $check['db_order']['mall_id'],
                        'linkId' => $this->put('orderId'),
                        'type' => 'tagihan'
                    ]);

                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => "edit data success",
                        'mailing' => $mailing,
                    ]);
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }
}
