{{-- Inicializa DataTables (búsqueda, orden, paginación y selector de cantidad) sobre la tabla $table --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
    $(function () {
        $('#{{ $table }}').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[{{ $orderColumn ?? 0 }}, 'asc']],
            columnDefs: [{ orderable: false, targets: {{ $noOrder ?? '-1' }} }],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' }
        });
    });
</script>
@endpush
