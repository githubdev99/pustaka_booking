<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8" />
	<title><?= $core['title_page'] ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />

	<!-- App favicon -->
	<link rel="shortcut icon" href="<?= $core['logo_mini'] ?>">

	<!-- Plugin CSS -->
	<link href="<?= base_url() ?>assets/plugins/sweet-alert2/sweetalert2.min.css" rel="stylesheet" type="text/css">
	<link href="<?= base_url() ?>assets/plugins/animate/animate.css" rel="stylesheet" type="text/css">
	<link href="<?= base_url() ?>assets/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= base_url() ?>assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
	<link href="<?= base_url() ?>assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= base_url() ?>assets/plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

	<!-- Main CSS -->
	<link href="<?= base_url() ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= base_url() ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= base_url() ?>assets/css/metisMenu.min.css" rel="stylesheet" type="text/css" />
	<link href="<?= base_url() ?>assets/css/app.min.css" rel="stylesheet" type="text/css" />

	<!-- Custom CSS -->
	<link href="<?= base_url() ?>assets/custom/custom.css" rel="stylesheet" type="text/css" />
	<style></style>
</head>

<body class="<?= ($this->uri->segment(1) != 'auth' && $this->uri->segment(1) != '') ? 'dark-sidenav' : 'account-body accountbg' ?>">