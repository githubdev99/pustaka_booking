<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Order extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function configOrder($id = null)
    {
        if (empty($id)) {
            $filters = [];
        } else {
            $filters = [
                'type' => 'dropdown',
                'slug' => 'status',
                'name' => 'Status',
                'items' => [
                    [
                        'name' => 'Semua Pesanan',
                        'value' => '',
                    ],
                ]
            ];

            $parsing['db_order_status'] = $this->api_model->select_data([
                'field' => '*',
                'table' => 'db_order_status',
                'order_by' => [
                    'sort' => 'asc'
                ],
            ])->result();
            foreach ($parsing['db_order_status'] as $key_db_order_status) {
                $filters['items'][] = [
                    'name' => $key_db_order_status->name,
                    'value' => $key_db_order_status->order_status_id,
                ];
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
                'name' => 'Invoice',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'm.invoice_no',
                'order' => '',
            ],
            [
                'name' => 'Customer',
                'isOrder' => true,
                'inActive' => false,
                'value' => "CONCAT(m.shipping_firstname,' ',m.shipping_lastname)",
                'order' => '',
            ],
            [
                'name' => 'NPSN',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'm.npsn',
                'order' => '',
            ],
            [
                'name' => 'Sekolah',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'm.shipping_company',
                'order' => '',
            ],
            [
                'name' => 'Wilayah',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'm.shipping_city',
                'order' => '',
            ],
            [
                'name' => 'Status',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'o.name',
                'order' => '',
            ],
            [
                'name' => 'Total',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'dot.value',
                'order' => '',
            ],
            [
                'name' => 'Tanggal Order',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'm.date_added',
                'order' => '',
            ],
            [
                'name' => 'TOP',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'm.payment_tempo',
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

    private function configBilling()
    {
        $column = [
            [
                'name' => 'No.',
                'isOrder' => false,
                'inActive' => false,
                'value' => '',
                'order' => '',
            ],
            [
                'name' => 'Kode Pesanan',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'pg.invoice_no',
                'order' => '',
            ],
            [
                'name' => 'Customer',
                'isOrder' => true,
                'inActive' => false,
                'value' => "CONCAT(cs.firstname,' ',cs.lastname)",
                'order' => '',
            ],
            [
                'name' => 'Tgl Penagihan',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'pg.tgl_penagihan',
                'order' => '',
            ],
            [
                'name' => 'Tgl Bayar',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'pg.tgl_bayar',
                'order' => '',
            ],
            [
                'name' => 'Tgl Tanggapan',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'pg.tgl_created',
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
                    $filters = $this->configOrder($this->core['seller']['id'])['filters'];
                    $column = $this->configOrder()['column'];

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
                        if (!empty($this->get('search'))) {
                            $param['db_order']['column_search'] = [];
                            foreach ($column as $key_column) {
                                if ($key_column['isOrder']) {
                                    $param['db_order']['column_search'][] = $key_column['value'];
                                }
                            }
                            $param['db_order']['search'] = $this->get('search');
                        }

                        $param['db_order']['field'] = '
                        m.order_id,
                        m.firstname,
                        m.lastname,
                        m.customer_id,
                        m.shipping_firstname,
                        m.shipping_lastname,
                        m.total,
                        m.date_added,
                        m.invoice_prefix,
                        m.invoice_no,
                        m.date_modified,
                        m.order_status_id,
                        m.shipping_kurir,
                        m.shipping_kurir_type,
                        m.shipping_company,
                        m.shipping_city,
                        m.shipping_province,
                        m.npsn,
                        m.awb,
                        m.mall_id,
                        m.payment_tempo,
                        m.payment_va,
                        o.name,
                        dot.value';
                        $param['db_order']['table'] = 'db_order m';
                        $param['db_order']['join'] = [
                            [
                                'table' => 'db_order_status o',
                                'on' => 'm.order_status_id=o.order_status_id',
                                'type' => 'left'
                            ],
                            [
                                'table' => 'db_order_total dot',
                                'on' => 'dot.order_id=m.order_id',
                                'type' => 'inner'
                            ],
                        ];

                        $arr_filter_status = ($this->get('filter_status') != '') ? [
                            'm.order_status_id' => $this->get('filter_status')
                        ] : [];

                        $param['db_order']['where'] = array_merge([
                            'm.mall_id' => $this->core['seller']['id'],
                            'dot.code' => 'total'
                        ], $arr_filter_status);

                        $getSort = (!empty($this->get('sort'))) ? explode('-', $this->get('sort')) : null;
                        if (!empty($getSort)) {
                            $param['db_order']['order_by'] = [
                                $getSort[0] => $getSort[1]
                            ];
                        } else {
                            $param['db_order']['order_by'] = [
                                'm.order_id' => 'desc'
                            ];
                        }

                        $param['db_order']['limit'] = [
                            $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                        ];
                        $parsing['db_order'] = $this->api_model->select_data($param['db_order'])->result();

                        $output = [];
                        if (empty($parsing['db_order'])) {
                            $data['total'] = 0;
                            $data['items'] = [];
                            $code = self::HTTP_NO_CONTENT;
                        } else {
                            $code = self::HTTP_OK;
                            $totalRecord = $this->api_model->count_all_data($param['db_order']);

                            $limit = (int) $this->get('limit');
                            $currentPage = (int) $this->get('page');
                            $prevPage = ($currentPage > 1) ? $currentPage - 1 : 0;
                            $totalPage = ceil($totalRecord / $limit);

                            $data['path'] = base_url() . "seller/order";
                            $data['firstPageUrl'] = base_url() . "seller/order?page=1&limit={$limit}";
                            $data['prevPageUrl'] = ($prevPage > 0) ? base_url() . "seller/order?page={$prevPage}&limit={$limit}" : null;

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
                            foreach ($parsing['db_order'] as $key_db_order) {
                                $items['no'] = $no;
                                $items['id'] = $key_db_order->order_id;
                                $items['invoice'] = $key_db_order->invoice_no;
                                $items['customerName'] = "{$key_db_order->shipping_firstname} {$key_db_order->shipping_lastname}";
                                $items['npsn'] = $key_db_order->npsn;
                                $items['schoolName'] = $key_db_order->shipping_company;
                                $items['schoolCity'] = $key_db_order->shipping_city;

                                if ($key_db_order->name == 'Dibatalkan') {
                                    $statusType = 'badge-danger';
                                } elseif ($key_db_order->name == 'Diterima') {
                                    $statusType = 'badge-success';
                                } elseif ($key_db_order->name == 'Ditolak Penyedia') {
                                    $statusType = 'badge-danger';
                                } elseif ($key_db_order->name == 'Diproses') {
                                    $statusType = 'badge-warning';
                                } else {
                                    $statusType = 'badge-success';
                                }

                                $items['status'] = '<span class="badge ' . $statusType . '">' . $key_db_order->name . '</span>';
                                $items['total'] = rupiah($key_db_order->value);
                                $items['date'] = date('d-m-Y', strtotime($key_db_order->date_added));
                                $items['paymentDue'] = "{$key_db_order->payment_tempo} hari";

                                $data['items'][] = $items;

                                $no++;
                            }
                        }

                        $data['filters'] = $this->configOrder($this->core['seller']['id'])['filters'];

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
                    // $parsing['db_product'] = $this->api_model->select_data([
                    //     'field' => '*',
                    //     'table' => 'db_product',
                    //     'where' => [
                    //         'product_id' => $id
                    //     ]
                    // ])->row();
                    // if (empty($parsing['db_product'])) {
                    //     $checking = false;
                    //     $response = $this->formatter([
                    //         'code' => self::HTTP_NOT_FOUND,
                    //         'message' => 'data not found',
                    //         'data' => (object) []
                    //     ]);
                    // } else {
                    //     if ($this->core['seller']['id'] != $parsing['db_product']->mall_id) {
                    //         $checking = false;
                    //         $response = $this->formatter([
                    //             'code' => self::HTTP_BAD_REQUEST,
                    //             'message' => 'bad request',
                    //         ]);
                    //     }
                    // }

                    // if ($checking === true) {
                    //     $param['db_order']['field'] = '
                    //     dc.*,
                    //     dc.length AS p,
                    //     dc.width AS l,
                    //     dc.height AS t,
                    //     dc.quantity AS stok,
                    //     dc.storage_quantity AS stok_gudang,
                    //     dc.store_quantity AS stok_toko,
                    //     dm.name AS penerbit,
                    //     dm.manufacturer_id,
                    //     dpd.name,
                    //     dpd.tag,
                    //     dpd.meta_description,
                    //     dpd.meta_keyword,
                    //     dpd.description,
                    //     dpd.seo,
                    //     dc.kondisi,
                    //     dc.image,
                    //     dpc.category_id,
                    //     dc.pph,dc.ppn';
                    //     $param['db_order']['table'] = 'db_product dc';
                    //     $param['db_order']['join'] = [
                    //         [
                    //             'table' => 'db_product_description dpd',
                    //             'on' => 'dc.product_id=dpd.product_id',
                    //             'type' => 'inner'
                    //         ],
                    //         [
                    //             'table' => 'db_manufacturer dm',
                    //             'on' => 'dc.manufacturer_id=dm.manufacturer_id',
                    //             'type' => 'left'
                    //         ],
                    //         [
                    //             'table' => 'db_product_to_category dpc',
                    //             'on' => 'dc.product_id=dpc.product_id',
                    //             'type' => 'inner'
                    //         ],
                    //     ];

                    //     $param['db_order']['where'] = [
                    //         'dc.product_id' => $id
                    //     ];

                    //     $parsing['db_order'] = $this->api_model->select_data($param['db_order'])->row();

                    //     if (empty($parsing['db_order'])) {
                    //         $output = (object) [];
                    //         $code = self::HTTP_NO_CONTENT;
                    //     } else {
                    //         $output = [];
                    //         $code = self::HTTP_OK;

                    //         $items['id'] = $parsing['db_order']->product_id;
                    //         $items['isLayoutBook'] = ($parsing['db_order']->layout == '1') ? true : false;

                    //         if ($items['isLayoutBook']) {
                    //             $sectionProductInformation['name'] = $parsing['db_order']->name;
                    //             $sectionProductInformation['slug'] = $parsing['db_order']->seo;
                    //             $sectionProductInformation['skKelulusan'] = $parsing['db_order']->sk_kelulusan;
                    //             $sectionProductInformation['sku'] = $parsing['db_order']->model;
                    //             $sectionProductInformation['isbn'] = $parsing['db_order']->isbn;

                    //             $sectionProductInformation['class'] = [];
                    //             for ($iClass = 1; $iClass <= 12; $iClass++) {
                    //                 $sectionProductInformation['class'][] = [
                    //                     'value' => $iClass,
                    //                     'name' => $iClass,
                    //                     'isSelected' => ($iClass == $parsing['db_order']->kelas) ? true : false,
                    //                 ];
                    //             }

                    //             $sectionProductInformation['semester'] = [];
                    //             for ($iSemester = 1; $iSemester <= 6; $iSemester++) {
                    //                 $sectionProductInformation['semester'][] = [
                    //                     'value' => $iSemester,
                    //                     'name' => $iSemester,
                    //                     'isSelected' => ($iSemester == $parsing['db_order']->semester) ? true : false,
                    //                 ];
                    //             }

                    //             $parsing['getFullCategory'] = $this->api_model->select_data([
                    //                 'field' => 'db_product_to_category.*, db_category.parent_id',
                    //                 'table' => 'db_product_to_category',
                    //                 'join' => [
                    //                     [
                    //                         'table' => 'db_category',
                    //                         'on' => 'db_category.category_id=db_product_to_category.category_id',
                    //                         'type' => 'inner'
                    //                     ],
                    //                 ],
                    //                 'where' => [
                    //                     'product_id' => $parsing['db_order']->product_id
                    //                 ],
                    //                 'order_by' => [
                    //                     'db_category.parent_id' => 'ASC'
                    //                 ]
                    //             ])->result();
                    //             $sectionProductInformation['category'] = [];
                    //             $sectionProductInformation['categoryChildren'] = [];
                    //             foreach ($parsing['getFullCategory'] as $key_getFullCategory) {
                    //                 $parentId = '';
                    //                 if ($key_getFullCategory->parent_id == '0') {
                    //                     $parentId = $key_getFullCategory->category_id;

                    //                     $parsing['db_category'] = $this->api_model->select_data([
                    //                         'field' => 'aa.category_id,bb.name as nama_kategori,aa.status',
                    //                         'table' => 'db_category aa',
                    //                         'join' => [
                    //                             [
                    //                                 'table' => 'db_category_description bb',
                    //                                 'on' => 'aa.category_id=bb.category_id',
                    //                                 'type' => 'inner'
                    //                             ],
                    //                         ],
                    //                         'where' => [
                    //                             'aa.status' => '1',
                    //                             'aa.parent_id' => '0'
                    //                         ]
                    //                     ])->result();
                    //                     foreach ($parsing['db_category'] as $key_db_category) {
                    //                         $category['value'] = $key_db_category->category_id;
                    //                         $category['name'] = $key_db_category->nama_kategori;
                    //                         $category['isSelected'] = ($key_db_category->category_id == $key_getFullCategory->category_id) ? true : false;

                    //                         $sectionProductInformation['category'][] = $category;
                    //                     }
                    //                 } else {
                    //                     $parsing['db_category_children'] = $this->api_model->select_data([
                    //                         'field' => 'aa.category_id,bb.name as nama_kategori,aa.status',
                    //                         'table' => 'db_category aa',
                    //                         'join' => [
                    //                             [
                    //                                 'table' => 'db_category_description bb',
                    //                                 'on' => 'aa.category_id=bb.category_id',
                    //                                 'type' => 'inner'
                    //                             ],
                    //                         ],
                    //                         'where' => [
                    //                             'aa.status' => '1',
                    //                             'aa.parent_id' => $parentId
                    //                         ]
                    //                     ])->result();
                    //                     foreach ($parsing['db_category_children'] as $key_db_category_children) {
                    //                         $categoryChildren['value'] = $key_db_category_children->category_id;
                    //                         $categoryChildren['name'] = $key_db_category_children->nama_kategori;
                    //                         $categoryChildren['isSelected'] = ($key_db_category_children->category_id == $key_getFullCategory->category_id) ? true : false;

                    //                         $sectionProductInformation['categoryChildren'][] = $categoryChildren;
                    //                     }
                    //                 }
                    //             }

                    //             $parsing['db_manufacturer'] = $this->api_model->select_data([
                    //                 'field' => '*',
                    //                 'table' => 'db_manufacturer',
                    //                 'order_by' => [
                    //                     'name' => 'ASC'
                    //                 ]
                    //             ])->result();
                    //             $sectionProductInformation['manufacturer'] = [];
                    //             foreach ($parsing['db_manufacturer'] as $key_db_manufacturer) {
                    //                 $manufacturer['value'] = $key_db_manufacturer->manufacturer_id;
                    //                 $manufacturer['name'] = $key_db_manufacturer->name;
                    //                 $manufacturer['isSelected'] = ($key_db_manufacturer->manufacturer_id == $parsing['db_order']->manufacturer_id) ? true : false;

                    //                 $sectionProductInformation['manufacturer'][] = $manufacturer;
                    //             }

                    //             $sectionProductInformation['description'] = $parsing['db_order']->description;
                    //             $items['sectionProductInformation'] = $sectionProductInformation;

                    //             $sectionPriceInformation['price'] = $parsing['db_order']->price;
                    //             $sectionPriceInformation['priceCurrencyFormat'] = rupiah($sectionPriceInformation['price']);
                    //             $sectionPriceInformation['priceNego'] = $parsing['db_order']->price_nego;
                    //             $sectionPriceInformation['priceNegoCurrencyFormat'] = rupiah($sectionPriceInformation['priceNego']);
                    //             $sectionPriceInformation['priceZone'] = [
                    //                 [
                    //                     'price' => $parsing['db_order']->price1,
                    //                     'priceCurrencyFormat' => rupiah($parsing['db_order']->price1)
                    //                 ],
                    //                 [
                    //                     'price' => $parsing['db_order']->price2,
                    //                     'priceCurrencyFormat' => rupiah($parsing['db_order']->price2)
                    //                 ],
                    //                 [
                    //                     'price' => $parsing['db_order']->price3,
                    //                     'priceCurrencyFormat' => rupiah($parsing['db_order']->price3)
                    //                 ],
                    //                 [
                    //                     'price' => $parsing['db_order']->price4,
                    //                     'priceCurrencyFormat' => rupiah($parsing['db_order']->price4)
                    //                 ],
                    //                 [
                    //                     'price' => $parsing['db_order']->price5,
                    //                     'priceCurrencyFormat' => rupiah($parsing['db_order']->price5)
                    //                 ]
                    //             ];
                    //             $sectionPriceInformation['stock'] = $parsing['db_order']->stok_gudang;
                    //             $sectionPriceInformation['ppn'] = [
                    //                 [
                    //                     'value' => '0',
                    //                     'name' => 'No',
                    //                     'isSelected' => ($parsing['db_order']->ppn > 0) ? false : true,
                    //                 ],
                    //                 [
                    //                     'value' => '1',
                    //                     'name' => 'Yes',
                    //                     'isSelected' => ($parsing['db_order']->ppn > 0) ? true : false,
                    //                 ],
                    //             ];
                    //             $items['sectionPriceInformation'] = $sectionPriceInformation;

                    //             $sectionMediaInformation['imagePrimary'] = (!empty($parsing['db_order']->image) || $parsing['db_order']->image != '') ? $this->core['url_image_product'] . $parsing['db_order']->image : $this->core['image_not_found'];

                    //             $parsing['db_product_image'] = $this->api_model->select_data([
                    //                 'field' => '*',
                    //                 'table' => 'db_product_image',
                    //                 'where' => [
                    //                     'product_id' => $parsing['db_order']->product_id
                    //                 ]
                    //             ])->result();
                    //             $sectionMediaInformation['imageOther'] = [];
                    //             foreach ($parsing['db_product_image'] as $key_db_product_image) {
                    //                 $imageOther['value'] = $key_db_product_image->product_image_id;
                    //                 $imageOther['image'] = (!empty($key_db_product_image->image) || $key_db_product_image->image != '') ? $this->core['url_image_product'] . $key_db_product_image->image : $this->core['image_not_found'];

                    //                 $sectionMediaInformation['imageOther'][] = $imageOther;
                    //             }
                    //             $items['sectionMediaInformation'] = $sectionMediaInformation;

                    //             $sectionSpecInformation['pages'] = $parsing['db_order']->pages;
                    //             $sectionSpecInformation['weight'] = $parsing['db_order']->weight;
                    //             $sectionSpecInformation['dimension'] = [
                    //                 'long' => $parsing['db_order']->p,
                    //                 'wide' => $parsing['db_order']->l,
                    //                 'high' => $parsing['db_order']->t,
                    //             ];
                    //             $sectionSpecInformation['condition'] = [
                    //                 [
                    //                     'value' => 'Baru',
                    //                     'name' => 'Baru',
                    //                     'isSelected' => ($parsing['db_order']->kondisi == 'Baru') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Bekas',
                    //                     'name' => 'Bekas',
                    //                     'isSelected' => ($parsing['db_order']->kondisi == 'Bekas') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['status'] = [
                    //                 [
                    //                     'value' => '0',
                    //                     'name' => 'Disable',
                    //                     'isSelected' => ($parsing['db_order']->status == '0') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '1',
                    //                     'name' => 'Enable',
                    //                     'isSelected' => ($parsing['db_order']->status == '1') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['contentPaper'] = [
                    //                 [
                    //                     'value' => '',
                    //                     'name' => 'Pilih Jenis Kertas Isi',
                    //                     'isSelected' => ($parsing['db_order']->kertas_isi == '') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'HVS 70',
                    //                     'name' => 'HVS 70',
                    //                     'isSelected' => ($parsing['db_order']->kertas_isi == 'HVS 70') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'HVS 80',
                    //                     'name' => 'HVS 80',
                    //                     'isSelected' => ($parsing['db_order']->kertas_isi == 'HVS 80') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'HVS 100',
                    //                     'name' => 'HVS 100',
                    //                     'isSelected' => ($parsing['db_order']->kertas_isi == 'HVS 100') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['coverPaper'] = [
                    //                 [
                    //                     'value' => '',
                    //                     'name' => 'Pilih Jenis Kertas Cover',
                    //                     'isSelected' => ($parsing['db_order']->kertas_cover == '') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Art Carton 210',
                    //                     'name' => 'Art Carton 210',
                    //                     'isSelected' => ($parsing['db_order']->kertas_cover == 'Art Carton 210') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'HVS 70',
                    //                     'name' => 'HVS 70',
                    //                     'isSelected' => ($parsing['db_order']->kertas_cover == 'HVS 70') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'HVS 80',
                    //                     'name' => 'HVS 80',
                    //                     'isSelected' => ($parsing['db_order']->kertas_cover == 'HVS 80') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'HVS 100',
                    //                     'name' => 'HVS 100',
                    //                     'isSelected' => ($parsing['db_order']->kertas_cover == 'HVS 100') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['fillColor'] = [
                    //                 [
                    //                     'value' => '',
                    //                     'name' => 'Pilih Jenis Warna Isi',
                    //                     'isSelected' => ($parsing['db_order']->warna_isi == '') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Full Color',
                    //                     'name' => 'Full Color',
                    //                     'isSelected' => ($parsing['db_order']->warna_isi == 'Full Color') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Black White',
                    //                     'name' => 'Black White',
                    //                     'isSelected' => ($parsing['db_order']->warna_isi == 'Black White') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['coverColor'] = [
                    //                 [
                    //                     'value' => '',
                    //                     'name' => 'Pilih Jenis Warna Cover',
                    //                     'isSelected' => ($parsing['db_order']->warna_cover == '') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Full Color',
                    //                     'name' => 'Full Color',
                    //                     'isSelected' => ($parsing['db_order']->warna_cover == 'Full Color') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Black White',
                    //                     'name' => 'Black White',
                    //                     'isSelected' => ($parsing['db_order']->warna_cover == 'Black White') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['finishing'] = [
                    //                 [
                    //                     'value' => '',
                    //                     'name' => 'Pilih Jenis Finishing',
                    //                     'isSelected' => ($parsing['db_order']->finishing == '') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'UV Varnish',
                    //                     'name' => 'UV Varnish',
                    //                     'isSelected' => ($parsing['db_order']->finishing == 'UV Varnish') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Laminating',
                    //                     'name' => 'Laminating',
                    //                     'isSelected' => ($parsing['db_order']->finishing == 'Laminating') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Hard Cover',
                    //                     'name' => 'Hard Cover',
                    //                     'isSelected' => ($parsing['db_order']->finishing == 'Hard Cover') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Embos',
                    //                     'name' => 'Embos',
                    //                     'isSelected' => ($parsing['db_order']->finishing == 'Embos') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['binding'] = [
                    //                 [
                    //                     'value' => '',
                    //                     'name' => 'Pilih Jenis Jilid',
                    //                     'isSelected' => ($parsing['db_order']->penjilidan == '') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Perfect Binding',
                    //                     'name' => 'Perfect Binding',
                    //                     'isSelected' => ($parsing['db_order']->penjilidan == 'Perfect Binding') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Spiral',
                    //                     'name' => 'Spiral',
                    //                     'isSelected' => ($parsing['db_order']->penjilidan == 'Spiral') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Jahit benang',
                    //                     'name' => 'Jahit benang',
                    //                     'isSelected' => ($parsing['db_order']->penjilidan == 'Jahit benang') ? true : false,
                    //                 ],
                    //             ];
                    //             $items['sectionSpecInformation'] = $sectionSpecInformation;
                    //         } else {
                    //             $sectionProductInformation['name'] = $parsing['db_order']->name;
                    //             $sectionProductInformation['slug'] = $parsing['db_order']->seo;
                    //             $sectionProductInformation['skKelulusan'] = $parsing['db_order']->sk_kelulusan;
                    //             $sectionProductInformation['sku'] = $parsing['db_order']->model;
                    //             $sectionProductInformation['isbn'] = $parsing['db_order']->isbn;

                    //             $sectionProductInformation['class'] = [];
                    //             for ($iClass = 1; $iClass <= 12; $iClass++) {
                    //                 $sectionProductInformation['class'][] = [
                    //                     'value' => $iClass,
                    //                     'name' => $iClass,
                    //                     'isSelected' => ($iClass == $parsing['db_order']->kelas) ? true : false,
                    //                 ];
                    //             }

                    //             $sectionProductInformation['semester'] = [];
                    //             for ($iSemester = 1; $iSemester <= 6; $iSemester++) {
                    //                 $sectionProductInformation['semester'][] = [
                    //                     'value' => $iSemester,
                    //                     'name' => $iSemester,
                    //                     'isSelected' => ($iSemester == $parsing['db_order']->semester) ? true : false,
                    //                 ];
                    //             }

                    //             $parsing['getFullCategory'] = $this->api_model->select_data([
                    //                 'field' => 'db_product_to_category.*, db_category.parent_id',
                    //                 'table' => 'db_product_to_category',
                    //                 'join' => [
                    //                     [
                    //                         'table' => 'db_category',
                    //                         'on' => 'db_category.category_id=db_product_to_category.category_id',
                    //                         'type' => 'inner'
                    //                     ],
                    //                 ],
                    //                 'where' => [
                    //                     'product_id' => $parsing['db_order']->product_id
                    //                 ],
                    //                 'order_by' => [
                    //                     'db_category.parent_id' => 'ASC'
                    //                 ]
                    //             ])->result();
                    //             $sectionProductInformation['category'] = [];
                    //             $sectionProductInformation['categoryChildren'] = [];
                    //             foreach ($parsing['getFullCategory'] as $key_getFullCategory) {
                    //                 $parentId = '';
                    //                 if ($key_getFullCategory->parent_id == '0') {
                    //                     $parentId = $key_getFullCategory->category_id;

                    //                     $parsing['db_category'] = $this->api_model->select_data([
                    //                         'field' => 'aa.category_id,bb.name as nama_kategori,aa.status',
                    //                         'table' => 'db_category aa',
                    //                         'join' => [
                    //                             [
                    //                                 'table' => 'db_category_description bb',
                    //                                 'on' => 'aa.category_id=bb.category_id',
                    //                                 'type' => 'inner'
                    //                             ],
                    //                         ],
                    //                         'where' => [
                    //                             'aa.status' => '1',
                    //                             'aa.parent_id' => '0'
                    //                         ]
                    //                     ])->result();
                    //                     foreach ($parsing['db_category'] as $key_db_category) {
                    //                         $category['value'] = $key_db_category->category_id;
                    //                         $category['name'] = $key_db_category->nama_kategori;
                    //                         $category['isSelected'] = ($key_db_category->category_id == $key_getFullCategory->category_id) ? true : false;

                    //                         $sectionProductInformation['category'][] = $category;
                    //                     }
                    //                 } else {
                    //                     $parsing['db_category_children'] = $this->api_model->select_data([
                    //                         'field' => 'aa.category_id,bb.name as nama_kategori,aa.status',
                    //                         'table' => 'db_category aa',
                    //                         'join' => [
                    //                             [
                    //                                 'table' => 'db_category_description bb',
                    //                                 'on' => 'aa.category_id=bb.category_id',
                    //                                 'type' => 'inner'
                    //                             ],
                    //                         ],
                    //                         'where' => [
                    //                             'aa.status' => '1',
                    //                             'aa.parent_id' => $parentId
                    //                         ]
                    //                     ])->result();
                    //                     foreach ($parsing['db_category_children'] as $key_db_category_children) {
                    //                         $categoryChildren['value'] = $key_db_category_children->category_id;
                    //                         $categoryChildren['name'] = $key_db_category_children->nama_kategori;
                    //                         $categoryChildren['isSelected'] = ($key_db_category_children->category_id == $key_getFullCategory->category_id) ? true : false;

                    //                         $sectionProductInformation['categoryChildren'][] = $categoryChildren;
                    //                     }
                    //                 }
                    //             }

                    //             $parsing['db_manufacturer'] = $this->api_model->select_data([
                    //                 'field' => '*',
                    //                 'table' => 'db_manufacturer',
                    //                 'order_by' => [
                    //                     'name' => 'ASC'
                    //                 ]
                    //             ])->result();
                    //             $sectionProductInformation['manufacturer'] = [];
                    //             foreach ($parsing['db_manufacturer'] as $key_db_manufacturer) {
                    //                 $manufacturer['value'] = $key_db_manufacturer->manufacturer_id;
                    //                 $manufacturer['name'] = $key_db_manufacturer->name;
                    //                 $manufacturer['isSelected'] = ($key_db_manufacturer->manufacturer_id == $parsing['db_order']->manufacturer_id) ? true : false;

                    //                 $sectionProductInformation['manufacturer'][] = $manufacturer;
                    //             }

                    //             $sectionProductInformation['description'] = $parsing['db_order']->description;
                    //             $items['sectionProductInformation'] = $sectionProductInformation;

                    //             $sectionPriceInformation['isPriceGrosirActive'] = (array_sum([
                    //                 $parsing['db_order']->grosir_price1,
                    //                 $parsing['db_order']->grosir_price2,
                    //                 $parsing['db_order']->grosir_price3,
                    //                 $parsing['db_order']->grosir_price4
                    //             ]) > 0) ? true : false;
                    //             $sectionPriceInformation['price'] = $parsing['db_order']->price;
                    //             $sectionPriceInformation['priceCurrencyFormat'] = rupiah($sectionPriceInformation['price']);
                    //             $sectionPriceInformation['priceNego'] = $parsing['db_order']->price_nego;
                    //             $sectionPriceInformation['priceNegoCurrencyFormat'] = rupiah($sectionPriceInformation['priceNego']);
                    //             $sectionPriceInformation['priceGrosir'] = [
                    //                 [
                    //                     'min' => $parsing['db_order']->grosir_min1,
                    //                     'price' => $parsing['db_order']->grosir_price1,
                    //                     'priceCurrencyFormat' => rupiah($parsing['db_order']->grosir_price1),
                    //                 ],
                    //                 [
                    //                     'min' => $parsing['db_order']->grosir_min2,
                    //                     'price' => $parsing['db_order']->grosir_price2,
                    //                     'priceCurrencyFormat' => rupiah($parsing['db_order']->grosir_price2),
                    //                 ],
                    //                 [
                    //                     'min' => $parsing['db_order']->grosir_min3,
                    //                     'price' => $parsing['db_order']->grosir_price3,
                    //                     'priceCurrencyFormat' => rupiah($parsing['db_order']->grosir_price3),
                    //                 ],
                    //                 [
                    //                     'min' => $parsing['db_order']->grosir_min4,
                    //                     'price' => $parsing['db_order']->grosir_price4,
                    //                     'priceCurrencyFormat' => rupiah($parsing['db_order']->grosir_price4),
                    //                 ],
                    //             ];
                    //             $sectionPriceInformation['unitType'] = [
                    //                 [
                    //                     'value' => 'Centimeter',
                    //                     'name' => 'Centimeter',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Centimeter') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Gram',
                    //                     'name' => 'Gram',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Gram') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Inch',
                    //                     'name' => 'Inch',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Inch') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Kilogram',
                    //                     'name' => 'Kilogram',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Kilogram') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Liter',
                    //                     'name' => 'Liter',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Liter') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Milligram',
                    //                     'name' => 'Milligram',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Milligram') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Milliliter',
                    //                     'name' => 'Milliliter',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Milliliter') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Meter',
                    //                     'name' => 'Meter',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Meter') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'm³',
                    //                     'name' => 'm³',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'm³') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Lusin',
                    //                     'name' => 'Lusin',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Lusin') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Kodi',
                    //                     'name' => 'Kodi',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Kodi') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Gross',
                    //                     'name' => 'Gross',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Gross') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Rim',
                    //                     'name' => 'Rim',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Rim') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Box',
                    //                     'name' => 'Box',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Box') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Roll',
                    //                     'name' => 'Roll',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Roll') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Set',
                    //                     'name' => 'Set',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Set') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Unit',
                    //                     'name' => 'Unit',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Unit') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Pcs',
                    //                     'name' => 'Pcs',
                    //                     'isSelected' => ($parsing['db_order']->unit_type == 'Pcs') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionPriceInformation['ppn'] = [
                    //                 [
                    //                     'value' => '0',
                    //                     'name' => 'No',
                    //                     'isSelected' => ($parsing['db_order']->ppn > 0) ? false : true,
                    //                 ],
                    //                 [
                    //                     'value' => '1',
                    //                     'name' => 'Yes',
                    //                     'isSelected' => ($parsing['db_order']->ppn > 0) ? true : false,
                    //                 ],
                    //             ];
                    //             $items['sectionPriceInformation'] = $sectionPriceInformation;

                    //             $sectionMediaInformation['imagePrimary'] = (!empty($parsing['db_order']->image) || $parsing['db_order']->image != '') ? $this->core['url_image_product'] . $parsing['db_order']->image : $this->core['image_not_found'];

                    //             $parsing['db_product_image'] = $this->api_model->select_data([
                    //                 'field' => '*',
                    //                 'table' => 'db_product_image',
                    //                 'where' => [
                    //                     'product_id' => $parsing['db_order']->product_id
                    //                 ]
                    //             ])->result();
                    //             $sectionMediaInformation['imageOther'] = [];
                    //             foreach ($parsing['db_product_image'] as $key_db_product_image) {
                    //                 $imageOther['value'] = $key_db_product_image->product_image_id;
                    //                 $imageOther['image'] = (!empty($key_db_product_image->image) || $key_db_product_image->image != '') ? $this->core['url_image_product'] . $key_db_product_image->image : $this->core['image_not_found'];

                    //                 $sectionMediaInformation['imageOther'][] = $imageOther;
                    //             }
                    //             $items['sectionMediaInformation'] = $sectionMediaInformation;

                    //             $sectionSpecInformation['condition'] = [
                    //                 [
                    //                     'value' => 'Baru',
                    //                     'name' => 'Baru',
                    //                     'isSelected' => ($parsing['db_order']->kondisi == 'Baru') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Bekas',
                    //                     'name' => 'Bekas',
                    //                     'isSelected' => ($parsing['db_order']->kondisi == 'Bekas') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['stock'] = $parsing['db_order']->stok_gudang;
                    //             $sectionSpecInformation['warranty'] = [
                    //                 [
                    //                     'value' => 'Garansi Toko',
                    //                     'name' => 'Garansi Toko',
                    //                     'isSelected' => ($parsing['db_order']->garansi == 'Garansi Toko') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Garansi Distributor',
                    //                     'name' => 'Garansi Distributor',
                    //                     'isSelected' => ($parsing['db_order']->garansi == 'Garansi Distributor') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Garansi Resmi Nasional',
                    //                     'name' => 'Garansi Resmi Nasional',
                    //                     'isSelected' => ($parsing['db_order']->garansi == 'Garansi Resmi Nasional') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Garansi Global',
                    //                     'name' => 'Garansi Global',
                    //                     'isSelected' => ($parsing['db_order']->garansi == 'Garansi Global') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['weight'] = $parsing['db_order']->weight;
                    //             $sectionSpecInformation['dimension'] = [
                    //                 'long' => $parsing['db_order']->p,
                    //                 'wide' => $parsing['db_order']->l,
                    //                 'high' => $parsing['db_order']->t,
                    //             ];
                    //             $sectionSpecInformation['status'] = [
                    //                 [
                    //                     'value' => '0',
                    //                     'name' => 'Disable',
                    //                     'isSelected' => ($parsing['db_order']->status == '0') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '1',
                    //                     'name' => 'Enable',
                    //                     'isSelected' => ($parsing['db_order']->status == '1') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['processor'] = [
                    //                 [
                    //                     'value' => '',
                    //                     'name' => 'Pilih Type Processor',
                    //                     'isSelected' => ($parsing['db_order']->processor == '') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Intel Core i3',
                    //                     'name' => 'Intel Core i3',
                    //                     'isSelected' => ($parsing['db_order']->processor == 'Intel Core i3') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Intel Core i5',
                    //                     'name' => 'Intel Core i5',
                    //                     'isSelected' => ($parsing['db_order']->processor == 'Intel Core i5') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Intel Core i7',
                    //                     'name' => 'Intel Core i7',
                    //                     'isSelected' => ($parsing['db_order']->processor == 'Intel Core i7') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['memory'] = [
                    //                 [
                    //                     'value' => '',
                    //                     'name' => 'Pilih Type Memory RAM',
                    //                     'isSelected' => ($parsing['db_order']->memory == '') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '4 GB',
                    //                     'name' => '4 GB',
                    //                     'isSelected' => ($parsing['db_order']->memory == '4 GB') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '8 GB',
                    //                     'name' => '8 GB',
                    //                     'isSelected' => ($parsing['db_order']->memory == '8 GB') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '16 GB',
                    //                     'name' => '16 GB',
                    //                     'isSelected' => ($parsing['db_order']->memory == '16 GB') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['harddisk'] = [
                    //                 [
                    //                     'value' => '',
                    //                     'name' => 'Pilih Type Hard Disk',
                    //                     'isSelected' => ($parsing['db_order']->harddisk == '') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'HDD 500 GB',
                    //                     'name' => 'HDD 500 GB',
                    //                     'isSelected' => ($parsing['db_order']->harddisk == 'HDD 500 GB') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'HDD 1 TB',
                    //                     'name' => 'HDD 1 TB',
                    //                     'isSelected' => ($parsing['db_order']->harddisk == 'HDD 1 TB') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'SSD 120 GB',
                    //                     'name' => 'SSD 120 GB',
                    //                     'isSelected' => ($parsing['db_order']->harddisk == 'SSD 120 GB') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'SSD 250 GB',
                    //                     'name' => 'SSD 250 GB',
                    //                     'isSelected' => ($parsing['db_order']->harddisk == 'SSD 250 GB') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'SSD 500 GB',
                    //                     'name' => 'SSD 500 GB',
                    //                     'isSelected' => ($parsing['db_order']->harddisk == 'SSD 500 GB') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['cdDvd'] = [
                    //                 [
                    //                     'value' => '',
                    //                     'name' => 'Pilih Type DVD/CD',
                    //                     'isSelected' => ($parsing['db_order']->cd_dvd == '') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Non',
                    //                     'name' => 'Non',
                    //                     'isSelected' => ($parsing['db_order']->cd_dvd == 'Non') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'DVD',
                    //                     'name' => 'DVD',
                    //                     'isSelected' => ($parsing['db_order']->cd_dvd == 'DVD') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'CD',
                    //                     'name' => 'CD',
                    //                     'isSelected' => ($parsing['db_order']->cd_dvd == 'CD') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['monitor'] = [
                    //                 [
                    //                     'value' => '',
                    //                     'name' => 'Pilih Type Monitor',
                    //                     'isSelected' => ($parsing['db_order']->monitor == '') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '10',
                    //                     'name' => '10',
                    //                     'isSelected' => ($parsing['db_order']->monitor == '10') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '11',
                    //                     'name' => '11',
                    //                     'isSelected' => ($parsing['db_order']->monitor == '11') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '12',
                    //                     'name' => '12',
                    //                     'isSelected' => ($parsing['db_order']->monitor == '12') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '13',
                    //                     'name' => '13',
                    //                     'isSelected' => ($parsing['db_order']->monitor == '13') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '15',
                    //                     'name' => '15',
                    //                     'isSelected' => ($parsing['db_order']->monitor == '15') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '19',
                    //                     'name' => '19',
                    //                     'isSelected' => ($parsing['db_order']->monitor == '19') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '20',
                    //                     'name' => '20',
                    //                     'isSelected' => ($parsing['db_order']->monitor == '20') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '22',
                    //                     'name' => '22',
                    //                     'isSelected' => ($parsing['db_order']->monitor == '22') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => '24',
                    //                     'name' => '24',
                    //                     'isSelected' => ($parsing['db_order']->monitor == '24') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['os'] = [
                    //                 [
                    //                     'value' => '',
                    //                     'name' => 'Pilih Type OS',
                    //                     'isSelected' => ($parsing['db_order']->sistem_operasi == '') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'DOS',
                    //                     'name' => 'DOS',
                    //                     'isSelected' => ($parsing['db_order']->sistem_operasi == 'DOS') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Windows',
                    //                     'name' => 'Windows',
                    //                     'isSelected' => ($parsing['db_order']->sistem_operasi == 'Windows') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Linux',
                    //                     'name' => 'Linux',
                    //                     'isSelected' => ($parsing['db_order']->sistem_operasi == 'Linux') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Unix',
                    //                     'name' => 'Unix',
                    //                     'isSelected' => ($parsing['db_order']->sistem_operasi == 'Unix') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'MacOS',
                    //                     'name' => 'MacOS',
                    //                     'isSelected' => ($parsing['db_order']->sistem_operasi == 'MacOS') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'Android',
                    //                     'name' => 'Android',
                    //                     'isSelected' => ($parsing['db_order']->sistem_operasi == 'Android') ? true : false,
                    //                 ],
                    //                 [
                    //                     'value' => 'iOS',
                    //                     'name' => 'iOS',
                    //                     'isSelected' => ($parsing['db_order']->sistem_operasi == 'iOS') ? true : false,
                    //                 ],
                    //             ];
                    //             $sectionSpecInformation['installedApplication'] = $parsing['db_order']->aplikasi_terpasang;
                    //             $items['sectionSpecInformation'] = $sectionSpecInformation;
                    //         }

                    //         $output = $items;
                    //     }

                    //     $response = $this->formatter([
                    //         'code' => $code,
                    //         'message' => 'get data success',
                    //         'data' => $output
                    //     ]);
                    // }
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function billing_get()
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
                $column = $this->configBilling()['column'];

                if ($this->get('page') == null || $this->get('limit') == null) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'page or limit not found',
                        'data' => [
                            'total' => 0,
                            'items' => [],
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
                                'column' => $column,
                            ]
                        ]);
                    }
                }

                if ($checking === true) {
                    if (!empty($this->get('search'))) {
                        $param['db_order_penagihan']['column_search'] = [];
                        foreach ($column as $key_column) {
                            if ($key_column['isOrder']) {
                                $param['db_order_penagihan']['column_search'][] = $key_column['value'];
                            }
                        }
                        $param['db_order_penagihan']['search'] = $this->get('search');
                    }

                    $param['db_order_penagihan']['field'] = 'pg.id,pg.order_id,pg.invoice_no,cs.firstname,cs.lastname,pg.tgl_penagihan,pg.tgl_bayar,pg.tgl_created';
                    $param['db_order_penagihan']['table'] = 'db_order_penagihan pg';
                    $param['db_order_penagihan']['join'] = [
                        [
                            'table' => 'db_customer cs',
                            'on' => 'cs.customer_id=pg.customer_id',
                            'type' => 'inner'
                        ],
                    ];

                    $param['db_order_penagihan']['where'] = [
                        'pg.mall_id' => $this->core['seller']['id'],
                    ];

                    $getSort = (!empty($this->get('sort'))) ? explode('-', $this->get('sort')) : null;
                    if (!empty($getSort)) {
                        $param['db_order_penagihan']['order_by'] = [
                            $getSort[0] => $getSort[1]
                        ];
                    } else {
                        $param['db_order_penagihan']['order_by'] = [
                            'pg.id' => 'desc'
                        ];
                    }

                    $param['group_by'] = 'pg.order_id';

                    $param['db_order_penagihan']['limit'] = [
                        $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                    ];
                    $parsing['db_order_penagihan'] = $this->api_model->select_data($param['db_order_penagihan'])->result();

                    $output = [];
                    if (empty($parsing['db_order_penagihan'])) {
                        $data['total'] = 0;
                        $data['items'] = [];
                        $code = self::HTTP_NO_CONTENT;
                    } else {
                        $code = self::HTTP_OK;
                        $totalRecord = $this->api_model->count_all_data($param['db_order_penagihan']);

                        $limit = (int) $this->get('limit');
                        $currentPage = (int) $this->get('page');
                        $prevPage = ($currentPage > 1) ? $currentPage - 1 : 0;
                        $totalPage = ceil($totalRecord / $limit);

                        $data['path'] = base_url() . "seller/order/billing";
                        $data['firstPageUrl'] = base_url() . "seller/order/billing?page=1&limit={$limit}";
                        $data['prevPageUrl'] = ($prevPage > 0) ? base_url() . "seller/order/billing?page={$prevPage}&limit={$limit}" : null;

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
                        foreach ($parsing['db_order_penagihan'] as $key_db_order_penagihan) {
                            $items['no'] = $no;
                            $items['id'] = $key_db_order_penagihan->id;
                            $items['invoice'] = $key_db_order_penagihan->invoice_no;
                            $items['customerName'] = "{$key_db_order_penagihan->firstname} {$key_db_order_penagihan->lastname}";
                            $items['billingDate'] = ((!empty($key_db_order_penagihan->tgl_penagihan)) && strtotime($key_db_order_penagihan->tgl_penagihan)) ? $key_db_order_penagihan->tgl_penagihan : null;
                            $items['paymentDate'] = ((!empty($key_db_order_penagihan->tgl_bayar)) && strtotime($key_db_order_penagihan->tgl_bayar)) ? $key_db_order_penagihan->tgl_bayar : null;
                            $items['createdAt'] = ((!empty($key_db_order_penagihan->tgl_created)) && strtotime($key_db_order_penagihan->tgl_created)) ? $key_db_order_penagihan->tgl_created : null;

                            $data['items'][] = $items;

                            $no++;
                        }
                    }

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
            }
        }

        $this->response($response['result'], $response['status']);
    }
}
