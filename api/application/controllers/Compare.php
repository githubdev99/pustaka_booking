<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Compare extends MY_Controller
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
                                'compareList' => [],
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
                                    'compareList' => [],
                                ]
                            ]);
                        }
                    }

                    if ($checking === true) {
                        $param['db_product_compare']['field'] = '
                        cus.firstname,
                        cus.lastname,
                        cusd.nama_sekolah as sekolah,
                        aa.id_compare,
                        aa.note,
                        aa.date_created,
                        aa.sumber_dana,
                        aa.nilai_transaksi,
                        bb.unit_type as jenis,
                        dd.mall_id,
                        dd.name as mall_name,
                        dd.zone_1 as mall_kec,
                        dd.city as mall_kota,
                        dd.province as mall_provinsi,
                        dd.alamat_perusahaan as mall_address,
                        ee.qty,
                        COUNT(aa.id_compare) as jml';
                        $param['db_product_compare']['table'] = 'db_product_compare aa';
                        $param['db_product_compare']['join'] = [
                            [
                                'table' => 'db_product_compare_detail ee',
                                'on' => 'aa.id_compare=ee.id_compare',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_product bb',
                                'on' => 'bb.product_id=ee.id_product',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_product_description cc',
                                'on' => 'cc.product_id=ee.id_product',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_mall dd',
                                'on' => 'dd.mall_id=bb.mall_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_customer cus',
                                'on' => 'cus.customer_id=aa.customer_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_customer_school cusd',
                                'on' => 'cus.sekolah_id=cusd.sekolah_id',
                                'type' => 'inner'
                            ],
                        ];

                        $param['db_product_compare']['where'] = [
                            'cus.sekolah_id' => $this->core['customer']['school']['id'],
                        ];

                        $param['db_product_compare']['group_by'] = 'aa.id_compare';
                        $param['db_product_compare']['order_by'] = [
                            'aa.date_created' => 'desc',
                        ];

                        $param['db_product_compare']['limit'] = [
                            $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                        ];
                        $parsing['db_product_compare'] = $this->api_model->select_data($param['db_product_compare'])->result();

                        $output = [];
                        if (empty($parsing['db_product_compare'])) {
                            $data['total'] = 0;
                            $data['compareList'] = [];
                            $code = self::HTTP_NO_CONTENT;
                        } else {
                            $code = self::HTTP_OK;
                            $total_record = $this->api_model->count_all_data($param['db_product_compare']);

                            $limit = (int) $this->get('limit');
                            $current_page = (int) $this->get('page');
                            $total_page = ceil($total_record / $limit);

                            $data['page'] = $current_page;
                            $data['limit'] = $limit;
                            $data['total'] = $total_record;
                            $data['pages'] = $total_page;
                            $data['compareList'] = [];

                            foreach ($parsing['db_product_compare'] as $key_db_product_compare) {
                                $date_created = explode(' ', $key_db_product_compare->date_created);

                                $compareList['id'] = $key_db_product_compare->id_compare;
                                $compareList['date'] = date_indo($date_created[0]) . ' ' . $date_created[1];
                                $compareList['user'] = (!empty($key_db_product_compare->lastname)) ? "{$key_db_product_compare->firstname} {$key_db_product_compare->lastname}" : $key_db_product_compare->firstname;
                                $compareList['school'] = $key_db_product_compare->sekolah;
                                $compareList['note'] = $key_db_product_compare->note;

                                $data['compareList'][] = $compareList;
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
                        $parsing['db_product_compare'] = $this->api_model->select_data(array_merge([
                            'field' => '
                            aa.id_compare,
                            aa.note,
                            aa.date_created,
                            aa.sumber_dana,
                            aa.nilai_transaksi,
                            cus.firstname as user,
                            cusd.nama_sekolah as sekolah,
                            bb.price, 
                            bb.price1,
                            bb.price2,
                            bb.price3,
                            bb.price4,
                            bb.price5,
                            bb.mall_id,
                            bb.image,
                            bb.unit_type as jenis,
                            dd.mall_id,
                            dd.slug as mall_slug,
                            dd.name as mall_name,
                            dd.zone_1 as mall_kec,
                            dd.city as mall_kota,
                            dd.province as mall_provinsi,
                            dd.alamat_perusahaan as mall_address,
                            cc.name,cc.seo as slug,cc.description,
                            ee.id_product,
                            ee.qty',
                            'table' => 'db_product_compare aa',
                            'join' => [
                                [
                                    'table' => 'db_product_compare_detail ee',
                                    'on' => 'aa.id_compare=ee.id_compare',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_product bb',
                                    'on' => 'bb.product_id=ee.id_product',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_product_description cc',
                                    'on' => 'cc.product_id=ee.id_product',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_mall dd',
                                    'on' => 'dd.mall_id=bb.mall_id',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_customer cus',
                                    'on' => 'cus.customer_id=aa.customer_id',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_customer_school cusd',
                                    'on' => 'cus.sekolah_id=cusd.sekolah_id',
                                    'type' => 'inner'
                                ],
                            ],
                            'where' => [
                                'aa.sekolah_id' => $this->core['customer']['school']['id'],
                                'aa.id_compare' => $id,
                            ],
                        ], (!empty($this->get('type')) && $this->get('type') == 'group') ? ['group_by' => 'dd.mall_id'] : []))->result();

                        if (empty($parsing['db_product_compare'])) {
                            $output = (object) [];
                            $code = self::HTTP_NO_CONTENT;
                        } else {
                            $output = [];
                            $code = self::HTTP_OK;

                            $date_created = explode(' ', $parsing['db_product_compare'][0]->date_created);

                            if ($parsing['db_product_compare'][0]->nilai_transaksi > 0) {
                                $between = '<b>0 s.d 10</b> Juta';
                            } elseif ($parsing['db_product_compare'][0]->nilai_transaksi >= 10000000) {
                                $between = '<b>10 s.d 50</b> Juta';
                            } elseif ($parsing['db_product_compare'][0]->nilai_transaksi >= 50000000) {
                                $between = '<b>50 s.d 200</b> Juta';
                            } elseif ($parsing['db_product_compare'][0]->nilai_transaksi >= 200000000) {
                                $between = '<b>>200</b> Juta';
                            } else {
                                $between = 'Tidak diketahui';
                            }

                            $data['id'] = $parsing['db_product_compare'][0]->id_compare;
                            $data['date'] = date_indo($date_created[0]) . ' ' . $date_created[1];
                            $data['title'] = $parsing['db_product_compare'][0]->note;
                            $data['subTitle'] = "Sumber Dana <b>{$parsing['db_product_compare'][0]->sumber_dana}</b> Nilai Transaksi {$between}";

                            $data['compareList'] = [];
                            foreach ($parsing['db_product_compare'] as $key_db_product_compare) {
                                $badges = [];

                                $compareList['longDelivery'] = '14 Hari';
                                $compareList['total'] = 0;
                                $compareList['totalCurrencyFormat'] = rupiah($compareList['total']);
                                $compareList['warranty'] = 'Toko';

                                $compareList['mall'] = [
                                    'id' => $key_db_product_compare->mall_id,
                                    'name' => $key_db_product_compare->mall_name,
                                    'location' => "{$key_db_product_compare->mall_kota}, {$key_db_product_compare->mall_provinsi}",
                                    'address' => $key_db_product_compare->mall_address,
                                    'slug' => $key_db_product_compare->mall_slug,
                                    'city' => $key_db_product_compare->mall_kota,
                                    'province' => $key_db_product_compare->mall_provinsi,
                                    'product' => [],
                                ];

                                $totalAll = [];

                                if (!empty($this->get('type')) && $this->get('type') == 'group') {
                                    $parsing['db_product_compare_detail'] = $this->api_model->select_data([
                                        'field' => '
                                        cus.firstname as user,
                                        cusd.nama_sekolah as sekolah,
                                        bb.price, 
                                        bb.price1,
                                        bb.price2,
                                        bb.price3,
                                        bb.price4,
                                        bb.price5,
                                        bb.mall_id,
                                        bb.image,
                                        bb.unit_type as jenis,
                                        dd.name as mall_name,
                                        dd.zone_1 as mall_kec,
                                        dd.city as mall_kota,
                                        dd.province as mall_provinsi,
                                        dd.alamat_perusahaan as mall_address,
                                        cc.name,cc.seo as slug,cc.description,
                                        ee.id_product,
                                        ee.qty',
                                        'table' => 'db_product_compare_detail ee',
                                        'join' => [
                                            [
                                                'table' => 'db_product_compare aa',
                                                'on' => 'aa.id_compare=ee.id_compare',
                                                'type' => 'inner'
                                            ],
                                            [
                                                'table' => 'db_product bb',
                                                'on' => 'bb.product_id=ee.id_product',
                                                'type' => 'inner'
                                            ],
                                            [
                                                'table' => 'db_product_description cc',
                                                'on' => 'cc.product_id=ee.id_product',
                                                'type' => 'inner'
                                            ],
                                            [
                                                'table' => 'db_mall dd',
                                                'on' => 'dd.mall_id=bb.mall_id',
                                                'type' => 'inner'
                                            ],
                                            [
                                                'table' => 'db_customer cus',
                                                'on' => 'cus.customer_id=aa.customer_id',
                                                'type' => 'inner'
                                            ],
                                            [
                                                'table' => 'db_customer_school cusd',
                                                'on' => 'cus.sekolah_id=cusd.sekolah_id',
                                                'type' => 'inner'
                                            ],
                                        ],
                                        'where' => [
                                            'aa.sekolah_id' => $this->core['customer']['school']['id'],
                                            'ee.mall_id' => $key_db_product_compare->mall_id,
                                            'aa.id_compare' => $id,
                                        ],
                                        'group_by' => 'ee.id_product',
                                    ])->result();
                                    foreach ($parsing['db_product_compare_detail'] as $key_db_product_compare_detail) {
                                        if ($key_db_product_compare_detail->price1 != '0' || !empty($key_db_product_compare_detail->price1)) {
                                            if (!empty($this->core['customer'])) {
                                                if ($this->core['customer']['school']['location']['zone'] == '1') {
                                                    $price = $key_db_product_compare_detail->price1;
                                                    $badges[] = 'het';
                                                } elseif ($this->core['customer']['school']['location']['zone'] == '2') {
                                                    $price = $key_db_product_compare_detail->price2;
                                                    $badges[] = 'het';
                                                } elseif ($this->core['customer']['school']['location']['zone'] == '3') {
                                                    $price = $key_db_product_compare_detail->price3;
                                                    $badges[] = 'het';
                                                } elseif ($this->core['customer']['school']['location']['zone'] == '4') {
                                                    $price = $key_db_product_compare_detail->price4;
                                                    $badges[] = 'het';
                                                } elseif ($this->core['customer']['school']['location']['zone'] == '5') {
                                                    $price = $key_db_product_compare_detail->price5;
                                                    $badges[] = 'het';
                                                } else {
                                                    $price = $key_db_product_compare_detail->price;
                                                }
                                            } else {
                                                $price = $key_db_product_compare_detail->price;
                                            }
                                        } else {
                                            $price = $key_db_product_compare_detail->price;
                                        }

                                        $compareList['mall']['product'][] = [
                                            'name' => $key_db_product_compare_detail->name,
                                            'slug' => $key_db_product_compare_detail->slug,
                                            'image' => (!empty($key_db_product_compare_detail->image) || $key_db_product_compare_detail->image != '') ? $this->core['url_image_product'] . $key_db_product_compare_detail->image : $this->core['image_not_found'],
                                            'description' => $key_db_product_compare_detail->description,
                                            'price' => $price,
                                            'priceCurrencyFormat' => rupiah($price),
                                            'qty' => $key_db_product_compare_detail->qty,
                                            'badges' => $badges,
                                            'unitType' => (!empty($key_db_product_compare_detail->jenis)) ? $key_db_product_compare_detail->jenis : null,
                                        ];

                                        $total = $price * $key_db_product_compare_detail->qty;
                                        $totalAll[] = $total;
                                    }
                                } else {
                                    if ($key_db_product_compare->price1 != '0' || !empty($key_db_product_compare->price1)) {
                                        if (!empty($this->core['customer'])) {
                                            if ($this->core['customer']['school']['location']['zone'] == '1') {
                                                $price = $key_db_product_compare->price1;
                                                $badges[] = 'het';
                                            } elseif ($this->core['customer']['school']['location']['zone'] == '2') {
                                                $price = $key_db_product_compare->price2;
                                                $badges[] = 'het';
                                            } elseif ($this->core['customer']['school']['location']['zone'] == '3') {
                                                $price = $key_db_product_compare->price3;
                                                $badges[] = 'het';
                                            } elseif ($this->core['customer']['school']['location']['zone'] == '4') {
                                                $price = $key_db_product_compare->price4;
                                                $badges[] = 'het';
                                            } elseif ($this->core['customer']['school']['location']['zone'] == '5') {
                                                $price = $key_db_product_compare->price5;
                                                $badges[] = 'het';
                                            } else {
                                                $price = $key_db_product_compare->price;
                                            }
                                        } else {
                                            $price = $key_db_product_compare->price;
                                        }
                                    } else {
                                        $price = $key_db_product_compare->price;
                                    }

                                    $compareList['mall']['product'] = [
                                        'name' => $key_db_product_compare->name,
                                        'slug' => $key_db_product_compare->slug,
                                        'image' => (!empty($key_db_product_compare->image) || $key_db_product_compare->image != '') ? $this->core['url_image_product'] . $key_db_product_compare->image : $this->core['image_not_found'],
                                        'description' => $key_db_product_compare->description,
                                        'price' => $price,
                                        'priceCurrencyFormat' => rupiah($price),
                                        'qty' => $key_db_product_compare->qty,
                                        'badges' => $badges,
                                        'unitType' => (!empty($key_db_product_compare->jenis)) ? $key_db_product_compare->jenis : null,
                                    ];

                                    $total = $price * $key_db_product_compare->qty;
                                    $totalAll[] = $total;
                                }

                                $compareList['total'] = array_sum($totalAll);
                                $compareList['totalCurrencyFormat'] = rupiah($compareList['total']);

                                $data['compareList'][] = $compareList;
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

    public function onGoing_get($id = null)
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if (empty($id)) {
                if (empty($this->core['customer'])) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_UNAUTHORIZED,
                        'message' => 'unauthorized',
                    ]);
                }

                if ($checking === true) {
                    $parsing['db_compare'] = $this->api_model->select_data([
                        'field' => '
                        cus.sekolah_id,
                        dd.name as mall_name,
                        dd.slug as mall_slug,
                        dd.mall_id ,
                        dd.slug as seo ,
                        dd.zone_1 as kec ,
                        dd.city as kota ,
                        dd.city_id as mall_kota_id ,
                        dd.province as prop,
                        aa.id_compare,
                        aa.id_user,
                        aa.expedisi,
                        aa.expedisi_ongkir',
                        'table' => 'db_compare aa',
                        'join' => [
                            [
                                'table' => 'db_mall dd',
                                'on' => 'dd.mall_id=aa.id_mall',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_customer cus',
                                'on' => 'cus.customer_id=aa.id_user',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_customer_school cusd',
                                'on' => 'cus.sekolah_id=cusd.sekolah_id',
                                'type' => 'inner'
                            ],
                        ],
                        'where' => [
                            'aa.id_user' => $this->core['customer']['id']
                        ],
                        'group_by' => 'aa.id_mall',
                        'order_by' => [
                            'aa.id_compare' => 'ASC'
                        ],
                    ])->result();
                    if (empty($parsing['db_compare'])) {
                        $output = null;
                        $code = self::HTTP_NO_CONTENT;
                    } else {
                        $output = [];
                        $code = self::HTTP_OK;

                        $data['alert'] = "";
                        $data['manyStores'] = count($parsing['db_compare']);
                        $data['compare'] = [];
                        foreach ($parsing['db_compare'] as $key_db_compare) {
                            $parsing['rating'] = $this->api_model->select_data([
                                'field' => 'count(mall_id) as jml, SUM(rate) as rate',
                                'table' => 'db_mall_ulasan',
                                'where' => [
                                    'mall_id' => $key_db_compare->mall_id,
                                ],
                                'group_by' => 'mall_id'
                            ])->row();

                            $compare['id'] = $key_db_compare->id_compare;
                            $compare['storeId'] = $key_db_compare->mall_id;
                            $compare['storeName'] = $key_db_compare->mall_name;
                            $compare['storeSlug'] = $key_db_compare->seo;
                            $compare['storeAddress'] = "{$key_db_compare->kota}, {$key_db_compare->prop}";

                            if (!empty($parsing['rating'])) {
                                $compare['storeTotalRating'] = $parsing['rating']->jml;
                                $compare['storeAverageRating'] = $parsing['rating']->rate / $compare['storeTotalRating'];
                            } else {
                                $compare['storeTotalRating'] = 0;
                                $compare['storeAverageRating'] = 0;
                            }

                            $compare['weight'] = 0;
                            $compare['weightText'] = '';
                            $compare['subTotal'] = 0;
                            $compare['subTotalCurrencyFormat'] = rupiah($compare['subTotal']);
                            $compare['ppn'] = 0;
                            $compare['ppnCurrencyFormat'] = rupiah($compare['ppn']);
                            $compare['total'] = 0;
                            $compare['totalCurrencyFormat'] = rupiah($compare['total']);

                            $parsing['getProduct'] = $this->api_model->select_data([
                                'field' => '
                                aa.id_user,
                                aa.sumber_dana,
                                aa.nilai_transaksi,
                                bb.price,
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
                                bb.grosir_min4,
                                bb.weight,
                                bb.unit_type as jenis,bb.mall_id,
                                bb.image,
                                bb.product_id,
                                dd.name as mall_name,
                                dd.slug as mall_slug,
                                dd.zone_1 as mall_kec,
                                dd.city_id as mall_kota_id,
                                dd.city as mall_kota,
                                dd.province as mall_provinsi,
                                dd.alamat_perusahaan as mall_address,
                                dd.jenis as pkp,
                                cc.name,
                                cc.seo as slug,
                                aa.id_compare,
                                aa.qty,
                                aa.price as harga,
                                aa.price_type,
                                aa.id_product,
                                bb.storage_quantity,
                                bb.ppn,
                                bb.model,
                                bb.manufacturer_id',
                                'table' => 'db_compare aa',
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
                                        'on' => 'dd.mall_id=bb.mall_id',
                                        'type' => 'inner'
                                    ],
                                    [
                                        'table' => 'db_customer cus',
                                        'on' => 'cus.customer_id=aa.id_user',
                                        'type' => 'inner'
                                    ],
                                    [
                                        'table' => 'db_customer_school cusd',
                                        'on' => 'cus.sekolah_id=cusd.sekolah_id',
                                        'type' => 'inner'
                                    ],
                                ],
                                'where' => [
                                    'aa.id_mall' => $key_db_compare->mall_id,
                                    'aa.id_user' => $this->core['customer']['id'],
                                ]
                            ])->result();
                            $compare['products'] = [];

                            $arrPrice = [];
                            $arrPpn = [];
                            $arrBerat = [];

                            if (!empty($parsing['getProduct'])) {
                                foreach ($parsing['getProduct'] as $key_getProduct) {
                                    $badges = [];

                                    if ($key_getProduct->price1 != '0' || !empty($key_getProduct->price1)) {
                                        if (!empty($this->core['customer'])) {
                                            if ($this->core['customer']['school']['location']['zone'] == '1') {
                                                $price = $key_getProduct->price1;
                                                $badges[] = 'het';
                                            } elseif ($this->core['customer']['school']['location']['zone'] == '2') {
                                                $price = $key_getProduct->price2;
                                                $badges[] = 'het';
                                            } elseif ($this->core['customer']['school']['location']['zone'] == '3') {
                                                $price = $key_getProduct->price3;
                                                $badges[] = 'het';
                                            } elseif ($this->core['customer']['school']['location']['zone'] == '4') {
                                                $price = $key_getProduct->price4;
                                                $badges[] = 'het';
                                            } elseif ($this->core['customer']['school']['location']['zone'] == '5') {
                                                $price = $key_getProduct->price5;
                                                $badges[] = 'het';
                                            } else {
                                                $price = $key_getProduct->harga;
                                            }
                                        } else {
                                            $price = $key_getProduct->harga;
                                        }
                                    } else {
                                        if ($key_getProduct->grosir_min1 != '0') {
                                            if ($key_getProduct->qty >= $key_getProduct->grosir_min1) {
                                                if ($key_getProduct->grosir_price1 != '0') {
                                                    $price = $key_getProduct->grosir_price1;
                                                    $badges[] = 'grosir';
                                                } else {
                                                    $price = $key_getProduct->harga;
                                                }
                                            } else if ($key_getProduct->qty >= $key_getProduct->grosir_min2) {
                                                if ($key_getProduct->grosir_price2 != '0') {
                                                    $price = $key_getProduct->grosir_price2;
                                                    $badges[] = 'grosir';
                                                } else {
                                                    $price = $key_getProduct->harga;
                                                }
                                            } else if ($key_getProduct->qty >= $key_getProduct->grosir_min3) {
                                                if ($key_getProduct->grosir_price3 != '0') {
                                                    $price = $key_getProduct->grosir_price3;
                                                    $badges[] = 'grosir';
                                                } else {
                                                    $price = $key_getProduct->harga;
                                                }
                                            } else if ($key_getProduct->qty >= $key_getProduct->grosir_min4) {
                                                if ($key_getProduct->grosir_price4 != '0') {
                                                    $price = $key_getProduct->grosir_price4;
                                                    $badges[] = 'grosir';
                                                } else {
                                                    $price = $key_getProduct->harga;
                                                }
                                            } else {
                                                $price = $key_getProduct->harga;
                                            }
                                        } else {
                                            $price = $key_getProduct->harga;
                                        }
                                    }

                                    $products['id'] = $key_getProduct->id_compare;
                                    $products['productId'] = $key_getProduct->id_product;
                                    $products['manufacturerId'] = $key_getProduct->manufacturer_id;
                                    $products['name'] = $key_getProduct->name;
                                    $products['model'] = $key_getProduct->model;
                                    $products['slug'] = $key_getProduct->slug;
                                    $products['image'] = (!empty($key_getProduct->image) || $key_getProduct->image != '') ? $this->core['url_image_product'] . $key_getProduct->image : $this->core['image_not_found'];
                                    $products['badges'] = $badges;
                                    $products['stock'] = $key_getProduct->storage_quantity;
                                    $products['qty'] = $key_getProduct->qty;
                                    $products['price'] = $price;
                                    $products['priceCurrencyFormat'] = rupiah($products['price']);
                                    $products['ppn'] = ($key_getProduct->ppn == '1') ? ($key_getProduct->qty * $price) * 0.1 : 0;
                                    $products['ppnCurrencyFormat'] = rupiah($products['ppn']);
                                    $products['subTotalUnit'] = ($products['price'] * $products['qty']) + $products['ppn'];
                                    $products['subTotalUnitCurrencyFormat'] = rupiah($products['subTotalUnit']);

                                    $arrPpn[] = $products['ppn'];
                                    $arrPrice[] = $key_getProduct->qty * $price;
                                    $arrBerat[] = ceil($key_getProduct->qty * $key_getProduct->weight);

                                    $compare['products'][] = $products;
                                }
                            }

                            $compare['weight'] = array_sum($arrBerat);
                            $compare['weightText'] = $compare['weight'] . ' Kg';
                            $compare['subTotal'] = array_sum($arrPrice);
                            $compare['subTotalCurrencyFormat'] = rupiah($compare['subTotal']);
                            $compare['ppn'] = array_sum($arrPpn);
                            $compare['ppnCurrencyFormat'] = rupiah($compare['ppn']);
                            $compare['total'] = array_sum($arrPrice) + array_sum($arrPpn);
                            $compare['totalCurrencyFormat'] = rupiah($compare['total']);

                            $data['compare'][] = $compare;
                        }

                        if ($compare['total'] >= 50000000) {
                            $data['alert'] = 'Nilai transaksi melebihi Rp50.000.000, harap lakukan perbandingan terhadap 1 calon penyedia lainnya.';
                        } elseif ($compare['total'] >= 100000000) {
                            $data['alert'] = 'Nilai transaksi melebihi Rp100.000.000, harap lakukan perbandingan terhadap 2 calon penyedia lainnya.';
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
                if (empty($this->core['customer'])) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_UNAUTHORIZED,
                        'message' => 'unauthorized',
                    ]);
                } else {
                    $getCompareOnGoing = json_decode(shoot_api([
                        'url' => base_url() . "compare/onGoing",
                        'method' => 'GET',
                        'header' => [
                            "Authorization: {$this->input->request_headers()['Authorization']}"
                        ],
                    ]), true);
                    if ($getCompareOnGoing['status']['code'] !== 200) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => $getCompareOnGoing['status']['code'],
                            'message' => $getCompareOnGoing['status']['message'],
                            'data' => null
                        ]);
                    } else {
                        $countCompareProduct = [];
                        foreach ($getCompareOnGoing['data']['compare'] as $key_compare) {
                            $countCompareProduct[] = count($key_compare['products']);
                        }

                        if (count(array_unique($countCompareProduct)) !== 1) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_BAD_REQUEST,
                                'message' => 'all product compare is not same',
                                'data' => null
                            ]);
                        } else {
                            if (($getCompareOnGoing['data']['compare'][0]['total'] >= 50000000) && ($getCompareOnGoing['data']['manyStores'] < 2)) {
                                $checking = false;
                                $response = $this->formatter([
                                    'code' => self::HTTP_BAD_REQUEST,
                                    'message' => 'minimal 2 store to compare',
                                    'data' => null
                                ]);
                            } elseif (($getCompareOnGoing['data']['compare'][0]['total'] >= 100000000) && ($getCompareOnGoing['data']['manyStores'] < 3)) {
                                $checking = false;
                                $response = $this->formatter([
                                    'code' => self::HTTP_BAD_REQUEST,
                                    'message' => 'minimal 3 store to compare',
                                    'data' => null
                                ]);
                            }
                        }
                    }
                }

                if ($checking === true) {
                    $parsing['db_compare'] = $this->api_model->select_data([
                        'field' => '
                        cus.sekolah_id,
                        dd.name as mall_name,
                        dd.slug as mall_slug,
                        dd.mall_id,
                        dd.slug as seo,
                        dd.zone_1 as kec,
                        dd.city as kota,
                        dd.city_id as mall_kota_id,
                        dd.province as prop,
                        ee.name as kategori,
                        ee.category_id,
                        SUM(bb.weight*aa.qty) as tberat,
                        bb.product_id, 
                        aa.id_compare,
                        aa.id_user,
                        aa.expedisi,
                        aa.expedisi_ongkir',
                        'table' => 'db_compare aa',
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
                                'on' => 'dd.mall_id=bb.mall_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_category_description ee',
                                'on' => 'ee.category_id=aa.id_kategori',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_customer cus',
                                'on' => 'cus.customer_id=aa.id_user',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_customer_school cusd',
                                'on' => 'cus.sekolah_id=cusd.sekolah_id',
                                'type' => 'inner'
                            ],
                        ],
                        'where' => [
                            'aa.id_user' => $this->core['customer']['id'],
                            'aa.id_compare' => $id
                        ],
                        'group_by' => 'aa.id_mall',
                        'order_by' => [
                            'aa.id_compare' => 'ASC'
                        ],
                    ])->row();
                    if (empty($parsing['db_compare'])) {
                        $output = null;
                        $code = self::HTTP_NO_CONTENT;
                    } else {
                        $output = [];
                        $code = self::HTTP_OK;

                        $parsing['rating'] = $this->api_model->select_data([
                            'field' => 'count(mall_id) as jml, SUM(rate) as rate',
                            'table' => 'db_mall_ulasan',
                            'where' => [
                                'mall_id' => $parsing['db_compare']->mall_id,
                            ],
                            'group_by' => 'mall_id'
                        ])->row();

                        $compare['id'] = $parsing['db_compare']->id_compare;
                        $compare['storeId'] = $parsing['db_compare']->mall_id;
                        $compare['storeName'] = $parsing['db_compare']->mall_name;
                        $compare['storeSlug'] = $parsing['db_compare']->seo;
                        $compare['storeAddress'] = "{$parsing['db_compare']->kota}, {$parsing['db_compare']->prop}";

                        if (!empty($parsing['rating'])) {
                            $compare['storeTotalRating'] = $parsing['rating']->jml;
                            $compare['storeAverageRating'] = $parsing['rating']->rate / $compare['storeTotalRating'];
                        } else {
                            $compare['storeTotalRating'] = 0;
                            $compare['storeAverageRating'] = 0;
                        }

                        $compare['weight'] = 0;
                        $compare['weightText'] = '';
                        $compare['subTotal'] = 0;
                        $compare['subTotalCurrencyFormat'] = rupiah($compare['subTotal']);
                        $compare['ppn'] = 0;
                        $compare['ppnCurrencyFormat'] = rupiah($compare['ppn']);
                        $compare['total'] = 0;
                        $compare['totalCurrencyFormat'] = rupiah($compare['total']);

                        $parsing['getProduct'] = $this->api_model->select_data([
                            'field' => '
                            aa.id_user,
                            aa.sumber_dana,
                            aa.nilai_transaksi,
                            bb.price,
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
                            bb.grosir_min4,
                            bb.weight,
                            bb.unit_type as jenis,bb.mall_id,
                            bb.image,
                            bb.product_id,
                            dd.name as mall_name,
                            dd.slug as mall_slug,
                            dd.zone_1 as mall_kec,
                            dd.city_id as mall_kota_id,
                            dd.city as mall_kota,
                            dd.province as mall_provinsi,
                            dd.alamat_perusahaan as mall_address,
                            dd.jenis as pkp,
                            cc.name,
                            cc.seo as slug,
                            aa.id_compare,
                            aa.qty,
                            aa.price as harga,
                            aa.price_type,
                            aa.id_product,
                            bb.storage_quantity,
                            bb.ppn,
                            bb.model,
                            bb.manufacturer_id',
                            'table' => 'db_compare aa',
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
                                    'on' => 'dd.mall_id=bb.mall_id',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_customer cus',
                                    'on' => 'cus.customer_id=aa.id_user',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_customer_school cusd',
                                    'on' => 'cus.sekolah_id=cusd.sekolah_id',
                                    'type' => 'inner'
                                ],
                            ],
                            'where' => [
                                'aa.id_mall' => $parsing['db_compare']->mall_id,
                                'aa.id_user' => $this->core['customer']['id'],
                            ]
                        ])->result();
                        $compare['products'] = [];

                        $arrPrice = [];
                        $arrPpn = [];
                        $arrBerat = [];
                        foreach ($parsing['getProduct'] as $key_getProduct) {
                            $badges = [];
                            $badges[] = 'banding';
                            $priceType = 'regular';

                            if ($key_getProduct->price1 != '0' || !empty($key_getProduct->price1)) {
                                if (!empty($this->core['customer'])) {
                                    if ($this->core['customer']['school']['location']['zone'] == '1') {
                                        $price = $key_getProduct->price1;
                                        $badges[] = 'het';
                                        $priceType = 'zone1';
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '2') {
                                        $price = $key_getProduct->price2;
                                        $badges[] = 'het';
                                        $priceType = 'zone2';
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '3') {
                                        $price = $key_getProduct->price3;
                                        $badges[] = 'het';
                                        $priceType = 'zone3';
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '4') {
                                        $price = $key_getProduct->price4;
                                        $badges[] = 'het';
                                        $priceType = 'zone4';
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '5') {
                                        $price = $key_getProduct->price5;
                                        $badges[] = 'het';
                                        $priceType = 'zone5';
                                    } else {
                                        $price = $key_getProduct->harga;
                                    }
                                } else {
                                    $price = $key_getProduct->harga;
                                }
                            } else {
                                if ($key_getProduct->grosir_min1 != '0') {
                                    if ($key_getProduct->qty >= $key_getProduct->grosir_min1) {
                                        if ($key_getProduct->grosir_price1 != '0') {
                                            $price = $key_getProduct->grosir_price1;
                                            $badges[] = 'grosir';
                                            $priceType = 'grosir';
                                        } else {
                                            $price = $key_getProduct->harga;
                                        }
                                    } else if ($key_getProduct->qty >= $key_getProduct->grosir_min2) {
                                        if ($key_getProduct->grosir_price2 != '0') {
                                            $price = $key_getProduct->grosir_price2;
                                            $badges[] = 'grosir';
                                            $priceType = 'grosir';
                                        } else {
                                            $price = $key_getProduct->harga;
                                        }
                                    } else if ($key_getProduct->qty >= $key_getProduct->grosir_min3) {
                                        if ($key_getProduct->grosir_price3 != '0') {
                                            $price = $key_getProduct->grosir_price3;
                                            $badges[] = 'grosir';
                                            $priceType = 'grosir';
                                        } else {
                                            $price = $key_getProduct->harga;
                                        }
                                    } else if ($key_getProduct->qty >= $key_getProduct->grosir_min4) {
                                        if ($key_getProduct->grosir_price4 != '0') {
                                            $price = $key_getProduct->grosir_price4;
                                            $badges[] = 'grosir';
                                            $priceType = 'grosir';
                                        } else {
                                            $price = $key_getProduct->harga;
                                        }
                                    } else {
                                        $price = $key_getProduct->harga;
                                    }
                                } else {
                                    $price = $key_getProduct->harga;
                                }
                            }

                            $products['id'] = $key_getProduct->id_compare;
                            $products['productId'] = $key_getProduct->id_product;
                            $products['manufacturerId'] = $key_getProduct->manufacturer_id;
                            $products['name'] = $key_getProduct->name;
                            $products['model'] = $key_getProduct->model;
                            $products['slug'] = $key_getProduct->slug;
                            $products['image'] = (!empty($key_getProduct->image) || $key_getProduct->image != '') ? $this->core['url_image_product'] . $key_getProduct->image : $this->core['image_not_found'];
                            $products['badges'] = $badges;
                            $products['stock'] = $key_getProduct->storage_quantity;
                            $products['qty'] = $key_getProduct->qty;
                            $products['priceType'] = $priceType;
                            $products['price'] = $price;
                            $products['priceCurrencyFormat'] = rupiah($products['price']);
                            $products['ppn'] = ($key_getProduct->ppn == '1') ? ($key_getProduct->qty * $price) * 0.1 : 0;
                            $products['ppnCurrencyFormat'] = rupiah($products['ppn']);
                            $products['subTotalUnit'] = ($products['price'] * $products['qty']) + $products['ppn'];
                            $products['subTotalUnitCurrencyFormat'] = rupiah($products['subTotalUnit']);

                            $arrPpn[] = $products['ppn'];
                            $arrPrice[] = $key_getProduct->qty * $price;
                            $arrBerat[] = ceil($key_getProduct->qty * $key_getProduct->weight);

                            $compare['products'][] = $products;
                        }

                        $compare['weight'] = array_sum($arrBerat);
                        $compare['weightText'] = $compare['weight'] . ' Kg';
                        $compare['subTotal'] = array_sum($arrPrice);
                        $compare['subTotalCurrencyFormat'] = rupiah($compare['subTotal']);
                        $compare['ppn'] = array_sum($arrPpn);
                        $compare['ppnCurrencyFormat'] = rupiah($compare['ppn']);
                        $compare['total'] = array_sum($arrPrice) + array_sum($arrPpn);
                        $compare['totalCurrencyFormat'] = rupiah($compare['total']);

                        $output = $compare;
                    }

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
                    if (!empty($this->post('productSlug')) && !empty($this->post('qty'))) {
                        $getProductDetail = json_decode(shoot_api([
                            'url' => base_url() . "product/{$this->post('productSlug')}?mall={$this->post('mallId')}",
                            'method' => 'GET',
                        ]), true);
                        if ($getProductDetail['status']['code'] !== 200) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => ($getProductDetail['status']['code'] === 204) ? self::HTTP_BAD_REQUEST : $getProductDetail['status']['code'],
                                'message' => ($getProductDetail['status']['code'] === 204) ? 'data not recognized' : $getProductDetail['status']['message']
                            ]);
                        } else {
                            $check['db_compare'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_compare',
                                'where' => [
                                    'id_user' => $this->core['customer']['id'],
                                    'id_product' => $getProductDetail['data']['id']
                                ]
                            ])->row_array();
                            if (!empty($check['db_compare'])) {
                                $checking = false;
                                $response = $this->formatter([
                                    'code' => self::HTTP_CONFLICT,
                                    'message' => 'product has insert',
                                ]);
                            }
                        }
                    } else {
                        $check['db_mall'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_mall',
                            'where' => [
                                'mall_id' => $this->post('mallId')
                            ]
                        ])->row_array();
                        if (empty($check['db_mall'])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_NOT_FOUND,
                                'message' => 'mall not found',
                            ]);
                        } else {
                            $check['getCompare'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_compare',
                                'where' => [
                                    'id_mall' => $this->post('mallId')
                                ]
                            ])->row_array();
                            if (!empty($check['getCompare'])) {
                                $checking = false;
                                $response = $this->formatter([
                                    'code' => self::HTTP_CONFLICT,
                                    'message' => 'mall has insert',
                                ]);
                            }
                        }
                    }
                }
            }

            if ($checking === true) {
                if (!empty($check['db_mall'])) {
                    // Just Add Mall
                    $query = $this->api_model->send_data([
                        'data' => [
                            'id_user' => $this->core['customer']['id'],
                            'id_mall' => $check['db_mall']['mall_id'],
                            'mall' => $check['db_mall']['name'],
                        ],
                        'table' => 'db_compare'
                    ]);

                    if ($query['error'] === true) {
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => "add data failed [{$query['system']}]",
                        ]);
                    } else {
                        $response = $this->formatter([
                            'code' => self::HTTP_OK,
                            'message' => "add data success",
                        ]);
                    }
                } else {
                    // Add Product
                    $priceType = 'regular';

                    if (!empty($getProductDetail['data']['price']['zone'])) {
                        if (!empty($this->core['customer'])) {
                            if ($this->core['customer']['school']['location']['zone'] == '1') {
                                $price = $getProductDetail['data']['price']['zone'][0]['price'];
                                $priceType = 'zone1';
                            } elseif ($this->core['customer']['school']['location']['zone'] == '2') {
                                $price = $getProductDetail['data']['price']['zone'][1]['price'];
                                $priceType = 'zone2';
                            } elseif ($this->core['customer']['school']['location']['zone'] == '3') {
                                $price = $getProductDetail['data']['price']['zone'][2]['price'];
                                $priceType = 'zone3';
                            } elseif ($this->core['customer']['school']['location']['zone'] == '4') {
                                $price = $getProductDetail['data']['price']['zone'][3]['price'];
                                $priceType = 'zone4';
                            } elseif ($this->core['customer']['school']['location']['zone'] == '5') {
                                $price = $getProductDetail['data']['price']['zone'][4]['price'];
                                $priceType = 'zone5';
                            } else {
                                $price = $getProductDetail['data']['price']['primary'];
                            }
                        } else {
                            $price = $getProductDetail['data']['price']['primary'];
                        }
                    } else {
                        if (!empty($getProductDetail['data']['price']['grosir'])) {
                            if ($this->post('qty') >= $getProductDetail['data']['price']['grosir'][0]['min']) {
                                if ($getProductDetail['data']['price']['grosir'][0]['price'] != '0') {
                                    $price = $getProductDetail['data']['price']['grosir'][0]['price'];
                                    $priceType = 'grosir';
                                } else {
                                    $price = $getProductDetail['data']['price']['primary'];
                                }
                            } else if ($this->post('qty') >= $getProductDetail['data']['price']['grosir'][1]['min']) {
                                if ($getProductDetail['data']['price']['grosir'][1]['price'] != '0') {
                                    $price = $getProductDetail['data']['price']['grosir'][1]['price'];
                                    $priceType = 'grosir';
                                } else {
                                    $price = $getProductDetail['data']['price']['primary'];
                                }
                            } else if ($this->post('qty') >= $getProductDetail['data']['price']['grosir'][2]['min']) {
                                if ($getProductDetail['data']['price']['grosir'][2]['price'] != '0') {
                                    $price = $getProductDetail['data']['price']['grosir'][2]['price'];
                                    $priceType = 'grosir';
                                } else {
                                    $price = $getProductDetail['data']['price']['primary'];
                                }
                            } else if ($this->post('qty') >= $getProductDetail['data']['price']['grosir'][3]['min']) {
                                if ($getProductDetail['data']['price']['grosir'][3]['price'] != '0') {
                                    $price = $getProductDetail['data']['price']['grosir'][3]['price'];
                                    $priceType = 'grosir';
                                } else {
                                    $price = $getProductDetail['data']['price']['primary'];
                                }
                            } else {
                                $price = $getProductDetail['data']['price']['primary'];
                            }
                        } else {
                            $price = $getProductDetail['data']['price']['primary'];
                        }
                    }

                    $parsing['getCompare'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_compare',
                        'where' => [
                            'id_user' => $this->core['customer']['id'],
                            'id_product' => 0,
                            'id_mall' => $getProductDetail['data']['mall']['id'],
                        ]
                    ])->row();

                    if (!empty($parsing['getCompare'])) {
                        $query = $this->api_model->send_data([
                            'where' => [
                                'id_user' => $this->core['customer']['id'],
                                'id_product' => 0,
                                'id_mall' => $getProductDetail['data']['mall']['id'],
                            ],
                            'data' => [
                                'id_product' => $getProductDetail['data']['id'],
                                'id_kategori' => $getProductDetail['data']['category'][0]['id'],
                                'title' => $getProductDetail['data']['name'],
                                'berat' => ceil($getProductDetail['data']['specification']['weight']),
                                'qty' => $this->post('qty'),
                                'price' => $price,
                                'price_type' => $priceType,
                                'ppn' => $getProductDetail['data']['ppn'],
                            ],
                            'table' => 'db_compare'
                        ]);
                    } else {
                        $query = $this->api_model->send_data([
                            'data' => [
                                'id_user' => $this->core['customer']['id'],
                                'id_product' => $getProductDetail['data']['id'],
                                'id_kategori' => $getProductDetail['data']['category'][0]['id'],
                                'id_mall' => $getProductDetail['data']['mall']['id'],
                                'mall' => $getProductDetail['data']['mall']['name'],
                                'title' => $getProductDetail['data']['name'],
                                'berat' => ceil($getProductDetail['data']['specification']['weight']),
                                'qty' => $this->post('qty'),
                                'price' => $price,
                                'price_type' => $priceType,
                                'ppn' => $getProductDetail['data']['ppn'],
                            ],
                            'table' => 'db_compare'
                        ]);
                    }

                    if ($query['error'] === true) {
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => "add data failed [{$query['system']}]",
                        ]);
                    } else {
                        $response = $this->formatter([
                            'code' => self::HTTP_OK,
                            'message' => "add data success",
                        ]);
                    }
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }
}
