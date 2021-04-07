<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('encrypt_text')) {
    function encrypt_text($string)
    {
        $output = false;
        /*

        * read encrypt_key.ini file & get encryption_key | iv | encryption_mechanism value for generating encryption code

        */
        $encrypt_key    = parse_ini_file("files/encrypt_key.ini");
        $secret_key     = $encrypt_key["encryption_key"];
        $secret_iv      = $encrypt_key["iv"];
        $encrypt_method = $encrypt_key["encryption_mechanism"];

        // hash
        $key    = hash("sha256", $secret_key);

        // iv – encrypt method AES-256-CBC expects 16 bytes – else you will get a warning
        $iv     = substr(hash("sha256", $secret_iv), 0, 16);

        //do the encryption given text/string/number
        $result = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($result);
        return $output;
    }
}

if (!function_exists('decrypt_text')) {
    function decrypt_text($string)
    {
        $output = false;
        /*
        * read encrypt_key.ini file & get encryption_key | iv | encryption_mechanism value for generating encryption code
        */

        $encrypt_key    = parse_ini_file("files/encrypt_key.ini");
        $secret_key     = $encrypt_key["encryption_key"];
        $secret_iv      = $encrypt_key["iv"];
        $encrypt_method = $encrypt_key["encryption_mechanism"];

        // hash
        $key    = hash("sha256", $secret_key);

        // iv – encrypt method AES-256-CBC expects 16 bytes – else you will get a warning
        $iv = substr(hash("sha256", $secret_iv), 0, 16);

        //do the decryption given text/string/number

        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        return $output;
    }
}

if (!function_exists('date_indo')) {
    function date_indo($date)
    {
        $month = array(
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );

        $split = explode('-', $date);

        return $split[2] . ' ' . $month[(int)$split[1]] . ' ' . $split[0];
    }
}

if (!function_exists('rupiah')) {
    function rupiah($angka)
    {
        $rupiah = "Rp" . number_format($angka, 0, '', '.');
        return $rupiah;
    }
}

if (!function_exists('shoot_api')) {
    function shoot_api($param)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $param['url']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $param['method']);

        if (array_key_exists('header', $param)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $param['header']);
        }

        if (array_key_exists('data', $param)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $param['data']);
        }

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}

if (!function_exists('seo')) {
    function seo($title)
    {
        return url_title($title, '-', TRUE);
    }
}

if (!function_exists('clean_rupiah')) {
    function clean_rupiah($rupiah)
    {
        return str_replace('.', '', $rupiah);
    }
}

if (!function_exists('check_file_exists')) {
    function check_file_exists($url)
    {
        return stripos(get_headers($url)[0], "200 OK") ? true : false;
    }
}

if (!function_exists('time_ago')) {
    function time_ago($time)
    {
        $time_ago = strtotime($time);
        $cur_time = time();
        $time_elapsed = $cur_time - $time_ago;
        $seconds = $time_elapsed;
        $minutes = round($time_elapsed / 60);
        $hours = round($time_elapsed / 3600);
        $days = round($time_elapsed / 86400);
        $weeks = round($time_elapsed / 604800);
        $months = round($time_elapsed / 2600640);
        $years = round($time_elapsed / 31207680);
        if ($seconds <= 60) {
            return "baru saja";
        } else if ($minutes <= 60) {
            if ($minutes == 1) {
                return "satu menit lalu";
            } else {
                return "$minutes menit lalu";
            }
        } else if ($hours <= 24) {
            if ($hours == 1) {
                return "satu jam lalu";
            } else {
                return "$hours jam lalu";
            }
        } else if ($days <= 7) {
            if ($days == 1) {
                return "kemarin";
            } else {
                return "$days hari lalu";
            }
        } else if ($weeks <= 4.3) {
            if ($weeks == 1) {
                return "seminggu lalu";
            } else {
                return "$weeks minggu lalu";
            }
        } else if ($months <= 12) {
            if ($months == 1) {
                return "sebulan lalu";
            } else {
                return "$months bulan lalu";
            }
        } else {
            if ($years == 1) {
                return "setahun lalu";
            } else {
                return "$years tahun lalu";
            }
        }
    }
}

if (!function_exists('day_indo')) {
    function day_indo($hari)
    {
        switch ($hari) {
            case 'Sun':
                $hari_ini = "Minggu";
                break;

            case 'Mon':
                $hari_ini = "Senin";
                break;

            case 'Tue':
                $hari_ini = "Selasa";
                break;

            case 'Wed':
                $hari_ini = "Rabu";
                break;

            case 'Thu':
                $hari_ini = "Kamis";
                break;

            case 'Fri':
                $hari_ini = "Jumat";
                break;

            case 'Sat':
                $hari_ini = "Sabtu";
                break;

            default:
                $hari_ini = "Tidak di ketahui";
                break;
        }

        return $hari_ini;
    }
}

if (!function_exists('send_mail')) {
    function send_mail($param)
    {
        $ci = get_instance();
        $ci->load->library('email');
        $ci->load->library('email_template');
        $ci->email->set_newline("\r\n");

        $config['protocol'] = $ci->config->item('protocol');
        $config['smtp_host'] = $ci->config->item('smtp_host');
        $config['smtp_port'] = $ci->config->item('smtp_port');
        $config['smtp_user'] = $ci->config->item('smtp_user');
        $config['smtp_pass'] = $ci->config->item('smtp_pass');
        $config['charset'] = $ci->config->item('charset');
        $config['mailtype'] = $ci->config->item('mailtype');
        $config['newline'] = $ci->config->item('newline');

        $ci->email->initialize($config);
        $ci->email->from($config['smtp_user'], 'SIPLah Eureka Bookhouse');

        if (!empty($param['to'])) {
            $ci->email->to($param['to']);
        }

        if (!empty($param['bcc'])) {
            $ci->email->bcc($param['bcc']);
        }

        $ci->email->subject($param['subject']);
        $ci->email->message($ci->email_template->template([
            'judul' => $param['subject'],
            'logo' => 'https://siplah.eurekabookhouse.co.id/assets/front/images/icons/ebhcom-header_new.png',
            'content' =>  $ci->email_template->content([
                'isi' => $param['message'],
                'dataInvoice' => $param['dataInvoice']
            ]),
        ]));

        if (!empty($param['attach'])) {
            foreach ($param['attach'] as $key_attach) {
                $ci->email->attach($key_attach);
            }
        }

        if ($ci->email->send()) {
            return true;
        } else {
            return false;
        }
    }
}
