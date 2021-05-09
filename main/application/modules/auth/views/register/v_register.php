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
                                <p class="text-muted  mb-0">Buat akun jika belum memiliki akun di <?= $core['app_name'] ?>.</p>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <form method="post" enctype="multipart/form-data" name="register">
                                <div class="form-group mb-2">
                                    <label for="name">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="name" id="name" required>
                                </div>
                                <!--end form-group-->

                                <div class="form-group mb-2">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" name="email" id="email" required>
                                </div>
                                <!--end form-group-->

                                <div class="form-group mb-2">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" name="password" id="password" required>
                                </div>
                                <!--end form-group-->

                                <div class="form-group mb-2">
                                    <label for="confirm_password">Konfirmasi Password</label>
                                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                                    <small class="form-text text-danger" id="errorConfirmPassword"></small>
                                </div>
                                <!--end form-group-->

                                <div class="g-recaptcha" data-sitekey="6LdGWnYaAAAAAFRdgCfX6a7fmg59lIeu0wSDRWtP"></div>

                                <div class="form-group mt-4 row">
                                    <div class="col-12">
                                        <button class="btn btn-primary btn-block waves-effect waves-light" type="submit" name="register">Register</button>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end form-group-->
                            </form>
                            <!--end form-->
                            <div class="m-3 text-center text-muted">
                                <p class="mb-0">
                                    Sudah memiliki akun? <a href="<?= base_url() ?>auth/login" class="text-primary">Login disini</a>
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