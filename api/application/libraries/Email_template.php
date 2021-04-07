<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Email_template
{
    public function __construct()
    {
    }

    function content($param)
    {
        ob_start();
?>
        <table style="width:100%;" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tbody>
                <tr>
                    <td valign="top">
                        <p style="margin:0;padding:0;font-size:12px;color:#202020;font-family:Helvetica, Arial, sans-serif;line-height:24px;font-weight:normal;"> <?php echo $param['isi']; ?>
                        </p>
                    </td>
                </tr>

            </tbody>
        </table>
        <div style="font-size:10px;line-height:10px;min-height:10px;"></div>
        <?php
        if (!empty($param['dataInvoice'])) :
            $dataInvoice = $param['dataInvoice'];
        ?>
            <div class="card bs1" style="margin-bottom: 20px;">
                <div class="card-body">
                    <table class="table" style="width: 100% !important; padding-right: 10px !important;">
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                        <?php foreach ($dataInvoice['items'] as $key_items) : ?>
                            <tr>
                                <td>
                                    <div class="img-produk-cart">
                                        <img src="<?= $key_items['image'] ?>" alt="IMG">
                                    </div>
                                </td>
                                <td>
                                    <h6 class="m-b-5 text1l cl2"><?= $key_items['name'] ?></h6>
                                </td>
                                <td><?= $key_items['priceCurrencyFormat'] ?></td>
                                <td>x <?= $key_items['qty'] ?>
                                <td><b><?= $key_items['totalPriceCurrencyFormat'] ?></b></td>
                            </tr>
                        <?php endforeach ?>
                    </table>
                </div>
            </div>
            <ul class="bg0 bs1 list-group list-group-flush">
                <?php foreach ($dataInvoice['descriptionTotal'] as $key_descriptionTotal) : ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <table border="0" style="width: 100% !important;">
                            <tr>
                                <td><?= $key_descriptionTotal[0] ?></td>
                                <td align="right"><b><?= $key_descriptionTotal[1] ?></b></td>
                            </tr>
                        </table>
                    </li>
                    <br>
                <?php endforeach ?>
            </ul>
        <?php endif ?>

    <?php
        return ob_get_clean();
    }

    function template($param)
    {
        ob_start();
    ?>

        <html xmlns="http://w3.org/1999/xhtml">

        <head>
            <meta http-equiv="content-type" content="text/html; charset=utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0;">
            <meta name="format-detection" content="telephone=no" />

            <style>
                /* Reset styles */
                body {
                    margin: 0;
                    padding: 0;
                    min-width: 100%;
                    width: 100% !important;
                    height: 100% !important;
                }

                body,
                table,
                td,
                div,
                p,
                a {
                    -webkit-font-smoothing: antialiased;
                    text-size-adjust: 100%;
                    -ms-text-size-adjust: 100%;
                    -webkit-text-size-adjust: 100%;
                    line-height: 100%;
                }

                table,
                td {
                    mso-table-lspace: 0pt;
                    mso-table-rspace: 0pt;
                    border-collapse: collapse !important;
                    border-spacing: 0;
                }

                img {
                    border: 0;
                    line-height: 100%;
                    outline: none;
                    text-decoration: none;
                    -ms-interpolation-mode: bicubic;
                }

                #outlook a {
                    padding: 0;
                }

                .ReadMsgBody {
                    width: 100%;
                }

                .ExternalClass {
                    width: 100%;
                }

                .ExternalClass,
                .ExternalClass p,
                .ExternalClass span,
                .ExternalClass font,
                .ExternalClass td,
                .ExternalClass div {
                    line-height: 100%;
                }

                /* Rounded corners for advanced mail clients only */
                @media all and (min-width: 560px) {
                    .container {
                        border-radius: 8px;
                        -webkit-border-radius: 8px;
                        -moz-border-radius: 8px;
                        -khtml-border-radius: 8px;
                    }
                }

                /* Set color for auto links (addresses, dates, etc.) */
                a,
                a:hover {
                    color: #127DB3;
                }

                .footer a,
                .footer a:hover {
                    color: #999999;
                }

                /* datainvoice */
                .card {
                    position: relative !important;
                    display: -ms-flexbox !important;
                    display: flex !important;
                    -ms-flex-direction: column !important;
                    flex-direction: column !important;
                    min-width: 0 !important;
                    word-wrap: break-word !important;
                    background-color: #fff !important;
                    background-clip: border-box !important;
                    border: 1px solid rgba(0, 0, 0, .125) !important;
                    border-radius: .25rem !important;
                }

                .card-body {
                    -ms-flex: 1 1 auto !important;
                    flex: 1 1 auto !important;
                    padding: 1.25rem !important;
                }

                .list-group-item.active {
                    z-index: 2 !important;
                    color: #fff !important;
                    background-color: #007bff !important;
                    border-color: #007bff !important;
                }

                .list-group-item:first-child {
                    border-top-left-radius: .25rem !important;
                    border-top-right-radius: .25rem !important;
                }

                .stext-107 {
                    font-family: Poppins-Regular !important;
                    font-size: 13px !important;
                    line-height: 1.466667 !important;
                }

                ul,
                li {
                    margin: 0 !important;
                    margin-bottom: 0px !important;
                    list-style-type: none !important;
                }

                .list-group-item {
                    position: relative !important;
                    display: block !important;
                    padding: .75rem 1.25rem !important;
                    margin-bottom: -1px !important;
                    background-color: #fff !important;
                    border: 1px solid rgba(0, 0, 0, .125) !important;
                    border-top-color: rgba(0, 0, 0, 0.125) !important;
                    border-right-color: rgba(0, 0, 0, 0.125) !important;
                    border-bottom-color: rgba(0, 0, 0, 0.125) !important;
                    border-left-color: rgba(0, 0, 0, 0.125) !important;
                }

                .text-right {
                    text-align: right !important;
                }

                .img-produk-cart img {
                    width: 50px !important;
                }

                img {
                    vertical-align: middle !important;
                    border-style: none !important;
                }

                .table {
                    width: 100% !important;
                    margin-bottom: 1rem !important;
                    background-color: transparent !important;
                }

                table {
                    border-collapse: collapse !important;
                }

                .table-responsive {
                    display: block !important;
                    width: 100% !important;
                    overflow-x: auto !important;
                    -webkit-overflow-scrolling: touch !important;
                    -ms-overflow-style: -ms-autohiding-scrollbar !important;
                }

                .row {
                    display: -ms-flexbox !important;
                    display: flex !important;
                    -ms-flex-wrap: wrap !important;
                    flex-wrap: wrap !important;
                    margin-right: -15px !important;
                    margin-left: -15px !important;
                }

                .table td,
                .table th {
                    padding: .75rem !important;
                    vertical-align: top !important;
                    border-top: 1px solid #dee2e6 !important;
                }

                th {
                    text-align: inherit !important;
                }

                .cl2 {
                    color: #333 !important;
                }

                a {
                    color: #007bff !important;
                    text-decoration: none !important;
                    background-color: transparent !important;
                }

                .text1l {
                    overflow: hidden !important;
                    text-overflow: ellipsis !important;
                    white-space: nowrap !important;
                    display: block !important;
                    width: 100% !important;
                    min-width: 1px !important;
                }

                .m-b-5,
                .m-tb-5,
                .m-all-5 {
                    margin-bottom: 5px !important;
                }

                h1,
                h2,
                h3,
                h4,
                h5,
                h6,
                p {
                    margin: 0 !important;
                    margin-bottom: 0px !important;
                }

                .h6,
                h6 {
                    font-size: 1rem !important;
                }

                .h1,
                .h2,
                .h3,
                .h4,
                .h5,
                .h6,
                h1,
                h2,
                h3,
                h4,
                h5,
                h6 {
                    margin-bottom: .5rem !important;
                    font-family: inherit !important;
                    font-weight: 500 !important;
                    line-height: 1.2 !important;
                    color: inherit !important;
                }

                .bs1 {
                    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, .24) !important;
                }

                .bg0 {
                    background-color: #fff !important;
                }

                .list-group {
                    display: -ms-flexbox !important;
                    -ms-flex-direction: column !important;
                    flex-direction: column !important;
                    padding-left: 0 !important;
                    margin-bottom: 0 !important;
                }

                .list-group-flush .list-group-item {
                    border-right: 0 !important;
                    border-left: 0 !important;
                    border-radius: 0 !important;
                }

                .list-group-item:first-child {
                    border-top-left-radius: .25rem !important;
                    border-top-right-radius: .25rem !important;
                }

                .align-items-center {
                    -ms-flex-align: center !important;
                    align-items: center !important;
                }

                .justify-content-between {
                    -ms-flex-pack: justify !important;
                    justify-content: space-between !important;
                }

                .d-flex {
                    display: -ms-flexbox !important;
                    display: flex !important;
                }
            </style>

            <!-- MESSAGE SUBJECT -->
            <title>Get this responsive email template</title>

        </head>

        <!-- BODY -->
        <!-- Set message background color (twice) and text color (twice) -->

        <body topmargin="0" rightmargin="0" bottommargin="0" leftmargin="0" marginwidth="0" marginheight="0" width="100%" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%; height: 100%; -webkit-font-smoothing: antialiased; text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; line-height: 100%;
																										background-color: #F0F0F0;
																										color: #000000;" bgcolor="#F0F0F0" text="#000000">

            <!-- SECTION / BACKGROUND -->
            <!-- Set message background color one again -->
            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%;" class="background">
                <tr>
                    <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0;" bgcolor="#F0F0F0">



                        <!-- WRAPPER / CONTEINER -->
                        <!-- Set conteiner background color -->
                        <table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#FFFFFF" width="700" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
																											max-width: inherit;" class="container">

                            <!-- HEADER -->
                            <!-- Set text color and font family ("sans-serif" or "Georgia, serif") -->
                            <tr>
                                <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 24px; font-weight: bold; line-height: 130%;
																												padding-top: 25px;padding-bottom: 25px;
																												color: #000000;
																												font-family: sans-serif;" class="header">
                                    <?php echo $param['judul']; ?>
                                </td>
                            </tr>

                            <!-- SUBHEADER -->
                            <!-- Set text color and font family ("sans-serif" or "Georgia, serif") -->


                            <!-- HERO IMAGE -->
                            <!-- Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2 (wrapper x2). Do not set height for flexible images (including "auto"). URL format: http://domain.com/?utm_source={{Campaign-Source}}&utm_medium=email&utm_content={{Ìmage-Name}}&utm_campaign={{Campaign-Name}} -->
                            <tr>
                                <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0;
																											padding-top: 20px;padding-bottom:20px;background-color: #0E336D" class="hero"><a target="_blank" style="text-decoration: none;" href="https://siplah.eurekabookhouse.co.id/"><img border="0" vspace="0" hspace="0" src="<?php echo $param['logo']; ?>" alt="Please enable images to view this content" title="Hero Image" width="560" style="
																											width: 100%;
																											max-width: 300px;
																											color: #000000; font-size: 13px; margin: 0; padding: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;" /></a></td>
                            </tr>

                            <!-- PARAGRAPH -->
                            <!-- Set text color and font family ("sans-serif" or "Georgia, serif"). Duplicate all text styles in links, including line-height -->
                            <tr>
                                <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
																											padding-top: 25px; 
																											color: #000000;
																											font-family: sans-serif;" class="paragraph">

                                    <table style="width:100%;" width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tbody>
                                            <?php if (isset($param['user'])) { ?> <tr>

                                                    <td valign="top">
                                                        <p style="margin:0;padding:0;font-size:12px;color:#202020;font-family:Helvetica, Arial, sans-serif;line-height:24px;font-weight:normal;">Dear, <?php echo $param['user']; ?>,
                                                        </p>
                                                    </td>

                                                </tr>

                                            <?php } ?>

                                        </tbody>
                                    </table>
                                    <?php echo $param['content']; ?>



                                </td>
                            </tr>

                            <!-- LINE -->
                            <!-- Set line color -->
                            <tr>
                                <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
																									padding-top: 25px;" class="line">
                                    <hr color="#E0E0E0" align="center" width="100%" size="1" noshade style="margin: 0; padding: 0;" />
                                </td>
                            </tr>

                            <!-- PARAGRAPH -->
                            <!-- Set text color and font family ("sans-serif" or "Georgia, serif"). Duplicate all text styles in links, including line-height -->
                            <tr>
                                <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
																								padding-top: 20px;
																								padding-bottom: 25px;
																								color: #000000;
																								font-family: sans-serif;font-size: 12px;" class="paragraph">
                                    <p>Jl. H. Baping No.100, Ciracas, </p>
                                    <p>Jakarta Timur, DKI Jakarta 13740</p>
                                    <p>087888337555</p>

                                </td>
                            </tr>

                            <!-- End of WRAPPER -->
                        </table>

                        <!-- WRAPPER -->
                        <!-- Set wrapper width (twice) -->
                        <table border="0" cellpadding="0" cellspacing="0" align="center" width="560" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
																					max-width: 560px;" class="wrapper">

                            <!-- SOCIAL NETWORKS -->
                            <!-- Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2 -->
                            <tr>
                                <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
																						padding-top: 25px;" class="social-icons">
                                    <table width="256" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse; border-spacing: 0; padding: 0;">
                                        <tr>

                                            <!-- ICON 1 -->
                                            <td align="center" valign="middle" style="margin: 0; padding: 0; padding-left: 10px; padding-right: 10px; border-collapse: collapse; border-spacing: 0;"><a target="_blank" href="https://www.facebook.com/ebhcom" style="text-decoration: none;"><img border="0" vspace="0" hspace="0" style="padding: 0; margin: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: inline-block;
																								color: #000000;" alt="F" title="Facebook" width="44" height="44" src="https://raw.githubusercontent.com/konsav/email-templates/master/images/social-icons/facebook.png"></a></td>

                                            <!-- ICON 2 -->
                                            <td align="center" valign="middle" style="margin: 0; padding: 0; padding-left: 10px; padding-right: 10px; border-collapse: collapse; border-spacing: 0;"><a target="_blank" href="https://twitter.com/eurekabookhouse" style="text-decoration: none;"><img border="0" vspace="0" hspace="0" style="padding: 0; margin: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: inline-block;
																									color: #000000;" alt="T" title="Twitter" width="44" height="44" src="https://raw.githubusercontent.com/konsav/email-templates/master/images/social-icons/twitter.png"></a></td>



                                            <!-- ICON 4 -->
                                            <td align="center" valign="middle" style="margin: 0; padding: 0; padding-left: 10px; padding-right: 10px; border-collapse: collapse; border-spacing: 0;"><a target="_blank" href="https://www.instagram.com/eurekabookhouse/" style="text-decoration: none;"><img border="0" vspace="0" hspace="0" style="padding: 0; margin: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: inline-block;
																										color: #000000;" alt="I" title="Instagram" width="44" height="44" src="https://raw.githubusercontent.com/konsav/email-templates/master/images/social-icons/instagram.png"></a></td>

                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- FOOTER -->
                            <!-- Set text color and font family ("sans-serif" or "Georgia, serif"). Duplicate all text styles in links, including line-height -->


                            <!-- End of WRAPPER -->
                        </table>

                        <!-- End of SECTION / BACKGROUND -->
                    </td>
                </tr>
            </table>

        </body>

        </html>

<?php
        return ob_get_clean();
    }
}
