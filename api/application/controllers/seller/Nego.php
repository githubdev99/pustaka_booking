<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Nego extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function configNego()
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
                'name' => 'Customer',
                'isOrder' => true,
                'inActive' => false,
                'value' => "CONCAT(dd.firstname,' ',dd.lastname)",
                'order' => '',
            ],
            [
                'name' => 'Sekolah',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'ee.nama_sekolah',
                'order' => '',
            ],
            [
                'name' => 'Barang',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'cc.name',
                'order' => '',
            ],
            [
                'name' => 'Tanggal',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'aa.tgl_added',
                'order' => '',
            ],
            [
                'name' => 'Status',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'aa.status',
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

    public function index_get()
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
                $column = $this->configNego()['column'];

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
                        $param['db_nego']['column_search'] = [];
                        foreach ($column as $key_column) {
                            if ($key_column['isOrder']) {
                                $param['db_nego']['column_search'][] = $key_column['value'];
                            }
                        }
                        $param['db_nego']['search'] = $this->get('search');
                    }

                    $param['db_nego']['field'] = 'aa.id_nego,aa.status,bb.price,bb.image,cc.seo,cc.name,aa.tgl_added,dd.firstname,dd.lastname,ee.nama_sekolah';
                    $param['db_nego']['table'] = 'db_nego aa';
                    $param['db_nego']['join'] = [
                        [
                            'table' => 'db_product bb',
                            'on' => 'bb.product_id=aa.id_product',
                            'type' => 'left'
                        ],
                        [
                            'table' => 'db_product_description cc',
                            'on' => 'cc.product_id=aa.id_product',
                            'type' => 'left'
                        ],
                        [
                            'table' => 'db_customer dd',
                            'on' => 'dd.customer_id=aa.id_customer',
                            'type' => 'left'
                        ],
                        [
                            'table' => 'db_customer_school ee',
                            'on' => 'dd.sekolah_id=ee.sekolah_id',
                            'type' => 'left'
                        ],
                    ];

                    $param['db_nego']['where'] = [
                        'aa.id_mall' => $this->core['seller']['id'],
                    ];

                    $getSort = (!empty($this->get('sort'))) ? explode('-', $this->get('sort')) : null;
                    if (!empty($getSort)) {
                        $param['db_nego']['order_by'] = [
                            $getSort[0] => $getSort[1]
                        ];
                    } else {
                        $param['db_nego']['order_by'] = [
                            'aa.id_nego' => 'desc'
                        ];
                    }

                    $param['group_by'] = 'pg.order_id';

                    $param['db_nego']['limit'] = [
                        $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                    ];
                    $parsing['db_nego'] = $this->api_model->select_data($param['db_nego'])->result();

                    $output = [];
                    if (empty($parsing['db_nego'])) {
                        $data['total'] = 0;
                        $data['items'] = [];
                        $code = self::HTTP_NO_CONTENT;
                    } else {
                        $code = self::HTTP_OK;
                        $totalRecord = $this->api_model->count_all_data($param['db_nego']);

                        $limit = (int) $this->get('limit');
                        $currentPage = (int) $this->get('page');
                        $prevPage = ($currentPage > 1) ? $currentPage - 1 : 0;
                        $totalPage = ceil($totalRecord / $limit);

                        $data['path'] = base_url() . "seller/nego";
                        $data['firstPageUrl'] = base_url() . "seller/nego?page=1&limit={$limit}";
                        $data['prevPageUrl'] = ($prevPage > 0) ? base_url() . "seller/nego?page={$prevPage}&limit={$limit}" : null;

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
                        foreach ($parsing['db_nego'] as $key_db_nego) {
                            if ($key_db_nego->status == '0') {
                                $isDeal = false;
                                $isReject = false;
                            } else if ($key_db_nego->status == '1') {
                                $isDeal = true;
                                $isReject = false;
                            } else if ($key_db_nego->status == '2') {
                                $isDeal = false;
                                $isReject = true;
                            }

                            $items['no'] = $no;
                            $items['id'] = $key_db_nego->id_nego;
                            $items['customerName'] = "{$key_db_nego->firstname} {$key_db_nego->lastname}";
                            $items['schoolName'] = $key_db_nego->nama_sekolah;
                            $items['itemName'] = $key_db_nego->name;
                            $items['isDeal'] = $isDeal;
                            $items['isReject'] = $isReject;
                            $items['createdAt'] = ((!empty($key_db_nego->tgl_added)) && strtotime($key_db_nego->tgl_added)) ? date('d M Y H:i:s', strtotime($key_db_nego->tgl_added)) : null;

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
