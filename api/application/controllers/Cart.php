<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cart extends MY_Controller
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


            if (empty($this->core['customer'])) {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_UNAUTHORIZED,
                    'message' => 'unauthorized',
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
            }

            if ($checking === true) {
                $param['db_cart']['field'] = '
                aa.id_compare, aa.id_cart, cc.mall_id,cc.name as mall_name,cc.slug as mall_slug,cc.image as mall_image';
                $param['db_cart']['table'] = 'db_cart aa';
                $param['db_cart']['join'] = [
                    [
                        'table' => 'db_product bb',
                        'on' => 'bb.product_id=aa.id_produk',
                        'type' => 'inner'
                    ],
                    [
                        'table' => 'db_mall cc',
                        'on' => 'cc.mall_id=bb.mall_id',
                        'type' => 'inner'
                    ],
                ];

                $param['db_cart']['where'] = [
                    'aa.sekolah_id' => $this->core['customer']['school']['id'],
                ];

                $param['db_cart']['group_by'] = 'cc.mall_id';
                $param['db_cart']['order_by'] = [
                    'aa.id_cart' => 'desc',
                ];

                $param['db_cart']['limit'] = [
                    $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                ];
                $parsing['db_cart'] = $this->api_model->select_data($param['db_cart'])->result();

                $output = [];
                if (empty($parsing['db_cart'])) {
                    $data['total'] = 0;
                    $data['items'] = [];
                    $code = self::HTTP_NO_CONTENT;
                } else {
                    $code = self::HTTP_OK;
                    $total_record = $this->api_model->count_all_data($param['db_cart']);

                    $limit = (int) $this->get('limit');
                    $current_page = (int) $this->get('page');
                    $total_page = ceil($total_record / $limit);

                    $data['page'] = $current_page;
                    $data['limit'] = $limit;
                    $data['total'] = $total_record;
                    $data['totalAllProduct'] = $this->api_model->select_data([
                        'field' => 'SUM(qty) AS total',
                        'table' => 'db_cart',
                        'where' => [
                            'sekolah_id' => $this->core['customer']['school']['id'],
                        ],
                    ])->row()->total;
                    $data['pages'] = $total_page;
                    $data['items'] = [];
                    foreach ($parsing['db_cart'] as $key_db_cart) {
                        $items['id'] = $key_db_cart->id_cart;
                        $items['subTotal'] = 0;
                        $items['subTotalCurrencyFormat'] = rupiah($items['subTotal']);
                        $items['ppn'] = 0;
                        $items['ppnCurrencyFormat'] = rupiah($items['ppn']);
                        $items['total'] = 0;
                        $items['totalCurrencyFormat'] = rupiah($items['total']);
                        $items['isCompare'] = false;
                        $items['alertCompare'] = '';
                        $items['shipping'] = [
                            'name' => null,
                            'weight' => null,
                            'cost' => null,
                            'costCurrencyFormat' => null,
                        ];

                        $items['mall'] = [
                            'id' => $key_db_cart->mall_id,
                            'name' => $key_db_cart->mall_name,
                            'slug' => $key_db_cart->mall_slug,
                            'image' => (!empty($key_db_cart->mall_image) || $key_db_cart->mall_image != '') ? $this->core['url_image_mall'] . $key_db_cart->mall_image : $this->core['image_not_found'],
                        ];

                        $items['product'] = [];
                        $parsing['db_cart'] = $this->api_model->select_data([
                            'field' => '
                            aa.id_cart,aa.id_produk,aa.sekolah_id,aa.id_compare,aa.id_customer,aa.qty as qty, bb.model,bb.image,bb.weight,bb.mall_id,bb.diskon,
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
                            bb.ppn,
                            bb.status,cc.name,bb.storage_quantity,bb.minimum,cc.seo as slug,aa.nego,aa.nego_harga, 
                            pcd.expedisi,
                            pcd.ongkir',
                            'table' => 'db_cart aa',
                            'join' => [
                                [
                                    'table' => 'db_product bb',
                                    'on' => 'bb.product_id=aa.id_produk',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_product_description cc',
                                    'on' => 'bb.product_id=cc.product_id',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => "(SELECT * FROM db_product_compare_detail  WHERE mall_id='{$key_db_cart->mall_id}') pcd",
                                    'on' => 'pcd.id_compare=aa.id_compare',
                                    'type' => 'left'
                                ],
                            ],
                            'where' => [
                                'aa.sekolah_id' => $this->core['customer']['school']['id'],
                                'bb.mall_id' => $key_db_cart->mall_id,
                            ],
                            'group_by' => 'aa.id_produk,aa.nego',
                        ])->result();
                        $arrPrice = [];
                        $arrPpn = [];
                        $arrBerat = [];
                        $arrCompare = [];
                        $expedisi = 'penyedia';
                        $ongkir = 0;
                        foreach ($parsing['db_cart'] as $key_cartProduct) {
                            $badges = [];

                            if ($key_cartProduct->price1 != '0' || !empty($key_cartProduct->price1)) {
                                if (!empty($this->core['customer'])) {
                                    if ($this->core['customer']['school']['location']['zone'] == '1') {
                                        $price = $key_cartProduct->price1;
                                        $badges[] = 'het';
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '2') {
                                        $price = $key_cartProduct->price2;
                                        $badges[] = 'het';
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '3') {
                                        $price = $key_cartProduct->price3;
                                        $badges[] = 'het';
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '4') {
                                        $price = $key_cartProduct->price4;
                                        $badges[] = 'het';
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '5') {
                                        $price = $key_cartProduct->price5;
                                        $badges[] = 'het';
                                    } else {
                                        $price = $key_cartProduct->price;
                                    }
                                } else {
                                    $price = $key_cartProduct->price;
                                }
                            } else {
                                if ($key_cartProduct->grosir_min1 != '0') {
                                    if ($key_cartProduct->qty >= $key_cartProduct->grosir_min1) {
                                        if ($key_cartProduct->grosir_price1 != '0') {
                                            $price = $key_cartProduct->grosir_price1;
                                            $badges[] = 'grosir';
                                        } else {
                                            $price = $key_cartProduct->price;
                                        }
                                    } else if ($key_cartProduct->qty >= $key_cartProduct->grosir_min2) {
                                        if ($key_cartProduct->grosir_price2 != '0') {
                                            $price = $key_cartProduct->grosir_price2;
                                            $badges[] = 'grosir';
                                        } else {
                                            $price = $key_cartProduct->price;
                                        }
                                    } else if ($key_cartProduct->qty >= $key_cartProduct->grosir_min3) {
                                        if ($key_cartProduct->grosir_price3 != '0') {
                                            $price = $key_cartProduct->grosir_price3;
                                            $badges[] = 'grosir';
                                        } else {
                                            $price = $key_cartProduct->price;
                                        }
                                    } else if ($key_cartProduct->qty >= $key_cartProduct->grosir_min4) {
                                        if ($key_cartProduct->grosir_price4 != '0') {
                                            $price = $key_cartProduct->grosir_price4;
                                            $badges[] = 'grosir';
                                        } else {
                                            $price = $key_cartProduct->price;
                                        }
                                    } else {
                                        $price = $key_cartProduct->price;
                                    }
                                } else {
                                    $price = $key_cartProduct->price;
                                }
                            }

                            $isCrossPrice = false;
                            if ($key_cartProduct->nego_harga != '0') {
                                $price = $key_cartProduct->nego_harga;
                                $badges[] = 'nego';
                                $isCrossPrice = true;
                            }

                            $parsing['compare'] = $this->api_model->select_data([
                                'field' => 'id_compare',
                                'table' => 'db_cart',
                                'where' => [
                                    'sekolah_id' => $this->core['customer']['school']['id'],
                                    'id_produk' => $key_cartProduct->id_produk,
                                ],
                                'group_by' => 'id_produk',
                            ])->row_array();
                            $arrCompare[] = count($parsing['compare']);
                            if ($parsing['compare']['id_compare'] != '0') {
                                $badges[] = 'banding';
                            }

                            $product['id'] = $key_cartProduct->id_produk;
                            $product['name'] = $key_cartProduct->name;
                            $product['slug'] = $key_cartProduct->slug;
                            $product['image'] = (!empty($key_cartProduct->image) || $key_cartProduct->image != '') ? $this->core['url_image_product'] . $key_cartProduct->image : $this->core['image_not_found'];
                            $product['badges'] = $badges;
                            $product['compareId'] = $key_cartProduct->id_compare;
                            $product['stock'] = $key_cartProduct->storage_quantity;
                            $product['isCrossPrice'] = $isCrossPrice;
                            $product['crossPrice'] = rupiah($key_cartProduct->price);
                            $product['qty'] = $key_cartProduct->qty;
                            $product['price'] = $price;
                            $product['priceCurrencyFormat'] = rupiah($product['price']);
                            $product['ppn'] = ($key_cartProduct->ppn == '1') ? ($key_cartProduct->qty * $price) * 0.1 : 0;
                            $product['ppnCurrencyFormat'] = rupiah($product['ppn']);
                            $product['subTotalUnit'] = ($product['price'] * $product['qty']) + $product['ppn'];
                            $product['subTotalUnitCurrencyFormat'] = rupiah($product['subTotalUnit']);

                            $arrPpn[] = $product['ppn'];
                            $arrPrice[] = $key_cartProduct->qty * $price;
                            $arrBerat[] = ceil($key_cartProduct->qty * $key_cartProduct->weight);
                            $ongkir = $key_cartProduct->ongkir;
                            $expedisi = $key_cartProduct->expedisi;

                            $items['product'][] = $product;
                        }

                        $items['subTotal'] = array_sum($arrPrice);
                        $items['subTotalCurrencyFormat'] = rupiah($items['subTotal']);
                        $items['ppn'] = array_sum($arrPpn);
                        $items['ppnCurrencyFormat'] = rupiah($items['ppn']);

                        if (!empty($expedisi)) {
                            $items['shipping']['name'] = $expedisi;
                            $items['shipping']['weight'] = number_format(array_sum($arrBerat));
                            $items['shipping']['cost'] = $ongkir * $items['shipping']['weight'];
                            $items['shipping']['costCurrencyFormat'] = rupiah($items['shipping']['cost']);
                        }

                        $items['total'] = array_sum($arrPrice) + array_sum($arrPpn) + ($ongkir * array_sum($arrBerat));
                        $items['totalCurrencyFormat'] = rupiah($items['total']);

                        if ($items['total'] >= 50000000) {
                            $isCompare = true;
                            $alertCompare = 'Nilai transaksi melebihi Rp50.000.000, harap lakukan perbandingan terhadap 1 calon penyedia lainnya.';
                        } elseif ($items['total'] >= 100000000) {
                            $isCompare = true;
                            $alertCompare = 'Nilai transaksi melebihi Rp100.000.000, harap lakukan perbandingan terhadap 2 calon penyedia lainnya.';
                        } else {
                            $isCompare = false;
                            $alertCompare = '';
                        }

                        $items['isCompare'] = $isCompare;
                        $items['alertCompare'] = $alertCompare;

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

        $this->response($response['result'], $response['status']);
    }

    public function mini_get()
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
            }

            if ($checking === true) {
                $param['db_cart']['field'] = '
                aa.id_cart,aa.id_produk,aa.sekolah_id,aa.id_compare,aa.id_customer,aa.qty as qty, bb.model,bb.image,bb.weight,bb.diskon,
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
                bb.grosir_min4,bb.ppn,
                bb.status,cc.name,bb.storage_quantity,bb.minimum,cc.seo as slug,aa.nego,aa.nego_harga, 
                pcd.expedisi,
                pcd.ongkir,
                db_mall.mall_id,db_mall.name as mall_name,db_mall.slug as mall_slug,db_mall.image as mall_image';
                $param['db_cart']['table'] = 'db_cart aa';
                $param['db_cart']['join'] = [
                    [
                        'table' => 'db_product bb',
                        'on' => 'bb.product_id=aa.id_produk',
                        'type' => 'inner'
                    ],
                    [
                        'table' => 'db_product_description cc',
                        'on' => 'bb.product_id=cc.product_id',
                        'type' => 'inner'
                    ],
                    [
                        'table' => "db_product_compare_detail pcd",
                        'on' => 'pcd.id_compare=aa.id_compare',
                        'type' => 'left'
                    ],
                    [
                        'table' => 'db_mall',
                        'on' => 'db_mall.mall_id=bb.mall_id',
                        'type' => 'inner'
                    ],
                ];

                $param['db_cart']['where'] = [
                    'aa.sekolah_id' => $this->core['customer']['school']['id'],
                ];

                $param['db_cart']['group_by'] = 'aa.id_produk,aa.nego';
                $param['db_cart']['order_by'] = [
                    'aa.id_cart' => 'desc',
                ];

                $param['db_cart']['limit'] = [
                    $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                ];
                $parsing['db_cart'] = $this->api_model->select_data($param['db_cart'])->result();

                $output = [];
                if (empty($parsing['db_cart'])) {
                    $data['total'] = 0;
                    $data['items'] = [];
                    $code = self::HTTP_NO_CONTENT;
                } else {
                    $code = self::HTTP_OK;
                    $total_record = $this->api_model->count_all_data($param['db_cart']);

                    $limit = (int) $this->get('limit');
                    $current_page = (int) $this->get('page');
                    $total_page = ceil($total_record / $limit);

                    $data['page'] = $current_page;
                    $data['limit'] = $limit;
                    $data['total'] = $total_record;
                    $data['totalAllProduct'] = $this->api_model->select_data([
                        'field' => 'SUM(qty) AS total',
                        'table' => 'db_cart',
                        'where' => [
                            'sekolah_id' => $this->core['customer']['school']['id'],
                        ],
                    ])->row()->total;
                    $data['pages'] = $total_page;

                    $items['subTotal'] = 0;
                    $items['subTotalCurrencyFormat'] = rupiah($items['subTotal']);
                    $items['ppn'] = 0;
                    $items['ppnCurrencyFormat'] = rupiah($items['ppn']);
                    $items['total'] = 0;
                    $items['totalCurrencyFormat'] = rupiah($items['total']);
                    $items['isCompare'] = false;
                    $items['alertCompare'] = '';
                    $items['shipping'] = [
                        'name' => null,
                        'weight' => null,
                        'cost' => null,
                        'costCurrencyFormat' => null,
                    ];

                    $arrPrice = [];
                    $arrPpn = [];
                    $arrBerat = [];
                    $arrCompare = [];
                    $expedisi = 'penyedia';
                    $ongkir = 0;
                    foreach ($parsing['db_cart'] as $key_cartProduct) {
                        $badges = [];

                        if ($key_cartProduct->price1 != '0' || !empty($key_cartProduct->price1)) {
                            if (!empty($this->core['customer'])) {
                                if ($this->core['customer']['school']['location']['zone'] == '1') {
                                    $price = $key_cartProduct->price1;
                                    $badges[] = 'het';
                                } elseif ($this->core['customer']['school']['location']['zone'] == '2') {
                                    $price = $key_cartProduct->price2;
                                    $badges[] = 'het';
                                } elseif ($this->core['customer']['school']['location']['zone'] == '3') {
                                    $price = $key_cartProduct->price3;
                                    $badges[] = 'het';
                                } elseif ($this->core['customer']['school']['location']['zone'] == '4') {
                                    $price = $key_cartProduct->price4;
                                    $badges[] = 'het';
                                } elseif ($this->core['customer']['school']['location']['zone'] == '5') {
                                    $price = $key_cartProduct->price5;
                                    $badges[] = 'het';
                                } else {
                                    $price = $key_cartProduct->price;
                                }
                            } else {
                                $price = $key_cartProduct->price;
                            }
                        } else {
                            if ($key_cartProduct->grosir_min1 != '0') {
                                if ($key_cartProduct->qty >= $key_cartProduct->grosir_min1) {
                                    if ($key_cartProduct->grosir_price1 != '0') {
                                        $price = $key_cartProduct->grosir_price1;
                                        $badges[] = 'grosir';
                                    } else {
                                        $price = $key_cartProduct->price;
                                    }
                                } else if ($key_cartProduct->qty >= $key_cartProduct->grosir_min2) {
                                    if ($key_cartProduct->grosir_price2 != '0') {
                                        $price = $key_cartProduct->grosir_price2;
                                        $badges[] = 'grosir';
                                    } else {
                                        $price = $key_cartProduct->price;
                                    }
                                } else if ($key_cartProduct->qty >= $key_cartProduct->grosir_min3) {
                                    if ($key_cartProduct->grosir_price3 != '0') {
                                        $price = $key_cartProduct->grosir_price3;
                                        $badges[] = 'grosir';
                                    } else {
                                        $price = $key_cartProduct->price;
                                    }
                                } else if ($key_cartProduct->qty >= $key_cartProduct->grosir_min4) {
                                    if ($key_cartProduct->grosir_price4 != '0') {
                                        $price = $key_cartProduct->grosir_price4;
                                        $badges[] = 'grosir';
                                    } else {
                                        $price = $key_cartProduct->price;
                                    }
                                } else {
                                    $price = $key_cartProduct->price;
                                }
                            } else {
                                $price = $key_cartProduct->price;
                            }
                        }

                        $isCrossPrice = false;
                        if ($key_cartProduct->nego_harga != '0') {
                            $price = $key_cartProduct->nego_harga;
                            $badges[] = 'nego';
                            $isCrossPrice = true;
                        }

                        $parsing['compare'] = $this->api_model->select_data([
                            'field' => 'id_compare',
                            'table' => 'db_cart',
                            'where' => [
                                'sekolah_id' => $this->core['customer']['school']['id'],
                                'id_produk' => $key_cartProduct->id_produk,
                            ],
                            'group_by' => 'id_produk',
                        ])->row_array();
                        $arrCompare[] = count($parsing['compare']);
                        if ($parsing['compare']['id_compare'] != '0') {
                            $badges[] = 'banding';
                        }

                        $product['id'] = $key_cartProduct->id_produk;
                        $product['name'] = $key_cartProduct->name;
                        $product['slug'] = $key_cartProduct->slug;
                        $product['image'] = (!empty($key_cartProduct->image) || $key_cartProduct->image != '') ? $this->core['url_image_product'] . $key_cartProduct->image : $this->core['image_not_found'];
                        $product['badges'] = $badges;
                        $product['stock'] = $key_cartProduct->storage_quantity;
                        $product['qty'] = $key_cartProduct->qty;
                        $product['isCrossPrice'] = $isCrossPrice;
                        $product['crossPrice'] = rupiah($key_cartProduct->price);
                        $product['price'] = $price;
                        $product['priceCurrencyFormat'] = rupiah($product['price']);
                        $product['ppn'] = ($key_cartProduct->ppn == '1') ? ($key_cartProduct->qty * $price) * 0.1 : 0;
                        $product['ppnCurrencyFormat'] = rupiah($product['ppn']);
                        $product['subTotalUnit'] = ($product['price'] * $product['qty']) + $product['ppn'];
                        $product['subTotalUnitCurrencyFormat'] = rupiah($product['subTotalUnit']);

                        $product['mall'] = [
                            'id' => $key_cartProduct->mall_id,
                            'name' => $key_cartProduct->mall_name,
                            'slug' => $key_cartProduct->mall_slug,
                            'image' => (!empty($key_cartProduct->mall_image) || $key_cartProduct->mall_image != '') ? $this->core['url_image_mall'] . $key_cartProduct->mall_image : $this->core['image_not_found'],
                        ];

                        $arrPpn[] = $product['ppn'];
                        $arrPrice[] = $key_cartProduct->qty * $price;
                        $arrBerat[] = ceil($key_cartProduct->qty * $key_cartProduct->weight);
                        $ongkir = $key_cartProduct->ongkir;
                        $expedisi = $key_cartProduct->expedisi;

                        $items['product'][] = $product;
                    }

                    $items['subTotal'] = array_sum($arrPrice);
                    $items['subTotalCurrencyFormat'] = rupiah($items['subTotal']);
                    $items['ppn'] = array_sum($arrPpn);
                    $items['ppnCurrencyFormat'] = rupiah($items['ppn']);

                    if (!empty($expedisi)) {
                        $items['shipping']['name'] = $expedisi;
                        $items['shipping']['weight'] = number_format(array_sum($arrBerat));
                        $items['shipping']['cost'] = $ongkir * $items['shipping']['weight'];
                        $items['shipping']['costCurrencyFormat'] = rupiah($items['shipping']['cost']);
                    }

                    $items['total'] = array_sum($arrPrice) + array_sum($arrPpn) + ($ongkir * array_sum($arrBerat));
                    $items['totalCurrencyFormat'] = rupiah($items['total']);

                    if ($items['total'] >= 50000000) {
                        $isCompare = true;
                        $alertCompare = 'Nilai transaksi melebihi Rp50.000.000, harap lakukan perbandingan terhadap 1 calon penyedia lainnya.';
                    } elseif ($items['total'] >= 100000000) {
                        $isCompare = true;
                        $alertCompare = 'Nilai transaksi melebihi Rp100.000.000, harap lakukan perbandingan terhadap 2 calon penyedia lainnya.';
                    } else {
                        $isCompare = false;
                        $alertCompare = '';
                    }

                    $items['isCompare'] = $isCompare;
                    $items['alertCompare'] = $alertCompare;

                    $data['items'] = $items;
                }

                $output = $data;

                $response = $this->formatter([
                    'code' => $code,
                    'message' => 'get data success',
                    'data' => $output
                ]);
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
                    } else {
                        $check['db_cart'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_cart',
                            'where' => [
                                'sekolah_id' => $this->core['customer']['school']['id'],
                                'id_produk' => $this->post('productId'),
                            ]
                        ])->row();

                        if (!empty($check['db_cart'])) {
                            if ($check['db_cart']->nego == $this->post('negoId')) {
                                $checking = false;
                                $response = $this->formatter([
                                    'code' => self::HTTP_CONFLICT,
                                    'message' => 'cart negotiation has been insert',
                                ]);
                            }
                        }

                        if (!empty($check['db_cart'])) {
                            $qty = $this->post('qty') + $check['db_cart']->qty;
                        } else {
                            $qty = $this->post('qty');
                        }

                        if ($qty > $check['db_product']->storage_quantity) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_BAD_REQUEST,
                                'message' => 'quantity cannot more than stock',
                            ]);
                        } elseif ($qty < 1) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_BAD_REQUEST,
                                'message' => 'quantity cannot less than 1',
                            ]);
                        }
                    }
                }
            }

            if ($checking === true) {
                $negoId = (!empty($this->post('negoId'))) ? $this->post('negoId') : 0;
                $negoPrice = (!empty($this->post('negoPrice'))) ? $this->post('negoPrice') : 0;

                if (!empty($check['db_cart'])) {
                    $query = $this->api_model->send_data([
                        'where' => [
                            'sekolah_id' => $this->core['customer']['school']['id'],
                            'id_produk' => $this->post('productId'),
                        ],
                        'data' => [
                            'qty' => $qty,
                            'date_add' => date('Y-m-d H:i:s'),
                        ],
                        'table' => 'db_cart'
                    ]);
                } else {
                    $query = $this->api_model->send_data([
                        'data' => [
                            'id_produk' => $this->post('productId'),
                            'id_compare' => '',
                            'id_customer' => $this->core['customer']['id'],
                            'nego' => $negoId,
                            'nego_harga' => $negoPrice,
                            'sekolah_id' => $this->core['customer']['school']['id'],
                            'date_add' => date('Y-m-d H:i:s'),
                            'qty' => $qty,
                        ],
                        'table' => 'db_cart'
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

        $this->response($response['result'], $response['status']);
    }

    public function qty_put()
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
                if (!$this->put() || empty($this->input->get('action'))) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                    ]);
                } else {
                    $arrAction = [
                        'change', 'add', 'less'
                    ];

                    if (!in_array($this->input->get('action'), $arrAction)) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_NOT_FOUND,
                            'message' => 'action not found, valid action is change, add, less',
                        ]);
                    } else {
                        $check['db_product'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_product',
                            'where' => [
                                'product_id' => $this->put('productId'),
                            ]
                        ])->row();

                        if (empty($check['db_product'])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_NOT_FOUND,
                                'message' => 'product not found',
                            ]);
                        } else {
                            $check['db_cart'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_cart',
                                'where' => [
                                    'sekolah_id' => $this->core['customer']['school']['id'],
                                    'id_produk' => $this->put('productId'),
                                ]
                            ])->row();

                            if (empty($check['db_cart'])) {
                                $checking = false;
                                $response = $this->formatter([
                                    'code' => self::HTTP_NOT_FOUND,
                                    'message' => 'cart not found',
                                ]);
                            } else {
                                if ($this->input->get('action') == 'change') {
                                    $qty = $this->put('qty');
                                } elseif ($this->input->get('action') == 'add') {
                                    $qty = $check['db_cart']->qty + 1;
                                } elseif ($this->input->get('action') == 'less') {
                                    $qty = $check['db_cart']->qty - 1;
                                }

                                if ($qty > $check['db_product']->storage_quantity) {
                                    $checking = false;
                                    $response = $this->formatter([
                                        'code' => self::HTTP_BAD_REQUEST,
                                        'message' => 'quantity cannot more than stock',
                                    ]);
                                } elseif ($qty < 1) {
                                    $checking = false;
                                    $response = $this->formatter([
                                        'code' => self::HTTP_BAD_REQUEST,
                                        'message' => 'quantity cannot less than 1',
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            if ($checking === true) {
                $query = $this->api_model->send_data([
                    'where' => [
                        'sekolah_id' => $this->core['customer']['school']['id'],
                        'id_produk' => $this->put('productId'),
                    ],
                    'data' => [
                        'qty' => $qty,
                        'date_add' => date('Y-m-d H:i:s'),
                    ],
                    'table' => 'db_cart'
                ]);

                if ($query['error'] === true) {
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => "update data failed [{$query['system']}]",
                    ]);
                } else {
                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => "update data success",
                    ]);
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function index_delete($id = null)
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
                if (empty($id)) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                    ]);
                } else {
                    $check['db_cart'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_cart',
                        'where' => [
                            'sekolah_id' => $this->core['customer']['school']['id'],
                            'id_produk' => $id,
                        ]
                    ])->row();

                    if (empty($check['db_cart'])) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_NOT_FOUND,
                            'message' => 'cart not found',
                        ]);
                    }
                }
            }

            if ($checking === true) {
                $query = $this->api_model->delete_data([
                    'where' => [
                        'sekolah_id' => $this->core['customer']['school']['id'],
                        'id_produk' => $id,
                    ],
                    'table' => 'db_cart'
                ]);

                if ($query['error'] === true) {
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => "delete data failed [{$query['system']}]",
                    ]);
                } else {
                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => "delete data success",
                    ]);
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }
}
