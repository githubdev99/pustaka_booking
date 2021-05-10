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
                targets: 0,
                orderable: false
            }],
            drawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            },
            ajax: {
                url: "<?= base_url() ?>member/booking/datatableBookingDetail/<?= $this->uri->segment('4') ?>",
                type: "post",
                dataType: "json",
                error: function() {
                    show_alert_mini();
                }
            }
        });
    }
</script>