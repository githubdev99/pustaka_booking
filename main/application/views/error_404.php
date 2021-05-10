<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8" />
	<title><?= $core['title_page'] ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />

	<!-- App favicon -->
	<link rel="shortcut icon" href="<?= $core['logo_mini'] ?>">

	<!-- Main CSS -->
	<link href="<?= base_url() ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= base_url() ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= base_url() ?>assets/css/app.min.css" rel="stylesheet" type="text/css" />
</head>

<body class="account-body accountbg">
	<div class="container">
		<div class="row vh-100 d-flex justify-content-center">
			<div class="col-12 align-self-center">
				<div class="row">
					<div class="col-lg-5 mx-auto">
						<div class="card">
							<div class="card-body p-0 auth-header-box">
								<div class="text-center p-3">
									<a href="<?= base_url() ?>" class="logo logo-admin">
										<img src="<?= $core['logo_full'] ?>" height="50" alt="logo" class="app-logo">
									</a>
									<h4 class="mt-3 mb-1 font-weight-semibold text-white font-18">Oops! Sorry page does not found</h4>
									<p class="text-muted  mb-0">Back to home of <?= $core['app_name'] ?>.</p>
								</div>
							</div>
							<div class="card-body">
								<div class="ex-page-content text-center">
									<img src="<?= base_url() ?>assets/images/error.svg" alt="0" class="" height="170">
									<h1 class="mt-5 mb-4">404!</h1>
									<h5 class="font-16 text-muted mb-5">Something went wrong</h5>
								</div>
								<a class="btn btn-primary btn-block waves-effect waves-light" href="<?= base_url() ?>">Back to Home <i class="fas fa-redo ml-1"></i></a>
							</div>
							<div class="card-body bg-light-alt text-center">
								<span class="text-muted d-none d-sm-inline-block"><?= $core['app_name'] ?> &copy; <?= date('Y') ?></span>
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

	<!-- Main JS -->
	<script src="<?= base_url() ?>assets/js/jquery.min.js"></script>
	<script src="<?= base_url() ?>assets/js/bootstrap.bundle.min.js"></script>
	<script src="<?= base_url() ?>assets/js/waves.js"></script>
	<script src="<?= base_url() ?>assets/js/feather.min.js"></script>
	<script src="<?= base_url() ?>assets/js/simplebar.min.js"></script>
</body>

</html>