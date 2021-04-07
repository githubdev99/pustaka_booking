<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Config extends MY_Controller
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

            if ($checking === true) {
                $output = [];

                $data['images'] = [
                    'siplah' => $this->core['url_front_image'] . 'icons/siplah-logo2.png',
                    'bos' => $this->core['url_front_image'] . 'icons/logo-bos.png',
                    'kemendikbudGif' => 'https://siplah.eurekabookhouse.co.id/assets/image/kemdikbud_anim.gif',
                    'banner' => [
                        $this->core['url_front_image'] . 'banner/larangan-bos.png'
                    ],
                    'payment' => [
                        $this->core['url_front_image'] . 'icons/bank_dki-min.png',
                        $this->core['url_front_image'] . 'icons/bri_logo.png',
                        $this->core['url_front_image'] . 'icons/mandiri_logo.png',
                        $this->core['url_front_image'] . 'icons/bank_bjb.jpg',
                        $this->core['url_front_image'] . 'icons/bank_jatim.jpg',
                        $this->core['url_front_image'] . 'icons/bank_jateng.png',
                        $this->core['url_front_image'] . 'icons/bank_mandiri_syariah.png',
                        $this->core['url_front_image'] . 'icons/bank_nagari.png',
                        $this->core['url_front_image'] . 'icons/bank_sumsel_babel.png',
                        $this->core['url_front_image'] . 'icons/bank_sumut.png',
                        $this->core['url_front_image'] . 'icons/bankaltimtara.png',
                    ],
                    'shipping' => [
                        $this->core['url_front_image'] . 'footer/kurir/ic_rajacepat.png',
                        $this->core['url_front_image'] . 'footer/kurir/jne-min.png',
                        $this->core['url_front_image'] . 'footer/kurir/pos-min.jpg',
                        $this->core['url_front_image'] . 'footer/kurir/tiki-min.jpg',
                    ],
                ];
                $data['section'] = [
                    'profitUsing' => [
                        'title' => 'Keuntungan Menggunakan SIPLah Eureka Bookhouse',
                        'items' => [
                            [
                                'icon' => $this->core['url_icon_banner_index'] . '027-shipment.png',
                                'title' => 'Produk Bervariasi',
                                'text' => 'Pilihan produk dari berbagai penyedia se-Indonesia',
                            ],
                            [
                                'icon' => $this->core['url_icon_banner_index'] . '004-dollars.png',
                                'title' => 'Pembayaran Fleksibel',
                                'text' => 'Tentukan sendiri kemampuan tempo pembayaran saat membuat pesanan',
                            ],
                            [
                                'icon' => $this->core['url_icon_banner_index'] . '006-wallet.png',
                                'title' => 'Cashless',
                                'text' => 'Tanpa ribet membawa uang tunai',
                            ],
                            [
                                'icon' => $this->core['url_icon_banner_index'] . '039-security-shield.png',
                                'title' => 'Aman',
                                'text' => 'Pembayaran dengan perantara pihak SIPlah',
                            ],
                            [
                                'icon' => $this->core['url_icon_banner_index'] . '044-buy-button.png',
                                'title' => 'Mudah',
                                'text' => 'Lakukan pembelian di ujung jari anda',
                            ],
                            [
                                'icon' => $this->core['url_icon_banner_index'] . '014-discount-label.png',
                                'title' => 'Nego',
                                'text' => 'Dapatkan harga terbaik untuk pembelian anda',
                            ],
                        ],
                    ],
                    'aboutSiplah' => [
                        'title' => 'Sekilas SIPLah',
                        'items' => [
                            [
                                'title' => 'Ringkasan',
                                'text' => 'Sistem Informasi Pengadaan Sekolah (SIPLah) adalah sistem elektronik yang dapat digunakan oleh sekolah untuk melaksanakan proses PBJ secara daring yang dananya bersumber dari dana BOS. SIPLah dirancang untuk memanfaatkan Sistem Pasar Daring (Online Marketplace) yang dioperasikan oleh pihak ketiga. Sistem pasar daring yang dapat dikategorikan sebagai SIPLah harus memiliki fitur tertentu dan memenuhi kebutuhan Kementerian Pendidikan dan Kebudayaan.',
                            ],
                            [
                                'title' => 'Fitur',
                                'text' => 'SIPLah harus memiliki fitur utama yang dapat memfasilitasi sekolah untuk merealisasikan rencana kerja anggaran sekolah, memperoleh informasi mengenai penyedia barang dan jasa, informasi mengenai barang dan jasa yang akan dibeli, melakukan perbandingan harga barang dan jasa, melakukan pemesanan barang dan jasa, melakukan pemantauan pemenuhan pesanan, melaksanakan pembayaran non tunai, dan mengelola dokumentasi proses serta bukti transaksi PBJ. SIPLah harus dapat menjadi media interaksi daring antara sekolah sebagai pembeli dengan penyedia barang dan jasa dan sebagai penjual. SIPLah juga harus dapat menjadi alat bantu supervisi proses PBJ oleh Kepala Sekolah dan/atau Bendahara Sekolah. SIPLah juga harus dapat memenuhi kebutuhan Kementerian Pendidikan dan Kebudayaan dalam melakukan pengawasan atas proses pengadaan barang dan jasa sekolah serta realisasi penggunaan dana BOS sesuai dengan ketentuan yang berlaku.',
                            ],
                            [
                                'title' => 'Tujuan',
                                'text' => 'Sistem berbasis teknologi informasi dan komunikasi dengan konsep sistem elektronik BOS bertujuan mewujudkan tata kelola keuangan yang transparan dan efektif khususnya dalam pengelolaan dana BOS. Didalamnya akan terdiri dari beberapa aplikasi berbasis TIK untuk melakukan tata kelola, mulai dari perencanaan, realisasi dan pelaporan dana BOS. Dengan adanya SIPLah diharapkan tata kelolah dana BOS dapat lebih terdokumentasi dengan baik, lebih transpadan dan akuntabel. Pengembangan sistem elektronik BOS juga juga untuk mendukung kebijakan pengaplikasian proses transaksi non tunai (cashless) dalam penyaluran dan pemanfaatan Dana BOS.',
                            ],
                        ],
                    ],
                ];

                $output = $data;

                $response = $this->formatter([
                    'code' => self::HTTP_OK,
                    'message' => 'get data success',
                    'data' => $output
                ]);
            }
        }

        $this->response($response['result'], $response['status']);
    }

    public function modal_add_nego_get()
    {
        if (!empty($this->auth())) {
            $response = $this->auth();
        } else {
            $checking = true;

            if ($checking === true) {
                $output = [];

                $output = [
                    [
                        'type' => 'dropdown',
                        'name' => 'Lama Pembayaran (Term of Payment)',
                        'value' => 'termOfPayment',
                        'items' => [
                            [
                                'name' => '1 Hari',
                                'value' => '1',
                            ],
                            [
                                'name' => '3 Hari',
                                'value' => '3',
                            ],
                            [
                                'name' => '7 Hari',
                                'value' => '7',
                            ],
                            [
                                'name' => '14 Hari',
                                'value' => '14',
                            ],
                            [
                                'name' => '30 Hari',
                                'value' => '30',
                            ],
                        ],
                    ],
                    [
                        'type' => 'dropdown',
                        'name' => 'Kurir',
                        'value' => 'courier',
                        'items' => [
                            [
                                'name' => 'JNE',
                                'value' => 'jne',
                            ],
                            [
                                'name' => 'TIKI',
                                'value' => 'tiki',
                            ],
                            [
                                'name' => 'POS Indonesia',
                                'value' => 'pos',
                            ],
                            [
                                'name' => 'Kurir Penyedia',
                                'value' => 'penyedia',
                            ],
                        ],
                    ],
                    [
                        'type' => 'dropdown',
                        'name' => 'Pembungkus',
                        'value' => 'wrapping',
                        'items' => [
                            [
                                'name' => 'Kardus dan Plastik',
                                'value' => 'Kardus dan Plastik',
                            ],
                            [
                                'name' => 'Kardus',
                                'value' => 'Kardus dan Bubble Wrap',
                            ],
                            [
                                'name' => 'Kardus, Plastik dan Bubble Wrap',
                                'value' => 'Kardus, Plastik dan Bubble Wrap',
                            ],
                            [
                                'name' => 'Bubble Wrap',
                                'value' => 'Bubble Wrap',
                            ],
                            [
                                'name' => 'Kayu',
                                'value' => 'Kayu',
                            ],
                        ],
                    ],
                    [
                        'type' => 'dropdown',
                        'name' => 'Asuransi',
                        'value' => 'insurance',
                        'items' => [
                            [
                                'name' => 'Tidak',
                                'value' => 'Tidak',
                            ],
                            [
                                'name' => 'Ya',
                                'value' => 'Ya',
                            ],
                        ],
                    ],
                ];

                $response = $this->formatter([
                    'code' => self::HTTP_OK,
                    'message' => 'get data success',
                    'data' => $output
                ]);
            }
        }

        $this->response($response['result'], $response['status']);
    }
}
