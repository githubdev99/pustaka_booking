<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Checkout extends MY_Controller
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
                ]);
            } else {
                if (empty($id)) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                        'data' => (object) [],
                    ]);
                } else {
                    if (empty($this->get('from'))) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => 'parameter not found',
                            'data' => (object) [],
                        ]);
                    } else {
                        if (!in_array($this->get('from'), [
                            'cart', 'compare'
                        ])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_BAD_REQUEST,
                                'message' => 'parameter not found, valid parameter is cart, compare',
                                'data' => (object) [],
                            ]);
                        } else {
                            if ($this->get('from') == 'compare') {
                                $getCompare = json_decode(shoot_api([
                                    'url' => base_url() . "compare/onGoing/{$id}",
                                    'method' => 'GET',
                                    'header' => [
                                        "Authorization: {$this->input->request_headers()['Authorization']}"
                                    ],
                                ]), true);
                                if ($getCompare['status']['code'] !== 200) {
                                    $checking = false;
                                    $response = $this->formatter([
                                        'code' => $getCompare['status']['code'],
                                        'message' => $getCompare['status']['message'],
                                        'data' => (object) []
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            if ($checking === true) {
                if ($this->get('from') == 'cart') {
                    $param['getSelected']['field'] = 'aa.id_compare, aa.nego, aa.id_cart, cc.mall_id,cc.name as mall_name,cc.slug as mall_slug,cc.image as mall_image, cc.id_rajaongkir, bb.manufacturer_id';
                    $param['getSelected']['table'] = 'db_cart aa';
                    $param['getSelected']['join'] = [
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

                    $param['getSelected']['where'] = [
                        'aa.sekolah_id' => $this->core['customer']['school']['id'],
                        'aa.id_cart' => $id,
                    ];

                    $param['getSelected']['group_by'] = 'cc.mall_id';
                    $param['getSelected']['order_by'] = [
                        'aa.date_add' => 'desc',
                    ];

                    $parsing['getSelected'] = $this->api_model->select_data($param['getSelected'])->row();

                    if (empty($parsing['getSelected'])) {
                        $output = (object) [];
                        $code = self::HTTP_NO_CONTENT;
                    } else {
                        $output = [];
                        $code = self::HTTP_OK;

                        $shippingCost = 0;
                        $getShipping = (!empty($this->get('shipping'))) ? $this->get('shipping') : 'penyedia';

                        $data['from'] = $this->get('from');
                        $data['transactionValue'] = '';
                        $data['weight'] = 0;
                        $data['weightText'] = '';
                        $data['shippingCost'] = 0;
                        $data['shippingCostCurrencyFormat'] = rupiah($data['shippingCost']);
                        $data['subTotal'] = 0;
                        $data['subTotalCurrencyFormat'] = rupiah($data['subTotal']);
                        $data['ppn'] = 0;
                        $data['ppnCurrencyFormat'] = rupiah($data['ppn']);
                        $data['total'] = 0;
                        $data['totalCurrencyFormat'] = rupiah($data['total']);
                        $data['cartSelected'] = [];

                        $cartSelected['id'] = $parsing['getSelected']->id_cart;
                        $cartSelected['negoId'] = $parsing['getSelected']->nego;

                        $parsing['getSelectedDetail'] = $this->api_model->select_data([
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
                            pcd.ongkir,
                            aa.cabang_id',
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
                                    'table' => "(SELECT * FROM db_product_compare_detail  WHERE mall_id='{$parsing['getSelected']->mall_id}') pcd",
                                    'on' => 'pcd.id_compare=aa.id_compare',
                                    'type' => 'left'
                                ],
                            ],
                            'where' => [
                                'aa.sekolah_id' => $this->core['customer']['school']['id'],
                                'bb.mall_id' => $parsing['getSelected']->mall_id,
                            ],
                            'group_by' => 'aa.id_produk,aa.nego',
                        ])->result();

                        if ($parsing['getSelectedDetail'][0]->cabang_id != '0') {
                            $parsing['mall'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_mall_cabang',
                                'where' => [
                                    'cabang_id' => $parsing['getSelectedDetail'][0]->cabang_id,
                                ],
                            ])->row();
                        } else {
                            $parsing['mall'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_mall',
                                'where' => [
                                    'mall_id' => $parsing['getSelected']->mall_id,
                                ],
                            ])->row();
                        }

                        $cartSelected['mall'] = [
                            'id' => $parsing['mall']->mall_id,
                            'name' => $parsing['mall']->name,
                            'slug' => $parsing['mall']->slug,
                            'email' => $parsing['mall']->email,
                            'image' => (!empty($parsing['mall']->image) || $parsing['mall']->image != '') ? $this->core['url_image_mall'] . $parsing['mall']->image : $this->core['image_not_found'],
                            'address' => $parsing['mall']->address,
                            'zone' => $parsing['mall']->zone_2,
                            'city' => $parsing['mall']->city,
                            'province' => $parsing['mall']->province,
                            'postalCode' => $parsing['mall']->postcode,
                            'phone' => $parsing['mall']->phone,
                        ];

                        $arrPrice = [];
                        $arrPpn = [];
                        $arrBerat = [];
                        $arrCompare = [];
                        $cartSelected['product'] = [];
                        foreach ($parsing['getSelectedDetail'] as $key_getSelectedDetail) {
                            $badges = [];

                            if ($key_getSelectedDetail->price1 != '0' || !empty($key_getSelectedDetail->price1)) {
                                if (!empty($this->core['customer'])) {
                                    if ($this->core['customer']['school']['location']['zone'] == '1') {
                                        $price = $key_getSelectedDetail->price1;
                                        $badges[] = 'het';
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '2') {
                                        $price = $key_getSelectedDetail->price2;
                                        $badges[] = 'het';
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '3') {
                                        $price = $key_getSelectedDetail->price3;
                                        $badges[] = 'het';
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '4') {
                                        $price = $key_getSelectedDetail->price4;
                                        $badges[] = 'het';
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '5') {
                                        $price = $key_getSelectedDetail->price5;
                                        $badges[] = 'het';
                                    } else {
                                        $price = $key_getSelectedDetail->price;
                                    }
                                } else {
                                    $price = $key_getSelectedDetail->price;
                                }
                            } else {
                                if ($key_getSelectedDetail->grosir_min1 != '0') {
                                    if ($key_getSelectedDetail->qty >= $key_getSelectedDetail->grosir_min1) {
                                        if ($key_getSelectedDetail->grosir_price1 != '0') {
                                            $price = $key_getSelectedDetail->grosir_price1;
                                            $badges[] = 'grosir';
                                        } else {
                                            $price = $key_getSelectedDetail->price;
                                        }
                                    } else if ($key_getSelectedDetail->qty >= $key_getSelectedDetail->grosir_min2) {
                                        if ($key_getSelectedDetail->grosir_price2 != '0') {
                                            $price = $key_getSelectedDetail->grosir_price2;
                                            $badges[] = 'grosir';
                                        } else {
                                            $price = $key_getSelectedDetail->price;
                                        }
                                    } else if ($key_getSelectedDetail->qty >= $key_getSelectedDetail->grosir_min3) {
                                        if ($key_getSelectedDetail->grosir_price3 != '0') {
                                            $price = $key_getSelectedDetail->grosir_price3;
                                            $badges[] = 'grosir';
                                        } else {
                                            $price = $key_getSelectedDetail->price;
                                        }
                                    } else if ($key_getSelectedDetail->qty >= $key_getSelectedDetail->grosir_min4) {
                                        if ($key_getSelectedDetail->grosir_price4 != '0') {
                                            $price = $key_getSelectedDetail->grosir_price4;
                                            $badges[] = 'grosir';
                                        } else {
                                            $price = $key_getSelectedDetail->price;
                                        }
                                    } else {
                                        $price = $key_getSelectedDetail->price;
                                    }
                                } else {
                                    $price = $key_getSelectedDetail->price;
                                }
                            }

                            $isCrossPrice = false;
                            if ($key_getSelectedDetail->nego_harga != '0') {
                                $price = $key_getSelectedDetail->nego_harga;
                                $badges[] = 'nego';
                                $isCrossPrice = true;
                            }

                            $parsing['compare'] = $this->api_model->select_data([
                                'field' => 'id_compare',
                                'table' => 'db_cart',
                                'where' => [
                                    'sekolah_id' => $this->core['customer']['school']['id'],
                                    'id_produk' => $key_getSelectedDetail->id_produk,
                                ],
                                'group_by' => 'id_produk',
                            ])->row_array();
                            $arrCompare[] = count($parsing['compare']);
                            if ($parsing['compare']['id_compare'] != '0') {
                                $badges[] = 'banding';
                            }

                            $product['detailId'] = $key_getSelectedDetail->id_cart;
                            $product['id'] = $key_getSelectedDetail->id_produk;
                            $product['name'] = $key_getSelectedDetail->name;
                            $product['model'] = $key_getSelectedDetail->model;
                            $product['slug'] = $key_getSelectedDetail->slug;
                            $product['image'] = (!empty($key_getSelectedDetail->image) || $key_getSelectedDetail->image != '') ? $this->core['url_image_product'] . $key_getSelectedDetail->image : $this->core['image_not_found'];
                            $product['badges'] = $badges;
                            $product['stock'] = $key_getSelectedDetail->storage_quantity;
                            $product['qty'] = $key_getSelectedDetail->qty;
                            $product['isCrossPrice'] = $isCrossPrice;
                            $product['crossPrice'] = rupiah($key_getSelectedDetail->price);
                            $product['price'] = $price;
                            $product['priceCurrencyFormat'] = rupiah($product['price']);
                            $product['ppn'] = ($key_getSelectedDetail->ppn == '1') ? ($key_getSelectedDetail->qty * $price) * 0.1 : 0;
                            $product['ppnCurrencyFormat'] = rupiah($product['ppn']);
                            $product['subTotalUnit'] = ($product['price'] * $product['qty']) + $product['ppn'];
                            $product['subTotalUnitCurrencyFormat'] = rupiah($product['subTotalUnit']);

                            if ($key_getSelectedDetail->ppn == '1') {
                                $arrPpn[] = ($key_getSelectedDetail->qty * $price) * 0.1;
                            } else {
                                $arrPpn[] = 0;
                            }

                            $arrPrice[] = $key_getSelectedDetail->qty * $price;
                            $arrBerat[] = ceil($key_getSelectedDetail->qty * $key_getSelectedDetail->weight);

                            $totalAllProduct[] = $product['qty'];

                            $cartSelected['product'][] = $product;
                        }

                        if ($getShipping == 'penyedia') {
                            $shippingCost = 0;
                        } else {
                            $getCost = $this->rajaongkir->cost([
                                'origin' => ($parsing['getSelected']->manufacturer_id == '1') ? $this->core['customer']['school']['location']['city']['id'] : $parsing['getSelected']->id_rajaongkir,
                                'destination' => $this->core['customer']['school']['rajaOngkirId'],
                                'weight' => array_sum($arrBerat) . '000',
                                'courier' => $getShipping,
                            ]);

                            $shippingCost = (!empty($getCost)) ? $getCost : 0;
                        }

                        $data['weight'] = array_sum($arrBerat);
                        $data['weightText'] = $data['weight'] . ' Kg';
                        $data['shippingCost'] = $shippingCost;
                        $data['shippingCostCurrencyFormat'] = rupiah($data['shippingCost']);
                        $data['subTotal'] = array_sum($arrPrice);
                        $data['subTotalCurrencyFormat'] = rupiah($data['subTotal']);
                        $data['ppn'] = array_sum($arrPpn);
                        $data['ppnCurrencyFormat'] = rupiah($data['ppn']);
                        $data['total'] = array_sum($arrPrice) + array_sum($arrPpn) + $data['shippingCost'];
                        $data['totalCurrencyFormat'] = rupiah($data['total']);

                        if ($data['total'] > 200000000) {
                            $transactionValue = 'diatas 200jt';
                        } elseif ($data['total'] > 50000000) {
                            $transactionValue = 'diatas 50jt s.d 200jt';
                        } elseif ($data['total'] > 10000000) {
                            $transactionValue = 'diatas 10jt s.d 50jt';
                        } else {
                            $transactionValue = 'dibawah 10jt';
                        }

                        $data['transactionValue'] = $transactionValue;
                        $data['cartSelected'] = $cartSelected;

                        $parsing['db_sumber_dana'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_sumber_dana',
                        ])->result();
                        $data['sourceOfFunds'] = [];
                        foreach ($parsing['db_sumber_dana'] as $key_db_sumber_dana) {
                            $data['sourceOfFunds'][] = [
                                'value' => $key_db_sumber_dana->kode_sumber_dana,
                                'name' => $key_db_sumber_dana->sumber_dana,
                            ];
                        }

                        $parsing['db_mall_ekspedisi'] = $this->api_model->select_data([
                            'field' => '
                            b.id_ekspedisi,
                            b.kode_ekspedisi,
                            b.nama_ekspedisi,
                            b.logo',
                            'table' => 'db_mall_ekspedisi a',
                            'join' => [
                                [
                                    'table' => 'db_ekspedisi b',
                                    'on' => 'b.id_ekspedisi=a.id_ekspedisi',
                                    'type' => 'inner'
                                ],
                            ],
                            'where' => [
                                'mall_id' => $parsing['getSelected']->mall_id,
                                'a.status' => '1',
                            ]
                        ])->result();
                        $data['shipping'] = [];
                        $data['shippingSelected'] = (object) [];
                        foreach ($parsing['db_mall_ekspedisi'] as $key_db_mall_ekspedisi) {
                            $data['shipping'][] = [
                                'value' => $key_db_mall_ekspedisi->kode_ekspedisi,
                                'name' => $key_db_mall_ekspedisi->nama_ekspedisi,
                            ];

                            if ($getShipping == $key_db_mall_ekspedisi->kode_ekspedisi) {
                                $data['shippingSelected'] = [
                                    'value' => $key_db_mall_ekspedisi->kode_ekspedisi,
                                    'name' => $key_db_mall_ekspedisi->nama_ekspedisi,
                                ];
                            }
                        }

                        $data['paymentMethod'] = [
                            [
                                'group' => 'Virtual Account',
                                'items' => [
                                    [
                                        'value' => 'bank_mandiri_va',
                                        'name' => 'Virtual Account Mandiri',
                                    ],
                                ]
                            ]
                        ];

                        if ($parsing['getSelected']->mall_name == 'EUREKA TRIAL') {
                            $data['paymentMethod'][0]['items'][] = [
                                'value' => 'bank_bri_va',
                                'name' => 'Virtual Account BRI (BRIVA)',
                            ];
                        }

                        $parsing['db_bank'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_bank',
                            'where' => [
                                'status' => '1',
                            ]
                        ])->result();
                        $data['paymentMethod'][] = [
                            'group' => 'Bank Transfer',
                            'items' => []
                        ];
                        foreach ($parsing['db_bank'] as $key_db_bank) {
                            if ($key_db_bank->slug != 'bank_mandiri_va') {
                                $data['paymentMethod'][1]['items'][] = [
                                    'value' => $key_db_bank->slug,
                                    'name' => $key_db_bank->bank,
                                ];
                            }
                        }

                        $data['paymentDue'] = [
                            [
                                'value' => '1',
                                'name' => '1 Hari',
                            ],
                            [
                                'value' => '3',
                                'name' => '3 Hari',
                            ],
                            [
                                'value' => '7',
                                'name' => '7 Hari',
                            ],
                            [
                                'value' => '14',
                                'name' => '14 Hari',
                            ],
                        ];

                        $data['custom'] = [
                            'origin' => ($parsing['getSelected']->manufacturer_id == '1') ? $this->core['customer']['school']['location']['city']['id'] : $parsing['getSelected']->id_rajaongkir,
                        ];

                        $output = $data;
                    }
                } elseif ($this->get('from') == 'compare') {
                    if (empty($getCompare)) {
                        $output = (object) [];
                        $code = self::HTTP_NO_CONTENT;
                    } else {
                        $output = [];
                        $code = self::HTTP_OK;

                        $shippingCost = 0;
                        $getShipping = (!empty($this->get('shipping'))) ? $this->get('shipping') : 'penyedia';

                        $data['from'] = $this->get('from');
                        $data['transactionValue'] = '';
                        $data['weight'] = 0;
                        $data['weightText'] = '';
                        $data['shippingCost'] = 0;
                        $data['shippingCostCurrencyFormat'] = rupiah($data['shippingCost']);
                        $data['subTotal'] = 0;
                        $data['subTotalCurrencyFormat'] = rupiah($data['subTotal']);
                        $data['ppn'] = 0;
                        $data['ppnCurrencyFormat'] = rupiah($data['ppn']);
                        $data['total'] = 0;
                        $data['totalCurrencyFormat'] = rupiah($data['total']);
                        $data['cartSelected'] = [];

                        $cartSelected['id'] = $getCompare['data']['id'];

                        $parsing['mall'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_mall',
                            'where' => [
                                'mall_id' => $getCompare['data']['storeId'],
                            ],
                        ])->row();

                        $cartSelected['mall'] = [
                            'id' => $parsing['mall']->mall_id,
                            'name' => $parsing['mall']->name,
                            'slug' => $parsing['mall']->slug,
                            'email' => $parsing['mall']->email,
                            'image' => (!empty($parsing['mall']->image) || $parsing['mall']->image != '') ? $this->core['url_image_mall'] . $parsing['mall']->image : $this->core['image_not_found'],
                            'address' => $parsing['mall']->address,
                            'zone' => $parsing['mall']->zone_2,
                            'city' => $parsing['mall']->city,
                            'province' => $parsing['mall']->province,
                            'postalCode' => $parsing['mall']->postcode,
                            'phone' => $parsing['mall']->phone,
                        ];

                        $arrPrice = [];
                        $arrPpn = [];
                        $arrBerat = [];
                        $arrCompare = [];
                        $cartSelected['product'] = [];
                        foreach ($getCompare['data']['products'] as $key_getSelectedDetail) {
                            $product['detailId'] = $key_getSelectedDetail['id'];
                            $product['id'] = $key_getSelectedDetail['productId'];
                            $product['name'] = $key_getSelectedDetail['name'];
                            $product['model'] = $key_getSelectedDetail['model'];
                            $product['slug'] = $key_getSelectedDetail['slug'];
                            $product['image'] = $key_getSelectedDetail['image'];
                            $product['badges'] = $key_getSelectedDetail['badges'];
                            $product['stock'] = $key_getSelectedDetail['stock'];
                            $product['priceType'] = $key_getSelectedDetail['priceType'];
                            $product['qty'] = $key_getSelectedDetail['qty'];
                            $product['price'] = $key_getSelectedDetail['price'];
                            $product['priceCurrencyFormat'] = $key_getSelectedDetail['priceCurrencyFormat'];
                            $product['ppn'] = $key_getSelectedDetail['ppn'];
                            $product['ppnCurrencyFormat'] = $key_getSelectedDetail['ppnCurrencyFormat'];
                            $product['subTotalUnit'] = $key_getSelectedDetail['subTotalUnit'];
                            $product['subTotalUnitCurrencyFormat'] = $key_getSelectedDetail['subTotalUnitCurrencyFormat'];

                            $totalAllProduct[] = $product['qty'];

                            $cartSelected['product'][] = $product;
                        }

                        if ($getShipping == 'penyedia') {
                            $shippingCost = 0;
                        } else {
                            $getCost = $this->rajaongkir->cost([
                                'origin' => ($getCompare['data']['products'][0]['manufacturerId'] == '1') ? $this->core['customer']['school']['location']['city']['id'] : $parsing['mall']->id_rajaongkir,
                                'destination' => $this->core['customer']['school']['rajaOngkirId'],
                                'weight' => $getCompare['data']['weight'] . '000',
                                'courier' => $getShipping,
                            ]);

                            $shippingCost = (!empty($getCost)) ? $getCost : 0;
                        }

                        $data['weight'] = $getCompare['data']['weight'];
                        $data['weightText'] = $getCompare['data']['weightText'];
                        $data['shippingCost'] = $shippingCost;
                        $data['shippingCostCurrencyFormat'] = rupiah($data['shippingCost']);
                        $data['subTotal'] = $getCompare['data']['subTotal'];
                        $data['subTotalCurrencyFormat'] = $getCompare['data']['subTotalCurrencyFormat'];
                        $data['ppn'] = $getCompare['data']['ppn'];
                        $data['ppnCurrencyFormat'] = $getCompare['data']['ppnCurrencyFormat'];
                        $data['total'] = $getCompare['data']['total'];
                        $data['totalCurrencyFormat'] = $getCompare['data']['totalCurrencyFormat'];

                        if ($data['total'] > 200000000) {
                            $transactionValue = 'diatas 200jt';
                        } elseif ($data['total'] > 50000000) {
                            $transactionValue = 'diatas 50jt s.d 200jt';
                        } elseif ($data['total'] > 10000000) {
                            $transactionValue = 'diatas 10jt s.d 50jt';
                        } else {
                            $transactionValue = 'dibawah 10jt';
                        }

                        $data['transactionValue'] = $transactionValue;
                        $data['cartSelected'] = $cartSelected;

                        $parsing['db_sumber_dana'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_sumber_dana',
                        ])->result();
                        $data['sourceOfFunds'] = [];
                        foreach ($parsing['db_sumber_dana'] as $key_db_sumber_dana) {
                            $data['sourceOfFunds'][] = [
                                'value' => $key_db_sumber_dana->kode_sumber_dana,
                                'name' => $key_db_sumber_dana->sumber_dana,
                            ];
                        }

                        $parsing['db_mall_ekspedisi'] = $this->api_model->select_data([
                            'field' => '
                            b.id_ekspedisi,
                            b.kode_ekspedisi,
                            b.nama_ekspedisi,
                            b.logo',
                            'table' => 'db_mall_ekspedisi a',
                            'join' => [
                                [
                                    'table' => 'db_ekspedisi b',
                                    'on' => 'b.id_ekspedisi=a.id_ekspedisi',
                                    'type' => 'inner'
                                ],
                            ],
                            'where' => [
                                'mall_id' => $parsing['mall']->mall_id,
                                'a.status' => '1',
                            ]
                        ])->result();
                        $data['shipping'] = [];
                        $data['shippingSelected'] = (object) [];
                        foreach ($parsing['db_mall_ekspedisi'] as $key_db_mall_ekspedisi) {
                            $data['shipping'][] = [
                                'value' => $key_db_mall_ekspedisi->kode_ekspedisi,
                                'name' => $key_db_mall_ekspedisi->nama_ekspedisi,
                            ];

                            if ($getShipping == $key_db_mall_ekspedisi->kode_ekspedisi) {
                                $data['shippingSelected'] = [
                                    'value' => $key_db_mall_ekspedisi->kode_ekspedisi,
                                    'name' => $key_db_mall_ekspedisi->nama_ekspedisi,
                                ];
                            }
                        }

                        $data['paymentMethod'] = [
                            [
                                'group' => 'Virtual Account',
                                'items' => [
                                    [
                                        'value' => 'bank_mandiri_va',
                                        'name' => 'Virtual Account Mandiri',
                                    ],
                                ]
                            ]
                        ];

                        if ($parsing['mall']->name == 'EUREKA TRIAL') {
                            $data['paymentMethod'][0]['items'][] = [
                                'value' => 'bank_bri_va',
                                'name' => 'Virtual Account BRI (BRIVA)',
                            ];
                        }

                        $parsing['db_bank'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_bank',
                            'where' => [
                                'status' => '1',
                            ]
                        ])->result();
                        $data['paymentMethod'][] = [
                            'group' => 'Bank Transfer',
                            'items' => []
                        ];
                        foreach ($parsing['db_bank'] as $key_db_bank) {
                            if ($key_db_bank->slug != 'bank_mandiri_va') {
                                $data['paymentMethod'][1]['items'][] = [
                                    'value' => $key_db_bank->slug,
                                    'name' => $key_db_bank->bank,
                                ];
                            }
                        }

                        $data['paymentDue'] = [
                            [
                                'value' => '1',
                                'name' => '1 Hari',
                            ],
                            [
                                'value' => '3',
                                'name' => '3 Hari',
                            ],
                            [
                                'value' => '7',
                                'name' => '7 Hari',
                            ],
                            [
                                'value' => '14',
                                'name' => '14 Hari',
                            ],
                        ];

                        $data['custom'] = [
                            'origin' => ($getCompare['data']['products'][0]['manufacturerId'] == '1') ? $this->core['customer']['school']['location']['city']['id'] : $parsing['mall']->id_rajaongkir
                        ];

                        $output = $data;
                    }
                }

                $response = $this->formatter([
                    'code' => $code,
                    'message' => 'get data success',
                    'data' => $output,
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
                    if (empty($this->get('from'))) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => 'parameter not found',
                            'data' => (object) [],
                        ]);
                    } else {
                        if (!in_array($this->get('from'), [
                            'cart', 'compare'
                        ])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_BAD_REQUEST,
                                'message' => 'parameter not found, valid parameter is cart, compare',
                                'data' => (object) [],
                            ]);
                        } else {
                            if ($this->get('from') == 'compare') {
                                $getCompare = json_decode(shoot_api([
                                    'url' => base_url() . "compare/onGoing/{$this->post('id')}",
                                    'method' => 'GET',
                                    'header' => [
                                        "Authorization: {$this->input->request_headers()['Authorization']}"
                                    ],
                                ]), true);
                                if ($getCompare['status']['code'] !== 200) {
                                    $checking = false;
                                    $response = $this->formatter([
                                        'code' => $getCompare['status']['code'],
                                        'message' => $getCompare['status']['message'],
                                        'data' => (object) []
                                    ]);
                                }
                            } elseif ($this->get('from') == 'cart') {
                                $check['db_cart'] = $this->api_model->select_data([
                                    'field' => '*',
                                    'table' => 'db_cart',
                                    'where' => [
                                        'sekolah_id' => $this->core['customer']['school']['id'],
                                        'id_cart' => $this->post('id'),
                                    ]
                                ])->row();
                                if (empty($check['db_cart'])) {
                                    $checking = false;
                                    $response = $this->formatter([
                                        'code' => self::HTTP_NOT_FOUND,
                                        'message' => 'cart not found',
                                    ]);
                                } else {
                                    $check['db_product'] = $this->api_model->select_data([
                                        'field' => '*',
                                        'table' => 'db_product',
                                        'where' => [
                                            'product_id' => $check['db_cart']->id_produk,
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
                        }
                    }
                }
            }

            if ($checking === true) {
                $getCheckout = json_decode(shoot_api([
                    'url' => base_url() . "checkout/{$this->post('id')}?shipping={$this->post('shipping')}&from={$this->post('from')}",
                    'method' => 'GET',
                    'header' => [
                        "Authorization: {$this->input->request_headers()['Authorization']}"
                    ],
                ]), true);
                if ($getCheckout['status']['code'] !== 200) {
                    $response = $this->formatter([
                        'code' => $getCheckout['status']['code'],
                        'message' => $getCheckout['status']['message']
                    ]);
                } else {
                    $today = date('ymd');
                    $parsing['db_sumber_dana'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_sumber_dana',
                        'where' => [
                            'kode_sumber_dana' => $this->post('sourceOfFund'),
                        ]
                    ])->row_array();

                    $parsing['lastInvoice'] = $this->api_model->select_data([
                        'field' => 'max(invoice_no) AS last',
                        'table' => 'db_order',
                        'like' => [
                            'invoice_no' => $today,
                        ]
                    ])->row_array();
                    $invoiceNumber = $today . sprintf('%04s', substr($parsing['lastInvoice']['last'], 7, 4) + 1);

                    $this->db->trans_start();

                    $this->api_model->send_data([
                        'data' => [
                            'invoice_no' => $invoiceNumber,
                            'invoice_prefix' => 'SIPlah',
                            'customer_id' => $this->core['customer']['id'],
                            'email' => $this->core['customer']['email'],
                            'telephone' => $this->core['customer']['school']['telephone'],
                            'npsn' => $this->core['customer']['school']['npsn'],
                            'payment_tempo' => $this->post('paymentDue'),
                            'payment_method' => $this->post('paymentMethod'),
                            'sekolah_id' => $this->core['customer']['school']['id'],
                            'sekolah_zona' => $this->core['customer']['school']['location']['zone'],
                            'shipping_id' => $this->core['customer']['school']['schoolId'],
                            'shipping_firstname' => $this->core['customer']['firstName'],
                            'shipping_lastname' => $this->core['customer']['lastName'],
                            'shipping_company' => $this->core['customer']['school']['name'],
                            'shipping_address_1' => $this->core['customer']['school']['location']['latitude'] . ',' . $this->core['customer']['school']['location']['longitude'],
                            'shipping_address_2' => $this->core['customer']['school']['location']['address'],
                            'shipping_zone' => $this->core['customer']['school']['location']['village'],
                            'shipping_city' => $this->core['customer']['school']['location']['city']['name'],
                            'shipping_kecamatan' => $this->core['customer']['school']['location']['district']['name'],
                            'shipping_postcode' => $this->core['customer']['school']['location']['postalCode'],
                            'shipping_province' => $this->core['customer']['school']['location']['province']['name'],
                            'shipping_country' => 'Indonesia',
                            'comment' =>  $this->post('note'),
                            'order_status_id' => 0,
                            'ip' => $this->input->ip_address(),
                            'user_agent' => $this->agent->agent_string(),
                            'date_added' => date('Y-m-d H:i:s'),
                            'date_modified' => '0000-00-00 00:00:00',
                            'date_invoice' => '0000-00-00 00:00:00',
                            'date_sampai' => '0000-00-00',
                            'mall_id' => $getCheckout['data']['cartSelected']['mall']['id'],
                            'mall_name' => $getCheckout['data']['cartSelected']['mall']['name'],
                            'mall_address' => $getCheckout['data']['cartSelected']['mall']['address'],
                            'mall_zone' => $getCheckout['data']['cartSelected']['mall']['zone'],
                            'mall_city' => $getCheckout['data']['cartSelected']['mall']['city'],
                            'mall_province' => $getCheckout['data']['cartSelected']['mall']['province'],
                            'mall_postcode' => $getCheckout['data']['cartSelected']['mall']['postalCode'],
                            'mall_phone' => $getCheckout['data']['cartSelected']['mall']['phone'],
                            'mall_id_rajong' => $getCheckout['data']['custom']['origin'],
                            'berattotal' => $getCheckout['data']['weight'] * 1000,
                            'shipping_code' => $this->post('shipping'),
                            'shipping_method' => strtoupper($this->post('shipping')) . ' - ' . $this->core['customer']['school']['location']['city']['name'],
                            'perangkat' => $this->agent->platform(),
                            'tunjuk_cabang' => 0,
                            'nego' => '0',
                            'ongkoskirim' => $getCheckout['data']['shippingCost'],
                            'subtotal' => $getCheckout['data']['subTotal'],
                            'ppn' => $getCheckout['data']['ppn'],
                            'total' => $getCheckout['data']['total'],
                            'sumber_dana' => $parsing['db_sumber_dana']['sumber_dana'],
                            'sumber_dana_ws' => $this->post('sourceOfFund'),
                            'nilai_transaksi' => $getCheckout['data']['transactionValue']
                        ],
                        'table' => 'db_order'
                    ]);

                    $lastId = $this->db->insert_id();

                    if ($this->post('paymentMethod') == 'bank_mandiri_va') {
                        $va = '89859' . $lastId;
                        $vas = 'waiting';
                    } else if ($this->post('paymentMethod') == 'bank_bri_va') {
                        $va = '12623' . $lastId;
                        $vas = 'waiting';
                    } else {
                        $va = '';
                        $vas = 'none';
                    }

                    $this->api_model->send_data([
                        'where' => [
                            'order_id' => $lastId
                        ],
                        'data' => [
                            'invoice' => $invoiceNumber . '-' . $lastId,
                            'payment_va' => $va,
                            'payment_va_status' => $vas,
                        ],
                        'table' => 'db_order'
                    ]);

                    $this->api_model->send_data([
                        'data' => [
                            'order_id' => $lastId,
                            'order_status_id' => 0,
                            'notify' => 0,
                            'date_added' => date('Y-m-d H:i:s'),
                        ],
                        'table' => 'db_order_history'
                    ]);

                    $this->api_model->send_data([
                        'data' => [
                            'order_id' => $lastId,
                            'code' => 'total',
                            'title' => 'Total',
                            'value' => $getCheckout['data']['total'],
                            'sort_order' => '9'
                        ],
                        'table' => 'db_order_total'
                    ]);

                    $this->api_model->send_data([
                        'data' => [
                            'order_id' => $lastId,
                            'code' => 'sub_total',
                            'title' => 'Sub-Total',
                            'value' => $getCheckout['data']['subTotal'],
                            'sort_order' => '1'
                        ],
                        'table' => 'db_order_total'
                    ]);

                    $this->api_model->send_data([
                        'data' => [
                            'order_id' => $lastId,
                            'code' => 'shipping',
                            'title' => strtoupper($this->post('shipping')) . ' - ' . $this->core['customer']['school']['location']['city']['name'],
                            'value' => $getCheckout['data']['shippingCost'],
                            'sort_order' => '3'
                        ],
                        'table' => 'db_order_total'
                    ]);

                    $this->api_model->send_data([
                        'data' => [
                            'order_id' => $lastId,
                            'code' => 'pembungkus',
                            'title' => 'Pembungkus',
                            'value' => '0',
                            'sort_order' => '4'
                        ],
                        'table' => 'db_order_total'
                    ]);

                    $this->api_model->send_data([
                        'data' => [
                            'order_id' => $lastId,
                            'code' => 'asuransi',
                            'title' => 'Asuransi',
                            'value' => '0',
                            'sort_order' => '5'
                        ],
                        'table' => 'db_order_total'
                    ]);

                    $this->api_model->send_data([
                        'data' => [
                            'order_id' => $lastId,
                            'code' => 'biaya_tambahan',
                            'title' => 'Biaya Tambahan',
                            'value' => '0',
                            'sort_order' => '6'
                        ],
                        'table' => 'db_order_total'
                    ]);

                    if ($getCheckout['data']['ppn'] > 0) {
                        $this->api_model->send_data([
                            'data' => [
                                'order_id' => $lastId,
                                'code' => 'ppn',
                                'title' => 'PPN',
                                'value' => $getCheckout['data']['ppn'],
                                'sort_order' => '7'
                            ],
                            'table' => 'db_order_total'
                        ]);
                    }

                    foreach ($getCheckout['data']['cartSelected']['product'] as $key_product) {
                        $this->api_model->send_data([
                            'data' => [
                                'mall_id' => $getCheckout['data']['cartSelected']['mall']['id'],
                                'order_id' => $lastId,
                                'product_id' => $key_product['id'],
                                'name' => $key_product['name'],
                                'model' => $key_product['model'],
                                'quantity' => $key_product['qty'],
                                'id_nego' => $getCheckout['data']['cartSelected']['negoId'],
                                'id_banding' => ($this->post('from') == 'compare') ? $getCheckout['data']['cartSelected']['id'] : 0,
                                'price' => $key_product['price'],
                                'total' => $key_product['subTotalUnit'],
                                'tax' => 0,
                                'reward' => 0
                            ],
                            'table' => 'db_order_product'
                        ]);

                        $this->api_model->send_data([
                            'where' => [
                                'product_id' => $key_product['id']
                            ],
                            'data' => [
                                'storage_quantity' => $check['db_product']->storage_quantity - $key_product['qty'],
                            ],
                            'table' => 'db_product'
                        ]);

                        if ($this->post('from') == 'cart') {
                            $this->api_model->delete_data([
                                'where' => [
                                    'id_cart' => $key_product['detailId']
                                ],
                                'table' => 'db_cart'
                            ]);
                        }

                        if ($getCheckout['data']['cartSelected']['negoId'] != '0' || !empty($getCheckout['data']['cartSelected']['negoId'])) {
                            $this->api_model->send_data([
                                'where' => [
                                    'id_nego' => $getCheckout['data']['cartSelected']['negoId']
                                ],
                                'data' => [
                                    'selesai' => '1'
                                ],
                                'table' => 'db_nego'
                            ]);
                        }
                    }

                    if ($this->post('from') == 'compare') {
                        $transactionValue = 0;
                        if ($getCheckout['data'] > 50000000) {
                            $transactionValue = 50000000;
                        } else {
                            $transactionValue = 100000000;
                        }

                        $this->api_model->send_data([
                            'data' => [
                                'order_id' => $lastId,
                                'customer_id' => $this->core['customer']['id'],
                                'sekolah_id' => $this->core['customer']['school']['id'],
                                'note' => "Perbandingan Sumber Dana {$parsing['db_sumber_dana']['sumber_dana']} Nilai Transaksi " . rupiah($transactionValue),
                                'sumber_dana' => $parsing['db_sumber_dana']['sumber_dana'],
                                'nilai_transaksi' => $transactionValue,
                                'date_created' => date('Y-m-d H:i:s'),
                            ],
                            'table' => 'db_product_compare'
                        ]);

                        $lastIdProductCompare = $this->db->insert_id();

                        foreach ($getCheckout['data']['cartSelected']['product'] as $key_product) {
                            $parsing['db_product_to_category'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_product_to_category',
                                'where' => [
                                    'product_id' => $key_product['id']
                                ]
                            ])->row();

                            $this->api_model->send_data([
                                'data' => [
                                    'id_compare' => $lastIdProductCompare,
                                    'order_id' => $lastId,
                                    'id_product' => $key_product['id'],
                                    'id_kategori' => $parsing['db_product_to_category']->category_id,
                                    'mall_id' => $getCheckout['data']['cartSelected']['mall']['id'],
                                    'qty' => $key_product['qty'],
                                    'price' => $key_product['price'],
                                    'price_type' => $key_product['priceType'],
                                    'ppn' => $key_product['ppn'],
                                    'berat' => $getCheckout['data']['weight'],
                                    'expedisi' => $this->post('shipping'),
                                    'ongkir' => $getCheckout['data']['shippingCost'],
                                ],
                                'table' => 'db_product_compare_detail'
                            ]);
                        }

                        $this->api_model->delete_data([
                            'where' => [
                                'id_user' => $this->core['customer']['id']
                            ],
                            'table' => 'db_compare'
                        ]);
                    }

                    $this->db->trans_complete();

                    if ($this->db->trans_status() === false) {
                        $db_error = $this->db->error();
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => "add data failed [Database error! Error Code [{$db_error['code']}] Error: {$db_error['message']}]",
                        ]);
                    } else {
                        $mailing = $this->mailingWithNotif([
                            'subject' => 'Pesanan baru telah dibuat',
                            'message' => "Pesanan baru dengan invoice {$invoiceNumber} telah diminta oleh {$this->core['customer']['school']['name']}",
                            'to' => [
                                $getCheckout['data']['cartSelected']['mall']['email']
                            ],
                            'mallId' => $getCheckout['data']['cartSelected']['mall']['id'],
                            'linkId' => $lastId,
                            'type' => 'order',
                        ]);

                        $response = $this->formatter([
                            'code' => self::HTTP_OK,
                            'message' => "add data success",
                            'mailing' => $mailing,
                            'data' => $lastId
                        ]);
                    }
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }
}
