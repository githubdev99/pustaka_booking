<!DOCTYPE html>
<html>

<head>
    <!-- Default Meta -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?= $core['title_page'] ?></title>
    <!-- Icon Page -->
    <link rel="shortcut icon" href="<?= $core['logo_mini'] ?>">
    <style>
        .table-data {
            width: 100%;
            border-collapse: collapse;
        }

        .table-data tr th,
        .table-data tr td {
            border: 1px solid black;
            font-size: 10pt;
        }
    </style>
</head>

<body>
    <table style="width: 100%;">
        <tbody>
            <tr>
                <td align="left">
                    <img src="<?= $core['logo_mini'] ?>" alt="<?= $core['app_name'] ?>" style="width: 75px; height: 65px;">
                </td>
                <td align="right">
                    Di Print Pada Tanggal : <?= date("d-m-Y") ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                    <hr>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center" style="font-size: 26px;">
                    <u>Data Seluruh Buku</u>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    &emsp;
                </td>
            </tr>
        </tbody>
    </table>
    <table class="table-data" border="1">
        <thead>
            <tr>
                <th>NO.</th>
                <th>GAMBAR</th>
                <th>JUDUL</th>
                <th>KATEGORI</th>
                <th>ISBN</th>
                <th>PENGARANG</th>
                <th>PENERBIT</th>
                <th>TAHUN TERBIT</th>
                <th>STOK</th>
            </tr>
        </thead>
        <?php if (empty($data)) : ?>
            <tbody>
                <tr>
                    <td colspan="9">Data tidak ada...</td>
                </tr>
            </tbody>
        <?php else : ?>
            <tbody>
                <?php $no = 0; ?>
                <?php foreach ($data as $key_data) : ?>
                    <?php
                    $no++;
                    $image = (!empty($key_data->image)) ? "{$core['imageUpload']}books/{$key_data->image}" : $core['imageNotFound']
                    ?>
                    <tr>
                        <td><?= $no; ?></td>
                        <td align="center">
                            <img src="<?= $image ?>" style="width: 120px; height: 140px;">
                        </td>
                        <td><?= $key_data->name; ?></td>
                        <td><?= $key_data->category_name; ?></td>
                        <td><?= $key_data->isbn; ?></td>
                        <td><?= $key_data->author; ?></td>
                        <td><?= $key_data->publisher; ?></td>
                        <td><?= $key_data->publication_year; ?></td>
                        <td><?= $key_data->stock; ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        <?php endif ?>
    </table>
    <script>
        window.onload = function() {
            window.print();
            setTimeout(function() {
                // window.close();
            }, 1);
        };
    </script>
</body>

</html>