<script>
    $(document).ready(function() {
        let isConfirmPassword = false;

        $('[name="confirm_password"]').attr('disabled', 'true');

        $('[name="password"]').bind('keypress keyup keydown', function() {
            if ($(this).val()) {
                $('[name="confirm_password"]').removeAttr('disabled');
            } else {
                $('[name="confirm_password"]').attr('disabled', 'true');
            }
        });

        $('[name="confirm_password"]').bind('keypress keyup keydown', function() {
            let password = $('[name="password"]').val();

            if (password != $(this).val()) {
                isConfirmPassword = false;
                $('#errorConfirmPassword').html('Password tidak sama');
            } else {
                isConfirmPassword = true;
                $('#errorConfirmPassword').html('');
            }
        });

        $('form[name="register"]').submit(function(e) {
            e.preventDefault();

            let activeElement = document.activeElement;
            let url = '<?= base_url() ?>auth/register';
            let data = {};
            data['submit'] = activeElement.name
            $(this).serializeArray().forEach((item) => {
                data[item.name] = item.value;
            });

            $('button[name="' + activeElement.name + '"]').attr('disabled', 'true');
            $('button[name="' + activeElement.name + '"]').html(`<i class="fas fa-circle-notch fa-spin mr-2"></i>${activeElement.textContent}`);

            if (!isConfirmPassword) {
                $('button[name="' + activeElement.name + '"]').removeAttr('disabled');
                $('button[name="' + activeElement.name + '"]').html(activeElement.textContent);

                show_alert({
                    type: 'warning',
                    message: 'Password belum terisi atau tidak sama'
                });
            } else {
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
            }
        });
    });
</script>