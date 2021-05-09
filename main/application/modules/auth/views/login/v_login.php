<!-- Log In page -->
<div class="container">
    <div class="row vh-100 d-flex justify-content-center">
        <div class="col-12 align-self-center">
            <div class="row">
                <div class="col-lg-5 mx-auto">
                    <div class="card">
                        <div class="card-body p-0 auth-header-box">
                            <div class="text-center p-3">
                                <a href="index-2.html" class="logo logo-admin">
                                    <img src="<?= $core['logo_mini'] ?>" height="50" alt="logo" class="auth-logo">
                                </a>
                                <h4 class="mt-3 mb-1 font-weight-semibold text-white font-18"><?= $core['app_name'] ?></h4>
                                <p class="text-muted  mb-0">Log in untuk melanjutkan ke <?= $core['app_name'] ?>.</p>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <form method="post" enctype="multipart/form-data" name="login">
                                <div class="form-group mb-2">
                                    <label for="email">Email</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control login" name="email" id="email">
                                    </div>
                                </div>
                                <!--end form-group-->

                                <div class="form-group mb-2">
                                    <label for="password">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control login" name="password" id="password">
                                    </div>
                                </div>
                                <!--end form-group-->

                                <div class="form-group mt-4 row">
                                    <div class="col-12">
                                        <button class="btn btn-primary btn-block waves-effect waves-light" type="submit" name="login">Log in</button>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end form-group-->
                            </form>
                            <!--end form-->
                            <div class="m-3 text-center text-muted">
                                <p class="mb-0">
                                    Anda belum memiliki akun? <a href="<?= base_url() ?>auth/register" class="text-primary">Buat akun disini</a>
                                </p>
                            </div>
                        </div>
                        <!--end card-body-->
                        <div class="card-body bg-light-alt text-center">
                            <span class="text-muted d-none d-sm-inline-block">
                                &copy; <?= date('Y') ?> <?= $core['app_name'] ?> All Rights Reserved.
                            </span>
                        </div>
                    </div>
                    <!--end card-->
                </div>
                <!--end col-->
            </div>
            <!--end row-->
        </div>
        <!--end col-->
    </div>
    <!--end row-->
</div>
<!--end container-->
<!-- End Log In page -->