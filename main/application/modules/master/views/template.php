<?php if ($this->uri->segment('2') != 'report') : ?>
    <?= $this->load->view('layout/header'); ?>
    <?php if ($this->uri->segment(1) != 'auth' && $this->uri->segment(1) != '') : ?>
        <?= $this->load->view('layout/sidebar'); ?>
        <div class="page-wrapper">
            <?= $this->load->view('layout/navbar'); ?>
            <div class="page-content" style="background-color: #f5f5f5;">
                <div class="container-fluid">
                    <?php
                    if (!empty($get_view)) {
                        $this->load->view($get_view);
                    }
                    ?>
                </div>

                <footer class="footer text-center text-sm-left" style="background-color: white;">
                    &copy; <?= date('Y') ?> <?= $core['app_name'] ?> All Rights Reserved.
                </footer>
            </div>
        </div>
    <?php else : ?>
        <?php
        if (!empty($get_view)) {
            $this->load->view($get_view);
        }
        ?>
    <?php endif ?>
    <?= $this->load->view('layout/footer'); ?>
<?php endif ?>