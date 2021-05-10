<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h3 style="margin-top: 0px;">
                        <?php
                        $parsing['loaning'] = $this->api_model->select_data([
                            'field' => 'loaning.*, user.name',
                            'table' => 'loaning',
                            'join' => [
                                [
                                    'table' => 'user',
                                    'on' => 'user.id = loaning.user_id',
                                    'type' => 'inner'
                                ],
                            ],
                            'where' => [
                                'loaning.id' => decrypt_text($this->uri->segment('4')),
                            ],
                        ])->row();
                        ?>
                        Detail Peminjaman : <?= $parsing['loaning']->loaning_number ?>
                    </h3>
                </div>
                <!--end col-->
                <div class="col-auto align-self-top">
                    <a href="<?= base_url() ?>admin/returning" class="btn btn-secondary waves-effect waves-light"><i class="fas fa-arrow-left mr-2"></i>Kembali</a>
                </div>
                <!--end col-->
            </div>
            <!--end row-->
        </div>
        <!--end page-title-box-->
    </div>
    <!--end col-->
</div>
<!--end row-->
<!-- end page title end breadcrumb -->
<form method="post" enctype="multipart/form-data" name="process">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Nama Member</label>
                        <div class="col-sm-6">
                            <input class="form-control process" type="text" value="<?= $parsing['loaning']->name ?>" disabled readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Lama Pinjam</label>
                        <div class="col-sm-6">
                            <input class="form-control process" type="text" name="loaning_time" onkeypress="number_only(event)" value="<?= $data['loaning_time'] ?>" disabled readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Denda/Hari</label>
                        <div class="col-sm-6">
                            <input class="form-control process" type="text" name="penalty_price" onkeypress="number_only(event)" onkeyup="running_rupiah('penalty_price', this.value)" value="<?= number_format($data['penalty_price'], 0, '', '.') ?>" disabled readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Tanggal Jatuh Tempo</label>
                        <div class="col-sm-6">
                            <input class="form-control process" type="text" value="<?= $data['return_due_date'] ?>" disabled readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Tanggal Pengembalian</label>
                        <div class="col-sm-6">
                            <input class="form-control process" type="text" value="<?= date('Y-m-d') ?>" disabled readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Status Pengembalian</label>
                        <div class="col-sm-6">
                            <?php
                            $diff = date_diff(date_create($data['return_due_date']), date_create(date('Y-m-d')));
                            ?>
                            <?php if ($diff->format("%R%a") > 0) : ?>
                                <span class="badge badge-danger mt-2">Terlambat <?= $diff->format("%R%a") ?> hari</span>
                            <?php else : ?>
                                <span class="badge badge-success mt-2">Tepat Waktu</span>
                            <?php endif ?>
                            <input type="hidden" name="penalty_day" value="<?= ($diff->format("%R%a") > 0) ? $diff->format("%R%a") : 0; ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tableDefault" class="table table-bordered table-hover dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr class="table-info">
                                    <th style="white-space: nowrap;">No.</th>
                                    <th style="white-space: nowrap;">Gambar</th>
                                    <th style="white-space: nowrap;">Judul</th>
                                    <th style="white-space: nowrap;">Kategori</th>
                                    <th style="white-space: nowrap;">ISBN</th>
                                    <th style="white-space: nowrap;">Pengarang</th>
                                    <th style="white-space: nowrap;">Penerbit</th>
                                    <th style="white-space: nowrap;">Tahun Terbit</th>
                                </tr>
                            </thead>

                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="text-right mb-5">
                <input type="hidden" name="id" value="<?= $this->uri->segment('4') ?>">
                <a href="<?= base_url() ?>admin/returning" class="btn btn-danger btn-lg mr-2 waves-effect waves-light">Batal</a>
                <button type="submit" name="process" class="btn btn-success btn-lg waves-effect waves-light">Proses Pengembalian</button>
            </div>
        </div>
    </div>
</form>