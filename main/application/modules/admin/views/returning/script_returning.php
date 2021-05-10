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
                targets: [0, 6, 7],
                orderable: false
            }],
            drawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            },
            ajax: {
                url: "<?= base_url() ?>admin/returning/datatableBooking",
                type: "post",
                dataType: "json",
                error: function() {
                    show_alert_mini();
                }
            }
        });
    }
</script>