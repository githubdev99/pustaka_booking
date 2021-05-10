<script>
    $(document).ready(function() {
        loadTable();

        trigger_enter({
            selector: '.process',
            target: 'button[name="process"]'
        });

        $('form[name="process"]').submit(function(e) {
            e.preventDefault();

            let activeElement = document.activeElement;
            let url = '<?= base_url() ?>admin/booking/action';
            let data = {};
            data['submit'] = activeElement.name
            $(this).serializeArray().forEach((item) => {
                data[item.name] = item.value;
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
                targets: 0,
                orderable: false
            }],
            drawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            },
            ajax: {
                url: "<?= base_url() ?>admin/booking/datatableBookingDetail/<?= $this->uri->segment('4') ?>",
                type: "post",
                dataType: "json",
                error: function() {
                    show_alert_mini();
                }
            }
        });
    }
</script>