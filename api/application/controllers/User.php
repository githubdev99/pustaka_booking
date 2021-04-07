<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class User extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function oauth_get()
    {
        $checking = true;

        if ($this->core['isProduction']) {
            if (!$this->session->has_userdata('id')) {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_UNAUTHORIZED,
                    'message' => 'unauthorized',
                    'data' => null,
                ]);
            }

            if ($checking === true) {
                $token = $this->token->generate([
                    'id' => $this->session->userdata('id'),
                    'role' => 'customer'
                ]);

                if ($token['error']) {
                    $response = $this->formatter([
                        'code' => self::HTTP_UNAUTHORIZED,
                        'message' => $token['output'],
                        'data' => null,
                    ]);
                } else {
                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => 'get data success',
                        'data' => encrypt_text($token['output']),
                    ]);
                }
            }
        } else {
            if ($checking === true) {
                $token = $this->token->generate([
                    'id' => ($this->session->has_userdata('id')) ? $this->session->userdata('id') : '28519',
                    'role' => 'customer'
                ]);

                if ($token['error']) {
                    $response = $this->formatter([
                        'code' => self::HTTP_UNAUTHORIZED,
                        'message' => $token['output'],
                        'data' => null,
                    ]);
                } else {
                    $response = $this->formatter([
                        'code' => self::HTTP_OK,
                        'message' => 'get data success',
                        'data' => encrypt_text($token['output']),
                    ]);
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function logout_post($type = null)
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if (empty($type)) {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_BAD_REQUEST,
                    'message' => 'parameter not found',
                    'data' => [],
                ]);
            } else {
                if (!in_array($type, [
                    'customer', 'seller'
                ])) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'type not found, valid type is customer, seller',
                        'data' => [],
                    ]);
                } else {
                    if ($type === 'customer') {
                        if (empty($this->core['customer'])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_UNAUTHORIZED,
                                'message' => 'unauthorized',
                                'data' => [],
                            ]);
                        }
                    } elseif ($type === 'seller') {
                        if (empty($this->core['seller'])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_UNAUTHORIZED,
                                'message' => 'unauthorized',
                                'data' => [],
                            ]);
                        }
                    }
                }
            }

            if ($checking === true) {
                if ($type == 'customer') {
                    $query = $this->api_model->send_data([
                        'data' => [
                            'user_id' => $this->core['customer']['id'],
                            'sekolah_id' => $this->core['customer']['school']['id'],
                            'mall_id' => '',
                            'judul_notif' => 'SIPLah - Anda berhasil logout',
                            'id_tautan' => '',
                            'isi_notif' => "User {$this->core['customer']['name']} berhasil logout",
                            'tgl_added' => date('Y-m-d H:i:s'),
                            'jenis' => 'logout',
                            'ip' => $this->input->ip_address(),
                            'user_agent' => $this->agent->agent_string(),
                        ],
                        'table' => 'db_notification'
                    ]);

                    if ($query['error'] === TRUE) {
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => "bad request [{$query['system']}]",
                            'data' => null,
                        ]);
                    } else {
                        $response = $this->formatter([
                            'code' => self::HTTP_OK,
                            'message' => 'logout success',
                            'data' => null,
                        ]);
                    }

                    $this->session->sess_destroy();
                } elseif ($type == 'seller') {
                    $query = $this->api_model->send_data([
                        'data' => [
                            'user_id' => '',
                            'sekolah_id' => '',
                            'mall_id' => $this->core['seller']['id'],
                            'judul_notif' => 'SIPLah - Anda berhasil logout',
                            'id_tautan' => '',
                            'isi_notif' => "User {$this->core['seller']['name']} berhasil logout",
                            'tgl_added' => date('Y-m-d H:i:s'),
                            'jenis' => 'logout',
                            'ip' => $this->input->ip_address(),
                            'user_agent' => $this->agent->agent_string(),
                        ],
                        'table' => 'db_notification'
                    ]);

                    if ($query['error'] === TRUE) {
                        $response = $this->formatter([
                            'code' => self::HTTP_BAD_REQUEST,
                            'message' => "bad request [{$query['system']}]",
                            'data' => null,
                        ]);
                    } else {
                        $response = $this->formatter([
                            'code' => self::HTTP_OK,
                            'message' => 'logout success',
                            'data' => null,
                        ]);
                    }
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function profile_get($type = null)
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if (empty($type)) {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_BAD_REQUEST,
                    'message' => 'parameter not found',
                    'data' => [],
                ]);
            } else {
                if (!in_array($type, [
                    'customer', 'seller'
                ])) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'type not found, valid type is customer, seller',
                        'data' => [],
                    ]);
                } else {
                    if ($type === 'customer') {
                        if (empty($this->core['customer'])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_UNAUTHORIZED,
                                'message' => 'unauthorized',
                                'data' => [],
                            ]);
                        }
                    } elseif ($type === 'seller') {
                        if (empty($this->core['seller'])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_UNAUTHORIZED,
                                'message' => 'unauthorized',
                                'data' => [],
                            ]);
                        }
                    }
                }
            }

            if ($checking === true) {
                $output = [];
                if ($type == 'customer') {
                    $output = $this->core['customer'];
                } else {
                    $output = $this->core['seller'];
                }
                $response = $this->formatter([
                    'code' => self::HTTP_OK,
                    'message' => 'get data success',
                    'data' => $output,
                ]);
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function log_get($type = null)
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if (empty($type)) {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_BAD_REQUEST,
                    'message' => 'parameter not found',
                    'data' => [],
                ]);
            } else {
                $filters = $this->filter_notification();

                if (!in_array($type, [
                    'customer', 'seller'
                ])) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'type not found, valid type is customer, seller',
                        'data' => [],
                    ]);
                } else {
                    if ($type === 'customer') {
                        if (empty($this->core['customer'])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_UNAUTHORIZED,
                                'message' => 'unauthorized',
                                'data' => [],
                            ]);
                        }
                    } elseif ($type === 'seller') {
                        if (empty($this->core['seller'])) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_UNAUTHORIZED,
                                'message' => 'unauthorized',
                                'data' => [],
                            ]);
                        }
                    }
                }

                if ($this->get('page') == null || $this->get('limit') == null) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'page or limit not found',
                        'data' => [
                            'total' => 0,
                            'items' => [],
                            'filters' => $filters,
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
                            ]
                        ]);
                    }
                }
            }

            if ($checking === true) {
                $param['db_notification']['field'] = '*';
                $param['db_notification']['table'] = 'db_notification';

                if ($type == 'customer') {
                    $arr_type = [
                        'user_id' => $this->core['customer']['id'],
                    ];
                } elseif ($type == 'seller') {
                    $arr_type = [
                        'mall_id' => $this->core['seller']['id'],
                    ];
                }

                $filter_date = $this->get('filter_date');

                if (!empty($filter_date)) {
                    if (strpos($filter_date, '-') !== false) {
                        $exp_filter_date = explode('-', $filter_date);

                        $arr_filter_date = [
                            'DATE(tgl_added) >=' => date('Y-m-d', strtotime($exp_filter_date[0])),
                            'DATE(tgl_added) <=' => date('Y-m-d', strtotime($exp_filter_date[1])),
                        ];
                    } else {
                        $arr_filter_date = [
                            'DATE(tgl_added) >=' => $filter_date
                        ];
                    }
                } else {
                    $arr_filter_date = [];
                }

                $arr_filter_type = (!empty($this->get('filter_type')) && $this->get('filter_type') != 'all') ? [
                    'jenis' => $this->get('filter_type')
                ] : [];

                $param['db_notification']['where'] = array_merge($arr_type, $arr_filter_date, $arr_filter_type);

                $param['db_notification']['order_by'] = [
                    'tgl_added' => 'desc',
                ];

                $param['db_notification']['limit'] = [
                    $this->get('limit') => ($this->get('page') - 1) * $this->get('limit')
                ];
                $parsing['db_notification'] = $this->api_model->select_data($param['db_notification'])->result();

                $output = [];
                if (empty($parsing['db_notification'])) {
                    $data['total'] = 0;
                    $data['items'] = [];
                    $code = self::HTTP_NO_CONTENT;
                } else {
                    $code = self::HTTP_OK;
                    $limit = (int) $this->get('limit');

                    if ($type == 'customer') {
                        $total_record = $this->api_model->count_all_data($param['db_notification']);
                        $current_page = (int) $this->get('page');
                        $total_page = ceil($total_record / $limit);

                        $data['page'] = $current_page;
                        $data['limit'] = $limit;
                        $data['total'] = $total_record;
                        $data['pages'] = $total_page;
                        $data['items'] = [];
                    } elseif ($type == 'seller') {
                        $data['perPage'] = $limit;
                    }

                    foreach ($parsing['db_notification'] as $key_db_notification) {
                        if (strtolower($key_db_notification->jenis) == 'nego') {
                            $icon = $this->core['url_cdn'] . 'ebh/icon/other/approve.svg';
                        } elseif (strtolower($key_db_notification->jenis) == 'komplain') {
                            $icon = $this->core['url_cdn'] . 'ebh/icon/other/exxpired.svg';
                        } elseif (strtolower($key_db_notification->jenis) == 'order') {
                            $icon = $this->core['url_cdn'] . 'ebh/icon/other/kirim_mulai.svg';
                        } elseif (strtolower($key_db_notification->jenis) == 'delivery') {
                            $icon = $this->core['url_cdn'] . 'ebh/icon/other/kirim_mulai.svg';
                        } elseif (strtolower($key_db_notification->jenis) == 'login') {
                            $icon = $this->core['url_cdn'] . 'ebh/icon/other/exxpired.svg';
                        } elseif (strtolower($key_db_notification->jenis) == 'logout') {
                            $icon = $this->core['url_cdn'] . 'ebh/icon/other/exxpired.svg';
                        } else {
                            $icon = null;
                        }

                        $createdAt = explode(' ', $key_db_notification->tgl_added);

                        $items['title'] = $key_db_notification->judul_notif;
                        $items['text'] = $key_db_notification->isi_notif;
                        $items['type'] = $key_db_notification->jenis;
                        $items['icon'] = (!empty($icon)) ? $icon : $this->core['image_not_found'];
                        $items['read'] = boolval($key_db_notification->dibaca);

                        if (!empty($key_db_notification->id_tautan) || $key_db_notification->id_tautan != '0') {
                            $items['isRedirect'] = true;
                            $items['linkRedirect'] = $key_db_notification->id_tautan;
                        } else {
                            $items['isRedirect'] = false;
                            $items['linkRedirect'] = null;
                        }

                        $items['dateAt'] = date('d-m-Y', strtotime($createdAt[0]));
                        $items['timeAt'] = $createdAt[1];
                        $items['createdAt'] = time_ago($key_db_notification->tgl_added);
                        $items['ip'] = $key_db_notification->ip;
                        $items['userAgent'] = $key_db_notification->user_agent;

                        $data['items'][] = $items;
                    }
                }

                $data['filters'] = $filters;
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

    public function login_post($type = null)
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if (empty($type)) {
                $checking = false;
                $response = $this->formatter([
                    'code' => self::HTTP_BAD_REQUEST,
                    'message' => 'parameter not found',
                    'data' => null,
                ]);
            } else {
                if (!in_array($type, [
                    'seller', 'customer'
                ])) {
                    $checking = false;
                    $response = $this->formatter([
                        'code' => self::HTTP_BAD_REQUEST,
                        'message' => 'type not found, valid type is seller, customer',
                        'data' => null,
                    ]);
                } else {
                    if ($type === 'seller') {
                        if (empty($this->post('email')) || empty($this->post('password'))) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_BAD_REQUEST,
                                'message' => 'email or password cannot null',
                                'data' => null,
                            ]);
                        } else {
                            $parsing['db_mall'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_mall',
                                'where' => [
                                    'LOWER(email)' => trim(strtolower($this->post('email')))
                                ]
                            ])->row();
                            if (empty($parsing['db_mall'])) {
                                $checking = false;
                                $response = $this->formatter([
                                    'code' => self::HTTP_BAD_REQUEST,
                                    'message' => 'email not found',
                                    'data' => null,
                                ]);
                            } else {
                                if ($parsing['db_mall']->password !== sha1($this->post('password'))) {
                                    $checking = false;
                                    $response = $this->formatter([
                                        'code' => self::HTTP_BAD_REQUEST,
                                        'message' => 'incorrect email or password',
                                        'data' => null,
                                    ]);
                                }
                            }
                        }
                    } elseif ($type === 'customer') {
                        if (empty($this->post('email')) || empty($this->post('password'))) {
                            $checking = false;
                            $response = $this->formatter([
                                'code' => self::HTTP_BAD_REQUEST,
                                'message' => 'email or password cannot null',
                                'data' => null,
                            ]);
                        } else {
                            $parsing['db_customer'] = $this->api_model->select_data([
                                'field' => '*',
                                'table' => 'db_customer',
                                'where_custom' => "LOWER(email) = '" . strtolower($this->post('email')) . "'  AND password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->post('password') . "'))))) AND status = '1'"
                            ])->row();
                            if (empty($parsing['db_customer'])) {
                                $checking = false;
                                $response = $this->formatter([
                                    'code' => self::HTTP_NOT_FOUND,
                                    'message' => 'data not found',
                                    'data' => null,
                                ]);
                            }
                        }
                    }
                }
            }

            if ($checking === true) {
                if ($type == 'seller') {
                    $token = $this->token->generate([
                        'id' => $parsing['db_mall']->mall_id,
                        'role' => 'seller'
                    ]);

                    if ($token['error']) {
                        $response = $this->formatter([
                            'code' => self::HTTP_UNAUTHORIZED,
                            'message' => $token['output'],
                            'data' => null,
                        ]);
                    } else {
                        $this->api_model->send_data([
                            'data' => [
                                'user_id' => '',
                                'sekolah_id' => '',
                                'mall_id' => $parsing['db_mall']->mall_id,
                                'judul_notif' => 'SIPLah - Anda berhasil login',
                                'id_tautan' => '',
                                'isi_notif' => "User {$parsing['db_mall']->name} berhasil login",
                                'tgl_added' => date('Y-m-d H:i:s'),
                                'jenis' => 'login',
                                'ip' => $this->input->ip_address(),
                                'user_agent' => $this->agent->agent_string(),
                            ],
                            'table' => 'db_notification'
                        ]);

                        $response = $this->formatter([
                            'code' => self::HTTP_OK,
                            'message' => 'login success',
                            'data' => encrypt_text($token['output']),
                        ]);
                    }
                } elseif ($type == 'customer') {
                    $this->session->set_userdata([
                        'id' => $parsing['db_customer']->customer_id,
                        'role' => 'customer'
                    ]);

                    $token = $this->token->generate([
                        'id' => $parsing['db_customer']->customer_id,
                        'role' => 'customer'
                    ]);

                    if ($token['error']) {
                        $response = $this->formatter([
                            'code' => self::HTTP_UNAUTHORIZED,
                            'message' => $token['output'],
                            'data' => null,
                        ]);
                    } else {
                        $this->api_model->send_data([
                            'data' => [
                                'user_id' => $parsing['db_customer']->customer_id,
                                'sekolah_id' => $parsing['db_customer']->sekolah_id,
                                'mall_id' => '',
                                'judul_notif' => 'SIPLah - Anda berhasil login',
                                'id_tautan' => '',
                                'isi_notif' => "User {$parsing['db_customer']->firstname} {$parsing['db_customer']->lastname} berhasil login",
                                'tgl_added' => date('Y-m-d H:i:s'),
                                'jenis' => 'login',
                                'ip' => $this->input->ip_address(),
                                'user_agent' => $this->agent->agent_string(),
                            ],
                            'table' => 'db_notification'
                        ]);

                        $response = $this->formatter([
                            'code' => self::HTTP_OK,
                            'message' => 'login success',
                            'data' => encrypt_text($token['output']),
                        ]);
                    }
                }
            }
        }

        $this->response($response['result'], $response['status']);
    }
}
