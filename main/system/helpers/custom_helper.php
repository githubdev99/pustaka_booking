<?php defined('BASEPATH') or exit('No direct script access allowed');

// Custom Function
function penyebut($nilai)
{
    $nilai = abs($nilai);
    $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
    $temp = "";
    if ($nilai < 12) {
        $temp = " " . $huruf[$nilai];
    } else if ($nilai < 20) {
        $temp = penyebut($nilai - 10) . " belas";
    } else if ($nilai < 100) {
        $temp = penyebut($nilai / 10) . " puluh" . penyebut($nilai % 10);
    } else if ($nilai < 200) {
        $temp = " seratus" . penyebut($nilai - 100);
    } else if ($nilai < 1000) {
        $temp = penyebut($nilai / 100) . " ratus" . penyebut($nilai % 100);
    } else if ($nilai < 2000) {
        $temp = " seribu" . penyebut($nilai - 1000);
    } else if ($nilai < 1000000) {
        $temp = penyebut($nilai / 1000) . " ribu" . penyebut($nilai % 1000);
    } else if ($nilai < 1000000000) {
        $temp = penyebut($nilai / 1000000) . " juta" . penyebut($nilai % 1000000);
    } else if ($nilai < 1000000000000) {
        $temp = penyebut($nilai / 1000000000) . " milyar" . penyebut(fmod($nilai, 1000000000));
    } else if ($nilai < 1000000000000000) {
        $temp = penyebut($nilai / 1000000000000) . " trilyun" . penyebut(fmod($nilai, 1000000000000));
    }
    return $temp;
}
// End Custom Function

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

if (!function_exists('remainingTime')) {
    function remainingTime($time)
    {
        $time_ago = strtotime($time);
        $cur_time = time();
        $time_elapsed = $time_ago - $cur_time;
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
                return "satu menit lagi";
            } else {
                return "$minutes menit lagi";
            }
        } else if ($hours <= 24) {
            if ($hours == 1) {
                return "satu jam lagi";
            } else {
                return "$hours jam lagi";
            }
        } else if ($days <= 7) {
            if ($days == 1) {
                return "kemarin";
            } else {
                return "$days hari lagi";
            }
        } else if ($weeks <= 4.3) {
            if ($weeks == 1) {
                return "seminggu lagi";
            } else {
                return "$weeks minggu lagi";
            }
        } else if ($months <= 12) {
            if ($months == 1) {
                return "sebulan lagi";
            } else {
                return "$months bulan lagi";
            }
        } else {
            if ($years == 1) {
                return "setahun lagi";
            } else {
                return "$years tahun lagi";
            }
        }
    }
}

if (!function_exists('day_indo')) {
    function day_indo($hari)
    {
        switch ($hari) {
            case 'Sun':
            case 'sunday':
                $hari_ini = "Minggu";
                break;

            case 'Mon':
            case 'monday':
                $hari_ini = "Senin";
                break;

            case 'Tue':
            case 'tuesday':
                $hari_ini = "Selasa";
                break;

            case 'Wed':
            case 'wednesday':
                $hari_ini = "Rabu";
                break;

            case 'Thu':
            case 'thursday':
                $hari_ini = "Kamis";
                break;

            case 'Fri':
            case 'friday':
                $hari_ini = "Jumat";
                break;

            case 'Sat':
            case 'saturday':
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

        $config = [];
        $config['protocol'] = "smtp";
        $config['smtp_host'] = "tcp://eureka1.eurekabookhouse.co.id";
        $config['smtp_port'] = "587";
        $config['starttls'] = TRUE;
        $config['smtp_user'] = "info@jaja.id";
        $config['smtp_pass'] = "jajaid789yui";
        $config["smtp_crypto"] = "tls";
        $config["dsn"] = FALSE;
        $config['newline'] = "\r\n";
        $config['charset'] = 'utf-8';
        $config['mailtype'] = 'html';

        $ci->email->initialize($config);
        $ci->email->from($config['smtp_user'], 'Jaja ID');
        if (!empty($param['to'])) {
            $ci->email->to($param['to']);
        }
        if (!empty($param['bcc'])) {
            $ci->email->bcc($param['bcc']);
        }
        $ci->email->subject($param['subject']);
        $ci->email->message($param['message']);

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

if (!function_exists('terbilang')) {
    function terbilang($nilai)
    {
        if ($nilai < 0) {
            $hasil = "minus " . trim(penyebut($nilai));
        } else {
            $hasil = trim(penyebut($nilai));
        }
        return $hasil;
    }
}

if (!function_exists('differenceDate')) {
    function differenceDate($paramDate)
    {
        $getDate = new DateTime($paramDate);
        $dateNow = new DateTime(date('Y-m-d'));

        return [
            'isBefore' => ($getDate < $dateNow),
            'isSame' => ($getDate == $dateNow),
            'isAfter' => ($getDate > $dateNow),
        ];
    }
}

if (!function_exists('differenceTime')) {
    function differenceTime($paramTime)
    {
        $start = strtotime($paramTime);
        $end = strtotime(date('H:i'));
        $mins = ($end - $start) / 60;
        return $mins;
    }
}

if (!function_exists('enkripsi')) {
    function enkripsi($password)
    {
        return md5(md5($password));
    }
}

if (!function_exists('random_number')) {
    function random_number($length)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

if (!function_exists('setArrayUnique')) {
    function setArrayUnique($array, $keep_key_assoc = false)
    {
        $duplicate_keys = array();
        $tmp = array();

        foreach ($array as $key => $val) {
            // convert objects to arrays, in_array() does not support objects
            if (is_object($val))
                $val = (array)$val;

            if (!in_array($val, $tmp))
                $tmp[] = $val;
            else
                $duplicate_keys[] = $key;
        }

        foreach ($duplicate_keys as $key)
            unset($array[$key]);

        return $keep_key_assoc ? $array : array_values($array);
    }
}

if (!function_exists('paymentDueDate')) {
    function paymentDueDate($day = null)
    {
        if (!empty($day)) {
            $result = date('Y-m-d H:i:s', strtotime("+{$day} day"));
        } else {
            $result = null;
        }

        return $result;
    }
}
