<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if ($this->uri->segment('2') != 'logout') {
            $this->auth([
                'session' => 'admin',
                'login' => true
            ]);

            $this->auth([
                'session' => 'member',
                'login' => true
            ]);
        }
    }

    public function index()
    {
        redirect('auth/login', 'refresh');
    }

    public function login()
    {
        $title = 'Login';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'auth/login/v_login',
            'get_script' => 'auth/login/script_login'
        ];

        if (!$this->input->post()) {
            $this->master->template($data);
        } else {
            $isError = false;

            if ($this->input->post('submit') == 'login') {
                if (!$this->input->post()) {
                    $isError = true;
                    $output = [
                        'isError' => $isError,
                        'type' => 'error',
                        'message' => 'Permintaan tidak valid',
                    ];
                } else {
                    $check['user'] = $this->api_model->select_data([
                        'field' => 'user.*, role.name as role_name',
                        'table' => 'user',
                        'join' => [
                            [
                                'table' => 'role',
                                'on' => 'role.id = user.role_id',
                                'type' => 'inner'
                            ],
                        ],
                        'where' => [
                            'user.email' => $this->input->post('email'),
                        ],
                    ])->row();
                    if (empty($check['user'])) {
                        $isError = true;
                        $output = [
                            'isError' => $isError,
                            'type' => 'warning',
                            'message' => 'Email tidak ditemukan',
                        ];
                    } else {
                        if (!password_verify($this->input->post('password'), $check['user']->password)) {
                            $isError = true;
                            $output = [
                                'isError' => $isError,
                                'type' => 'error',
                                'message' => 'Email atau password salah',
                            ];
                        }
                    }
                }

                if (!$isError) {
                    $output = [
                        'isError' => $isError,
                        'type' => 'success',
                        'message' => 'Login berhasil',
                        'callback' => base_url() . 'admin/dashboard'
                    ];

                    $this->session->set_userdata([
                        'id' => $check['user']->id,
                        'role' => $check['user']->role_name,
                    ]);
                }
            }

            $this->output->set_content_type('application/json')->set_output(json_encode($output));
        }
    }

    public function register()
    {
        $title = 'Buat Akun';
        $data = [
            'core' => $this->core($title),
            'get_view' => 'auth/register/v_register',
            'get_script' => 'auth/register/script_register'
        ];

        if (!$this->input->post()) {
            $this->master->template($data);
        } else {
            $isError = false;

            if ($this->input->post('submit') == 'register') {
                if (!$this->input->post()) {
                    $isError = true;
                    $output = [
                        'isError' => $isError,
                        'type' => 'error',
                        'message' => 'Permintaan tidak valid',
                    ];
                } else {
                    $secret_key = "6LdGWnYaAAAAANk4u5nW_3bEuii-26C7hc3cAYFS";

                    $response = json_decode(shoot_api([
                        'url' => 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $this->input->post('g-recaptcha-response'),
                        'method' => 'GET',
                        'header' => [
                            "Content-Type: application/json"
                        ]
                    ]), true);

                    if (!$response['success'] || (!$response['success'] && $response['error-codes'][0] != 'timeout-or-duplicate')) {
                        $isError = true;
                        $output = [
                            'isError' => $isError,
                            'type' => 'error',
                            'message' => 'Verifikasi captcha gagal.',
                            'callback' => base_url() . 'auth/register'
                        ];
                    } else {
                        $check['user'] = $this->api_model->select_data([
                            'field' => '*',
                            'table' => 'user',
                            'where' => [
                                'email' => $this->input->post('email'),
                                'role_id' => 2,
                            ],
                        ])->row();
                        if (!empty($check['user'])) {
                            $isError = true;
                            $output = [
                                'isError' => $isError,
                                'type' => 'warning',
                                'message' => 'Email sudah digunakan',
                                'callback' => base_url() . 'auth/register'
                            ];
                        }
                    }
                }

                if (!$isError) {
                    $query = $this->api_model->send_data([
                        'data' => [
                            'name' => $this->input->post('name'),
                            'email' => $this->input->post('email'),
                            'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                            'role_id' => 2,
                            'created_at' => date('Y-m-d H:i:s'),
                        ],
                        'table' => 'user'
                    ]);

                    if ($query['error']) {
                        $isError = true;
                        $output = [
                            'isError' => $isError,
                            'type' => 'error',
                            'message' => "Registrasi gagal [{$query['system']}]",
                        ];
                    } else {
                        $output = [
                            'isError' => $isError,
                            'type' => 'success',
                            'message' => 'Registrasi berhasil dilakukan',
                            'callback' => base_url() . 'auth/login'
                        ];
                    }
                }
            }

            $this->output->set_content_type('application/json')->set_output(json_encode($output));
        }
    }

    public function logout()
    {
        $title = 'Waiting';
        $data = [
            'core' => $this->core($title)
        ];
        $this->master->template($data);

        $this->session->unset_userdata('id');
        $this->session->unset_userdata('role');

        $this->alert_popup([
            'name' => 'show_alert',
            'swal' => [
                'title' => 'Anda berhasil logout!',
                'type' => 'success'
            ]
        ]);

        redirect(base_url() . 'auth/login', 'refresh');
    }
}
