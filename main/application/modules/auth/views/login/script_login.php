<script>
    $(document).ready(function() {
        trigger_enter({
            selector: '.login',
            target: 'button[name="login"]'
        });

        $('form[name="login"]').submit(function(e) {
            e.preventDefault();

            let activeElement = document.activeElement;
            let url = '<?= base_url() ?>auth/login';
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
</script>