<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <small class="text-muted font-14">
                        <b>Master</b>
                    </small>
                    <h3 style="margin-top: 0px;">
                        Buku
                    </h3>
                </div>
                <!--end col-->
                <div class="col-auto align-self-top">
                    <a href="<?= base_url() ?>admin/book/add" class="btn btn-info waves-effect waves-light"><i class="fas fa-plus mr-2"></i>Tambah Buku</a>
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
<div class="row">
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
                                <th style="white-space: nowrap;">Stok</th>
                                <th style="white-space: nowrap;">Total Pinjam</th>
                                <th style="white-space: nowrap;">Total Booking</th>
                                <th style="white-space: nowrap;">Opsi</th>
                            </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>