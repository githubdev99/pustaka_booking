<script>
    function show_modal(params) {
        if (params) {
            if (params.id) {
                if (params.modal == 'add_booking') {
                    Swal.fire({
                        title: 'Konfirmasi!',
                        html: `Anda yakin ingin booking data buku <br> dengan judul ${params.name} ?`,
                        icon: 'warning',
                        showCloseButton: true,
                        showCancelButton: true,
                        confirmButtonColor: '#0487bf',
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
                                        show_alert({
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
                                    }
                                }
                            });
                        }
                    });
                }
            } else {
                show_alert();
            }
        } else {
            show_alert();
        }
    }
</script>