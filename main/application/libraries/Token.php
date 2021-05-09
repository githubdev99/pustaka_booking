<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'third_party/php-jwt/JWT.php';
require_once APPPATH . 'third_party/php-jwt/BeforeValidException.php';
require_once APPPATH . 'third_party/php-jwt/ExpiredException.php';
require_once APPPATH . 'third_party/php-jwt/SignatureInvalidException.php';

use \Firebase\JWT\JWT;

class Token
{
    protected $token_key;
    protected $token_algorithm;
    protected $token_header;
    protected $token_expire;
    protected $token_expire_time;

    public function __construct()
    {
        $this->CI = &get_instance();

        $this->CI->load->config('jwt');

        $this->token_key        = $this->CI->config->item('jwt_key');
        $this->token_algorithm  = $this->CI->config->item('jwt_algorithm');
        $this->token_header  = $this->CI->config->item('token_header');
        $this->token_expire  = $this->CI->config->item('token_expire');
        $this->token_expire_time  = $this->CI->config->item('token_expire_time');
    }

    public function generate($data = null)
    {
        if ($data and is_array($data)) {
            $data['token_time'] = time();

            try {
                return [
                    'error' => FALSE,
                    'output' => JWT::encode($data, $this->token_key, $this->token_algorithm)
                ];
            } catch (Exception $e) {
                return [
                    'error' => TRUE,
                    'output' => $e->getMessage()
                ];
            }
        } else {
            return [
                'error' => TRUE,
                'output' => 'token data undefined'
            ];
        }
    }

    public function validate($token)
    {
        if (!empty($token)) {
            try {
                try {
                    $token_decode = JWT::decode($token, $this->token_key, array($this->token_algorithm));
                } catch (Exception $e) {
                    return [
                        'error' => TRUE,
                        'output' => $e->getMessage()
                    ];
                }

                if (!empty($token_decode) and is_object($token_decode)) {
                    if ($this->token_expire == false) {
                        return [
                            'error' => FALSE,
                            'output' => $token_decode
                        ];
                    } else {
                        $time_difference = strtotime('now') - $token_decode->token_time;
                        if ($time_difference >= $this->token_expire_time) {
                            return [
                                'error' => TRUE,
                                'output' => 'token time expire'
                            ];
                        } else {
                            return [
                                'error' => FALSE,
                                'output' => $token_decode
                            ];
                        }
                    }
                } else {
                    return [
                        'error' => TRUE,
                        'output' => 'forbidden'
                    ];
                }
            } catch (Exception $e) {
                return [
                    'error' => TRUE,
                    'output' => $e->getMessage()
                ];
            }
        } else {
            return [
                'error' => TRUE,
                'output' => 'token is not define'
            ];
        }
    }
}
