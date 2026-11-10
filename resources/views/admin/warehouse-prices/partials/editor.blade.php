{{-- Modal Set Harga & Riwayat Harga master per warehouse.
     Tombol pemicu: .btn-set-price / .btn-history dengan data-product-id & data-product-name.
     Setelah harga berubah, event 'warehouse-price:changed' dipicu pada document. --}}
    <div class="modal fade" id="price-modal" tabindex="-1" role="dialog" aria-labelledby="price-modal-title">
        <div class="modal-dialog" role="document">
            <form class="modal-content" id="price-form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="price-modal-title">Harga Baru</h4>
                </div>
                <div class="modal-body">
                    <p id="price-product" class="text-muted"></p>
                    <input type="hidden" name="product_id" id="price-product-id">
                    <div class="form-group">
                        <label for="price-display">Harga</label>
                        <div class="input-group">
                            <span class="input-group-addon">Rp</span>
                            <input type="text" class="form-control" id="price-display" inputmode="numeric" autocomplete="off" placeholder="0" required>
                        </div>
                        <input type="hidden" id="price-value" name="price">
                    </div>
                    <p class="help-block">Harga baru langsung berlaku dan menggantikan harga sebelumnya di warehouse ini.</p>
                    <div class="form-group">
                        <label for="price-note">Catatan</label>
                        <input type="text" class="form-control" id="price-note" name="note" maxlength="255">
                    </div>
                    <div class="alert alert-danger" id="price-error" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="history-modal" tabindex="-1" role="dialog" aria-labelledby="history-modal-title">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="history-modal-title">Riwayat Harga</h4>
                </div>
                <div class="modal-body">
                    <p id="history-product" class="text-muted"></p>
                    <table class="table table-condensed">
                        <thead><tr><th>Harga</th><th>Berlaku</th><th>Status</th><th>Sumber</th><th>Catatan</th><th>Dibuat</th><th></th></tr></thead>
                        <tbody id="history-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    $(function () {
        var csrf = @json(csrf_token());
        var storeUrl = @json(route('admin.warehouse-prices.store', $warehouse));
        var historyUrl = @json(route('admin.warehouse-prices.history', [$warehouse, '__PRODUCT__']));
        var destroyUrl = @json(route('admin.warehouse-prices.destroy', [$warehouse, '__PRICE__']));

        // Input harga dengan pemisah ribuan (12.500); yang dikirim angka murni di input tersembunyi.
        function formatThousands(value) {
            return String(value).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        $('#price-display').on('input', function () {
            var digits = this.value.replace(/[^0-9]/g, '').replace(/^0+(?=\d)/, '');
            $('#price-value').val(digits);
            this.value = digits ? formatThousands(digits) : '';
        });

        $('#price-modal').on('shown.bs.modal', function () { $('#price-display').trigger('focus'); });

        function text(value) { return $('<div>').text(value === null || value === undefined ? '' : value).html(); }

        $(document).on('click', '.btn-set-price', function () {
            $('#price-form')[0].reset();
            $('#price-value').val('');
            $('#price-error').hide();
            $('#price-product-id').val($(this).data('product-id'));
            $('#price-product').text($(this).data('product-name'));
            $('#price-modal').modal('show');
        });

        $('#price-form').on('submit', function (event) {
            event.preventDefault();
            $.ajax({url: storeUrl, method: 'POST', data: $(this).serialize() + '&_token=' + encodeURIComponent(csrf)})
                .done(function () { $('#price-modal').modal('hide'); $(document).trigger('warehouse-price:changed'); })
                .fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan harga.';
                    $('#price-error').text(message).show();
                });
        });

        function loadHistory(productId, productName) {
            $('#history-product').text(productName);
            $('#history-body').html('<tr><td colspan="7">Memuat...</td></tr>');
            $.getJSON(historyUrl.replace('__PRODUCT__', productId)).done(function (data) {
                if (!data.rows.length) {
                    $('#history-body').html('<tr><td colspan="7" class="text-muted">Belum ada harga master. Harga global: ' + text(data.global_price) + '</td></tr>');
                    return;
                }
                var html = data.rows.map(function (row) {
                    var period = text(row.effective_from) + (row.effective_to ? ' s.d. ' + text(row.effective_to) : '');
                    var cancel = row.can_cancel ? '<button type="button" class="btn btn-danger btn-xs btn-cancel" data-price-id="' + text(row.id) + '">Batalkan</button>' : '';
                    return '<tr><td>' + text(row.price) + '</td><td>' + period + '</td><td>' + text(row.status) + '</td><td>' + text(row.source) + '</td><td>' + text(row.note) + '</td><td>' + text(row.created_by) + '<br><small class="text-muted">' + text(row.created_at) + '</small></td><td>' + cancel + '</td></tr>';
                }).join('');
                $('#history-body').html(html);
            });
            $('#history-modal').data('product-id', productId).data('product-name', productName);
        }

        $(document).on('click', '.btn-history', function () {
            loadHistory($(this).data('product-id'), $(this).data('product-name'));
            $('#history-modal').modal('show');
        });

        $('#history-body').on('click', '.btn-cancel', function () {
            if (!confirm('Batalkan harga terjadwal ini?')) { return; }
            $.ajax({url: destroyUrl.replace('__PRICE__', $(this).data('price-id')), method: 'POST', data: {_method: 'DELETE', _token: csrf}})
                .done(function () {
                    var modal = $('#history-modal');
                    loadHistory(modal.data('product-id'), modal.data('product-name'));
                    $(document).trigger('warehouse-price:changed');
                })
                .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal membatalkan.'); });
        });
    });
</script>
@endpush
