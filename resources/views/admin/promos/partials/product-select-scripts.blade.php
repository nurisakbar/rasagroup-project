@push('scripts')
<script>
$(document).ready(function() {
    $('#product_ids').select2({
        theme: 'bootstrap',
        width: '100%',
        placeholder: $('#product_ids').data('placeholder'),
        allowClear: true,
        ajax: {
            url: @json(route('admin.promos.search-products')),
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term || '' };
            },
            processResults: function(data) {
                return { results: data.results || [] };
            },
            cache: true
        },
        minimumInputLength: 1
    });

    $('#target_audience').select2({
        theme: 'bootstrap',
        width: '100%',
        placeholder: $('#target_audience').data('placeholder')
    });
});
</script>
@endpush
