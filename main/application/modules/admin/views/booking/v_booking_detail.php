<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h3 style="margin-top: 0px;">
                        <?php
                        $parsing['booking'] = $this->api_model->select_data([
                            'field' => 'booking.*, user.name',
                            'table' => 'booking',
                            'join' => [
                                [
                                    'table' => 'user',
                                    'on' => 'user.id = booking.user_id',
                                    'type' => 'inner'
                                ],
                            ],
                            'where' => [
                                'booking.id' => decrypt_text($this->uri->segment('4')),
                            ],
                        ])->row();
                        ?>
                        Detail Booking : <?= $parsing['booking']->booking_number ?>
                    </h3>
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
                            <input class="form-control process" type="text" value="<?= $parsing['booking']->name ?>" disabled readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Lama Pinjam <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            <input class="form-control process" type="text" name="loaning_time" onkeypress="number_only(event)" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Denda/Hari <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            <input class="form-control process" type="text" name="penalty_price" onkeypress="number_only(event)" required>
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
                <input type="hidden" name="booking_id" value="<?= $this->uri->segment('4') ?>">
                <a href="<?= base_url() ?>admin/book" class="btn btn-danger btn-lg mr-2 waves-effect waves-light">Batal</a>
                <button type="submit" name="process" class="btn btn-success btn-lg waves-effect waves-light">Proses Peminjaman</button>
            </div>
        </div>
    </div>
</form>