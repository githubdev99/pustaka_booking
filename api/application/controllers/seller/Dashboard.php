<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends MY_Controller
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

            if (empty($this->core['seller'])) {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_UNAUTHORIZED,
                    'message' => 'unauthorized',
                    'data' => (object) [],
                ]);
            }

            if ($checking === true) {
                $output = (object) [];
                if ($this->core['seller']['isMustUpdate']) {
                    $code = self::HTTP_NO_CONTENT;
                    $data = (object) [];
                } else {
                    $code = self::HTTP_OK;

                    $data['card'] = [
                        [
                            'title' => 'Pesanan Baru',
                            'icon' => '<i class="now-ui-icons shopping_cart-simple" style="font-size: 20px; font-weight:500"></i>',
                            'total' => $this->api_model->count_all_data([
                                'where' => [
                                    'mall_id' => $this->core['seller']['id'],
                                    'order_status_id' => '0'
                                ],
                                'table' => 'db_order'
                            ]),
                        ],
                        [
                            'title' => 'Semua Pesanan',
                            'icon' => '<i class="now-ui-icons business_chart-bar-32" style="font-size: 20px; font-weight:500"></i>',
                            'total' => $this->api_model->count_all_data([
                                'where' => [
                                    'mall_id' => $this->core['seller']['id'],
                                    'order_status_id !=' => '90'
                                ],
                                'table' => 'db_order'
                            ]),
                        ],
                        [
                            'title' => 'Sekolah',
                            'icon' => '<i class="now-ui-icons business_bank" style="font-size: 20px; font-weight:500"></i>',
                            'total' => $this->api_model->count_all_data([
                                'where' => [
                                    'mall_id' => $this->core['seller']['id'],
                                    'order_status_id !=' => '90'
                                ],
                                'group_by' => 'npsn',
                                'table' => 'db_order'
                            ]),
                        ],
                        [
                            'title' => 'Produk Aktif',
                            'icon' => '<i class="now-ui-icons shopping_box" style="font-size: 20px; font-weight:500"></i>',
                            'total' => $this->api_model->count_all_data([
                                'where' => [
                                    'blokir' => '0',
                                    'status' => '1',
                                    'disabled' => 'N',
                                    'mall_id' => $this->core['seller']['id'],
                                ],
                                'table' => 'db_product'
                            ]),
                        ],
                    ];

                    $parsing['db_mall_to_rek'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_mall_to_rek',
                        'where' => [
                            'mall_id' => $this->core['seller']['id']
                        ],
                        'limit' => 2
                    ])->result();
                    $data['account'] = [];
                    foreach ($parsing['db_mall_to_rek'] as $key_db_mall_to_rek) {
                        $account['id'] = $key_db_mall_to_rek->id;
                        $account['bank'] = $key_db_mall_to_rek->nama_bank;
                        $account['branch'] = $key_db_mall_to_rek->cabang;
                        $account['number'] = $key_db_mall_to_rek->nomor_rekening;
                        $account['asName'] = $key_db_mall_to_rek->atas_nama;

                        $data['account'][] = $account;
                    }

                    $parsing['areaDistribution'] = $this->api_model->select_data([
                        'field' => 'a.shipping_province,COUNT(a.shipping_province) as brpa,SUM(b.value) as brp_duid',
                        'table' => 'db_order a',
                        'join' => [
                            [
                                'table' => 'db_order_total b',
                                'on' => 'b.order_id=a.order_id',
                                'type' => 'inner'
                            ]
                        ],
                        'where' => [
                            'a.mall_id' => $this->core['seller']['id'],
                            'b.sort_order' => '9',
                        ],
                        'where_not_in' => [
                            'a.order_status_id' => ['0', '7', '8', '10', '14', '19', '90']
                        ],
                        'group_by' => 'a.shipping_province',
                        'order_by' => [
                            'brp_duid' => 'DESC'
                        ],
                        'limit' => 6
                    ])->result();
                    $sectionStatistic['areaDistribution'] = [];
                    foreach ($parsing['areaDistribution'] as $key_areaDistribution) {
                        $areaDistribution['province'] = $key_areaDistribution->shipping_province;
                        $areaDistribution['total'] = $key_areaDistribution->brpa;
                        $areaDistribution['value'] = (int) $key_areaDistribution->brp_duid;
                        $areaDistribution['valueCurrencyFormat'] = rupiah($areaDistribution['value']);

                        $sectionStatistic['areaDistribution'][] = $areaDistribution;
                    }

                    $parsing['popularSchool'] = $this->api_model->select_data([
                        'field' => 'a.shipping_company,a.shipping_city,COUNT(a.customer_id) as brpa',
                        'table' => 'db_order a',
                        'where' => [
                            'a.mall_id' => $this->core['seller']['id'],
                        ],
                        'where_not_in' => [
                            'a.order_status_id' => ['0', '7', '8', '10', '14', '19', '90']
                        ],
                        'group_by' => 'a.customer_id',
                        'order_by' => [
                            'brpa' => 'DESC'
                        ],
                        'limit' => 6
                    ])->result();
                    $sectionStatistic['popularSchool'] = [];
                    $chartStatistic_popularSchool = [
                        'label' => [],
                        'value' => [],
                    ];
                    foreach ($parsing['popularSchool'] as $key_popularSchool) {
                        $popularSchool['name'] = $key_popularSchool->shipping_company;
                        $popularSchool['city'] = $key_popularSchool->shipping_city;
                        $popularSchool['total'] = $key_popularSchool->brpa;

                        $sectionStatistic['popularSchool'][] = $popularSchool;
                        $chartStatistic_popularSchool['label'][] = $key_popularSchool->shipping_company;
                        $chartStatistic_popularSchool['value'][] = (int) $key_popularSchool->brpa;
                    }

                    $parsing['popularProduct'] = $this->api_model->select_data([
                        'field' => 'a.product_id,a.model,b.mall_name,a.name,SUM(a.quantity) as brpa,SUM(c.value) as brpduid',
                        'table' => 'db_order_product a',
                        'join' => [
                            [
                                'table' => 'db_order b',
                                'on' => 'b.order_id=a.order_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_order_total c',
                                'on' => 'c.order_id=a.order_id',
                                'type' => 'inner'
                            ],
                        ],
                        'where' => [
                            'b.mall_id' => $this->core['seller']['id'],
                            'c.sort_order' => '9',
                        ],
                        'where_not_in' => [
                            'b.order_status_id' => ['0', '7', '8', '10', '14', '19', '90']
                        ],
                        'group_by' => 'a.product_id',
                        'order_by' => [
                            'brpa' => 'DESC'
                        ],
                        'limit' => 10
                    ])->result();
                    $sectionStatistic['popularProduct'] = [];
                    foreach ($parsing['popularProduct'] as $key_popularProduct) {
                        $popularProduct['code'] = $key_popularProduct->model;
                        $popularProduct['name'] = $key_popularProduct->name;
                        $popularProduct['total'] = $key_popularProduct->brpa;

                        $sectionStatistic['popularProduct'][] = $popularProduct;
                    }

                    $parsing['transactionValue'] = $this->api_model->select_data([
                        'field' => 'c.name,COUNT(a.order_id) as kali, SUM(b.value) AS value',
                        'table' => 'db_order a',
                        'join' => [
                            [
                                'table' => 'db_order_total b',
                                'on' => 'b.order_id=a.order_id',
                                'type' => 'inner'
                            ],
                            [
                                'table' => 'db_order_status c',
                                'on' => 'c.order_status_id=a.order_status_id',
                                'type' => 'inner'
                            ],
                        ],
                        'where' => [
                            'a.mall_id' => $this->core['seller']['id'],
                            'b.code' => 'total',
                            'a.order_status_id !=' => '90',
                        ],
                        'group_by' => 'a.order_status_id'
                    ])->result();
                    $transactionValue['total'] = 0;
                    $transactionValue['value'] = 0;
                    $transactionValue['valueCurrencyFormat'] = rupiah($transactionValue['value']);

                    $transactionValue['items'] = [];
                    $sumTotal = [];
                    $sumValue = [];
                    foreach ($parsing['transactionValue'] as $key_transactionValue) {
                        $sumTotal[] = $key_transactionValue->kali;
                        $sumValue[] = $key_transactionValue->value;

                        $items['name'] = $key_transactionValue->name;
                        $items['total'] = $key_transactionValue->kali;
                        $items['value'] = $key_transactionValue->value;
                        $items['valueCurrencyFormat'] = rupiah($items['value']);

                        $transactionValue['items'][] = $items;
                    }

                    $transactionValue['total'] = array_sum($sumTotal);
                    $transactionValue['value'] = array_sum($sumValue);
                    $transactionValue['valueCurrencyFormat'] = rupiah($transactionValue['value']);

                    $sectionStatistic['transactionValue'] = $transactionValue;

                    $data['sectionStatistic'] = $sectionStatistic;

                    $chartStatistic['areaDistribution'] = [
                        'label' => [],
                        'value' => [],
                    ];
                    foreach ($parsing['areaDistribution'] as $key_areaDistribution) {
                        $chartStatistic['areaDistribution']['label'][] = $key_areaDistribution->shipping_province;
                        $chartStatistic['areaDistribution']['value'][] = $key_areaDistribution->brpa;
                    }

                    $chartStatistic['bigChart'] = [];
                    for ($month = 1; $month <= 12; $month++) {
                        $addZero = ($month < 10) ? '0' : '';
                        $getMonth = "{$addZero}{$month}";

                        $chartStatistic['bigChart'][] = $this->api_model->count_all_data([
                            'where' => [
                                'a.mall_id' => $this->core['seller']['id'],
                                'b.code' => 'total',
                                'a.order_status_id !=' => '90',
                                'MONTH(a.date_added)' => $getMonth,
                            ],
                            'join' => [
                                [
                                    'table' => 'db_order_total b',
                                    'on' => 'b.order_id=a.order_id',
                                    'type' => 'inner'
                                ],
                                [
                                    'table' => 'db_order_status c',
                                    'on' => 'c.order_status_id=a.order_status_id',
                                    'type' => 'inner'
                                ],
                            ],
                            'table' => 'db_order a'
                        ]);
                    }

                    $chartStatistic['popularSchool'] = $chartStatistic_popularSchool;

                    $data['chartStatistic'] = $chartStatistic;
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
}
