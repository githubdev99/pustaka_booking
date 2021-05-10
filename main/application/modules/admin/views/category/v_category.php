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
                        Kategori Buku
                    </h3>
                </div>
                <!--end col-->
                <div class="col-auto align-self-top">
                    <button type="button" class="btn btn-info waves-effect waves-light" data-toggle="modal" data-target="#add"><i class="fas fa-plus mr-2"></i>Tambah Kategori Buku</button>
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
                                <th>No.</th>
                                <th>Nama Kategori</th>
                                <th>Opsi</th>
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

<div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="add1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data" name="add">
                <div class="modal-header bg-info">
                    <h6 class="modal-title m-0 text-white" id="add1">Tambah Kategori Buku</h6>
                    <button type="button" class="close " data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="la la-times text-white"></i></span>
                    </button>
                </div>
                <!--end modal-header-->
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-left">Nama <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input class="form-control add" type="text" name="name" required>
                        </div>
                    </div>
                </div>
                <!--end modal-body-->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" name="add" class="btn btn-info">Tambah</button>
                </div>
                <!--end modal-footer-->
            </form>
        </div>
        <!--end modal-content-->
    </div>
    <!--end modal-dialog-->
</div>
<!--end modal-->

<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="edit1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data" name="edit">
                <div class="modal-header bg-success">
                    <h6 class="modal-title m-0 text-white" id="edit1">Edit Kategori Buku</h6>
                    <button type="button" class="close " data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="la la-times text-white"></i></span>
                    </button>
                </div>
                <!--end modal-header-->
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-left">Nama <span class="text-danger">*</span></label>
                        <div class="col-sm-10">
                            <input class="form-control edit" type="text" name="name" required>
                        </div>
                    </div>
                </div>
                <!--end modal-body-->
                <div class="modal-footer">
                    <input type="hidden" name="id">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" name="edit" class="btn btn-success">Edit</button>
                </div>
                <!--end modal-footer-->
            </form>
        </div>
        <!--end modal-content-->
    </div>
    <!--end modal-dialog-->
</div>
<!--end modal-->