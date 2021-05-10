<script>
    $(document).ready(function() {
        loadTable();
    });

    function loadTable() {
        $('#tableDefault').DataTable({
            processing: true,
            serverSide: true,
            pagingType: "full_numbers",
            destroy: true,
            order: [],
            columnDefs: [{
                targets: [0, 9],
                orderable: false
            }],
            drawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            },
            ajax: {
                url: "<?= base_url() ?>admin/book/datatable",
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
                $.ajax({
                    type: 'get',
                    url: '<?= base_url() ?>admin/book/get_data/' + params.id,
                    dataType: 'json',
                    success: function(response) {
                        var data = response.data;

                        if (params.modal == 'delete') {
                            Swal.fire({
                                title: 'Konfirmasi!',
                                html: `Anda yakin ingin menghapus data buku <br> dengan judul ${data.name} ?`,
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
                                        url: '<?= base_url() ?>admin/book',
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
                    },
                    error: function() {
                        show_alert();
                    }
                });
            } else {
                show_alert();
            }
        } else {
            show_alert();
        }
    }
</script>