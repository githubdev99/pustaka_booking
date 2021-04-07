<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Product extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function configProduct($id = null)
    {
        if (empty($id)) {
            $filters = [];
        } else {
            $filters = [
                [
                    'type' => 'dropdown',
                    'slug' => 'keyword',
                    'name' => 'Jenis Cari',
                    'items' => [
                        [
                            'name' => 'Judul',
                            'value' => 'dpd.name',
                        ],
                        [
                            'name' => 'Kode',
                            'value' => 'dc.model',
                        ],
                        [
                            'name' => 'Merk',
                            'value' => 'dm.name',
                        ],
                    ]
                ],
                [
                    'type' => 'dropdown',
                    'slug' => 'category',
                    'name' => 'Kategori',
                    'items' => []
                ],
                [
                    'type' => 'dropdown',
                    'slug' => 'status',
                    'name' => 'Status',
                    'items' => [
                        [
                            'name' => 'Semua Produk',
                            'value' => '',
                        ],
                        [
                            'name' => 'Aktif & Memiliki Stok',
                            'value' => 'aktif-stok',
                        ],
                        [
                            'name' => 'Aktif',
                            'value' => 'aktif',
                        ],
                        [
                            'name' => 'Tidak Memiliki Stok',
                            'value' => '!stok',
                        ],
                        [
                            'name' => 'Aktif & Tidak Memiliki Stok',
                            'value' => 'aktif-!stok',
                        ],
                        [
                            'name' => 'Tidak Aktif',
                            'value' => '!aktif',
                        ],
                        [
                            'name' => 'Blokir',
                            'value' => 'blokir',
                        ],
                    ]
                ],
            ];

            $parsing['category_parent'] = $this->api_model->select_data([
                'field' => '
                dc.category_id,
                dc.status,
                dc.image,
                dc.parent_id,
                dcp.name,
                dcp.meta_description AS deskripsi,
                dcp.seo,
                dcp.meta_keyword AS keyword,
                dcp.name as parentKat,
                COUNT(ptc.category_id) as jml',
                'table' => 'db_product p',
                'join' => [
                    [
                        'table' => 'db_product_to_category ptc',
                        'on' => 'ptc.product_id=p.product_id',
                        'type' => 'inner'
                    ],
                    [
                        'table' => 'db_category dc',
                        'on' => 'dc.category_id=ptc.category_id',
                        'type' => 'inner'
                    ],
                    [
                        'table' => 'db_category_description dcp',
                        'on' => 'dcp.category_id=dc.category_id',
                        'type' => 'inner'
                    ],
                ],
                'where' => [
                    'dc.parent_id' => '0',
                    'p.mall_id' => $this->core['seller']['id']
                ],
                'group_by' => 'ptc.category_id',
                'order_by' => [
                    'dcp.name' => 'ASC'
                ],
            ])->result();

            if (empty($parsing['category_parent'])) {
                $filters[1]['items'] = [];
            } else {
                $filters[1]['items'] = [];
                foreach ($parsing['category_parent'] as $key_category_parent) {
                    $items['name'] = $key_category_parent->name;
                    $items['value'] = $key_category_parent->category_id;
                    $items['total'] = $key_category_parent->jml;

                    $parsing['category_children'] = $this->api_model->select_data([
                        'field' => '
                        dc.category_id,
                        dc.status,
                        dc.image,
                        dc.parent_id,
                        dcp.name,
                        dcp.meta_description AS deskripsi,
                        dcp.seo,
                        dcp.meta_keyword AS keyword,
                        dcp.name as parentKat,
                        COUNT(ptc.category_id) as jml',
                        'table' => 'db_product p',
                        'join' => [
                            [
                                'table' => 'db_product_to_category ptc',
                                'on' => 'ptc.product_id=p.product_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_category dc',
                                'on' => 'dc.category_id=ptc.category_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_category_description dcp',
                                'on' => 'dcp.category_id=dc.category_id',
                                'type' => 'inner'
                            ],
                        ],
                        'where' => [
                            'dc.parent_id' => $key_category_parent->category_id,
                            'p.mall_id' => $id
                        ],
                        'group_by' => 'ptc.category_id',
                        'order_by' => [
                            'dc.category_id' => 'ASC'
                        ],
                    ])->result();
                    if (empty($parsing['category_children'])) {
                        $items['children'] = [];
                    } else {
                        $items['children'] = [];
                        foreach ($parsing['category_children'] as $key_category_children) {
                            $children['name'] = $key_category_children->name;
                            $children['value'] = $key_category_children->category_id;
                            $children['total'] = $key_category_children->jml;

                            $items['children'][] = $children;
                        }
                    }

                    $filters[1]['items'][] = $items;
                }
            }
        }

        $column = [
            [
                'name' => 'No.',
                'isOrder' => false,
                'inActive' => false,
                'value' => '',
                'order' => '',
            ],
            [
                'name' => 'Status',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'dc.status',
                'order' => '',
            ],
            [
                'name' => 'Gambar',
                'isOrder' => false,
                'inActive' => false,
                'value' => '',
                'order' => '',
            ],
            [
                'name' => 'Nama Produk',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'dpd.name',
                'order' => '',
            ],
            [
                'name' => 'Model',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'dc.model',
                'order' => '',
            ],
            [
                'name' => 'Harga',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'dc.price',
                'order' => '',
            ],
            [
                'name' => 'Zona 1',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'dc.price1',
                'order' => '',
            ],
            [
                'name' => 'Zona 2',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'dc.price2',
                'order' => '',
            ],
            [
                'name' => 'Zona 3',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'dc.price3',
                'order' => '',
            ],
            [
                'name' => 'Zona 4',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'dc.price4',
                'order' => '',
            ],
            [
                'name' => 'Zona 5',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'dc.price5',
                'order' => '',
            ],
            [
                'name' => 'Stok',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'dc.storage_quantity',
                'order' => '',
            ],
            [
                'name' => 'Kategori',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'cd.name',
                'order' => '',
            ],
            [
                'name' => 'Opsi',
                'isOrder' => false,
                'inActive' => false,
                'value' => '',
                'order' => '',
            ],
        ];

        return [
            'filters' => $filters,
            'column' => $column,
        ];
    }

    public function index_get($id = null)
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if (empty($this->core['seller'])) {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_UNAUTHORIZED,
                    'message' => 'unauthorized',
                ]);
            } else {
                if (empty($id)) {
                    $filters = $this->configProduct($this->core['seller']['id'])['filters'];
                    $column = $this->configProduct()['column'];

                    if ($this->get('page') == null || $this->get('limit') == null) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => 'page or limit not found',
                            'data' => [
                                'total' => 0,
                                'items' => [],
                                'filters' => $filters,
                                'column' => $column,
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
                                    'column' => $column,
                                ]
                            ]);
                        }
                    }

                    if ($checking === true) {
                        $param['product']['field'] = '
                        dc.product_id,
                        dc.model,
                        dc.price,
                        dc.price1,
                        dc.price2,
                        dc.price3,
                        dc.price4,
                        dc.price5,
                        dc.points,
                        dc.weight,
                        dc.quantity,
                        dc.storage_quantity,
                        dc.store_quantity,
                        dc.status,
                        dc.blokir,
                        cd.name AS cat_name,
                        dpd.name,
                        dc.image,
                        dc.date_added';
                        $param['product']['table'] = 'db_product dc';
                        $param['product']['join'] = [
                            [
                                'table' => 'db_product_description dpd',
                                'on' => 'dc.product_id=dpd.product_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_product_to_category ptc',
                                'on' => 'ptc.product_id=dc.product_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_category cat',
                                'on' => 'cat.category_id=ptc.category_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_category_description cd',
                                'on' => 'cd.category_id=ptc.category_id',
                                'type' => 'inner'
                            ],
                        ];

                        $arr_filter_category = (empty($this->get('filter_category'))) ? [] : [
                            'cat.category_id' => $this->get('filter_category')
                        ];

                        if (empty($this->get('filter_status'))) {
                            $arr_filter_status = [];
                        } else {
                            switch ($this->get('filter_status')) {
                                case "aktif":
                                    $arr_filter_status = [
                                        'dc.status' => '1'
                                    ];
                                    break;
                                case "aktif-stok":
                                    $arr_filter_status = [
                                        'dc.status' => '1',
                                        'storage_quantity >' => 0
                                    ];
                                    break;
                                case "!stok":
                                    $arr_filter_status = [
                                        'dc.storage_quantity <' => 1
                                    ];
                                    break;
                                case "aktif-!stok":
                                    $arr_filter_status = [
                                        'dc.status' => '1',
                                        'storage_quantity <' => 1
                                    ];
                                    break;
                                case "!aktif":
                                    $arr_filter_status = [
                                        'dc.status' => '0'
                                    ];
                                    break;
                                case "blokir":
                                    $arr_filter_status = [
                                        'dc.blokir' => '1'
                                    ];
                                    break;
                                default:
                                    $arr_filter_status = [];
                            }
                        }

                        $param['product']['where'] = array_merge([
                            'dc.mall_id' => $this->core['seller']['id'],
                            'dc.status' => '1',
                            'dc.blokir' => '0',
                        ], $arr_filter_category, $arr_filter_status);

                        if (!empty($this->get('keyword')) && !empty($this->get('filter_keyword'))) {
                            $param['product']['like'] = [
                                $this->get('filter_keyword') => $this->get('keyword')
                            ];
                        }

                        $param['product']['group_by'] = 'dc.product_id';

                        $getSort = (!empty($this->get('sort'))) ? explode('-', $this->get('sort')) : null;
                        if (!empty($getSort)) {
                            $param['product']['order_by'] = [
                                $getSort[0] => $getSort[1]
                            ];
                        } else {
                            $param['product']['order_by'] = [
                                'dc.product_id' => 'desc'
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
                            $totalRecord = $this->api_model->count_all_data($param['product']);

                            $limit = (int) $this->get('limit');
                            $currentPage = (int) $this->get('page');
                            $prevPage = ($currentPage > 1) ? $currentPage - 1 : 0;
                            $totalPage = ceil($totalRecord / $limit);

                            $data['path'] = base_url() . "seller/product";
                            $data['firstPageUrl'] = base_url() . "seller/product?page=1&limit={$limit}";
                            $data['prevPageUrl'] = ($prevPage > 0) ? base_url() . "seller/product?page={$prevPage}&limit={$limit}" : null;

                            $data['perPage'] = $limit;
                            $data['currentPage'] = $currentPage;
                            $data['lastPage'] = $totalPage;
                            $data['nextPage'] = ($currentPage < $totalPage) ? $currentPage + 1 : null;

                            $data['from'] = ($totalRecord > 0) ? ($currentPage - 1) * $limit + 1 : 0;

                            if ($limit > $totalRecord) {
                                $data['to'] = $totalRecord;
                            } else {
                                $data['to'] = ($currentPage > 1) ? $data['from'] + $limit - 1 : $limit;
                            }

                            $data['total'] = $totalRecord;

                            $data['sort'] = (!empty($this->get('sort'))) ? $this->get('sort') : 'default';
                            $data['items'] = [];

                            $no = $data['from'];
                            foreach ($parsing['product'] as $key_product) {
                                $items['no'] = $no;
                                $items['id'] = $key_product->product_id;
                                $items['isActive'] = ($key_product->status) ? true : false;
                                $items['isBlocked'] = ($key_product->blokir == '1') ? true : false;

                                if ($items['isBlocked']) {
                                    $items['isActive'] = false;
                                }

                                $items['image'] = (!empty($key_product->image) || $key_product->image != '') ? $this->core['url_image_product'] . $key_product->image : $this->core['image_not_found'];
                                $items['name'] = $key_product->name;
                                $items['model'] = $key_product->model;
                                $items['price'] = rupiah($key_product->price);
                                $items['priceZone1'] = rupiah($key_product->price1);
                                $items['priceZone2'] = rupiah($key_product->price2);
                                $items['priceZone3'] = rupiah($key_product->price3);
                                $items['priceZone4'] = rupiah($key_product->price4);
                                $items['priceZone5'] = rupiah($key_product->price5);
                                $items['stock'] = $key_product->storage_quantity;
                                $items['category'] = $key_product->cat_name;

                                $data['items'][] = $items;

                                $no++;
                            }
                        }

                        $data['filters'] = $this->configProduct($this->core['seller']['id'])['filters'];

                        foreach ($column as $key_column) {
                            $data['column'][] = [
                                'name' => $key_column['name'],
                                'isOrder' => $key_column['isOrder'],
                                'inActive' => ($getSort[0] == $key_column['value']) ? true : false,
                                'value' => ($getSort[0] == $key_column['value']) ? $getSort[0] : $key_column['value'],
                                'order' => ($getSort[0] == $key_column['value']) ? $getSort[1] : '',
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
                    $parsing['db_product'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_product',
                        'where' => [
                            'product_id' => $id
                        ]
                    ])->row();
                    if (empty($parsing['db_product'])) {
                        $checking = false;
                        $response = $this->formatter([
                            'code' => self::HTTP_NOT_FOUND,
                            'message' => 'data not found',
                            'data' => (object) []
                        ]);
                    } else {
                        if ($this->core['seller']['id'] != $parsing['db_product']->mall_id) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_BAD_REQUEST,
                                'message' => 'bad request',
                            ]);
                        }
                    }

                    if ($checking === true) {
                        $param['product']['field'] = '
                        dc.*,
                        dc.length AS p,
                        dc.width AS l,
                        dc.height AS t,
                        dc.quantity AS stok,
                        dc.storage_quantity AS stok_gudang,
                        dc.store_quantity AS stok_toko,
                        dm.name AS penerbit,
                        dm.manufacturer_id,
                        dpd.name,
                        dpd.tag,
                        dpd.meta_description,
                        dpd.meta_keyword,
                        dpd.description,
                        dpd.seo,
                        dc.kondisi,
                        dc.image,
                        dpc.category_id,
                        dc.pph,dc.ppn';
                        $param['product']['table'] = 'db_product dc';
                        $param['product']['join'] = [
                            [
                                'table' => 'db_product_description dpd',
                                'on' => 'dc.product_id=dpd.product_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_manufacturer dm',
                                'on' => 'dc.manufacturer_id=dm.manufacturer_id',
                                'type' => 'left'
                            ],
                            [
                                'table' => 'db_product_to_category dpc',
                                'on' => 'dc.product_id=dpc.product_id',
                                'type' => 'inner'
                            ],
                        ];

                        $param['product']['where'] = [
                            'dc.product_id' => $id
                        ];

                        $parsing['product'] = $this->api_model->select_data($param['product'])->row();

                        if (empty($parsing['product'])) {
                            $output = (object) [];
                            $code = self::HTTP_NO_CONTENT;
                        } else {
                            $output = [];
                            $code = self::HTTP_OK;

                            $items['id'] = $parsing['product']->product_id;
                            $items['isLayoutBook'] = ($parsing['product']->layout == '1') ? true : false;

                            if ($items['isLayoutBook']) {
                                $sectionProductInformation['name'] = $parsing['product']->name;
                                $sectionProductInformation['slug'] = $parsing['product']->seo;
                                $sectionProductInformation['skKelulusan'] = $parsing['product']->sk_kelulusan;
                                $sectionProductInformation['sku'] = $parsing['product']->model;
                                $sectionProductInformation['isbn'] = $parsing['product']->isbn;

                                $sectionProductInformation['class'] = [];
                                for ($iClass = 1; $iClass <= 12; $iClass++) {
                                    $sectionProductInformation['class'][] = [
                                        'value' => $iClass,
                                        'name' => $iClass,
                                        'isSelected' => ($iClass == $parsing['product']->kelas) ? true : false,
                                    ];
                                }

                                $sectionProductInformation['semester'] = [];
                                for ($iSemester = 1; $iSemester <= 6; $iSemester++) {
                                    $sectionProductInformation['semester'][] = [
                                        'value' => $iSemester,
                                        'name' => $iSemester,
                                        'isSelected' => ($iSemester == $parsing['product']->semester) ? true : false,
                                    ];
                                }

                                $parsing['getFullCategory'] = $this->api_model->select_data([
                                    'field' => 'db_product_to_category.*, db_category.parent_id',
                                    'table' => 'db_product_to_category',
                                    'join' => [
                                        [
                                            'table' => 'db_category',
                                            'on' => 'db_category.category_id=db_product_to_category.category_id',
                                            'type' => 'inner'
                                        ],
                                    ],
                                    'where' => [
                                        'product_id' => $parsing['product']->product_id
                                    ],
                                    'order_by' => [
                                        'db_category.parent_id' => 'ASC'
                                    ]
                                ])->result();
                                $sectionProductInformation['category'] = [];
                                $sectionProductInformation['categoryChildren'] = [];
                                foreach ($parsing['getFullCategory'] as $key_getFullCategory) {
                                    $parentId = '';
                                    if ($key_getFullCategory->parent_id == '0') {
                                        $parentId = $key_getFullCategory->category_id;

                                        $parsing['db_category'] = $this->api_model->select_data([
                                            'field' => 'aa.category_id,bb.name as nama_kategori,aa.status',
                                            'table' => 'db_category aa',
                                            'join' => [
                                                [
                                                    'table' => 'db_category_description bb',
                                                    'on' => 'aa.category_id=bb.category_id',
                                                    'type' => 'inner'
                                                ],
                                            ],
                                            'where' => [
                                                'aa.status' => '1',
                                                'aa.parent_id' => '0'
                                            ]
                                        ])->result();
                                        foreach ($parsing['db_category'] as $key_db_category) {
                                            $category['value'] = $key_db_category->category_id;
                                            $category['name'] = $key_db_category->nama_kategori;
                                            $category['isSelected'] = ($key_db_category->category_id == $key_getFullCategory->category_id) ? true : false;

                                            $sectionProductInformation['category'][] = $category;
                                        }
                                    } else {
                                        $parsing['db_category_children'] = $this->api_model->select_data([
                                            'field' => 'aa.category_id,bb.name as nama_kategori,aa.status',
                                            'table' => 'db_category aa',
                                            'join' => [
                                                [
                                                    'table' => 'db_category_description bb',
                                                    'on' => 'aa.category_id=bb.category_id',
                                                    'type' => 'inner'
                                                ],
                                            ],
                                            'where' => [
                                                'aa.status' => '1',
                                                'aa.parent_id' => $parentId
                                            ]
                                        ])->result();
                                        foreach ($parsing['db_category_children'] as $key_db_category_children) {
                                            $categoryChildren['value'] = $key_db_category_children->category_id;
                                            $categoryChildren['name'] = $key_db_category_children->nama_kategori;
                                            $categoryChildren['isSelected'] = ($key_db_category_children->category_id == $key_getFullCategory->category_id) ? true : false;

                                            $sectionProductInformation['categoryChildren'][] = $categoryChildren;
                                        }
                                    }
                                }

                                $parsing['db_manufacturer'] = $this->api_model->select_data([
                                    'field' => '*',
                                    'table' => 'db_manufacturer',
                                    'order_by' => [
                                        'name' => 'ASC'
                                    ]
                                ])->result();
                                $sectionProductInformation['manufacturer'] = [];
                                foreach ($parsing['db_manufacturer'] as $key_db_manufacturer) {
                                    $manufacturer['value'] = $key_db_manufacturer->manufacturer_id;
                                    $manufacturer['name'] = $key_db_manufacturer->name;
                                    $manufacturer['isSelected'] = ($key_db_manufacturer->manufacturer_id == $parsing['product']->manufacturer_id) ? true : false;

                                    $sectionProductInformation['manufacturer'][] = $manufacturer;
                                }

                                $sectionProductInformation['description'] = $parsing['product']->description;
                                $items['sectionProductInformation'] = $sectionProductInformation;

                                $sectionPriceInformation['price'] = $parsing['product']->price;
                                $sectionPriceInformation['priceCurrencyFormat'] = rupiah($sectionPriceInformation['price']);
                                $sectionPriceInformation['priceNego'] = $parsing['product']->price_nego;
                                $sectionPriceInformation['priceNegoCurrencyFormat'] = rupiah($sectionPriceInformation['priceNego']);
                                $sectionPriceInformation['priceZone'] = [
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
                                ];
                                $sectionPriceInformation['stock'] = $parsing['product']->stok_gudang;
                                $sectionPriceInformation['ppn'] = [
                                    [
                                        'value' => '0',
                                        'name' => 'No',
                                        'isSelected' => ($parsing['product']->ppn > 0) ? false : true,
                                    ],
                                    [
                                        'value' => '1',
                                        'name' => 'Yes',
                                        'isSelected' => ($parsing['product']->ppn > 0) ? true : false,
                                    ],
                                ];
                                $items['sectionPriceInformation'] = $sectionPriceInformation;

                                $sectionMediaInformation['imagePrimary'] = (!empty($parsing['product']->image) || $parsing['product']->image != '') ? $this->core['url_image_product'] . $parsing['product']->image : $this->core['image_not_found'];

                                $parsing['db_product_image'] = $this->api_model->select_data([
                                    'field' => '*',
                                    'table' => 'db_product_image',
                                    'where' => [
                                        'product_id' => $parsing['product']->product_id
                                    ]
                                ])->result();
                                $sectionMediaInformation['imageOther'] = [];
                                foreach ($parsing['db_product_image'] as $key_db_product_image) {
                                    $imageOther['value'] = $key_db_product_image->product_image_id;
                                    $imageOther['image'] = (!empty($key_db_product_image->image) || $key_db_product_image->image != '') ? $this->core['url_image_product'] . $key_db_product_image->image : $this->core['image_not_found'];

                                    $sectionMediaInformation['imageOther'][] = $imageOther;
                                }
                                $items['sectionMediaInformation'] = $sectionMediaInformation;

                                $sectionSpecInformation['pages'] = $parsing['product']->pages;
                                $sectionSpecInformation['weight'] = $parsing['product']->weight;
                                $sectionSpecInformation['dimension'] = [
                                    'long' => $parsing['product']->p,
                                    'wide' => $parsing['product']->l,
                                    'high' => $parsing['product']->t,
                                ];
                                $sectionSpecInformation['condition'] = [
                                    [
                                        'value' => 'Baru',
                                        'name' => 'Baru',
                                        'isSelected' => ($parsing['product']->kondisi == 'Baru') ? true : false,
                                    ],
                                    [
                                        'value' => 'Bekas',
                                        'name' => 'Bekas',
                                        'isSelected' => ($parsing['product']->kondisi == 'Bekas') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['status'] = [
                                    [
                                        'value' => '0',
                                        'name' => 'Disable',
                                        'isSelected' => ($parsing['product']->status == '0') ? true : false,
                                    ],
                                    [
                                        'value' => '1',
                                        'name' => 'Enable',
                                        'isSelected' => ($parsing['product']->status == '1') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['contentPaper'] = [
                                    [
                                        'value' => '',
                                        'name' => 'Pilih Jenis Kertas Isi',
                                        'isSelected' => ($parsing['product']->kertas_isi == '') ? true : false,
                                    ],
                                    [
                                        'value' => 'HVS 70',
                                        'name' => 'HVS 70',
                                        'isSelected' => ($parsing['product']->kertas_isi == 'HVS 70') ? true : false,
                                    ],
                                    [
                                        'value' => 'HVS 80',
                                        'name' => 'HVS 80',
                                        'isSelected' => ($parsing['product']->kertas_isi == 'HVS 80') ? true : false,
                                    ],
                                    [
                                        'value' => 'HVS 100',
                                        'name' => 'HVS 100',
                                        'isSelected' => ($parsing['product']->kertas_isi == 'HVS 100') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['coverPaper'] = [
                                    [
                                        'value' => '',
                                        'name' => 'Pilih Jenis Kertas Cover',
                                        'isSelected' => ($parsing['product']->kertas_cover == '') ? true : false,
                                    ],
                                    [
                                        'value' => 'Art Carton 210',
                                        'name' => 'Art Carton 210',
                                        'isSelected' => ($parsing['product']->kertas_cover == 'Art Carton 210') ? true : false,
                                    ],
                                    [
                                        'value' => 'HVS 70',
                                        'name' => 'HVS 70',
                                        'isSelected' => ($parsing['product']->kertas_cover == 'HVS 70') ? true : false,
                                    ],
                                    [
                                        'value' => 'HVS 80',
                                        'name' => 'HVS 80',
                                        'isSelected' => ($parsing['product']->kertas_cover == 'HVS 80') ? true : false,
                                    ],
                                    [
                                        'value' => 'HVS 100',
                                        'name' => 'HVS 100',
                                        'isSelected' => ($parsing['product']->kertas_cover == 'HVS 100') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['fillColor'] = [
                                    [
                                        'value' => '',
                                        'name' => 'Pilih Jenis Warna Isi',
                                        'isSelected' => ($parsing['product']->warna_isi == '') ? true : false,
                                    ],
                                    [
                                        'value' => 'Full Color',
                                        'name' => 'Full Color',
                                        'isSelected' => ($parsing['product']->warna_isi == 'Full Color') ? true : false,
                                    ],
                                    [
                                        'value' => 'Black White',
                                        'name' => 'Black White',
                                        'isSelected' => ($parsing['product']->warna_isi == 'Black White') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['coverColor'] = [
                                    [
                                        'value' => '',
                                        'name' => 'Pilih Jenis Warna Cover',
                                        'isSelected' => ($parsing['product']->warna_cover == '') ? true : false,
                                    ],
                                    [
                                        'value' => 'Full Color',
                                        'name' => 'Full Color',
                                        'isSelected' => ($parsing['product']->warna_cover == 'Full Color') ? true : false,
                                    ],
                                    [
                                        'value' => 'Black White',
                                        'name' => 'Black White',
                                        'isSelected' => ($parsing['product']->warna_cover == 'Black White') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['finishing'] = [
                                    [
                                        'value' => '',
                                        'name' => 'Pilih Jenis Finishing',
                                        'isSelected' => ($parsing['product']->finishing == '') ? true : false,
                                    ],
                                    [
                                        'value' => 'UV Varnish',
                                        'name' => 'UV Varnish',
                                        'isSelected' => ($parsing['product']->finishing == 'UV Varnish') ? true : false,
                                    ],
                                    [
                                        'value' => 'Laminating',
                                        'name' => 'Laminating',
                                        'isSelected' => ($parsing['product']->finishing == 'Laminating') ? true : false,
                                    ],
                                    [
                                        'value' => 'Hard Cover',
                                        'name' => 'Hard Cover',
                                        'isSelected' => ($parsing['product']->finishing == 'Hard Cover') ? true : false,
                                    ],
                                    [
                                        'value' => 'Embos',
                                        'name' => 'Embos',
                                        'isSelected' => ($parsing['product']->finishing == 'Embos') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['binding'] = [
                                    [
                                        'value' => '',
                                        'name' => 'Pilih Jenis Jilid',
                                        'isSelected' => ($parsing['product']->penjilidan == '') ? true : false,
                                    ],
                                    [
                                        'value' => 'Perfect Binding',
                                        'name' => 'Perfect Binding',
                                        'isSelected' => ($parsing['product']->penjilidan == 'Perfect Binding') ? true : false,
                                    ],
                                    [
                                        'value' => 'Spiral',
                                        'name' => 'Spiral',
                                        'isSelected' => ($parsing['product']->penjilidan == 'Spiral') ? true : false,
                                    ],
                                    [
                                        'value' => 'Jahit benang',
                                        'name' => 'Jahit benang',
                                        'isSelected' => ($parsing['product']->penjilidan == 'Jahit benang') ? true : false,
                                    ],
                                ];
                                $items['sectionSpecInformation'] = $sectionSpecInformation;
                            } else {
                                $sectionProductInformation['name'] = $parsing['product']->name;
                                $sectionProductInformation['slug'] = $parsing['product']->seo;
                                $sectionProductInformation['skKelulusan'] = $parsing['product']->sk_kelulusan;
                                $sectionProductInformation['sku'] = $parsing['product']->model;
                                $sectionProductInformation['isbn'] = $parsing['product']->isbn;

                                $sectionProductInformation['class'] = [];
                                for ($iClass = 1; $iClass <= 12; $iClass++) {
                                    $sectionProductInformation['class'][] = [
                                        'value' => $iClass,
                                        'name' => $iClass,
                                        'isSelected' => ($iClass == $parsing['product']->kelas) ? true : false,
                                    ];
                                }

                                $sectionProductInformation['semester'] = [];
                                for ($iSemester = 1; $iSemester <= 6; $iSemester++) {
                                    $sectionProductInformation['semester'][] = [
                                        'value' => $iSemester,
                                        'name' => $iSemester,
                                        'isSelected' => ($iSemester == $parsing['product']->semester) ? true : false,
                                    ];
                                }

                                $parsing['getFullCategory'] = $this->api_model->select_data([
                                    'field' => 'db_product_to_category.*, db_category.parent_id',
                                    'table' => 'db_product_to_category',
                                    'join' => [
                                        [
                                            'table' => 'db_category',
                                            'on' => 'db_category.category_id=db_product_to_category.category_id',
                                            'type' => 'inner'
                                        ],
                                    ],
                                    'where' => [
                                        'product_id' => $parsing['product']->product_id
                                    ],
                                    'order_by' => [
                                        'db_category.parent_id' => 'ASC'
                                    ]
                                ])->result();
                                $sectionProductInformation['category'] = [];
                                $sectionProductInformation['categoryChildren'] = [];
                                foreach ($parsing['getFullCategory'] as $key_getFullCategory) {
                                    $parentId = '';
                                    if ($key_getFullCategory->parent_id == '0') {
                                        $parentId = $key_getFullCategory->category_id;

                                        $parsing['db_category'] = $this->api_model->select_data([
                                            'field' => 'aa.category_id,bb.name as nama_kategori,aa.status',
                                            'table' => 'db_category aa',
                                            'join' => [
                                                [
                                                    'table' => 'db_category_description bb',
                                                    'on' => 'aa.category_id=bb.category_id',
                                                    'type' => 'inner'
                                                ],
                                            ],
                                            'where' => [
                                                'aa.status' => '1',
                                                'aa.parent_id' => '0'
                                            ]
                                        ])->result();
                                        foreach ($parsing['db_category'] as $key_db_category) {
                                            $category['value'] = $key_db_category->category_id;
                                            $category['name'] = $key_db_category->nama_kategori;
                                            $category['isSelected'] = ($key_db_category->category_id == $key_getFullCategory->category_id) ? true : false;

                                            $sectionProductInformation['category'][] = $category;
                                        }
                                    } else {
                                        $parsing['db_category_children'] = $this->api_model->select_data([
                                            'field' => 'aa.category_id,bb.name as nama_kategori,aa.status',
                                            'table' => 'db_category aa',
                                            'join' => [
                                                [
                                                    'table' => 'db_category_description bb',
                                                    'on' => 'aa.category_id=bb.category_id',
                                                    'type' => 'inner'
                                                ],
                                            ],
                                            'where' => [
                                                'aa.status' => '1',
                                                'aa.parent_id' => $parentId
                                            ]
                                        ])->result();
                                        foreach ($parsing['db_category_children'] as $key_db_category_children) {
                                            $categoryChildren['value'] = $key_db_category_children->category_id;
                                            $categoryChildren['name'] = $key_db_category_children->nama_kategori;
                                            $categoryChildren['isSelected'] = ($key_db_category_children->category_id == $key_getFullCategory->category_id) ? true : false;

                                            $sectionProductInformation['categoryChildren'][] = $categoryChildren;
                                        }
                                    }
                                }

                                $parsing['db_manufacturer'] = $this->api_model->select_data([
                                    'field' => '*',
                                    'table' => 'db_manufacturer',
                                    'order_by' => [
                                        'name' => 'ASC'
                                    ]
                                ])->result();
                                $sectionProductInformation['manufacturer'] = [];
                                foreach ($parsing['db_manufacturer'] as $key_db_manufacturer) {
                                    $manufacturer['value'] = $key_db_manufacturer->manufacturer_id;
                                    $manufacturer['name'] = $key_db_manufacturer->name;
                                    $manufacturer['isSelected'] = ($key_db_manufacturer->manufacturer_id == $parsing['product']->manufacturer_id) ? true : false;

                                    $sectionProductInformation['manufacturer'][] = $manufacturer;
                                }

                                $sectionProductInformation['description'] = $parsing['product']->description;
                                $items['sectionProductInformation'] = $sectionProductInformation;

                                $sectionPriceInformation['isPriceGrosirActive'] = (array_sum([
                                    $parsing['product']->grosir_price1,
                                    $parsing['product']->grosir_price2,
                                    $parsing['product']->grosir_price3,
                                    $parsing['product']->grosir_price4
                                ]) > 0) ? true : false;
                                $sectionPriceInformation['price'] = $parsing['product']->price;
                                $sectionPriceInformation['priceCurrencyFormat'] = rupiah($sectionPriceInformation['price']);
                                $sectionPriceInformation['priceNego'] = $parsing['product']->price_nego;
                                $sectionPriceInformation['priceNegoCurrencyFormat'] = rupiah($sectionPriceInformation['priceNego']);
                                $sectionPriceInformation['priceGrosir'] = [
                                    [
                                        'min' => $parsing['product']->grosir_min1,
                                        'price' => $parsing['product']->grosir_price1,
                                        'priceCurrencyFormat' => rupiah($parsing['product']->grosir_price1),
                                    ],
                                    [
                                        'min' => $parsing['product']->grosir_min2,
                                        'price' => $parsing['product']->grosir_price2,
                                        'priceCurrencyFormat' => rupiah($parsing['product']->grosir_price2),
                                    ],
                                    [
                                        'min' => $parsing['product']->grosir_min3,
                                        'price' => $parsing['product']->grosir_price3,
                                        'priceCurrencyFormat' => rupiah($parsing['product']->grosir_price3),
                                    ],
                                    [
                                        'min' => $parsing['product']->grosir_min4,
                                        'price' => $parsing['product']->grosir_price4,
                                        'priceCurrencyFormat' => rupiah($parsing['product']->grosir_price4),
                                    ],
                                ];
                                $sectionPriceInformation['unitType'] = [
                                    [
                                        'value' => 'Centimeter',
                                        'name' => 'Centimeter',
                                        'isSelected' => ($parsing['product']->unit_type == 'Centimeter') ? true : false,
                                    ],
                                    [
                                        'value' => 'Gram',
                                        'name' => 'Gram',
                                        'isSelected' => ($parsing['product']->unit_type == 'Gram') ? true : false,
                                    ],
                                    [
                                        'value' => 'Inch',
                                        'name' => 'Inch',
                                        'isSelected' => ($parsing['product']->unit_type == 'Inch') ? true : false,
                                    ],
                                    [
                                        'value' => 'Kilogram',
                                        'name' => 'Kilogram',
                                        'isSelected' => ($parsing['product']->unit_type == 'Kilogram') ? true : false,
                                    ],
                                    [
                                        'value' => 'Liter',
                                        'name' => 'Liter',
                                        'isSelected' => ($parsing['product']->unit_type == 'Liter') ? true : false,
                                    ],
                                    [
                                        'value' => 'Milligram',
                                        'name' => 'Milligram',
                                        'isSelected' => ($parsing['product']->unit_type == 'Milligram') ? true : false,
                                    ],
                                    [
                                        'value' => 'Milliliter',
                                        'name' => 'Milliliter',
                                        'isSelected' => ($parsing['product']->unit_type == 'Milliliter') ? true : false,
                                    ],
                                    [
                                        'value' => 'Meter',
                                        'name' => 'Meter',
                                        'isSelected' => ($parsing['product']->unit_type == 'Meter') ? true : false,
                                    ],
                                    [
                                        'value' => 'm³',
                                        'name' => 'm³',
                                        'isSelected' => ($parsing['product']->unit_type == 'm³') ? true : false,
                                    ],
                                    [
                                        'value' => 'Lusin',
                                        'name' => 'Lusin',
                                        'isSelected' => ($parsing['product']->unit_type == 'Lusin') ? true : false,
                                    ],
                                    [
                                        'value' => 'Kodi',
                                        'name' => 'Kodi',
                                        'isSelected' => ($parsing['product']->unit_type == 'Kodi') ? true : false,
                                    ],
                                    [
                                        'value' => 'Gross',
                                        'name' => 'Gross',
                                        'isSelected' => ($parsing['product']->unit_type == 'Gross') ? true : false,
                                    ],
                                    [
                                        'value' => 'Rim',
                                        'name' => 'Rim',
                                        'isSelected' => ($parsing['product']->unit_type == 'Rim') ? true : false,
                                    ],
                                    [
                                        'value' => 'Box',
                                        'name' => 'Box',
                                        'isSelected' => ($parsing['product']->unit_type == 'Box') ? true : false,
                                    ],
                                    [
                                        'value' => 'Roll',
                                        'name' => 'Roll',
                                        'isSelected' => ($parsing['product']->unit_type == 'Roll') ? true : false,
                                    ],
                                    [
                                        'value' => 'Set',
                                        'name' => 'Set',
                                        'isSelected' => ($parsing['product']->unit_type == 'Set') ? true : false,
                                    ],
                                    [
                                        'value' => 'Unit',
                                        'name' => 'Unit',
                                        'isSelected' => ($parsing['product']->unit_type == 'Unit') ? true : false,
                                    ],
                                    [
                                        'value' => 'Pcs',
                                        'name' => 'Pcs',
                                        'isSelected' => ($parsing['product']->unit_type == 'Pcs') ? true : false,
                                    ],
                                ];
                                $sectionPriceInformation['ppn'] = [
                                    [
                                        'value' => '0',
                                        'name' => 'No',
                                        'isSelected' => ($parsing['product']->ppn > 0) ? false : true,
                                    ],
                                    [
                                        'value' => '1',
                                        'name' => 'Yes',
                                        'isSelected' => ($parsing['product']->ppn > 0) ? true : false,
                                    ],
                                ];
                                $items['sectionPriceInformation'] = $sectionPriceInformation;

                                $sectionMediaInformation['imagePrimary'] = (!empty($parsing['product']->image) || $parsing['product']->image != '') ? $this->core['url_image_product'] . $parsing['product']->image : $this->core['image_not_found'];

                                $parsing['db_product_image'] = $this->api_model->select_data([
                                    'field' => '*',
                                    'table' => 'db_product_image',
                                    'where' => [
                                        'product_id' => $parsing['product']->product_id
                                    ]
                                ])->result();
                                $sectionMediaInformation['imageOther'] = [];
                                foreach ($parsing['db_product_image'] as $key_db_product_image) {
                                    $imageOther['value'] = $key_db_product_image->product_image_id;
                                    $imageOther['image'] = (!empty($key_db_product_image->image) || $key_db_product_image->image != '') ? $this->core['url_image_product'] . $key_db_product_image->image : $this->core['image_not_found'];

                                    $sectionMediaInformation['imageOther'][] = $imageOther;
                                }
                                $items['sectionMediaInformation'] = $sectionMediaInformation;

                                $sectionSpecInformation['condition'] = [
                                    [
                                        'value' => 'Baru',
                                        'name' => 'Baru',
                                        'isSelected' => ($parsing['product']->kondisi == 'Baru') ? true : false,
                                    ],
                                    [
                                        'value' => 'Bekas',
                                        'name' => 'Bekas',
                                        'isSelected' => ($parsing['product']->kondisi == 'Bekas') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['stock'] = $parsing['product']->stok_gudang;
                                $sectionSpecInformation['warranty'] = [
                                    [
                                        'value' => 'Garansi Toko',
                                        'name' => 'Garansi Toko',
                                        'isSelected' => ($parsing['product']->garansi == 'Garansi Toko') ? true : false,
                                    ],
                                    [
                                        'value' => 'Garansi Distributor',
                                        'name' => 'Garansi Distributor',
                                        'isSelected' => ($parsing['product']->garansi == 'Garansi Distributor') ? true : false,
                                    ],
                                    [
                                        'value' => 'Garansi Resmi Nasional',
                                        'name' => 'Garansi Resmi Nasional',
                                        'isSelected' => ($parsing['product']->garansi == 'Garansi Resmi Nasional') ? true : false,
                                    ],
                                    [
                                        'value' => 'Garansi Global',
                                        'name' => 'Garansi Global',
                                        'isSelected' => ($parsing['product']->garansi == 'Garansi Global') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['weight'] = $parsing['product']->weight;
                                $sectionSpecInformation['dimension'] = [
                                    'long' => $parsing['product']->p,
                                    'wide' => $parsing['product']->l,
                                    'high' => $parsing['product']->t,
                                ];
                                $sectionSpecInformation['status'] = [
                                    [
                                        'value' => '0',
                                        'name' => 'Disable',
                                        'isSelected' => ($parsing['product']->status == '0') ? true : false,
                                    ],
                                    [
                                        'value' => '1',
                                        'name' => 'Enable',
                                        'isSelected' => ($parsing['product']->status == '1') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['processor'] = [
                                    [
                                        'value' => '',
                                        'name' => 'Pilih Type Processor',
                                        'isSelected' => ($parsing['product']->processor == '') ? true : false,
                                    ],
                                    [
                                        'value' => 'Intel Core i3',
                                        'name' => 'Intel Core i3',
                                        'isSelected' => ($parsing['product']->processor == 'Intel Core i3') ? true : false,
                                    ],
                                    [
                                        'value' => 'Intel Core i5',
                                        'name' => 'Intel Core i5',
                                        'isSelected' => ($parsing['product']->processor == 'Intel Core i5') ? true : false,
                                    ],
                                    [
                                        'value' => 'Intel Core i7',
                                        'name' => 'Intel Core i7',
                                        'isSelected' => ($parsing['product']->processor == 'Intel Core i7') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['memory'] = [
                                    [
                                        'value' => '',
                                        'name' => 'Pilih Type Memory RAM',
                                        'isSelected' => ($parsing['product']->memory == '') ? true : false,
                                    ],
                                    [
                                        'value' => '4 GB',
                                        'name' => '4 GB',
                                        'isSelected' => ($parsing['product']->memory == '4 GB') ? true : false,
                                    ],
                                    [
                                        'value' => '8 GB',
                                        'name' => '8 GB',
                                        'isSelected' => ($parsing['product']->memory == '8 GB') ? true : false,
                                    ],
                                    [
                                        'value' => '16 GB',
                                        'name' => '16 GB',
                                        'isSelected' => ($parsing['product']->memory == '16 GB') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['harddisk'] = [
                                    [
                                        'value' => '',
                                        'name' => 'Pilih Type Hard Disk',
                                        'isSelected' => ($parsing['product']->harddisk == '') ? true : false,
                                    ],
                                    [
                                        'value' => 'HDD 500 GB',
                                        'name' => 'HDD 500 GB',
                                        'isSelected' => ($parsing['product']->harddisk == 'HDD 500 GB') ? true : false,
                                    ],
                                    [
                                        'value' => 'HDD 1 TB',
                                        'name' => 'HDD 1 TB',
                                        'isSelected' => ($parsing['product']->harddisk == 'HDD 1 TB') ? true : false,
                                    ],
                                    [
                                        'value' => 'SSD 120 GB',
                                        'name' => 'SSD 120 GB',
                                        'isSelected' => ($parsing['product']->harddisk == 'SSD 120 GB') ? true : false,
                                    ],
                                    [
                                        'value' => 'SSD 250 GB',
                                        'name' => 'SSD 250 GB',
                                        'isSelected' => ($parsing['product']->harddisk == 'SSD 250 GB') ? true : false,
                                    ],
                                    [
                                        'value' => 'SSD 500 GB',
                                        'name' => 'SSD 500 GB',
                                        'isSelected' => ($parsing['product']->harddisk == 'SSD 500 GB') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['cdDvd'] = [
                                    [
                                        'value' => '',
                                        'name' => 'Pilih Type DVD/CD',
                                        'isSelected' => ($parsing['product']->cd_dvd == '') ? true : false,
                                    ],
                                    [
                                        'value' => 'Non',
                                        'name' => 'Non',
                                        'isSelected' => ($parsing['product']->cd_dvd == 'Non') ? true : false,
                                    ],
                                    [
                                        'value' => 'DVD',
                                        'name' => 'DVD',
                                        'isSelected' => ($parsing['product']->cd_dvd == 'DVD') ? true : false,
                                    ],
                                    [
                                        'value' => 'CD',
                                        'name' => 'CD',
                                        'isSelected' => ($parsing['product']->cd_dvd == 'CD') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['monitor'] = [
                                    [
                                        'value' => '',
                                        'name' => 'Pilih Type Monitor',
                                        'isSelected' => ($parsing['product']->monitor == '') ? true : false,
                                    ],
                                    [
                                        'value' => '10',
                                        'name' => '10',
                                        'isSelected' => ($parsing['product']->monitor == '10') ? true : false,
                                    ],
                                    [
                                        'value' => '11',
                                        'name' => '11',
                                        'isSelected' => ($parsing['product']->monitor == '11') ? true : false,
                                    ],
                                    [
                                        'value' => '12',
                                        'name' => '12',
                                        'isSelected' => ($parsing['product']->monitor == '12') ? true : false,
                                    ],
                                    [
                                        'value' => '13',
                                        'name' => '13',
                                        'isSelected' => ($parsing['product']->monitor == '13') ? true : false,
                                    ],
                                    [
                                        'value' => '15',
                                        'name' => '15',
                                        'isSelected' => ($parsing['product']->monitor == '15') ? true : false,
                                    ],
                                    [
                                        'value' => '19',
                                        'name' => '19',
                                        'isSelected' => ($parsing['product']->monitor == '19') ? true : false,
                                    ],
                                    [
                                        'value' => '20',
                                        'name' => '20',
                                        'isSelected' => ($parsing['product']->monitor == '20') ? true : false,
                                    ],
                                    [
                                        'value' => '22',
                                        'name' => '22',
                                        'isSelected' => ($parsing['product']->monitor == '22') ? true : false,
                                    ],
                                    [
                                        'value' => '24',
                                        'name' => '24',
                                        'isSelected' => ($parsing['product']->monitor == '24') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['os'] = [
                                    [
                                        'value' => '',
                                        'name' => 'Pilih Type OS',
                                        'isSelected' => ($parsing['product']->sistem_operasi == '') ? true : false,
                                    ],
                                    [
                                        'value' => 'DOS',
                                        'name' => 'DOS',
                                        'isSelected' => ($parsing['product']->sistem_operasi == 'DOS') ? true : false,
                                    ],
                                    [
                                        'value' => 'Windows',
                                        'name' => 'Windows',
                                        'isSelected' => ($parsing['product']->sistem_operasi == 'Windows') ? true : false,
                                    ],
                                    [
                                        'value' => 'Linux',
                                        'name' => 'Linux',
                                        'isSelected' => ($parsing['product']->sistem_operasi == 'Linux') ? true : false,
                                    ],
                                    [
                                        'value' => 'Unix',
                                        'name' => 'Unix',
                                        'isSelected' => ($parsing['product']->sistem_operasi == 'Unix') ? true : false,
                                    ],
                                    [
                                        'value' => 'MacOS',
                                        'name' => 'MacOS',
                                        'isSelected' => ($parsing['product']->sistem_operasi == 'MacOS') ? true : false,
                                    ],
                                    [
                                        'value' => 'Android',
                                        'name' => 'Android',
                                        'isSelected' => ($parsing['product']->sistem_operasi == 'Android') ? true : false,
                                    ],
                                    [
                                        'value' => 'iOS',
                                        'name' => 'iOS',
                                        'isSelected' => ($parsing['product']->sistem_operasi == 'iOS') ? true : false,
                                    ],
                                ];
                                $sectionSpecInformation['installedApplication'] = $parsing['product']->aplikasi_terpasang;
                                $items['sectionSpecInformation'] = $sectionSpecInformation;
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
        }

        $this->response($response['result'], $response['status']);
    }

    public function status_put()
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if (empty($this->core['seller'])) {
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
                    $check['db_product'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_product',
                        'where' => [
                            'mall_id' => $this->core['seller']['id'],
                            'product_id' => $this->put('id')
                        ]
                    ])->row_array();
                    if (empty($check['db_product'])) {
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
                        'mall_id' => $this->core['seller']['id'],
                        'product_id' => $this->put('id')
                    ],
                    'data' => [
                        'status' => ($check['db_product']['status'] == '1') ? 0 : 1
                    ],
                    'table' => 'db_product'
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
}
