<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row">
                <div class="col">
                    <h3>Dashboard</h3>
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
    <div class="col-3">
        <div class="card report-card">
            <div class="card-body">
                <div class="d-flex bd-highlight">
                    <div class="flex-shrink-1 bd-highlight align-self-center">
                        <i data-feather="users" class="align-self-center icon-lg text-danger">
                        </i>
                    </div>
                    <div class="flex-fill bd-highlight ml-2">
                        <p class="m-0 font-18 font-weight-bold"><?= $core['totalMember'] ?></p>
                        <p class="text-dark mb-0 font-weight-semibold">Jumlah Anggota</p>
                    </div>
                </div>
            </div>
            <!--end card-body-->
        </div>
        <!--end card-->
    </div>
    <!--end col-->
    <div class="col-3">
        <div class="card report-card">
            <div class="card-body">
                <div class="d-flex bd-highlight">
                    <div class="flex-shrink-1 bd-highlight align-self-center">
                        <i data-feather="book" class="align-self-center icon-lg text-warning">
                        </i>
                    </div>
                    <div class="flex-fill bd-highlight ml-2">
                        <p class="m-0 font-18 font-weight-bold"><?= $core['totalBook'] ?></p>
                        <p class="text-dark mb-0 font-weight-semibold">Stok Buku Terdaftar</p>
                    </div>
                </div>
            </div>
            <!--end card-body-->
        </div>
        <!--end card-->
    </div>
    <!--end col-->
    <div class="col-3">
        <div class="card report-card">
            <div class="card-body">
                <div class="d-flex bd-highlight">
                    <div class="flex-shrink-1 bd-highlight align-self-center">
                        <i data-feather="user-check" class="align-self-center icon-lg text-success">
                        </i>
                    </div>
                    <div class="flex-fill bd-highlight ml-2">
                        <p class="m-0 font-18 font-weight-bold"><?= $core['totalLoaning'] ?></p>
                        <p class="text-dark mb-0 font-weight-semibold">Buku Dipinjam</p>
                    </div>
                </div>
            </div>
            <!--end card-body-->
        </div>
        <!--end card-->
    </div>
    <!--end col-->
    <div class="col-3">
        <div class="card report-card">
            <div class="card-body">
                <div class="d-flex bd-highlight">
                    <div class="flex-shrink-1 bd-highlight align-self-center">
                        <i data-feather="bookmark" class="align-self-center icon-lg text-info">
                        </i>
                    </div>
                    <div class="flex-fill bd-highlight ml-2">
                        <p class="m-0 font-18 font-weight-bold"><?= $core['totalBooking'] ?></p>
                        <p class="text-dark mb-0 font-weight-semibold">Buku Dibooking</p>
                    </div>
                </div>
            </div>
            <!--end card-body-->
        </div>
        <!--end card-->
    </div>
    <!--end col-->
</div>
<!--end row-->
<div class="row">
    <div class="col-lg-12">
        <div class="card card-default">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>List Data Member</h3>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tableDefault" class="table table-bordered table-hover dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr class="table-info">
                                <th>No.</th>
                                <th>Email</th>
                                <th>Nama</th>
                                <th>Bergabung Sejak</th>
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