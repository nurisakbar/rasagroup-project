@extends('layouts.distributor')

@section('title', 'Checkout')
@section('page-title', 'Checkout')
@section('page-description', 'Selesaikan pesanan Anda')

@section('breadcrumb')
    <li><a href="{{ route('distributor.orders.products') }}">Order Produk</a></li>
    <li><a href="{{ route('distributor.orders.cart') }}">Keranjang</a></li>
    <li class="active">Checkout</li>
@endsection

@section('content')
    <form action="{{ route('distributor.orders.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <!-- Source Hub Selection -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-building"></i> Pilih Hub Sumber</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="source_warehouse_id">Hub Sumber Pengiriman <span class="text-danger">*</span></label>
                            <select name="source_warehouse_id" id="source_warehouse_id" class="form-control" required>
                                <option value="">-- Pilih Hub --</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('source_warehouse_id', Auth::user()->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} - {{ $warehouse->full_location }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="help-block">Pilih hub yang akan mengirimkan produk untuk pesanan ini.</span>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-map-marker"></i> Alamat Pengiriman</h3>
                        <div class="box-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addAddressModal">
                                <i class="fa fa-plus"></i> Tambah Alamat
                            </button>
                        </div>
                    </div>
                    <div class="box-body" id="address-list">
                        @if($addresses->count() > 0)
                            @foreach($addresses as $address)
                                <div class="radio address-item" data-address-id="{{ $address->id }}" style="padding: 15px; border: 2px solid {{ $address->is_default ? '#f39c12' : '#ddd' }}; border-radius: 5px; margin-bottom: 10px;">
                                    <label style="width: 100%;">
                                        <input type="radio" name="address_id" value="{{ $address->id }}" 
                                               {{ ($defaultAddress && $defaultAddress->id == $address->id) ? 'checked' : '' }}
                                               onchange="updateShipping()">
                                        <strong>{{ $address->label ?? 'Alamat' }}</strong>
                                        @if($address->is_default)
                                            <span class="label label-warning">Utama</span>
                                        @endif
                                        <br>
                                        <span style="margin-left: 20px;">{{ $address->recipient_name }} - {{ $address->phone }}</span><br>
                                        <span style="margin-left: 20px; color: #666;">{{ $address->full_address }}</span>
                                    </label>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-warning" id="no-address-message">
                                <i class="fa fa-warning"></i> Anda belum memiliki alamat. Silakan tambah alamat baru.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Expedition -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-truck"></i> Pilih Ekspedisi</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ekspedisi</label>
                                    <select name="expedition_id" id="expedition_id" class="form-control" onchange="loadExpeditionServices()">
                                        @foreach($expeditions as $expedition)
                                            <option value="{{ $expedition->id }}" {{ $defaultExpedition && $defaultExpedition->id == $expedition->id ? 'selected' : '' }}>
                                                {{ $expedition->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Layanan</label>
                                    <select name="expedition_service" id="expedition_service" class="form-control" onchange="updateShipping()">
                                        @if($defaultExpedition)
                                            @foreach($defaultExpedition->services as $service)
                                                <option value="{{ $service['code'] }}" data-multiplier="{{ $service['multiplier'] }}">
                                                    {{ $service['name'] }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="shipping-info" class="text-muted">
                            <i class="fa fa-info-circle"></i> Berat total: <span id="total-weight">{{ number_format($totalWeight / 1000, 1) }}</span> kg | 
                            Estimasi: <span id="estimated-delivery">2-4 hari</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-credit-card"></i> Metode Pembayaran</h3>
                    </div>
                    <div class="box-body">
                        <div class="radio" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px;">
                            <label>
                                <input type="radio" name="payment_method" value="transfer" checked>
                                <strong>Transfer Bank</strong>
                                <br><span style="margin-left: 20px; color: #666;">Pembayaran via transfer ke rekening perusahaan</span>
                            </label>
                        </div>
                        <div class="radio" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            <label>
                                <input type="radio" name="payment_method" value="cod">
                                <strong>COD (Bayar di Tempat)</strong>
                                <br><span style="margin-left: 20px; color: #666;">Bayar saat barang diterima</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-comment"></i> Catatan (Opsional)</h3>
                    </div>
                    <div class="box-body">
                        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan untuk pesanan..."></textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Order Summary -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Ringkasan Pesanan</h3>
                    </div>
                    <div class="box-body">
                        @foreach($carts as $cart)
                            <div style="display: flex; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                                <div style="flex: 0 0 50px;">
                                    @if($cart->product->image)
                                        <img src="{{ $cart->product->image_url }}" alt="" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div style="width: 50px; height: 50px; background: #f4f4f4; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-image text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div style="flex: 1; padding-left: 10px;">
                                    <small>{{ $cart->product->name }}</small><br>
                                    <small class="text-muted">{{ $cart->quantity }} x Rp {{ number_format($cart->display_price ?? Auth::user()->getProductPrice($cart->product), 0, ',', '.') }}</small>
                                    @if(isset($cart->display_price) && $cart->display_price != $cart->product->price)
                                        <br><small style="text-decoration: line-through; color: #999;">Rp {{ number_format($cart->product->price, 0, ',', '.') }}</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <table class="table table-condensed" style="margin-top: 15px;">
                            <tr>
                                <td>Subtotal ({{ $totalItems }} item)</td>
                                <td class="text-right" id="subtotal-display">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Ongkos Kirim</td>
                                <td class="text-right" id="shipping-display">Rp {{ number_format($shippingCost, 0, ',', '.') }}</td>
                            </tr>
                            <tr style="font-size: 16px; font-weight: bold;">
                                <td>Total</td>
                                <td class="text-right" id="total-display">Rp {{ number_format($total, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="bg-yellow">
                                <td><i class="fa fa-star"></i> Poin Didapat</td>
                                <td class="text-right"><strong>+{{ number_format($potentialPoints, 0, ',', '.') }}</strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-warning btn-lg btn-block" id="submit-btn">
                            <i class="fa fa-check"></i> Buat Pesanan
                        </button>
                        <a href="{{ route('distributor.orders.cart') }}" class="btn btn-default btn-block">
                            <i class="fa fa-arrow-left"></i> Kembali ke Keranjang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Add Address Modal -->
    <div class="modal fade" id="addAddressModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"><i class="fa fa-map-marker"></i> Tambah Alamat Baru</h4>
                </div>
                <form id="addAddressForm">
                    @csrf
                    <div class="modal-body">
                        <div id="address-form-errors" class="alert alert-danger" style="display: none;"></div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Label Alamat <span class="text-danger">*</span></label>
                                    <select class="form-control" name="label" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="Rumah">Rumah</option>
                                        <option value="Kantor">Kantor</option>
                                        <option value="Toko">Toko</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nama Penerima <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="recipient_name" value="{{ Auth::user()->name }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>No. HP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="phone" value="{{ Auth::user()->phone }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Provinsi <span class="text-danger">*</span></label>
                                    <select class="form-control" name="province_id" id="modal_province_id" required>
                                        <option value="">-- Pilih Provinsi --</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kabupaten/Kota <span class="text-danger">*</span></label>
                                    <select class="form-control" name="regency_id" id="modal_regency_id" required disabled>
                                        <option value="">-- Pilih Kabupaten/Kota --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kecamatan <span class="text-danger">*</span></label>
                                    <select class="form-control" name="district_id" id="modal_district_id" required disabled>
                                        <option value="">-- Pilih Kecamatan --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kelurahan/Desa <span class="text-danger">*</span></label>
                                    <select class="form-control" name="village_id" id="modal_village_id" required disabled>
                                        <option value="">-- Pilih Kelurahan/Desa --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="address_detail" rows="2" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kode Pos</label>
                                    <input type="text" class="form-control" name="postal_code">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Catatan</label>
                                    <textarea class="form-control" name="notes" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_default" value="1">
                                    Jadikan sebagai alamat utama
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan Alamat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Handle cascading dropdowns for address modal
$('#modal_province_id').on('change', function() {
    var provinceId = $(this).val();
    $('#modal_regency_id, #modal_district_id, #modal_village_id').prop('disabled', true).html('<option value="">Memuat...</option>');
    
    if (provinceId) {
        $.get('{{ route("buyer.addresses.get-regencies") }}', { province_id: provinceId })
            .done(function(data) {
                var options = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                $.each(data, function(key, value) {
                    options += '<option value="' + value.id + '">' + value.name + '</option>';
                });
                $('#modal_regency_id').html(options).prop('disabled', false);
            });
    }
});

$('#modal_regency_id').on('change', function() {
    var regencyId = $(this).val();
    $('#modal_district_id, #modal_village_id').prop('disabled', true).html('<option value="">Memuat...</option>');
    
    if (regencyId) {
        $.get('{{ route("buyer.addresses.get-districts") }}', { regency_id: regencyId })
            .done(function(data) {
                var options = '<option value="">-- Pilih Kecamatan --</option>';
                $.each(data, function(key, value) {
                    options += '<option value="' + value.id + '">' + value.name + '</option>';
                });
                $('#modal_district_id').html(options).prop('disabled', false);
            });
    }
});

$('#modal_district_id').on('change', function() {
    var districtId = $(this).val();
    $('#modal_village_id').prop('disabled', true).html('<option value="">Memuat...</option>');
    
    if (districtId) {
        $.get('{{ route("buyer.addresses.get-villages") }}', { district_id: districtId })
            .done(function(data) {
                var options = '<option value="">-- Pilih Kelurahan/Desa --</option>';
                $.each(data, function(key, value) {
                    options += '<option value="' + value.id + '">' + value.name + '</option>';
                });
                $('#modal_village_id').html(options).prop('disabled', false);
            });
    }
});

// Handle address form submission
$('#addAddressForm').on('submit', function(e) {
    e.preventDefault();
    
    var form = $(this);
    var submitBtn = form.find('button[type="submit"]');
    var originalText = submitBtn.html();
    
    submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
    $('#address-form-errors').hide().empty();
    
    $.ajax({
        url: '{{ route("buyer.addresses.store") }}',
        method: 'POST',
        data: form.serialize(),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                // Add new address to the list
                var address = response.address;
                var addressHtml = '<div class="radio address-item" data-address-id="' + address.id + '" style="padding: 15px; border: 2px solid ' + (address.is_default ? '#f39c12' : '#ddd') + '; border-radius: 5px; margin-bottom: 10px;">' +
                    '<label style="width: 100%;">' +
                    '<input type="radio" name="address_id" value="' + address.id + '" ' + (address.is_default ? 'checked' : '') + ' onchange="updateShipping()">' +
                    '<strong>' + (address.label || 'Alamat') + '</strong>' +
                    (address.is_default ? ' <span class="label label-warning">Utama</span>' : '') +
                    '<br>' +
                    '<span style="margin-left: 20px;">' + address.recipient_name + ' - ' + address.phone + '</span><br>' +
                    '<span style="margin-left: 20px; color: #666;">' + response.full_address + '</span>' +
                    '</label>' +
                    '</div>';
                
                $('#no-address-message').remove();
                $('#address-list').prepend(addressHtml);
                
                // If this is default, uncheck others
                if (address.is_default) {
                    $('.address-item').not('[data-address-id="' + address.id + '"]').css('border-color', '#ddd');
                    $('.address-item').not('[data-address-id="' + address.id + '"]').find('input[type="radio"]').prop('checked', false);
                    $('.address-item[data-address-id="' + address.id + '"]').css('border-color', '#f39c12');
                }
                
                // Update shipping if this address is selected
                if (address.is_default) {
                    updateShipping();
                }
                
                // Reset form and close modal
                form[0].reset();
                $('#addAddressModal').modal('hide');
                
                // Show success message
                alert('Alamat berhasil ditambahkan!');
            }
        },
        error: function(xhr) {
            var errors = '';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                $.each(xhr.responseJSON.errors, function(key, value) {
                    errors += '<li>' + value[0] + '</li>';
                });
            } else {
                errors = '<li>Terjadi kesalahan saat menyimpan alamat.</li>';
            }
            $('#address-form-errors').html('<ul class="mb-0">' + errors + '</ul>').show();
        },
        complete: function() {
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});
</script>
<script>
function updateShipping() {
    const addressId = document.querySelector('input[name="address_id"]:checked')?.value;
    const expeditionId = document.getElementById('expedition_id').value;
    const serviceCode = document.getElementById('expedition_service').value;

    if (!addressId || !expeditionId || !serviceCode) return;

    fetch('{{ route("distributor.orders.calculate-shipping") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            address_id: addressId,
            expedition_id: expeditionId,
            service_code: serviceCode
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('shipping-display').textContent = data.shipping_cost_formatted;
        document.getElementById('total-display').textContent = data.total_formatted;
        document.getElementById('total-weight').textContent = data.total_weight_formatted;
        document.getElementById('estimated-delivery').textContent = data.estimated_delivery;
    })
    .catch(error => console.error('Error:', error));
}

function loadExpeditionServices() {
    const expeditionId = document.getElementById('expedition_id').value;
    const addressId = document.querySelector('input[name="address_id"]:checked')?.value;
    const serviceSelect = document.getElementById('expedition_service');

    fetch('{{ route("distributor.orders.expedition-services") }}?expedition_id=' + expeditionId + '&address_id=' + (addressId || ''))
    .then(response => response.json())
    .then(data => {
        serviceSelect.innerHTML = '';
        data.services.forEach(service => {
            const option = document.createElement('option');
            option.value = service.code;
            option.textContent = service.name + ' - ' + service.cost_formatted + ' (' + service.estimated_days + ')';
            serviceSelect.appendChild(option);
        });
        updateShipping();
    })
    .catch(error => console.error('Error:', error));
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadExpeditionServices();
});
</script>
@endpush

