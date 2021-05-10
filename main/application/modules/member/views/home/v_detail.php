<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h3>Detail Buku</h3>
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
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 align-self-center">
                        <img src="<?= $data['image'] ?>" alt="" class=" mx-auto  d-block" height="300">
                    </div>
                    <!--end col-->
                    <div class="col-lg-6 align-self-center">
                        <div class="single-pro-detail">
                            <h3 class="pro-title"><?= $data['name'] ?></h3>
                            <p class="text-muted mb-0">Kategori Buku : <?= $data['categoryName'] ?></p>
                            <h6 class="text-muted font-13">Detail :</h6>
                            <ul class="list-unstyled pro-features border-0">
                                <li>ISBN : <?= $data['isbn'] ?></li>
                                <li>Pengarang : <?= $data['author'] ?></li>
                                <li>Penerbit : <?= $data['publisher'] ?> (<?= $data['publicationYear'] ?>)</li>
                                <li>Stok Tersedia : <?= $data['stock'] ?></li>
                            </ul>
                            <div class="quantity mt-3">
                                <a href="#" class="btn btn-info btn-sm text-white px-4 d-inline-block"><i class="mdi mdi-bookmark-plus mr-2"></i>Booking</a>
                                <a href="<?= base_url() ?>member/home" class="btn btn-secondary btn-sm text-white px-4 d-inline-block"><i class="mdi mdi-arrow-left mr-2"></i>Kembali</a>
                            </div>
                        </div>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <!--end card-body-->
        </div>
        <!--end card-->
    </div>
    <!--end col-->
</div>
<!--end row-->