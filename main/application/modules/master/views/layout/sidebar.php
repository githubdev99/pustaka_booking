<!-- Left Sidenav -->
<div class="left-sidenav">
    <!-- LOGO -->
    <div class="brand mt-2">
        <a href="<?= base_url() ?>" class="logo">
            <span>
                <img src="<?= $core['logo_mini'] ?>" alt="logo-small" class="logo-sm" style="width: 55px; height: 50px;">
            </span>
            <span class="text-white font-18">
                <?= $core['app_name'] ?>
            </span>
        </a>
    </div>
    <!--end logo-->
    <div class="menu-content h-100" data-simplebar>
        <ul class="metismenu left-sidenav-menu">
            <li class="mb-2">
                <a href="<?= base_url() ?>dashboard" class="custom-nav text-white <?= ($this->uri->segment(1) == 'dashboard') ? 'active' : ''; ?>">
                    <i class="mdi mdi-monitor-dashboard align-self-center"></i><span>Dashboard</span>
                </a>
            </li>

            <li class="mb-2">
                <a href="#" class="custom-nav text-white">
                    <i class="mdi mdi-clipboard-text-outline align-self-center"></i><span>Laporan</span>
                </a>
            </li>

            <hr class="hr-dashed hr-menu">

            <li class="mb-2">
                <a href="<?= base_url() ?>account" class="custom-nav text-white <?= ($this->uri->segment(1) == 'account' && $this->uri->segment(2) == '') ? 'active' : ''; ?>">
                    <i class="mdi mdi-bank align-self-center"></i><span>Kas & Bank</span>
                </a>
            </li>

            <li class="mb-2">
                <a href="<?= base_url() ?>invoice" class="custom-nav text-white <?= ($this->uri->segment(1) == 'invoice') ? 'active' : ''; ?>">
                    <i class="mdi mdi-tag-multiple align-self-center"></i><span>Penjualan</span>
                </a>
            </li>

            <li class="mb-2">
                <a href="<?= base_url() ?>purchase" class="custom-nav text-white <?= ($this->uri->segment(1) == 'purchase') ? 'active' : ''; ?>">
                    <i class="mdi mdi-cart align-self-center"></i><span>Pembelian</span>
                </a>
            </li>

            <li class="mb-2">
                <a href="<?= base_url() ?>expense" class="custom-nav text-white <?= ($this->uri->segment(1) == 'expense') ? 'active' : ''; ?>">
                    <i class="mdi mdi-card-plus align-self-center"></i><span>Biaya</span>
                </a>
            </li>

            <hr class="hr-dashed hr-menu">

            <li class="mb-2">
                <a href="<?= base_url() ?>contact" class="custom-nav text-white <?= ($this->uri->segment(1) == 'contact') ? 'active' : ''; ?>">
                    <i class="mdi mdi-contacts align-self-center"></i><span>Kontak</span>
                </a>
            </li>

            <li class="mb-2">
                <a href="<?= base_url() ?>product" class="custom-nav text-white <?= ($this->uri->segment(1) == 'product') ? 'active' : ''; ?>">
                    <i class="mdi mdi-archive align-self-center"></i><span>Produk</span>
                </a>
            </li>

            <li class="mb-2">
                <a href="<?= base_url() ?>asset" class="custom-nav text-white <?= ($this->uri->segment(1) == 'asset') ? 'active' : ''; ?>">
                    <i class="mdi mdi-garage align-self-center"></i><span>Pengaturan Aset</span>
                </a>
            </li>

            <li class="mb-2">
                <a href="<?= base_url() ?>account/chart" class="custom-nav text-white <?= ($this->uri->segment(2) == 'chart') ? 'active' : ''; ?>">
                    <i class="mdi mdi-clipboard-text align-self-center"></i><span>Daftar Akun</span>
                </a>
            </li>

            <li class="mb-2">
                <a href="<?= base_url() ?>setting" class="custom-nav text-white <?= ($this->uri->segment(1) == 'setting') ? 'active' : ''; ?>">
                    <i class="mdi mdi-settings align-self-center"></i><span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- end left-sidenav-->