<script>
    $(document).ready(function() {
        loadTable();
    });

    function loadTable() {
        <?php if (empty($core['totalBookingTemp'])) : ?>
            $('#endBooking').hide();
        <?php endif ?>

        $.ajax({
            url: "<?= base_url() ?>member/booking/getTotal",
            type: "get",
            dataType: "json",
            success: (response) => {
                $('#totalBookingTemp').html(response);
            }
        });

        $('#tableDefault').DataTable({
            processing: true,
            serverSide: true,
            pagingType: "full_numbers",
            destroy: true,
            order: [],
            columnDefs: [{
                targets: [0, 8],
                orderable: false
            }],
            drawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            },
            ajax: {
                url: "<?= base_url() ?>member/booking/datatable",
                type: "post",
                dataType: "json",
                error: function() {
                    show_alert_mini();
                }
            }
        });
    }

    function show_modal(params) {
        if (params) {
            if (params.id) {
                if (params.modal == 'delete_booking') {
                    Swal.fire({
                        title: 'Konfirmasi!',
                        html: `Anda yakin ingin menghapus data ini dari booking?`,
                        icon: 'warning',
                        showCloseButton: true,
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#9BA7CA',
                        confirmButtonText: 'OK',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.value) {
                            $.ajax({
                                type: 'post',
                                url: '<?= base_url() ?>member/booking/action',
                                data: {
                                    submit: params.modal,
                                    id: params.id
                                },
                                dataType: 'json',
                                success: function(response) {
                                    var data = response.data;

                                    let callback = (response.callback) ? {
                                        callback: response.callback
                                    } : null

                                    if (response.isError) {
                                        show_alert_mini({
                                            ...callback,
                                            type: response.type,
                                            message: response.message
                                        });
                                    } else {
                                        show_alert_mini({
                                            ...callback,
                                            type: response.type,
                                            message: response.message
                                        });

                                        loadTable();
                                    }
                                }
                            });
                        }
                    });
                }
            } else {
                if (params.modal == 'end_booking') {
                    Swal.fire({
                        title: 'Konfirmasi!',
                        html: `Anda yakin ingin menyelesaikan booking ini?<br>batas pengambilan buku 2 hari dari tanggal booking`,
                        icon: 'warning',
                        showCloseButton: true,
                        showCancelButton: true,
                        confirmButtonColor: '#02b269',
                        cancelButtonColor: '#9BA7CA',
                        confirmButtonText: 'OK',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.value) {
                            $.ajax({
                                type: 'post',
                                url: '<?= base_url() ?>member/booking/action',
                                data: {
                                    submit: params.modal,
                                    id: params.id
                                },
                                dataType: 'json',
                                success: function(response) {
                                    var data = response.data;

                                    let callback = (response.callback) ? {
                                        callback: response.callback
                                    } : null

                                    if (response.isError) {
                                        show_alert_mini({
                                            ...callback,
                                            type: response.type,
                                            message: response.message
                                        });
                                    } else {
                                        show_alert({
                                            ...callback,
                                            type: response.type,
                                            message: response.message
                                        });

                                        loadTable();
                                    }
                                }
                            });
                        }
                    });
                }
            }
        } else {
            show_alert();
        }
    }
</script>