<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h3>Daftar Buku di Pustaka Booking</h3>
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
    <?php foreach ($data as $key_data) : ?>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <img src="<?= $key_data['image'] ?>" alt="" class="d-block mx-auto my-4" height="150">
                    <div class="row my-4">
                        <div class="col">
                            <span class="badge badge-light mb-2"><?= $key_data['categoryName'] ?></span>
                            <a href="<?= base_url() ?>member/home/detail/<?= $key_data['id'] ?>" class="title-text d-block"><?= $key_data['name'] ?></a>
                            <?= $key_data['publisher'] ?>
                        </div>
                        <div class="col-auto">
                            <small class="text-muted"><?= $key_data['publicationYear'] ?></small>
                        </div>
                    </div>
                    <button class="btn btn-soft-info btn-block">Booking</button>
                    <a href="<?= base_url() ?>member/home/detail/<?= $key_data['id'] ?>" class="btn btn-soft-secondary btn-block">Detail</a>
                </div>
                <!--end card-body-->
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    <?php endforeach ?>
</div>
<!--end row-->