<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Setting extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function expedition_get()
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
                if ($checking === true) {
                    $param['db_mall_ekspedisi']['field'] = '*';
                    $param['db_mall_ekspedisi']['table'] = 'db_mall_ekspedisi a';
                    $param['db_mall_ekspedisi']['join'] = [
                        [
                            'table' => 'db_ekspedisi b',
                            'on' => 'b.id_ekspedisi=a.id_ekspedisi',
                            'type' => 'inner'
                        ],
                    ];

                    $param['db_mall_ekspedisi']['where'] = [
                        'a.mall_id' => $this->core['seller']['id'],
                        'b.berlaku' => '1',
                    ];

                    $parsing['db_mall_ekspedisi'] = $this->api_model->select_data($param['db_mall_ekspedisi'])->result();

                    $output = [];
                    if (empty($parsing['db_mall_ekspedisi'])) {
                        $data['total'] = 0;
                        $data['items'] = [];
                        $code = self::HTTP_NO_CONTENT;
                    } else {
                        $code = self::HTTP_OK;
                        $totalRecord = $this->api_model->count_all_data($param['db_mall_ekspedisi']);

                        $data['total'] = $totalRecord;
                        $data['items'] = [];

                        foreach ($parsing['db_mall_ekspedisi'] as $key_db_mall_ekspedisi) {
                            $items['id'] = $key_db_mall_ekspedisi->id_ekspedisi;
                            $items['name'] = $key_db_mall_ekspedisi->nama_ekspedisi;
                            $items['image'] = (!empty($key_db_mall_ekspedisi->logo) || $key_db_mall_ekspedisi->logo != '') ? $this->core['url_front_image'] . 'footer/kurir/' . $key_db_mall_ekspedisi->logo : $this->core['image_not_found'];
                            $items['isChecked'] = ($key_db_mall_ekspedisi->status == '1') ? true : false;

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
        }

        $this->response($response['result'], $response['status']);
    }

    public function expedition_post()
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
                if (!$this->post()) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'bad request',
                    ]);
                } else {
                    $check['db_mall_ekspedisi'] = $this->api_model->select_data([
                        'field' => '*',
                        'table' => 'db_mall_ekspedisi',
                        'where' => [
                            'mall_id' => $this->core['seller']['id'],
                            'id_ekspedisi' => $this->post('id')
                        ]
                    ])->row_array();
                    if (empty($check['db_mall_ekspedisi'])) {
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
                        'id_ekspedisi' => $this->post('id')
                    ],
                    'data' => [
                        'status' => ($check['db_mall_ekspedisi']['status'] == '1') ? 0 : 1
                    ],
                    'table' => 'db_mall_ekspedisi'
                ]);

                if ($query['error'] === true) {
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => "select data failed [{$query['system']}]",
                    ]);
                } else {
                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => "select data success",
                    ]);
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }
}
