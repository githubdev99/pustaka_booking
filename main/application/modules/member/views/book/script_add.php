<script>
    $(document).ready(function() {
        $('#gambar').change(function() {
            document.getElementById('nama_gambar').innerHTML = this.value.split('\\').pop().split('/').pop();
            read_image(this);
            if (this.value == '') {
                $('#preview_gambar').attr('src', '<?= base_url() ?>assets/images/img-thumbnail.png');
                $('#remove_preview').hide();
            }
        });

        $('#remove_preview').click(function() {
            document.getElementById('gambar').value = '';
            document.getElementById('nama_gambar').innerHTML = 'Choose file';
            $('.image-popup').attr('href', '<?= base_url() ?>assets/images/img-thumbnail.png');
            $('#preview_gambar').attr('src', '<?= base_url() ?>assets/images/img-thumbnail.png');
            $('#remove_preview').hide();
        });

        $('form[name="add"]').submit(function(e) {
            e.preventDefault();

            let activeElement = document.activeElement;
            let url = '<?= base_url() ?>admin/book/add';
            let data = {};
            data['submit'] = activeElement.name
            $(this).serializeArray().forEach((item) => {
                if (item.name != 'image') {
                    data[item.name] = item.value;
                }
            });

            $('button[name="' + activeElement.name + '"]').attr('disabled', 'true');
            $('button[name="' + activeElement.name + '"]').html(`<i class="fas fa-circle-notch fa-spin mr-2"></i>${activeElement.textContent}`);

            var form_data = new FormData();
            form_data.append('name', $('[name="name"]').val());
            form_data.append('image', $('[name="image"]')[0].files[0]);

            $.ajax({
                type: 'POST',
                url: '<?= base_url() ?>master/uploadBook',
                data: form_data,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                success: function(response) {
                    if (response.isError) {
                        $('button[name="' + activeElement.name + '"]').removeAttr('disabled');
                        $('button[name="' + activeElement.name + '"]').html(activeElement.textContent);

                        show_alert({
                            type: response.type,
                            message: response.message
                        });
                    } else {
                        data['image'] = response.data;

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
                }
            });
        });
    });

    function read_image(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('.image-popup').attr('href', e.target.result);
                $('#preview_gambar').attr('src', e.target.result);
                $('#remove_preview').show();
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>