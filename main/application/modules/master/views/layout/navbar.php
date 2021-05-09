<!-- Top Bar Start -->
<div class="topbar">
    <!-- Navbar -->
    <nav class="navbar-custom">
        <ul class="list-unstyled topbar-nav float-right mb-0">
            <li class="dropdown">
                <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <span class="ml-1 nav-user-name hidden-sm"><?= $core['user']['name'] ?></span>
                    <img src="<?= $core['user']['image'] ?>" alt="profile-user" class="ml-2 rounded-circle thumb-xs" />
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="<?= base_url() . $core['user']['role']['name'] ?>/profile"><i data-feather="user" class="align-self-center icon-xs icon-dual mr-1"></i> Profil Akun</a>
                    <div class="dropdown-divider mb-0"></div>
                    <a class="dropdown-item" href="<?= base_url() ?>auth/logout"><i data-feather="power" class="align-self-center icon-xs icon-dual mr-1"></i> Logout</a>
                </div>
            </li>
        </ul>
        <!--end topbar-nav-->

        <ul class="list-unstyled topbar-nav mb-0">
            <li>
                <button class="nav-link button-menu-mobile">
                    <i data-feather="menu" class="align-self-center topbar-icon"></i>
                </button>
            </li>
        </ul>
    </nav>
    <!-- end navbar-->
</div>
<!-- Top Bar End -->