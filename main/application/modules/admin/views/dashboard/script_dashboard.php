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
            language: {
                infoFiltered: ''
            },
            drawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            },
            ajax: {
                url: "<?= base_url() ?>admin/dashboard/datatableUser",
                type: "post",
                dataType: "json",
                error: function() {
                    show_alert_mini();
                }
            }
        });
    }
</script>