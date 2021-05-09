<script>
    $(document).ready(function() {
        loadTable();

        trigger_enter({
            selector: '.add',
            target: 'button[name="add"]'
        });

        trigger_enter({
            selector: '.edit',
            target: 'button[name="edit"]'
        });

        $('form[name="add"]').submit(function(e) {
            e.preventDefault();

            let activeElement = document.activeElement;
            let url = '<?= base_url() ?>admin/category';
            let data = {};
            let form = [];
            data['submit'] = activeElement.name
            $(this).serializeArray().forEach((item) => {
                data[item.name] = item.value;
                form.push(item.name);
            });

            $('button[name="' + activeElement.name + '"]').attr('disabled', 'true');
            $('button[name="' + activeElement.name + '"]').html(`<i class="fas fa-circle-notch fa-spin mr-2"></i>${activeElement.textContent}`);

            $.ajax({
                type: 'post',
                url: url,
                data: data,
                dataType: "json",
                success: function(response) {
                    let callback = (response.callback) ? {
                        callback: response.callback
                    } : null

                    if (response.isError) {
                        $('button[name="' + activeElement.name + '"]').removeAttr('disabled');
                        $('button[name="' + activeElement.name + '"]').html(activeElement.textContent);

                        show_alert_mini({
                            ...callback,
                            type: response.type,
                            message: response.message
                        });
                    } else {
                        setTimeout(() => {
                            form.forEach((item) => {
                                $(`#add [name="${item}"]`).val('');
                            });

                            $('button[name="' + activeElement.name + '"]').removeAttr('disabled');
                            $('button[name="' + activeElement.name + '"]').html(activeElement.textContent);

                            show_alert_mini({
                                ...callback,
                                type: response.type,
                                message: response.message
                            });

                            $('#add').modal('hide');

                            loadTable();
                        }, 700);
                    }
                }
            });
        });

        $('form[name="edit"]').submit(function(e) {
            e.preventDefault();

            let activeElement = document.activeElement;
            let url = '<?= base_url() ?>admin/category';
            let data = {};
            let form = [];
            data['submit'] = activeElement.name
            $(this).serializeArray().forEach((item) => {
                data[item.name] = item.value;
                form.push(item.name);
            });

            $('button[name="' + activeElement.name + '"]').attr('disabled', 'true');
            $('button[name="' + activeElement.name + '"]').html(`<i class="fas fa-circle-notch fa-spin mr-2"></i>${activeElement.textContent}`);

            $.ajax({
                type: 'post',
                url: url,
                data: data,
                dataType: "json",
                success: function(response) {
                    let callback = (response.callback) ? {
                        callback: response.callback
                    } : null

                    if (response.isError) {
                        $('button[name="' + activeElement.name + '"]').removeAttr('disabled');
                        $('button[name="' + activeElement.name + '"]').html(activeElement.textContent);

                        show_alert_mini({
                            ...callback,
                            type: response.type,
                            message: response.message
                        });
                    } else {
                        setTimeout(() => {
                            form.forEach((item) => {
                                $(`#edit [name="${item}"]`).val('');
                            });

                            $('button[name="' + activeElement.name + '"]').removeAttr('disabled');
                            $('button[name="' + activeElement.name + '"]').html(activeElement.textContent);

                            show_alert_mini({
                                ...callback,
                                type: response.type,
                                message: response.message
                            });

                            $('#edit').modal('hide');

                            loadTable();
                        }, 700);
                    }
                }
            });
        });
    });

    function loadTable() {
        $('#tableDefault').DataTable({
            processing: true,
            serverSide: true,
            pagingType: "full_numbers",
            destroy: true,
            order: [],
            columnDefs: [{
                targets: [0, 2],
                orderable: false
            }],
            drawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            },
            ajax: {
                url: "<?= base_url() ?>admin/category/datatable",
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
                    url: '<?= base_url() ?>admin/category/get_data/' + params.id,
                    dataType: 'json',
                    success: function(response) {
                        var data = response.data;

                        if (params.modal == 'delete') {
                            Swal.fire({
                                title: 'Konfirmasi!',
                                html: `Anda yakin ingin menghapus data kategori <br> dengan nama ${data.name} ?`,
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
                                        url: '<?= base_url() ?>admin/category',
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
                        } else if (params.modal == 'edit') {
                            $('#edit').modal('show');

                            $('#edit [name="id"]').val(params.id);
                            $('#edit [name="name"]').val(data.name);
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