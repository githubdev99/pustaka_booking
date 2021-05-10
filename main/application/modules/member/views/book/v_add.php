<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h3 style="margin-top: 0px;">
                        Tambah Buku
                    </h3>
                </div>
                <!--end col-->
                <div class="col-auto align-self-top">
                    <a href="<?= base_url() ?>admin/book" class="btn btn-secondary waves-effect waves-light"><i class="fas fa-arrow-left mr-2"></i>Kembali</a>
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
        <form method="post" enctype="multipart/form-data" name="add">
            <div class="card card-default">
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">ISBN <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            <input class="form-control edit" type="text" name="isbn" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Judul <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input class="form-control edit" type="text" name="name" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Kategori <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <select class="select2 form-control mb-3 custom-select" name="category_id" style="width: 100%; height:36px;" required>
                                <option value=""></option>
                                <?php foreach ($core['category'] as $key_category) : ?>
                                    <option value="<?= encrypt_text($key_category->id) ?>"><?= $key_category->name ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Penulis <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input class="form-control edit" type="text" name="author" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Penerbit <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input class="form-control edit" type="text" name="publisher" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Tahun Terbit <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input class="form-control edit" type="text" onkeypress="number_only(event)" name="publication_year" maxlength="4" required>
                            <small class="form-text text-muted">Hanya berisi angka (0-9)</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Stok Tersedia <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input class="form-control edit" type="text" onkeypress="number_only(event)" name="stock" required>
                            <small class="form-text text-muted">Hanya berisi angka (0-9)</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Gambar <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <div class="custom-file mb-3">
                                <input type="file" class="custom-file-input" name="image" id="gambar" style="cursor: pointer;" required>
                                <label class="custom-file-label" id="nama_gambar">Choose file</label>
                            </div>
                            <img class="img-thumbnail" id="preview_gambar" alt="Gambar Buku..." width="200" src="<?= base_url() ?>assets/images/img-thumbnail.png" data-holder-rendered="true">
                            <button type="button" id="remove_preview" class="btn btn-danger waves-effect waves-light mt-2" style="display: none;"><i class="far fa-trash-alt mr-2"></i>Remove Image</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-right mb-5">
                <a href="<?= base_url() ?>admin/book" class="btn btn-danger btn-lg mr-2 waves-effect waves-light">Batal</a>
                <button type="submit" name="add" class="btn btn-info btn-lg waves-effect waves-light">Tambah</button>
            </div>
        </form>
    </div>
</div>