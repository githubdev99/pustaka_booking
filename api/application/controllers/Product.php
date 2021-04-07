<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Product extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function category_get()
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if ($checking === true) {
                $parsing['category_parent'] = $this->product_category([
                    'is_parent' => true
                ]);

                if (empty($parsing['category_parent'])) {
                    $output = [];
                    $code = self::HTTP_NO_CONTENT;
                } else {
                    $output = [];
                    $code = self::HTTP_OK;
                    foreach ($parsing['category_parent'] as $key_category_parent) {
                        $data['title'] = $key_category_parent->name;
                        $data['url'] = $key_category_parent->slug;
                        $data['total'] = $this->api_model->count_all_data([
                            'where' => [
                                'category_id' => $key_category_parent->category_id
                            ],
                            'table' => 'db_product_to_category'
                        ]);

                        $parsing['category_children'] = $this->product_category([
                            'is_parent' => false,
                            'id' => $key_category_parent->category_id
                        ]);
                        if (empty($parsing['category_children'])) {
                            $data['submenu'] = [];
                        } else {
                            $submenu['type'] = 'menu';
                            $submenu['menu'] = [];
                            foreach ($parsing['category_children'] as $key_category_children) {
                                $menu['title'] = $key_category_children->name;
                                $menu['url'] = $key_category_children->slug;
                                $menu['total'] = $this->api_model->count_all_data([
                                    'where' => [
                                        'category_id' => $key_category_children->category_id
                                    ],
                                    'table' => 'db_product_to_category'
                                ]);

                                $parsing['category_children2'] = $this->product_category([
                                    'is_parent' => false,
                                    'id' => $key_category_children->category_id
                                ]);
                                if (empty($parsing['category_children2'])) {
                                    $menu['submenu'] = [];
                                } else {
                                    $menu['submenu'] = [];
                                    foreach ($parsing['category_children2'] as $key_category_children2) {
                                        $menu2['title'] = $key_category_children2->name;
                                        $menu2['url'] = $key_category_children2->slug;
                                        $menu2['total'] = $this->api_model->count_all_data([
                                            'where' => [
                                                'category_id' => $key_category_children2->category_id
                                            ],
                                            'table' => 'db_product_to_category'
                                        ]);

                                        $menu['submenu'][] = $menu2;
                                    }
                                }

                                $submenu['menu'][] = $menu;
                            }

                            $data['submenu'] = $submenu;
                        }

                        $output[] = $data;
                    }
                }

                $response = $this->formatter([
                    'code' => $code,
                    'message' => 'get data success',
                    'data' => $output
                ]);
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function index_get($id = null)
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if ($this->get('page') != null || $this->get('limit') != null) {
                if (empty($id)) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'parameter not found',
                        'data' => []
                    ]);
                } else {
                    $parsing['getCategory'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_category_description',
                        'where' => [
                            'slug' => $id
                        ]
                    ])->row();
                    if (empty($parsing['getCategory'])) {
                        $parsing['getMall'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_mall',
                            'where' => [
                                'slug' => $id
                            ]
                        ])->row();
                        if (empty($parsing['getMall'])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_NOT_FOUND,
                                'message' => 'data not found',
                                'data' => [
                                    'total' => 0,
                                    'items' => [],
                                ]
                            ]);
                        } else {
                            $filters = $this->filter_product_mall([
                                'mall_id' => $parsing['getMall']->mall_id,
                            ]);
                            $sorts = $this->sort_product_mall();

                            if ($this->get('page') == null || $this->get('limit') == null) {
                                $checking = false;
                                $response = $this->formatter([
                                    'code' => self::HTTP_BAD_REQUEST,
                                    'message' => 'page or limit not found',
                                    'data' => [
                                        'total' => 0,
                                        'items' => [],
                                        'filters' => $filters,
                                        'sorts' => $sorts,
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
                                            'filters' => $filters,
                                            'sorts' => $sorts,
                                        ]
                                    ]);
                                }
                            }
                        }
                    } else {
                        $filters = $this->filter_product();
                        $sorts = $this->sort_product();

                        if ($this->get('page') == null || $this->get('limit') == null) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_BAD_REQUEST,
                                'message' => 'page or limit not found',
                                'data' => [
                                    'total' => 0,
                                    'items' => [],
                                    'filters' => $filters,
                                    'sorts' => $sorts,
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
                                        'filters' => $filters,
                                        'sorts' => $sorts,
                                    ]
                                ]);
                            }
                        }

                        $parsing['db_category_description'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_category_description',
                            'where' => [
                                'slug' => $id
                            ]
                        ])->result();
                        if (empty($parsing['db_category_description'])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_NOT_FOUND,
                                'message' => 'category not found',
                                'data' => [
                                    'total' => 0,
                                    'items' => [],
                                    'filters' => $filters,
                                    'sorts' => $sorts,
                                ]
                            ]);
                        }
                    }
                }
            }

            if ($checking === true) {
                if (!empty($parsing['getCategory']) || !empty($parsing['getMall'])) {
                    $param['product']['field'] = '
                    aa.product_id, 
                    aa.storage_quantity as stok,
                    aa.sku,
                    cc.name,
                    cc.seo as slug,
                    aa.date_added,
                    aa.image,
                    aa.price,
                    aa.price1,
                    aa.price2,
                    aa.price3,
                    aa.price4,
                    aa.price5,
                    aa.grosir_price1,
                    aa.grosir_price2,
                    aa.grosir_price3,
                    aa.grosir_price4,
                    aa.mall_id,
                    aa.status,
                    cd.name as category,
                    mall.name as mall,
                    mall.slug as mall_slug,
                    mall.city as mall_city,
                    mall.province as mall_province,
                    aa.manufacturer_id,
                    AVG(db_product_ulasan.rate) AS rating,
                    COUNT(db_product_ulasan.customer_id) AS total_review,
                    aa.new,
                    mn.name as manufacturer_name,
                    mn.slug as manufacturer_slug,
                    cc.tag';
                    $param['product']['table'] = 'db_product aa';
                    $param['product']['join'] = [
                        [
                            'table' => 'db_product_to_category bb',
                            'on' => 'bb.product_id=aa.product_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_product_description cc',
                            'on' => 'cc.product_id=aa.product_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_category_description cd',
                            'on' => 'cd.category_id=bb.category_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_manufacturer mn',
                            'on' => 'mn.manufacturer_id=aa.manufacturer_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_mall as mall',
                            'on' => 'mall.mall_id=aa.mall_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_product_ulasan',
                            'on' => 'db_product_ulasan.product_id = aa.product_id',
                            'type' => 'left'
                        ]
                    ];

                    if (!empty($this->get('keyword'))) {
                        $param['product']['like'] = [
                            'cc.name' => $this->get('keyword')
                        ];

                        $arr_keyword = [
                            'aa.storage_quantity >=' => 2
                        ];
                    } else {
                        $arr_keyword = [];
                    }

                    $filter_price = $this->get('filter_price');

                    if (!empty($filter_price)) {
                        if (strpos($filter_price, '-') !== false) {
                            $exp_filter_price = explode('-', $filter_price);

                            $arr_filter_price = [
                                'aa.price >=' => $exp_filter_price[0],
                                'aa.price <=' => $exp_filter_price[1]
                            ];
                        } else {
                            $arr_filter_price = [
                                'aa.price >=' => $filter_price
                            ];
                        }
                    } else {
                        $arr_filter_price = [];
                    }

                    if (!empty($parsing['getCategory'])) {
                        $arr_slug = [
                            'cd.slug' => $id,
                            'aa.storage_quantity >=' => 2
                        ];
                    } else {
                        $arr_slug = array_merge([
                            'aa.mall_id' => $parsing['getMall']->mall_id
                        ], (!empty($this->get('filter_category'))) ? [
                            'cd.slug' => $this->get('filter_category')
                        ] : []);
                    }

                    $arr_filter_manufacturer = (!empty($this->get('filter_manufacturer'))) ? [
                        'mn.slug' => $this->get('filter_manufacturer')
                    ] : [];

                    $arr_filter_province = (!empty($this->get('filter_province'))) ? [
                        'mall.province' => $this->get('filter_province')
                    ] : [];

                    $param['product']['where'] = array_merge([
                        'aa.blokir' => '0',
                        'aa.status' => '1',
                        'aa.disabled' => 'N',
                        'cc.name !=' => '',
                    ], $arr_keyword, $arr_filter_price, $arr_slug, $arr_filter_manufacturer, $arr_filter_province);

                    $param['product']['group_by'] = 'aa.product_id';

                    $get_sort = (!empty($this->get('sort'))) ? explode('-', $this->get('sort')) : null;
                    if (!empty($get_sort)) {
                        if ($get_sort[0] == 'name') {
                            $param['product']['order_by'] = [
                                'cc.' . $get_sort[0] => $get_sort[1]
                            ];
                        } else {
                            $param['product']['order_by'] = [
                                'aa.' . $get_sort[0] => $get_sort[1]
                            ];
                        }
                    } else {
                        $param['product']['order_by'] = [
                            'aa.date_added' => 'desc'
                        ];
                    }

                    $param['product']['limit'] = [
                        $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                    ];
                    $parsing['product'] = $this->api_model->select_data($param['product'])->result();

                    $output = [];
                    if (empty($parsing['product'])) {
                        $data['total'] = 0;
                        $data['items'] = [];
                        $code = self::HTTP_NO_CONTENT;
                    } else {
                        $code = self::HTTP_OK;
                        $total_record = $this->api_model->count_all_data($param['product']);

                        $limit = (int) $this->get('limit');
                        $current_page = (int) $this->get('page');
                        $total_page = ceil($total_record / $limit);

                        $data['page'] = $current_page;
                        $data['limit'] = $limit;
                        $data['sort'] = (!empty($this->get('sort'))) ? $this->get('sort') : 'default';
                        $data['total'] = $total_record;
                        $data['pages'] = $total_page;
                        $data['items'] = [];

                        foreach ($parsing['product'] as $key_product) {
                            $items['id'] = $key_product->product_id;
                            $items['name'] = $key_product->name;
                            $items['sku'] = $key_product->sku;
                            $items['slug'] = $key_product->slug;
                            $items['stock'] = $key_product->stok;

                            if (empty($key_product->tag)) {
                                $items['tag'] = [];
                            } else {
                                $items['tag'] = [];
                                foreach (explode(', ', $key_product->tag) as $key_tag) {
                                    if (!empty($key_tag)) {
                                        $items['tag'][] = $key_tag;
                                    }
                                }
                            }

                            $items['mall'] = [
                                'id' => $key_product->mall_id,
                                'name' => $key_product->mall,
                                'slug' => $key_product->mall_slug,
                                'city' => $key_product->mall_city,
                                'province' => $key_product->mall_province
                            ];

                            $items['manufacturer'] = [
                                'name' => $key_product->manufacturer_name,
                                'slug' => $key_product->manufacturer_slug,
                            ];

                            $price_zone = (array_sum([
                                $key_product->price1,
                                $key_product->price2,
                                $key_product->price3,
                                $key_product->price4,
                                $key_product->price5
                            ]) > 0) ? [
                                [
                                    'price' => $key_product->price1,
                                    'priceCurrencyFormat' => rupiah($key_product->price1)
                                ],
                                [
                                    'price' => $key_product->price2,
                                    'priceCurrencyFormat' => rupiah($key_product->price2)
                                ],
                                [
                                    'price' => $key_product->price3,
                                    'priceCurrencyFormat' => rupiah($key_product->price3)
                                ],
                                [
                                    'price' => $key_product->price4,
                                    'priceCurrencyFormat' => rupiah($key_product->price4)
                                ],
                                [
                                    'price' => $key_product->price5,
                                    'priceCurrencyFormat' => rupiah($key_product->price5)
                                ]
                            ] : [];

                            if ($key_product->price1 != '0' || !empty($key_product->price1)) {
                                if (!empty($this->core['customer'])) {
                                    if ($this->core['customer']['school']['location']['zone'] == '1') {
                                        $price = $key_product->price1;
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '2') {
                                        $price = $key_product->price2;
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '3') {
                                        $price = $key_product->price3;
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '4') {
                                        $price = $key_product->price4;
                                    } elseif ($this->core['customer']['school']['location']['zone'] == '5') {
                                        $price = $key_product->price5;
                                    } else {
                                        $price = $key_product->price;
                                    }
                                } else {
                                    $price = $key_product->price;
                                }
                            } else {
                                $price = $key_product->price;
                            }

                            $items['price'] = [
                                'primary' => $price,
                                'primaryCurrencyFormat' => rupiah($price),
                                'zone' => $price_zone
                            ];

                            $parsing['db_product_image'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_product_image',
                                'where' => [
                                    'product_id' => $key_product->product_id
                                ]
                            ])->result();
                            $items['images'] = [];
                            $items['images'][] = (!empty($key_product->image) || $key_product->image != '') ? $this->core['url_image_product'] . $key_product->image : $this->core['image_not_found'];
                            foreach ($parsing['db_product_image'] as $key_db_product_image) {
                                $items['images'][] = (!empty($key_db_product_image->image) || $key_db_product_image->image != '') ? $this->core['url_image_product'] . $key_db_product_image->image : $this->core['image_not_found'];
                            }

                            $items['badges'] = [];
                            if ($key_product->new != 0) {
                                $items['badges'][] = 'new';
                            }
                            if (array_sum([
                                $key_product->grosir_price1,
                                $key_product->grosir_price2,
                                $key_product->grosir_price3,
                                $key_product->grosir_price4
                            ]) > 0) {
                                $items['badges'][] = 'grosir';
                            }
                            if (array_sum([
                                $key_product->price1,
                                $key_product->price2,
                                $key_product->price3,
                                $key_product->price4,
                                $key_product->price5
                            ]) > 0) {
                                $items['badges'][] = 'het';
                            }

                            $items['rating'] = number_format($key_product->rating, 1, '.', '');
                            $items['review'] = (int) $key_product->total_review;

                            $parsing['db_product_to_category'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_product_to_category',
                                'where' => [
                                    'product_id' => $key_product->product_id
                                ]
                            ])->result();
                            $items['category'] = [];
                            foreach ($parsing['db_product_to_category'] as $key_db_product_to_category) {
                                $parsing['db_category_description'] = $this->api_model->select_data([
                                    'field' => '*',
                                    'table' => 'db_category_description',
                                    'where' => [
                                        'category_id' => $key_db_product_to_category->category_id
                                    ]
                                ])->row();
                                $category['title'] = $parsing['db_category_description']->name;
                                $category['url'] = $parsing['db_category_description']->slug;

                                $items['category'][] = $category;
                            }

                            $data['items'][] = $items;
                        }
                    }

                    $data['filters'] = $filters;
                    $data['sorts'] = $sorts;
                    $output = $data;

                    $response = $this->formatter([
                        'code' => $code,
                        'message' => 'get data success',
                        'data' => $output
                    ]);
                } else {
                    $param['product']['field'] = '
                    aa.product_id, 
                    aa.storage_quantity as stok,
                    aa.sku,
                    cc.name,
                    cc.seo as slug,
                    aa.date_added,
                    aa.image,
                    aa.price,
                    aa.price1,
                    aa.price2,
                    aa.price3,
                    aa.price4,
                    aa.price5,
                    aa.grosir_min1,
                    aa.grosir_price1,
                    aa.grosir_min2,
                    aa.grosir_price2,
                    aa.grosir_min3,
                    aa.grosir_price3,
                    aa.grosir_min4,
                    aa.grosir_price4,
                    aa.mall_id,
                    aa.status,
                    cd.name as category,
                    mall.name as mall,
                    mall.slug as mall_slug,
                    mall.city as mall_city,
                    mall.province as mall_province,
                    aa.manufacturer_id,
                    AVG(db_product_ulasan.rate) AS rating,
                    COUNT(db_product_ulasan.customer_id) AS total_review,
                    aa.new,
                    mn.name as manufacturer_name,
                    mn.slug as manufacturer_slug,
                    cc.description,
                    cc.tag,
                    aa.model,
                    aa.unit_type,
                    aa.weight,
                    aa.length,
                    aa.width,
                    aa.height,
                    aa.minimum,
                    aa.viewed,
                    aa.sk_kelulusan,
                    aa.ppn';
                    $param['product']['table'] = 'db_product aa';
                    $param['product']['join'] = [
                        [
                            'table' => 'db_product_to_category bb',
                            'on' => 'bb.product_id=aa.product_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_product_description cc',
                            'on' => 'cc.product_id=aa.product_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_category_description cd',
                            'on' => 'cd.category_id=bb.category_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_manufacturer mn',
                            'on' => 'mn.manufacturer_id=aa.manufacturer_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_mall as mall',
                            'on' => 'mall.mall_id=aa.mall_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_product_ulasan',
                            'on' => 'db_product_ulasan.product_id = aa.product_id',
                            'type' => 'left'
                        ]
                    ];

                    $param['product']['where'] = [
                        'aa.mall_id' => $this->get('mall'),
                        'cc.seo' => $id,
                    ];

                    $parsing['product'] = $this->api_model->select_data($param['product'])->row();

                    if (empty($parsing['product']) || empty($parsing['product']->product_id)) {
                        $output = (object) [];
                        $code = self::HTTP_NO_CONTENT;
                    } else {
                        $output = [];
                        $code = self::HTTP_OK;

                        $items['id'] = $parsing['product']->product_id;
                        $items['name'] = $parsing['product']->name;
                        $items['ppn'] = (int) $parsing['product']->ppn;
                        $items['sku'] = $parsing['product']->sku;
                        $items['slug'] = $parsing['product']->slug;
                        $items['stock'] = $parsing['product']->stok;
                        $items['description'] = $parsing['product']->description;

                        if (empty($parsing['product']->tag)) {
                            $items['tag'] = [];
                        } else {
                            $items['tag'] = [];
                            foreach (explode(', ', $parsing['product']->tag) as $key_tag) {
                                if (!empty($key_tag)) {
                                    $items['tag'][] = $key_tag;
                                }
                            }
                        }

                        $items['viewed'] = $parsing['product']->viewed;

                        $items['specification'] = [
                            'code' => $parsing['product']->model,
                            'unitType' => $parsing['product']->unit_type,
                            'weight' => $parsing['product']->weight,
                            'length' => $parsing['product']->length,
                            'width' => $parsing['product']->width,
                            'height' => $parsing['product']->height,
                            'minimumOrder' => $parsing['product']->minimum,
                            'skKelulusan' => $parsing['product']->sk_kelulusan,
                        ];

                        $items['mall'] = [
                            'id' => $parsing['product']->mall_id,
                            'name' => $parsing['product']->mall,
                            'slug' => $parsing['product']->mall_slug,
                            'city' => $parsing['product']->mall_city,
                            'province' => $parsing['product']->mall_province
                        ];
                        $items['manufacturer'] = [
                            'name' => $parsing['product']->manufacturer_name,
                            'slug' => $parsing['product']->manufacturer_slug,
                        ];

                        $price_zone = (array_sum([
                            $parsing['product']->price1,
                            $parsing['product']->price2,
                            $parsing['product']->price3,
                            $parsing['product']->price4,
                            $parsing['product']->price5
                        ]) > 0) ? [
                            [
                                'price' => $parsing['product']->price1,
                                'priceCurrencyFormat' => rupiah($parsing['product']->price1)
                            ],
                            [
                                'price' => $parsing['product']->price2,
                                'priceCurrencyFormat' => rupiah($parsing['product']->price2)
                            ],
                            [
                                'price' => $parsing['product']->price3,
                                'priceCurrencyFormat' => rupiah($parsing['product']->price3)
                            ],
                            [
                                'price' => $parsing['product']->price4,
                                'priceCurrencyFormat' => rupiah($parsing['product']->price4)
                            ],
                            [
                                'price' => $parsing['product']->price5,
                                'priceCurrencyFormat' => rupiah($parsing['product']->price5)
                            ]
                        ] : [];

                        $price_grosir = [];
                        if (array_sum([
                            $parsing['product']->grosir_price1,
                            $parsing['product']->grosir_price2,
                            $parsing['product']->grosir_price3,
                            $parsing['product']->grosir_price4
                        ]) > 0) {
                            if ($parsing['product']->grosir_min1 > 0) {
                                $price_grosir[] = [
                                    'min' => $parsing['product']->grosir_min1,
                                    'price' => $parsing['product']->grosir_price1,
                                    'priceCurrencyFormat' => rupiah($parsing['product']->grosir_price1),
                                ];
                            } elseif ($parsing['product']->grosir_min2 > 0) {
                                $price_grosir[] = [
                                    'min' => $parsing['product']->grosir_min2,
                                    'price' => $parsing['product']->grosir_price2,
                                    'priceCurrencyFormat' => rupiah($parsing['product']->grosir_price2),
                                ];
                            } elseif ($parsing['product']->grosir_min3 > 0) {
                                $price_grosir[] = [
                                    'min' => $parsing['product']->grosir_min3,
                                    'price' => $parsing['product']->grosir_price3,
                                    'priceCurrencyFormat' => rupiah($parsing['product']->grosir_price3),
                                ];
                            } elseif ($parsing['product']->grosir_min4 > 0) {
                                $price_grosir[] = [
                                    'min' => $parsing['product']->grosir_min4,
                                    'price' => $parsing['product']->grosir_price4,
                                    'priceCurrencyFormat' => rupiah($parsing['product']->grosir_price4),
                                ];
                            } else {
                                $price_grosir = [];
                            }
                        } else {
                            $price_grosir = [];
                        }

                        $items['price'] = [
                            'primary' => $parsing['product']->price,
                            'primaryCurrencyFormat' => rupiah($parsing['product']->price),
                            'zone' => $price_zone,
                            'grosir' => $price_grosir,
                        ];

                        $parsing['db_product_image'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_product_image',
                            'where' => [
                                'product_id' => $parsing['product']->product_id
                            ]
                        ])->result();
                        $items['images'] = [];
                        $items['images'][] = (!empty($parsing['product']->image) || $parsing['product']->image != '') ? $this->core['url_image_product'] . $parsing['product']->image : $this->core['image_not_found'];
                        foreach ($parsing['db_product_image'] as $key_db_product_image) {
                            $items['images'][] = (!empty($key_db_product_image->image) || $key_db_product_image->image != '') ? $this->core['url_image_product'] . $key_db_product_image->image : $this->core['image_not_found'];
                        }

                        $items['badges'] = [];
                        if ($parsing['product']->new != 0) {
                            $items['badges'][] = 'new';
                        }
                        if (array_sum([
                            $parsing['product']->grosir_price1,
                            $parsing['product']->grosir_price2,
                            $parsing['product']->grosir_price3,
                            $parsing['product']->grosir_price4
                        ]) > 0) {
                            $items['badges'][] = 'grosir';
                        }
                        if (array_sum([
                            $parsing['product']->price1,
                            $parsing['product']->price2,
                            $parsing['product']->price3,
                            $parsing['product']->price4,
                            $parsing['product']->price5
                        ]) > 0) {
                            $items['badges'][] = 'het';
                        }

                        $items['rating'] = number_format($parsing['product']->rating, 1, '.', '');

                        $page_review = 1;
                        $limit_review = 10;
                        $items['review'] = [];
                        $param['db_product_ulasan'] = [
                            'field' => '*',
                            'table' => 'db_product_ulasan',
                            'where' => [
                                'product_id' => $parsing['product']->product_id
                            ],
                            'limit' => [
                                $limit_review => ($page_review - 1) * $limit_review
                            ],
                        ];
                        $parsing['db_product_ulasan'] = $this->api_model->select_data($param['db_product_ulasan'])->result();
                        $total_record = $this->api_model->count_all_data($param['db_product_ulasan']);

                        $limit = (int) $limit_review;
                        $current_page = (int) $page_review;
                        $total_page = ceil($total_record / $limit);

                        $review['page'] = $current_page;
                        $review['limit'] = $limit;
                        $review['total'] = $total_record;
                        $review['pages'] = $total_page;
                        $review['items'] = [];
                        foreach ($parsing['db_product_ulasan'] as $key_db_product_ulasan) {
                            $parsing['db_customer'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_customer',
                                'where' => [
                                    'customer_id' => $key_db_product_ulasan->customer_id
                                ]
                            ])->row();
                            $review['items'][] = [
                                'customerName' => (!empty($parsing['db_customer'])) ? "{$parsing['db_customer']->firstname} {$parsing['db_customer']->lastname}" : null,
                                'rate' => (int) $key_db_product_ulasan->rate,
                                'text' => $key_db_product_ulasan->ulasan,
                                'createdAt' => $key_db_product_ulasan->date,
                            ];
                        }

                        $items['review'] = $review;

                        $parsing['db_product_to_category'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_product_to_category',
                            'where' => [
                                'product_id' => $parsing['product']->product_id
                            ]
                        ])->result();
                        $items['category'] = [];
                        foreach ($parsing['db_product_to_category'] as $key_db_product_to_category) {
                            $parsing['db_category_description'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_category_description',
                                'where' => [
                                    'category_id' => $key_db_product_to_category->category_id
                                ]
                            ])->row();
                            $category['id'] = $key_db_product_to_category->category_id;
                            $category['title'] = $parsing['db_category_description']->name;
                            $category['url'] = $parsing['db_category_description']->slug;

                            $items['category'][] = $category;
                        }

                        $output = $items;
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

    public function search_get($key = null)
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            $filters = (!empty($key)) ? $this->filter_product() : [];
            $sorts = (!empty($key)) ? $this->sort_product() : [];
            if (!empty($key)) {
                if ($key != 'result') {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                        'data' => []
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
                                'filters' => $filters,
                                'sorts' => $sorts,
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
                                    'filters' => $filters,
                                    'sorts' => $sorts,
                                ]
                            ]);
                        }
                    }
                }
            } else {
                if ($this->get('limit') == null) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'limit not found',
                        'data' => [
                            'total' => 0,
                            'items' => [],
                            'filters' => $filters,
                            'sorts' => $sorts,
                        ]
                    ]);
                } else {
                    if ($this->get('limit') < 1) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => 'value must more than 1',
                            'data' => [
                                'total' => 0,
                                'items' => [],
                                'filters' => $filters,
                                'sorts' => $sorts,
                            ]
                        ]);
                    }
                }
            }

            if (empty($this->get('keyword'))) {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_BAD_REQUEST,
                    'message' => 'parameter not found',
                    'data' => []
                ]);
            }

            if ($checking === true) {
                $param['product']['field'] = '
                aa.product_id, 
                aa.storage_quantity as stok,
                aa.sku,
                cc.name,
                cc.seo as slug,
                aa.date_added,
                aa.image,
                aa.price,
                aa.price1,
                aa.price2,
                aa.price3,
                aa.price4,
                aa.price5,
                aa.grosir_price1,
                aa.grosir_price2,
                aa.grosir_price3,
                aa.grosir_price4,
                aa.mall_id,
                aa.status,
                cd.name as category,
                mall.name as mall,
                mall.slug as mall_slug,
                mall.city as mall_city,
                mall.province as mall_province,
                aa.manufacturer_id,
                AVG(db_product_ulasan.rate) AS rating,
                COUNT(db_product_ulasan.customer_id) AS total_review,
                aa.new,
                mn.name as manufacturer_name,
                mn.slug as manufacturer_slug,
                cc.tag';
                $param['product']['table'] = 'db_product aa';
                $param['product']['join'] = [
                    [
                        'table' => 'db_product_to_category bb',
                        'on' => 'bb.product_id=aa.product_id',
                        'type' => 'inner'
                    ],
                    [
                        'table' => 'db_product_description cc',
                        'on' => 'cc.product_id=aa.product_id',
                        'type' => 'inner'
                    ],
                    [
                        'table' => 'db_category_description cd',
                        'on' => 'cd.category_id=bb.category_id',
                        'type' => 'inner'
                    ],
                    [
                        'table' => 'db_manufacturer mn',
                        'on' => 'mn.manufacturer_id=aa.manufacturer_id',
                        'type' => 'inner'
                    ],
                    [
                        'table' => 'db_mall as mall',
                        'on' => 'mall.mall_id=aa.mall_id',
                        'type' => 'inner'
                    ],
                    [
                        'table' => 'db_product_ulasan',
                        'on' => 'db_product_ulasan.product_id = aa.product_id',
                        'type' => 'left'
                    ]
                ];

                if (!empty($this->get('keyword'))) {
                    $param['product']['like'] = [
                        'cc.name' => $this->get('keyword')
                    ];

                    $arr_keyword = [
                        'aa.storage_quantity >=' => 2
                    ];
                } else {
                    $arr_keyword = [];
                }

                $filter_price = $this->get('filter_price');

                if (!empty($filter_price)) {
                    if (strpos($filter_price, '-') !== false) {
                        $exp_filter_price = explode('-', $filter_price);

                        $arr_filter_price = [
                            'aa.price >=' => $exp_filter_price[0],
                            'aa.price <=' => $exp_filter_price[1]
                        ];
                    } else {
                        $arr_filter_price = [
                            'aa.price >=' => $filter_price
                        ];
                    }
                } else {
                    $arr_filter_price = [];
                }

                $arr_filter_manufacturer = (!empty($this->get('filter_manufacturer'))) ? [
                    'mn.slug' => $this->get('filter_manufacturer')
                ] : [];

                $arr_filter_province = (!empty($this->get('filter_province'))) ? [
                    'mall.province' => $this->get('filter_province')
                ] : [];

                $param['product']['where'] = array_merge([
                    'aa.blokir' => '0',
                    'aa.status' => '1',
                    'aa.disabled' => 'N',
                ], $arr_keyword, $arr_filter_price, $arr_filter_manufacturer, $arr_filter_province);

                $param['product']['group_by'] = 'aa.product_id';

                $get_sort = (!empty($this->get('sort'))) ? explode('-', $this->get('sort')) : null;
                if (!empty($get_sort)) {
                    if ($get_sort[0] == 'name') {
                        $param['product']['order_by'] = [
                            'cc.' . $get_sort[0] => $get_sort[1]
                        ];
                    } else {
                        $param['product']['order_by'] = [
                            'aa.' . $get_sort[0] => $get_sort[1]
                        ];
                    }
                } else {
                    $param['product']['order_by'] = [
                        'aa.date_added' => 'desc'
                    ];
                }

                if (!empty($key)) {
                    $param['product']['limit'] = [
                        $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                    ];
                } else {
                    $param['product']['limit'] = $this->get('limit');
                }

                $parsing['product'] = $this->api_model->select_data($param['product'])->result();

                $output = [];
                if (empty($parsing['product'])) {
                    $data['total'] = 0;
                    $data['items'] = [];
                    $code = self::HTTP_NO_CONTENT;
                } else {
                    $code = self::HTTP_OK;
                    $total_record = (!empty($key)) ? $this->api_model->count_all_data($param['product']) : 0;

                    $limit = (int) $this->get('limit');
                    $current_page = (!empty($key)) ? (int) $this->get('page') : 0;
                    $total_page = (!empty($key)) ? ceil($total_record / $limit) : 0;

                    $data['page'] = $current_page;
                    $data['limit'] = $limit;
                    $data['sort'] = (!empty($this->get('sort'))) ? $this->get('sort') : 'default';
                    $data['keyword'] = $this->get('keyword');
                    $data['total'] = $total_record;
                    $data['pages'] = $total_page;
                    $data['items'] = [];

                    foreach ($parsing['product'] as $key_product) {
                        $items['id'] = $key_product->product_id;
                        $items['name'] = $key_product->name;
                        $items['sku'] = $key_product->sku;
                        $items['slug'] = $key_product->slug;
                        $items['stock'] = $key_product->stok;

                        if (empty($key_product->tag)) {
                            $items['tag'] = [];
                        } else {
                            $items['tag'] = [];
                            foreach (explode(', ', $key_product->tag) as $key_tag) {
                                if (!empty($key_tag)) {
                                    $items['tag'][] = $key_tag;
                                }
                            }
                        }

                        $items['mall'] = [
                            'id' => $key_product->mall_id,
                            'name' => $key_product->mall,
                            'slug' => $key_product->mall_slug,
                            'city' => $key_product->mall_city,
                            'province' => $key_product->mall_province
                        ];

                        $items['manufacturer'] = [
                            'name' => $key_product->manufacturer_name,
                            'slug' => $key_product->manufacturer_slug,
                        ];

                        $price_zone = (array_sum([
                            $key_product->price1,
                            $key_product->price2,
                            $key_product->price3,
                            $key_product->price4,
                            $key_product->price5
                        ]) > 0) ? [
                            [
                                'price' => $key_product->price1,
                                'priceCurrencyFormat' => rupiah($key_product->price1)
                            ],
                            [
                                'price' => $key_product->price2,
                                'priceCurrencyFormat' => rupiah($key_product->price2)
                            ],
                            [
                                'price' => $key_product->price3,
                                'priceCurrencyFormat' => rupiah($key_product->price3)
                            ],
                            [
                                'price' => $key_product->price4,
                                'priceCurrencyFormat' => rupiah($key_product->price4)
                            ],
                            [
                                'price' => $key_product->price5,
                                'priceCurrencyFormat' => rupiah($key_product->price5)
                            ]
                        ] : [];

                        $items['price'] = [
                            'primary' => $key_product->price,
                            'primaryCurrencyFormat' => rupiah($key_product->price),
                            'zone' => $price_zone
                        ];

                        $parsing['db_product_image'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_product_image',
                            'where' => [
                                'product_id' => $key_product->product_id
                            ]
                        ])->result();
                        $items['images'] = [];
                        $items['images'][] = (!empty($key_product->image) || $key_product->image != '') ? $this->core['url_image_product'] . $key_product->image : $this->core['image_not_found'];
                        foreach ($parsing['db_product_image'] as $key_db_product_image) {
                            $items['images'][] = (!empty($key_db_product_image->image) || $key_db_product_image->image != '') ? $this->core['url_image_product'] . $key_db_product_image->image : $this->core['image_not_found'];
                        }

                        $items['badges'] = [];
                        if ($key_product->new != 0) {
                            $items['badges'][] = 'new';
                        }
                        if (array_sum([
                            $key_product->grosir_price1,
                            $key_product->grosir_price2,
                            $key_product->grosir_price3,
                            $key_product->grosir_price4
                        ]) > 0) {
                            $items['badges'][] = 'grosir';
                        }
                        if (array_sum([
                            $key_product->price1,
                            $key_product->price2,
                            $key_product->price3,
                            $key_product->price4,
                            $key_product->price5
                        ]) > 0) {
                            $items['badges'][] = 'het';
                        }

                        $items['rating'] = number_format($key_product->rating, 1, '.', '');
                        $items['review'] = (int) $key_product->total_review;

                        $parsing['db_product_to_category'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'db_product_to_category',
                            'where' => [
                                'product_id' => $key_product->product_id
                            ]
                        ])->result();
                        $items['category'] = [];
                        foreach ($parsing['db_product_to_category'] as $key_db_product_to_category) {
                            $parsing['db_category_description'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_category_description',
                                'where' => [
                                    'category_id' => $key_db_product_to_category->category_id
                                ]
                            ])->row();
                            $category['title'] = $parsing['db_category_description']->name;
                            $category['url'] = $parsing['db_category_description']->slug;

                            $items['category'][] = $category;
                        }

                        $data['items'][] = $items;
                    }
                }

                $data['filters'] = $filters;
                $data['sorts'] = $sorts;
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

    public function review_post()
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
                    $check['db_product_ulasan'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_product_ulasan',
                        'where' => [
                            'customer_id' => $this->core['customer']['id'],
                            'product_id' => $this->post('productId')
                        ]
                    ])->row();
                    if (!empty($check['db_product_ulasan'])) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_CONFLICT,
                            'message' => 'data has insert',
                        ]);
                    }
                }
            }

            if ($checking === true) {
                $query = $this->api_model->send_data([
                    'data' => [
                        'product_id' => $this->post('productId'),
                        'customer_id' => $this->core['customer']['id'],
                        'rate' => $this->post('rate'),
                        'ulasan' => $this->post('note'),
                        'date' => date('Y-m-d H:i:s'),
                    ],
                    'table' => 'db_product_ulasan'
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
            }
        }

        $this->response($response['result'], $response['status']);
    }
}
