@extends($manualLayout)

@section('title', 'Input Transaksi')
@section('page-title', 'Input Transaksi Manual')
@section('page-description', 'Buat pesanan seperti checkout pembeli (harga, hub, ekspedisi, sinkronisasi)')

@section('breadcrumb')
    <li><a href="{{ $manualUrls['index'] }}">Pesanan</a></li>
    <li class="active">Input Transaksi</li>
@endsection

@push('styles')
<style>
    #items-table td { vertical-align: middle; }
    .summary-row { font-size: 14px; }
    .summary-total { font-size: 18px; font-weight: 700; }
    .product-select2-option { display: flex; align-items: center; gap: 10px; }
    .product-select2-option img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #ddd;
        flex-shrink: 0;
        background: #f4f4f4;
    }
    #product-box { position: relative; }
    #product-insert-loading {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 20;
        background: rgba(255,255,255,.82);
        text-align: center;
        padding-top: 80px;
    }
    #product-insert-loading .fa { font-size: 28px; margin-bottom: 8px; }
</style>
@endpush

@section('content')
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0" style="margin-bottom:0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $manualUrls['store'] }}" id="manual-order-form" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-user"></i> Pelanggan & Pengiriman</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="purchase_order_number">Nomor Purchase Order</label>
                                    <input type="text" class="form-control" id="purchase_order_number" name="purchase_order_number" value="{{ old('purchase_order_number') }}" maxlength="50" placeholder="Contoh: PO-2026-001" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Dokumen Purchase Order (PDF)</label>
                                    <div>
                                        <a href="#" id="po-document-link"><i class="fa fa-upload"></i> Unggah dokumen PDF</a>
                                        <span id="po-document-name" class="text-muted" style="margin-left: 8px;"></span>
                                    </div>
                                    <input type="file" name="purchase_order_document" id="purchase_order_document" accept="application/pdf,.pdf" style="display: none;">
                                    <p class="help-block">Maksimal 10 MB, format PDF.</p>
                                    @error('purchase_order_document')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Pelanggan <span class="text-danger">*</span></label>
                                    <select id="user_id" name="user_id" class="form-control" required></select>
                                    <p class="help-block" id="customer-meta">Cari nama, email, telepon, atau kode QAD.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Alamat Kirim <span class="text-danger">*</span></label>
                                    <select id="address_id" name="address_id" class="form-control" required>
                                        <option value="">-- Pilih pelanggan dulu --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Hub pengirim <span class="text-danger">*</span></label>
                                    @if($lockWarehouse)
                                        @php $lockedWarehouse = $warehouses->first(); @endphp
                                        <input type="hidden" id="source_warehouse_id" name="source_warehouse_id" value="{{ $lockedWarehouse->id }}">
                                        <input type="text" class="form-control" value="{{ $lockedWarehouse->name }}{{ $lockedWarehouse->kode_hub ? ' ('.$lockedWarehouse->kode_hub.')' : '' }}" readonly>
                                    @else
                                        <select id="source_warehouse_id" name="source_warehouse_id" class="form-control" required style="width: 100%;">
                                            <option value="">-- Pilih Hub --</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}" {{ (string) old('source_warehouse_id', $defaultWarehouseId ?? '') === (string) $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}{{ $warehouse->kode_hub ? ' ('.$warehouse->kode_hub.')' : '' }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Ekspedisi <span class="text-danger">*</span></label>
                                    <select id="expedition_id" name="expedition_id" class="form-control" required>
                                        <option value="">-- Pilih --</option>
                                        @foreach($expeditions as $expedition)
                                            <option value="{{ $expedition->id }}" data-code="{{ $expedition->code }}" data-services="{{ e(json_encode($expedition->services)) }}">{{ $expedition->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Layanan <span class="text-danger">*</span></label>
                                    <select id="expedition_service" name="expedition_service" class="form-control" required>
                                        <option value="">-- Pilih ekspedisi --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Ongkir (Rp)</label>
                                    <input type="number" min="0" step="1" class="form-control" name="shipping_cost" id="shipping_cost" value="{{ old('shipping_cost', 0) }}">
                                    <p class="help-block">Isi 0 untuk pengambilan di tempat / sudah termasuk.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal kirim (opsional)</label>
                                    <input type="date" class="form-control" name="preferred_shipping_date" value="{{ old('preferred_shipping_date') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode sales (opsional)</label>
                                    <select id="sales_code" name="sales_code" class="form-control" style="width: 100%;">
                                        <option value="">-- Pilih sales --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Catatan internal / permintaan pelanggan">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-money"></i> Pembayaran & Ringkasan</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            @php
                                $pakaiPpn = (string) old('pakai_ppn', '1');
                            @endphp
                            <label for="pakai_ppn">Pakai PPN</label>
                            <select name="pakai_ppn" id="pakai_ppn" class="form-control">
                                <option value="1" {{ $pakaiPpn === '1' ? 'selected' : '' }}>{{ \App\Support\TaxAwarePrice::pakaiPpnYesLabel() }}</option>
                                <option value="0" {{ $pakaiPpn === '0' ? 'selected' : '' }}>{{ \App\Support\TaxAwarePrice::pakaiPpnNoLabel() }}</option>
                            </select>
                            <p class="help-block" id="pakai-ppn-hint">Mengikuti data pelanggan. Bisa diubah manual.</p>
                        </div>
                        <div class="form-group">
                            <label>Metode pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method" class="form-control" required>
                                <option value="cash">Tunai (lunas)</option>
                                <option value="manual_transfer">Transfer manual</option>
                                <option value="term_of_payment" id="opt-top" disabled>Term of Payment</option>
                            </select>
                        </div>
                        <div class="checkbox" id="mark-paid-wrap">
                            <label>
                                <input type="checkbox" name="mark_as_paid" id="mark_as_paid" value="1">
                                Tandai sudah lunas (sinkron QAD/Jubelio/WMS seperti checkout berbayar)
                            </label>
                        </div>
                        <hr>
                        <p class="summary-row">Harga katalog: <span class="pull-right" id="sum-catalog">Rp 0</span></p>
                        <p class="summary-row">Diskon pelanggan: <span class="pull-right" id="sum-discount">Rp 0</span></p>
                        <p class="summary-row"><strong>Subtotal:</strong> <span class="pull-right" id="sum-subtotal">Rp 0</span></p>
                        <p class="summary-row">Pajak (<span id="sum-ppn-label">{{ \App\Support\TaxAwarePrice::ppnLabel() }}</span>): <span class="pull-right" id="sum-ppn">Rp 0</span></p>
                        <p class="summary-row">Ongkir: <span class="pull-right" id="sum-shipping">Rp 0</span></p>
                        <hr>
                        <p class="summary-total">Total: <span class="pull-right" id="sum-total">Rp 0</span></p>
                    </div>
                    <div class="box-footer">
                        <a href="{{ $manualUrls['index'] }}" class="btn btn-default">Batal</a>
                        <button type="submit" class="btn btn-primary pull-right" id="btn-submit">
                            <i class="fa fa-save"></i> Simpan transaksi
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-success" id="product-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-cubes"></i> Produk</h3>
                    </div>
                    <div class="box-body">
                        <div id="product-insert-loading">
                            <i class="fa fa-spinner fa-spin text-green"></i>
                            <div class="product-insert-loading-text">Menambahkan produk...</div>
                        </div>
                        <p class="help-block" id="product-search-hint">Pilih pelanggan dulu. Harga diskon mengikuti pembeli.</p>
                        <select id="product_search" class="form-control" style="width: 100%;" disabled></select>
                        <div class="table-responsive" style="margin-top:15px;">
                            <table class="table table-bordered" id="items-table">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Produk</th>
                                        <th width="110">Satuan</th>
                                        <th width="90">Qty</th>
                                        <th width="140" class="text-right">Harga katalog</th>
                                        <th width="170" class="text-right">Harga jual</th>
                                        <th width="140" class="text-right">Subtotal</th>
                                        <th width="40"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="items-empty">
                                        <td colspan="8" class="text-muted text-center">Belum ada produk.</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="6" class="text-right">Subtotal:</th>
                                        <th class="text-right" id="table-subtotal">Rp 0</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="6" class="text-right">Pajak (<span id="table-ppn-label">{{ \App\Support\TaxAwarePrice::ppnLabel() }}</span>):</th>
                                        <th class="text-right" id="table-ppn">Rp 0</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    var items = [];
    var productInsertBusy = false;
    var money = new Intl.NumberFormat('id-ID');
    var lockWarehouse = @json((bool) $lockWarehouse);
    var urls = @json($manualUrls);

    $('#po-document-link').on('click', function (e) {
        e.preventDefault();
        $('#purchase_order_document').trigger('click');
    });
    $('#purchase_order_document').on('change', function () {
        var file = this.files && this.files[0];
        $('#po-document-name').text(file ? file.name : '');
    });

    var defaultTaxPercent = {{ (float) \App\Models\Setting::taxPercent() }};
    var taxPercent = defaultTaxPercent;

    function formatRp(n) {
        return 'Rp ' + money.format(Math.round(n || 0));
    }

    function formatPct(n) {
        var s = String(Math.round((n || 0) * 10) / 10);
        if (s.indexOf('.') >= 0) {
            s = s.replace(/0+$/, '').replace(/\.$/, '');
        }
        return s;
    }

    function catalogDpp(inclusive) {
        if (taxPercent <= 0) {
            return inclusive;
        }
        return inclusive / (1 + (taxPercent / 100));
    }

    function formatHargaJual(item) {
        var dpp = item.catalog_dpp != null ? item.catalog_dpp : catalogDpp(item.catalog_price);
        var html = '<div>' + formatRp(dpp) + '</div>';
        if ((item.discount_percent || 0) > 0) {
            html += '<div><strong>' + formatRp(item.unit_price) + '</strong>'
                + ' <small class="text-success">( disc ' + formatPct(item.discount_percent) + '% )</small></div>';
        }
        return html;
    }

    if (!lockWarehouse) {
        $('#source_warehouse_id').select2({
            theme: 'bootstrap',
            width: '100%',
            placeholder: 'Cari hub pengirim...',
            allowClear: true
        }).on('change', function () {
            items = [];
            renderItems();
            refreshPreview();
            setProductSearchEnabled(canAddProducts());
        });
    }

    $('#sales_code').select2({
        theme: 'bootstrap',
        width: '100%',
        placeholder: 'Cari kode / nama sales...',
        allowClear: true,
        ajax: {
            url: urls.searchSales,
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) { return data; }
        }
    });

    $('#user_id').select2({
        theme: 'bootstrap',
        width: '100%',
        placeholder: 'Cari pelanggan...',
        ajax: {
            url: urls.searchCustomers,
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) { return data; }
        }
    }).on('select2:select', function (e) {
        var d = e.params.data;
        $('#customer-meta').text((d.role || '') + (d.qad_customer_code ? ' · QAD ' + d.qad_customer_code : '') + (d.term_of_payment ? ' · TOP ' + d.term_of_payment + ' hari' : ''));
        toggleTop(d.is_distributor && d.term_of_payment > 0);
        setPakaiPpn(d.pakai_ppn);
        loadAddresses(d.id);
        setProductSearchEnabled(canAddProducts());
        items = [];
        renderItems();
        refreshPreview();
    }).on('select2:clear change', function () {
        if (!$('#user_id').val()) {
            setProductSearchEnabled(false);
            items = [];
            renderItems();
            refreshPreview();
        }
    });

    $('#product_search').select2({
        theme: 'bootstrap',
        width: '100%',
        placeholder: 'Cari produk (kode / nama)...',
        ajax: {
            url: urls.searchProducts,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term, user_id: $('#user_id').val() };
            },
            processResults: function (data) { return data; }
        },
        escapeMarkup: function (markup) { return markup; },
        templateResult: formatProductOption,
        templateSelection: function (item) {
            return item.text || item.name || '';
        }
    }).on('select2:select', function (e) {
        if (productInsertBusy) {
            $(this).val(null).trigger('change');
            return;
        }
        if (!canAddProducts()) {
            alert('Pilih pelanggan dan hub pengirim dulu.');
            $(this).val(null).trigger('change');
            return;
        }
        addItem(e.params.data);
        $(this).val(null).trigger('change');
    });

    function formatProductOption(item) {
        if (item.loading) {
            return item.text;
        }
        if (!item.id) {
            return item.text || 'Cari produk (kode / nama)...';
        }
        var img = item.image || @json(asset('logo/Rasa Connect - Logo 2_Maroon 1.png'));
        return '<div class="product-select2-option">'
            + '<img src="' + esc(img) + '" alt="">'
            + '<div><strong>' + esc(item.code || '') + '</strong><br>' + esc(item.name || item.text) + '</div>'
            + '</div>';
    }

    function canAddProducts() {
        return !!$('#user_id').val() && !!$('#source_warehouse_id').val();
    }

    function setProductSearchEnabled(enabled) {
        $('#product_search').prop('disabled', !enabled).trigger('change.select2');
        $('#product-search-hint').text(enabled
            ? 'Cari produk, lalu pilih batch. Qty tidak boleh melebihi stok batch.'
            : 'Pilih pelanggan dan hub pengirim dulu. Harga diskon dan batch mengikuti pembeli/hub.');
    }
    setProductSearchEnabled(false);

    function fillExpeditionServices() {
        var $opt = $('#expedition_id option:selected');
        var code = ($opt.attr('data-code') || '').toLowerCase();
        var services = [];
        var raw = $opt.attr('data-services');
        if (raw) {
            try {
                var parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    services = parsed;
                }
            } catch (err) {
                services = [];
            }
        }
        if (!services.length) {
            if (code === 'self_pickup') {
                services = [{ code: 'Pengambilan Ditempat', name: 'Pengambilan Ditempat' }];
            } else if (code === 'kurir_toko') {
                services = [{ code: 'Diantar Ketempat', name: 'Diantar Ketempat' }];
            } else if (code) {
                services = [{ code: 'REG', name: 'Reguler' }];
            }
        }
        var $svc = $('#expedition_service').empty();
        if (!code) {
            $svc.append('<option value="">-- Pilih ekspedisi --</option>');
            return;
        }
        services.forEach(function (s) {
            var value = s.code || s.service || 'REG';
            var label = s.name || s.service || value;
            $svc.append($('<option/>').val(value).text(label));
        });
        if (code === 'self_pickup' || code === 'kurir_toko') {
            $('#shipping_cost').val(0);
            refreshPreview();
        }
    }

    $('#expedition_id').on('change', fillExpeditionServices);

    $('#shipping_cost').on('change', refreshPreview);
    $('#payment_method').on('change', syncPaidUi);
    $('#pakai_ppn').on('change', function () {
        syncTaxPercent();
        if (items.length) {
            renderItems();
            refreshPreview();
        }
    });

    function setPakaiPpn(value) {
        $('#pakai_ppn').val(String(value) === '0' ? '0' : '1');
        syncTaxPercent();
    }

    function syncTaxPercent() {
        taxPercent = $('#pakai_ppn').val() === '1' ? defaultTaxPercent : 0;
    }

    function toggleTop(enabled) {
        $('#opt-top').prop('disabled', !enabled);
        if (!enabled && $('#payment_method').val() === 'term_of_payment') {
            $('#payment_method').val('cash');
        }
        syncPaidUi();
    }

    function syncPaidUi() {
        var method = $('#payment_method').val();
        if (method === 'cash') {
            $('#mark_as_paid').prop('checked', true).prop('disabled', true);
            $('#mark-paid-wrap').show();
        } else if (method === 'term_of_payment') {
            $('#mark_as_paid').prop('checked', false).prop('disabled', true);
            $('#mark-paid-wrap').hide();
        } else {
            $('#mark_as_paid').prop('disabled', false);
            $('#mark-paid-wrap').show();
        }
    }

    function loadAddresses(userId) {
        var addressTpl = urls.customerAddresses;
        $.get(addressTpl.replace('00000000-0000-0000-0000-000000000000', userId), function (res) {
            var $sel = $('#address_id').empty();
            if (!res.addresses.length) {
                $sel.append('<option value="">Pelanggan belum punya alamat</option>');
                return;
            }
            res.addresses.forEach(function (a) {
                $sel.append($('<option/>').val(a.id).text(a.text));
            });
            toggleTop(res.is_distributor && res.term_of_payment > 0);
            if (typeof res.pakai_ppn !== 'undefined') {
                setPakaiPpn(res.pakai_ppn);
            }
        });
    }

    function esc(s) {
        return $('<div/>').text(s == null ? '' : String(s)).html();
    }

    function baseQty(item) {
        var q = parseInt(item.quantity_ordered, 10) || 0;
        return item.order_uom === 'large' ? q * (parseInt(item.units_per_large, 10) || 1) : q;
    }

    function remainingProductQty(item, excludeIdx) {
        var stock = (item.batches || []).reduce(function (sum, b) {
            return sum + (parseInt(b.qty, 10) || 0);
        }, 0);
        items.forEach(function (other, i) {
            if (i !== excludeIdx && other.product_id === item.product_id) {
                stock -= baseQty(other);
            }
        });
        return stock;
    }

    function maxOrderable(item, idx) {
        var remain = remainingProductQty(item, idx);
        var per = item.order_uom === 'large' ? (parseInt(item.units_per_large, 10) || 1) : 1;
        return Math.max(0, Math.floor(remain / per));
    }

    function allocateFefo(item, qty) {
        var need = qty;
        var allocated = [];
        (item.batches || []).forEach(function (b) {
            if (need <= 0) {
                return;
            }
            var avail = parseInt(b.qty, 10) || 0;
            if (avail <= 0) {
                return;
            }
            var take = Math.min(need, avail);
            allocated.push({
                lot_serial: b.lot_serial,
                qty: take,
                expired: b.expired || null
            });
            need -= take;
        });
        return allocated;
    }

    function formatAllocation(allocated) {
        if (!allocated || !allocated.length) {
            return '-';
        }
        return allocated.map(function (a) {
            return a.lot_serial + ' × ' + a.qty;
        }).join(', ');
    }

    function formatBatchStock(item) {
        if (!(item.batches || []).length) {
            return '-';
        }
        return item.batches.map(function (b) {
            return b.lot_serial + ' stok ' + (parseInt(b.qty, 10) || 0) + (b.expired ? ' (exp ' + b.expired + ')' : '');
        }).join(', ');
    }

    function clampQty(idx) {
        var item = items[idx];
        if (!item) return;
        var max = maxOrderable(item, idx);
        if (max < 1) {
            item.quantity_ordered = 1;
            return false;
        }
        if (item.quantity_ordered > max) {
            item.quantity_ordered = max;
            return false;
        }
        if (item.quantity_ordered < 1) {
            item.quantity_ordered = 1;
        }
        return true;
    }

    function setProductInsertLoading(on, message) {
        productInsertBusy = !!on;
        $('#product-insert-loading .product-insert-loading-text').text(message || 'Menambahkan produk...');
        $('#product-insert-loading').toggle(!!on);
        if (on) {
            $('#product_search').prop('disabled', true).trigger('change.select2');
        } else {
            setProductSearchEnabled(canAddProducts());
        }
    }

    function addItem(p) {
        if (!canAddProducts() || productInsertBusy) {
            return;
        }
        setProductInsertLoading(true, 'Mengambil batch ' + (p.name || 'produk') + '...');
        $.get(urls.productBatches, {
            user_id: $('#user_id').val(),
            warehouse_id: $('#source_warehouse_id').val(),
            product_id: p.id
        }).then(function (res) {
            var batches = res.batches || [];
            if (!batches.length) {
                alert(res.error || ('Tidak ada batch untuk ' + p.name + ' di hub ini.'));
                return;
            }
            var first = batches[0];
            var exists = items.find(function (i) {
                return i.product_id === p.id;
            });
            if (exists) {
                alert(p.name + ' sudah ditambahkan. Ubah qty di baris yang ada.');
                return;
            }
            items.push({
                product_id: p.id,
                name: p.name,
                code: p.code,
                catalog_price: p.price,
                unit_price: p.price,
                quantity_ordered: 1,
                order_uom: 'base',
                unit: p.unit || 'PCS',
                large_unit: p.large_unit,
                has_large: !!p.has_large,
                units_per_large: p.units_per_large || 1,
                subtotal: p.price,
                lot_serial: first.lot_serial,
                batch_qty: first.qty,
                batch_expired: first.expired,
                batches: batches,
                allocated_batches: allocateFefo({ batches: batches }, 1)
            });
            if (!clampQty(items.length - 1)) {
                alert('Qty melebihi total stok batch ' + p.name + '.');
            }
            renderItems();
            setProductInsertLoading(true, 'Menghitung harga...');
            return refreshPreview();
        }, function () {
            alert('Gagal mengambil data batch. Coba lagi.');
        }).always(function () {
            setProductInsertLoading(false);
        });
    }

    function renderItems() {
        var $tb = $('#items-table tbody');
        $tb.find('tr:not(#items-empty)').remove();
        $('#items-empty').toggle(items.length === 0);
        items.forEach(function (item, idx) {
            var uomSelect = '<select class="form-control input-sm js-uom" data-idx="' + idx + '">'
                + '<option value="base"' + (item.order_uom === 'base' ? ' selected' : '') + '>' + esc(item.unit || 'PCS') + '</option>';
            if (item.has_large) {
                uomSelect += '<option value="large"' + (item.order_uom === 'large' ? ' selected' : '') + '>' + esc(item.large_unit || 'CTN') + '</option>';
            }
            uomSelect += '</select>';
            item.allocated_batches = allocateFefo(item, baseQty(item));
            $tb.append(
                '<tr>'
                + '<td class="text-center">' + (idx + 1) + '</td>'
                + '<td><strong>' + esc(item.code || '') + '</strong><br>' + esc(item.name)
                + '<br><small class="text-muted">Batch: ' + esc(formatBatchStock(item)) + '</small>'
                + '<br><small class="text-muted">Dipakai: ' + esc(formatAllocation(item.allocated_batches)) + '</small>'
                + '<input type="hidden" name="items[' + idx + '][product_id]" value="' + item.product_id + '">'
                + '</td>'
                + '<td>' + uomSelect + '<input type="hidden" name="items[' + idx + '][order_uom]" class="js-uom-hidden" value="' + item.order_uom + '"></td>'
                + '<td><input type="number" min="1" class="form-control input-sm js-qty" data-idx="' + idx + '" name="items[' + idx + '][quantity_ordered]" value="' + item.quantity_ordered + '"></td>'
                + '<td class="text-right js-catalog">' + formatRp(item.catalog_price) + '</td>'
                + '<td class="text-right js-price">' + formatHargaJual(item) + '</td>'
                + '<td class="text-right js-sub">' + formatRp(item.subtotal) + '</td>'
                + '<td><button type="button" class="btn btn-xs btn-danger js-remove" data-idx="' + idx + '"><i class="fa fa-times"></i></button></td>'
                + '</tr>'
            );
        });
    }

    $(document).on('change', '.js-qty, .js-uom', function () {
        var idx = $(this).data('idx');
        items[idx].quantity_ordered = parseInt($('.js-qty[data-idx="' + idx + '"]').val(), 10) || 1;
        items[idx].order_uom = $('.js-uom[data-idx="' + idx + '"]').val();
        $('.js-uom-hidden').eq(idx).val(items[idx].order_uom);
        if (!clampQty(idx)) {
            alert('Qty melebihi total stok batch. Maksimum ' + items[idx].quantity_ordered + '.');
            $('.js-qty[data-idx="' + idx + '"]').val(items[idx].quantity_ordered);
        }
        items[idx].allocated_batches = allocateFefo(items[idx], baseQty(items[idx]));
        renderItems();
        refreshPreview();
    });

    $(document).on('click', '.js-remove', function () {
        items.splice($(this).data('idx'), 1);
        renderItems();
        refreshPreview();
    });

    function refreshPreview() {
        if (!$('#user_id').val() || !items.length) {
            $('#sum-catalog, #sum-discount, #sum-subtotal, #sum-ppn, #sum-shipping, #sum-total, #table-subtotal, #table-ppn').text(formatRp(0));
            return $.Deferred().resolve().promise();
        }
        return $.ajax({
            url: urls.previewPricing,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                user_id: $('#user_id').val(),
                shipping_cost: $('#shipping_cost').val() || 0,
                pakai_ppn: $('#pakai_ppn').val(),
                items: items.map(function (i) {
                    return {
                        product_id: i.product_id,
                        quantity_ordered: i.quantity_ordered,
                        order_uom: i.order_uom,
                        lot_serial: i.lot_serial
                    };
                })
            },
            success: function (res) {
                $('#sum-catalog').text(formatRp(res.catalog_subtotal));
                $('#sum-discount').text(formatRp(res.distributor_discount));
                var dpp = res.subtotal_dpp != null ? res.subtotal_dpp : res.subtotal;
                $('#sum-subtotal').text(formatRp(dpp));
                $('#table-subtotal').text(formatRp(dpp));
                if (res.ppn_label) {
                    $('#sum-ppn-label').text(res.ppn_label);
                    $('#table-ppn-label').text(res.ppn_label);
                }
                if (typeof res.tax_percent !== 'undefined') {
                    taxPercent = parseFloat(res.tax_percent) || 0;
                }
                $('#sum-ppn').text(formatRp(res.ppn || 0));
                $('#table-ppn').text(formatRp(res.ppn || 0));
                $('#sum-shipping').text(formatRp(res.shipping_cost));
                $('#sum-total').text(formatRp(res.total));
                (res.lines || []).forEach(function (line, idx) {
                    var item = items[idx];
                    if (item && item.product_id === line.product_id) {
                        item.catalog_price = line.catalog_price;
                        item.catalog_dpp = line.catalog_dpp;
                        item.discount_percent = line.discount_percent;
                        item.unit_price = line.unit_price;
                        item.subtotal = line.subtotal;
                    }
                });
                $('#items-table tbody tr:not(#items-empty)').each(function (i) {
                    if (!items[i]) return;
                    $(this).find('.js-catalog').text(formatRp(items[i].catalog_price));
                    $(this).find('.js-price').html(formatHargaJual(items[i]));
                    $(this).find('.js-sub').text(formatRp(items[i].subtotal));
                });
            }
        });
    }

    $('#manual-order-form').on('submit', function () {
        if (!items.length) {
            alert('Tambahkan minimal satu produk.');
            return false;
        }
        var missing = items.find(function (i) { return !(i.batches || []).length; });
        if (missing) {
            alert('Produk ' + missing.name + ' belum punya data batch.');
            return false;
        }
        var over = items.find(function (item, idx) {
            return baseQty(item) > remainingProductQty(item, idx);
        });
        if (over) {
            alert('Qty ' + over.name + ' melebihi total stok batch.');
            return false;
        }
        $('#btn-submit').prop('disabled', true).text('Menyimpan...');
    });

    syncPaidUi();
})();
</script>
@endpush
