	<!-- Main JS  -->
	<script src="<?= base_url() ?>assets/js/jquery.min.js"></script>
	<script src="<?= base_url() ?>assets/js/bootstrap.bundle.min.js"></script>
	<script src="<?= base_url() ?>assets/js/metismenu.min.js"></script>
	<script src="<?= base_url() ?>assets/js/waves.js"></script>
	<script src="<?= base_url() ?>assets/js/feather.min.js"></script>
	<script src="<?= base_url() ?>assets/js/simplebar.min.js"></script>
	<script src="<?= base_url() ?>assets/js/moment.js"></script>

	<!-- Plugin JS -->
	<?php if ($this->uri->segment(1) == 'dashboard' || $this->uri->segment(1) == '') : ?>
		<script src="<?= base_url() ?>assets/pages/jquery.analytics_dashboard.init.js"></script>
		<script src="<?= base_url() ?>assets/pages/jquery.analytics_dashboard.init.js"></script>
		<script src="<?= base_url() ?>assets/plugins/apex-charts/apexcharts.min.js"></script>
		<script src="<?= base_url() ?>assets/plugins/apex-charts/irregular-data-series.js"></script>
		<script src="<?= base_url() ?>assets/plugins/apex-charts/ohlc.js"></script>
		<script src="<?= base_url() ?>assets/pages/jquery.apexcharts.init.js"></script>
	<?php endif ?>
	<script src="<?= base_url() ?>assets/plugins/sweet-alert2/sweetalert2.min.js"></script>
	<script src="<?= base_url() ?>assets/plugins/select2/select2.min.js"></script>
	<script src="<?= base_url() ?>assets/plugins/daterangepicker/daterangepicker.js"></script>
	<script src="<?= base_url() ?>assets/plugins/datatables/jquery.dataTables.min.js"></script>
	<script src="<?= base_url() ?>assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
	<script src="<?= base_url() ?>assets/plugins/datatables/dataTables.responsive.min.js"></script>
	<script src="<?= base_url() ?>assets/plugins/datatables/responsive.bootstrap4.min.js"></script>
	<script src="<?= base_url() ?>assets/plugins/tippy/tippy.all.min.js"></script>

	<script src="<?= base_url() ?>assets/js/app.js"></script>

	<!-- Custom JS -->
	<?php if ($this->uri->segment(1) == 'auth' && $this->uri->segment(2) == 'register') : ?>
		<script src="https://www.google.com/recaptcha/api.js"></script>
	<?php endif ?>
	<script src="<?= base_url() ?>assets/custom/custom.js"></script>
	<script>
		$(document).ready(function() {
			<?php if ($this->session->flashdata('show_alert')) : ?>
				<?= $this->session->flashdata('show_alert') ?>
			<?php endif ?>
		});
	</script>

	<?php if (!empty($get_script)) : ?>
		<?= $this->load->view($get_script); ?>
	<?php endif ?>
	</body>

	</html>