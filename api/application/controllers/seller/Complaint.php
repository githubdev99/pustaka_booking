<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Complaint extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function configComplaint()
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
                'name' => 'Invoice',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'do.invoice_no',
                'order' => '',
            ],
            [
                'name' => 'Produk',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'pds.name',
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
                'name' => 'Jenis Komplain',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'kpk.komplain',
                'order' => '',
            ],
            [
                'name' => 'Isi Komplain',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'kp.komplain',
                'order' => '',
            ],
            [
                'name' => 'Solusi',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'kp.solusi',
                'order' => '',
            ],
            [
                'name' => 'Status',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'kp.status',
                'order' => '',
            ],
            [
                'name' => 'Last Update',
                'isOrder' => true,
                'inActive' => false,
                'value' => 'kp.last_update',
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
                $column = $this->configComplaint()['column'];

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
                        $param['db_komplain']['column_search'] = [];
                        foreach ($column as $key_column) {
                            if ($key_column['isOrder']) {
                                $param['db_komplain']['column_search'][] = $key_column['value'];
                            }
                        }
                        $param['db_komplain']['search'] = $this->get('search');
                    }

                    $param['db_komplain']['field'] = '
                    kp.id,kp.order_id,kp.customer_id,kp.mall_id,kp.solusi,kp.status,kp.last_update,kp.gambar,kpk.komplain,
                    pds.name, 
                    cs.customer_id, cs.firstname, cs.lastname,
                    adr.address_1,
                    pd.image,pd.quantity,pd.price,
                    kpk.komplain,
                    kp.komplain as isi_komplain,
                    ml.name as mall_name,
                    do.invoice_no';
                    $param['db_komplain']['table'] = 'db_komplain kp';
                    $param['db_komplain']['join'] = [
                        [
                            'table' => 'db_product pd',
                            'on' => 'pd.product_id=kp.product_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_customer cs',
                            'on' => 'cs.customer_id=kp.customer_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_mall ml',
                            'on' => 'ml.mall_id=kp.mall_id',
                            'type' => 'inner'
                        ],
                        [
                            'table' => 'db_address adr',
                            'on' => 'adr.customer_id=cs.customer_id',
                            'type' => 'left'
                        ],
                        [
                            'table' => 'db_product_description pds',
                            'on' => 'pds.product_id=kp.product_id',
                            'type' => 'left'
                        ],
                        [
                            'table' => 'db_komplain_kategori kpk',
                            'on' => 'kpk.id=kp.id_komplain_kategori',
                            'type' => 'left'
                        ],
                        [
                            'table' => 'db_order do',
                            'on' => 'do.order_id=kp.order_id',
                            'type' => 'left'
                        ],
                    ];

                    $param['db_komplain']['where'] = [
                        'kp.mall_id' => $this->core['seller']['id'],
                    ];

                    $getSort = (!empty($this->get('sort'))) ? explode('-', $this->get('sort')) : null;
                    if (!empty($getSort)) {
                        $param['db_komplain']['order_by'] = [
                            $getSort[0] => $getSort[1]
                        ];
                    } else {
                        $param['db_komplain']['order_by'] = [
                            'kp.id' => 'desc'
                        ];
                    }

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
                        $totalRecord = $this->api_model->count_all_data($param['db_komplain']);

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
                        foreach ($parsing['db_komplain'] as $key_db_komplain) {
                            if ($key_db_komplain->status == '0') {
                                $isDeal = false;
                            } else {
                                $isDeal = true;
                            }

                            $items['no'] = $no;
                            $items['id'] = $key_db_komplain->id;
                            $items['invoice'] = $key_db_komplain->invoice_no;
                            $items['productName'] = $key_db_komplain->name;
                            $items['customerName'] = "{$key_db_komplain->firstname} {$key_db_komplain->lastname}";
                            $items['complaintType'] = $key_db_komplain->komplain;
                            $items['complaintText'] = $key_db_komplain->isi_komplain;
                            $items['solution'] = $key_db_komplain->solusi;
                            $items['isDeal'] = $isDeal;
                            $items['createdAt'] = ((!empty($key_db_komplain->last_update)) && strtotime($key_db_komplain->last_update)) ? $key_db_komplain->last_update : null;

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
