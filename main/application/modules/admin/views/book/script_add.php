<script>
    $(document).ready(function() {
        <?php if (empty($data->image)) : ?>
            $('#remove_preview').hide();
        <?php endif ?>

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
            <?php if (!empty($data->image)) : ?>
                document.getElementById('gambar_old').value = '';
            <?php endif ?>
            $('.image-popup').attr('href', '<?= base_url() ?>assets/images/img-thumbnail.png');
            $('#preview_gambar').attr('src', '<?= base_url() ?>assets/images/img-thumbnail.png');
            $('#remove_preview').hide();
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