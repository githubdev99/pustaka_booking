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
            <?php if ($core['user']['role']['id'] == 1) : ?>
                <li class="mb-2">
                    <a href="<?= base_url() ?>admin/dashboard" class="custom-nav text-white <?= ($this->uri->segment(2) == 'dashboard') ? 'active' : ''; ?>">
                        <i class="mdi mdi-monitor-dashboard align-self-center"></i><span>Dashboard</span>
                    </a>
                </li>

                <li class="mb-2">
                    <a href="<?= base_url() ?>admin/category" class="custom-nav text-white <?= ($this->uri->segment(2) == 'category') ? 'active' : ''; ?>">
                        <i class="mdi mdi-tag-multiple align-self-center"></i><span>Kategori Buku</span>
                    </a>
                </li>

                <li class="mb-2">
                    <a href="<?= base_url() ?>admin/book" class="custom-nav text-white <?= ($this->uri->segment(2) == 'book') ? 'active' : ''; ?>">
                        <i class="mdi mdi-book-multiple align-self-center"></i><span>Buku</span>
                    </a>
                </li>

                <li class="mb-2">
                    <a href="<?= base_url() ?>admin/booking" class="custom-nav text-white <?= ($this->uri->segment(2) == 'booking') ? 'active' : ''; ?>">
                        <i class="mdi mdi-bookmark align-self-center"></i><span>List Booking Member</span>
                    </a>
                </li>

                <li class="mb-2">
                    <a href="<?= base_url() ?>admin/returning" class="custom-nav text-white <?= ($this->uri->segment(2) == 'returning') ? 'active' : ''; ?>">
                        <i class="mdi mdi-book-open-page-variant align-self-center"></i><span>List Peminjaman</span>
                    </a>
                </li>
            <?php else : ?>
                <li class="mb-2">
                    <a href="<?= base_url() ?>member/home" class="custom-nav text-white <?= ($this->uri->segment(2) == 'home') ? 'active' : ''; ?>">
                        <i class="mdi mdi-home align-self-center"></i><span>Home</span>
                    </a>
                </li>

                <li class="mb-2">
                    <a href="<?= base_url() ?>member/booking" class="custom-nav text-white <?= (($this->uri->segment(2) == 'booking') && ($this->uri->segment(3) == '')) ? 'active' : ''; ?>">
                        <i class="mdi mdi-bookmark align-self-center"></i><span>History Booking</span>
                    </a>
                </li>
            <?php endif ?>
        </ul>
    </div>
</div>
<!-- end left-sidenav-->