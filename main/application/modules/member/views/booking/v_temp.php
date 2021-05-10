<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h3 style="margin-top: 0px;">
                        Booking Buku
                    </h3>
                </div>
                <!--end col-->
                <div class="col-auto align-self-top">
                    <a href="<?= base_url() ?>member/home" class="btn btn-info waves-effect waves-light"><i class="fas fa-plus mr-2"></i>Lanjutkan Booking</a>

                    <button type="button" id="endBooking" onclick="show_modal({ modal: 'end_booking' })" class="btn btn-success waves-effect waves-light"><i class="fas fa-bookmark mr-2"></i>Selesaikan Booking</button>
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
                                <th style="white-space: nowrap;">Penerbit</th>
                                <th style="white-space: nowrap;">Tahun Terbit</th>
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