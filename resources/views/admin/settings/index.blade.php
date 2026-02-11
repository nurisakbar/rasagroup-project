@extends('layouts.admin')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')
@section('page-description', 'Kelola pengaturan sistem')

@section('breadcrumb')
    <li class="active">Pengaturan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

        <!-- DRiiPPreneur Point Rate Setting -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cog"></i> Point Rate DRiiPPreneur</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.settings.update-driippreneur-point-rate') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="point_rate">Point per Item</label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="point_rate" 
                                   name="point_rate" 
                                   value="{{ old('point_rate', $driippreneurPointRate) }}" 
                                   min="0" 
                                   step="1" 
                                   required>
                            <span class="input-group-addon">point/item</span>
                        </div>
                        <p class="help-block">
                            Point yang akan diberikan kepada DRiiPPreneur untuk setiap item yang dibeli saat pesanan selesai.
                            <br>
                            <strong>Contoh:</strong> Jika di-set 1000 point/item, dan DRiiPPreneur membeli 5 item, maka akan mendapat 5000 point.
                        </p>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- WACloud Integration Setting -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-whatsapp"></i> Integrasi WACloud</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.settings.update-wacloud') }}" method="POST" id="wacloud-form">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="wacloud_api_key">API Key WACloud</label>
                        <input type="text" 
                               class="form-control" 
                               id="wacloud_api_key" 
                               name="wacloud_api_key" 
                               value="{{ old('wacloud_api_key', $wacloudApiKey) }}" 
                               placeholder="waha_your_api_key_here"
                               required>
                        <p class="help-block">
                            API Key yang didapatkan dari dashboard WACloud setelah mendaftar.
                            <br>
                            <strong>Format:</strong> waha_xxxxxxxxxxxxx
                            <br>
                            <a href="https://wacloud.id/docs.html" target="_blank" rel="noopener noreferrer">
                                <i class="fa fa-external-link"></i> Lihat dokumentasi WACloud
                            </a>
                        </p>
                    </div>

                    <div class="form-group">
                        <label for="wacloud_device_id">Device ID WACloud</label>
                        <input type="text" 
                               class="form-control" 
                               id="wacloud_device_id" 
                               name="wacloud_device_id" 
                               value="{{ old('wacloud_device_id', $wacloudDeviceId) }}" 
                               placeholder="550e8400-e29b-41d4-a716-446655440000"
                               required>
                        <p class="help-block">
                            Device ID WhatsApp yang terhubung di WACloud. Device ID dapat dilihat di dashboard WACloud atau melalui endpoint GET /devices.
                            <br>
                            <strong>Format:</strong> UUID format (contoh: 550e8400-e29b-41d4-a716-446655440000)
                        </p>
                    </div>

                    @if(session('wacloud_quota') || $wacloudQuota)
                        @php
                            $quota = session('wacloud_quota') ?? $wacloudQuota;
                            // Extract all quota information
                            $quotaBalance = isset($quota['quota_balance']) ? $quota['quota_balance'] : (isset($quota['quota']) ? $quota['quota'] : null);
                            $quotaText = isset($quota['quota_text']) ? $quota['quota_text'] : null;
                            $quotaMultimedia = isset($quota['quota_multimedia']) ? $quota['quota_multimedia'] : null;
                            $quotaFreeText = isset($quota['quota_free_text']) ? $quota['quota_free_text'] : null;
                            $quotaTotalText = isset($quota['quota_total_text']) ? $quota['quota_total_text'] : null;
                            $planValue = isset($quota['plan']) && (is_string($quota['plan']) || is_numeric($quota['plan'])) ? $quota['plan'] : null;
                            
                            // Convert to numeric
                            $quotaBalanceNumeric = is_numeric($quotaBalance) ? (float)$quotaBalance : (is_string($quotaBalance) && is_numeric($quotaBalance) ? (float)$quotaBalance : null);
                        @endphp
                        <div class="alert alert-success" id="wacloud-quota-info">
                            <h4><i class="icon fa fa-check-circle"></i> Informasi Quota WACloud</h4>
                            <table class="table table-bordered" style="margin-bottom: 0;">
                                <tbody>
                                    @if($quotaBalanceNumeric !== null)
                                        <tr>
                                            <td width="40%"><strong>Balance:</strong></td>
                                            <td>Rp {{ number_format($quotaBalanceNumeric, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    @if($quotaText !== null)
                                        <tr>
                                            <td><strong>Text Quota:</strong></td>
                                            <td>{{ number_format($quotaText, 0, ',', '.') }} pesan</td>
                                        </tr>
                                    @endif
                                    @if($quotaMultimedia !== null)
                                        <tr>
                                            <td><strong>Multimedia Quota:</strong></td>
                                            <td>{{ number_format($quotaMultimedia, 0, ',', '.') }} pesan</td>
                                        </tr>
                                    @endif
                                    @if($quotaFreeText !== null)
                                        <tr>
                                            <td><strong>Free Text Quota:</strong></td>
                                            <td>{{ number_format($quotaFreeText, 0, ',', '.') }} pesan</td>
                                        </tr>
                                    @endif
                                    @if($quotaTotalText !== null)
                                        <tr>
                                            <td><strong>Total Text Quota:</strong></td>
                                            <td><strong>{{ number_format($quotaTotalText, 0, ',', '.') }} pesan</strong></td>
                                        </tr>
                                    @endif
                                    @if($planValue !== null)
                                        <tr>
                                            <td><strong>Paket:</strong></td>
                                            <td>{{ $planValue }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-sm btn-default" onclick="refreshQuota(event)" style="margin-top: 10px;">
                                <i class="fa fa-refresh"></i> Refresh Quota
                            </button>
                        </div>
                    @endif

                    <div class="form-group">
                        <button type="submit" class="btn btn-info">
                            <i class="fa fa-save"></i> Simpan Pengaturan WACloud
                        </button>
                        @if(!empty($wacloudApiKey) && !empty($wacloudDeviceId))
                            <button type="button" class="btn btn-default" onclick="refreshQuota(event)">
                                <i class="fa fa-refresh"></i> Cek Quota
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        <!-- Expedition Setting -->
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-truck"></i> Pengaturan Ekspedisi</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.settings.update-expeditions') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Pilih Ekspedisi Aktif</label>
                        <div class="row">
                            @foreach ($expeditions as $expedition)
                                <div class="col-md-4">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="expeditions[]" value="{{ $expedition->id }}" 
                                                {{ $expedition->is_active ? 'checked' : '' }}>
                                            {{ $expedition->name }} ({{ strtoupper($expedition->code) }})
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="help-block">Ekspedisi yang dicentang akan muncul di pilihan pengiriman pada halaman checkout.</p>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-save"></i> Simpan Pengaturan Ekspedisi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function refreshQuota(event) {
        const quotaInfo = document.getElementById('wacloud-quota-info');
        let btn;
        
        if (event && event.target) {
            btn = event.target;
        } else {
            // Find button by class or ID
            btn = document.querySelector('button[onclick*="refreshQuota"]');
        }
        
        if (!btn) {
            btn = document.querySelector('.btn-default');
        }
        
        const originalText = btn ? btn.innerHTML : '';
        
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memuat...';
        }
        
        fetch('{{ route("admin.settings.wacloud-quota") }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
            
            if (data.success && data.data) {
                const quota = data.data;
                let html = '<h4><i class="icon fa fa-check-circle"></i> Informasi Quota WACloud</h4>';
                
                // Helper function to extract numeric value from quota
                function getNumericValue(value) {
                    if (typeof value === 'number') {
                        return value;
                    }
                    if (typeof value === 'string') {
                        const num = parseFloat(value);
                        return isNaN(num) ? null : num;
                    }
                    if (value && typeof value === 'object') {
                        // Try to find numeric value in object
                        if (value.value !== undefined) return getNumericValue(value.value);
                        if (value.total !== undefined) return getNumericValue(value.total);
                        if (value.remaining !== undefined) return getNumericValue(value.remaining);
                        if (value.available !== undefined) return getNumericValue(value.available);
                        // If object has numeric keys, try first one
                        const keys = Object.keys(value);
                        if (keys.length > 0) {
                            return getNumericValue(value[keys[0]]);
                        }
                    }
                    return null;
                }
                
                // Helper function to format display value
                function formatDisplayValue(value) {
                    if (value === null || value === undefined) return 'N/A';
                    const num = getNumericValue(value);
                    if (num !== null) {
                        return num.toLocaleString('id-ID');
                    }
                    if (typeof value === 'string') {
                        return value;
                    }
                    if (typeof value === 'object') {
                        // Try to find displayable string in object
                        if (value.name) return value.name;
                        if (value.label) return value.label;
                        if (value.text) return value.text;
                    }
                    return 'N/A';
                }
                
                // Extract all quota information
                const quotaBalance = getNumericValue(quota.quota_balance || quota.quota);
                const quotaText = quota.quota_text !== undefined && quota.quota_text !== null ? getNumericValue(quota.quota_text) : null;
                const quotaMultimedia = quota.quota_multimedia !== undefined && quota.quota_multimedia !== null ? getNumericValue(quota.quota_multimedia) : null;
                const quotaFreeText = quota.quota_free_text !== undefined && quota.quota_free_text !== null ? getNumericValue(quota.quota_free_text) : null;
                const quotaTotalText = quota.quota_total_text !== undefined && quota.quota_total_text !== null ? getNumericValue(quota.quota_total_text) : null;
                
                html += '<table class="table table-bordered" style="margin-bottom: 0;"><tbody>';
                
                if (quotaBalance !== null) {
                    html += '<tr><td width="40%"><strong>Balance:</strong></td><td>Rp ' + quotaBalance.toLocaleString('id-ID') + '</td></tr>';
                }
                
                if (quotaText !== null) {
                    html += '<tr><td><strong>Text Quota:</strong></td><td>' + quotaText.toLocaleString('id-ID') + ' pesan</td></tr>';
                }
                
                if (quotaMultimedia !== null) {
                    html += '<tr><td><strong>Multimedia Quota:</strong></td><td>' + quotaMultimedia.toLocaleString('id-ID') + ' pesan</td></tr>';
                }
                
                if (quotaFreeText !== null) {
                    html += '<tr><td><strong>Free Text Quota:</strong></td><td>' + quotaFreeText.toLocaleString('id-ID') + ' pesan</td></tr>';
                }
                
                if (quotaTotalText !== null) {
                    html += '<tr><td><strong>Total Text Quota:</strong></td><td><strong>' + quotaTotalText.toLocaleString('id-ID') + ' pesan</strong></td></tr>';
                }
                
                html += '</tbody></table>';
                
                if (quota.plan !== undefined && quota.plan !== null) {
                    let planDisplay = 'N/A';
                    if (typeof quota.plan === 'string' || typeof quota.plan === 'number') {
                        planDisplay = quota.plan.toString();
                    } else if (typeof quota.plan === 'object') {
                        if (quota.plan.name) planDisplay = quota.plan.name;
                        else if (quota.plan.label) planDisplay = quota.plan.label;
                        else if (quota.plan.value) planDisplay = quota.plan.value;
                        else planDisplay = JSON.stringify(quota.plan);
                    }
                    html += '<p><strong>Paket:</strong> ' + planDisplay + '</p>';
                }
                
                html += '<button type="button" class="btn btn-sm btn-default" onclick="refreshQuota(event)" style="margin-top: 10px;">' +
                    '<i class="fa fa-refresh"></i> Refresh Quota</button>';
                
                if (quotaInfo) {
                    quotaInfo.innerHTML = html;
                    quotaInfo.classList.remove('alert-danger');
                    quotaInfo.classList.add('alert-success');
                } else {
                    const form = document.getElementById('wacloud-form');
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success';
                    alertDiv.id = 'wacloud-quota-info';
                    alertDiv.innerHTML = html;
                    const infoAlert = form.querySelector('.alert.alert-info');
                    if (infoAlert) {
                        form.insertBefore(alertDiv, infoAlert);
                    } else {
                        form.appendChild(alertDiv);
                    }
                }
            } else {
                if (quotaInfo) {
                    quotaInfo.innerHTML = '<h4><i class="icon fa fa-exclamation-circle"></i> Error</h4><p>' + 
                        (data.message || 'Gagal mendapatkan informasi quota. Pastikan API Key dan Device ID valid.') + '</p>';
                    quotaInfo.classList.remove('alert-success');
                    quotaInfo.classList.add('alert-danger');
                } else {
                    alert('Gagal mendapatkan informasi quota: ' + (data.message || 'Unknown error'));
                }
            }
        })
        .catch(error => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengambil quota.');
        });
    }
</script>
@endpush
@endsection








