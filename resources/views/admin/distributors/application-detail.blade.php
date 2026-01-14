@extends('layouts.admin')

@section('title', 'Review Pengajuan')
@section('page-title', 'Review Pengajuan Distributor')
@section('page-description', 'Verifikasi data pengajuan')

@section('breadcrumb')
    <li><a href="{{ route('admin.distributors.index') }}">Distributor</a></li>
    <li><a href="{{ route('admin.distributors.applications') }}">Pengajuan</a></li>
    <li class="active">Review</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <!-- Applicant Info -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-user"></i> Data Pemohon</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-condensed">
                        <tr>
                            <th>Nama</th>
                            <td><strong>{{ $user->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>No. HP</th>
                            <td>{{ $user->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>No. KTP</th>
                            <td><code style="font-size: 14px;">{{ $user->no_ktp }}</code></td>
                        </tr>
                        <tr>
                            <th>No. NPWP</th>
                            <td><code style="font-size: 14px;">{{ $user->no_npwp }}</code></td>
                        </tr>
                        <tr>
                            <th>Provinsi</th>
                            <td>{{ $user->distributorProvince->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kabupaten/Kota</th>
                            <td>{{ $user->distributorRegency->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Alamat Usaha</th>
                            <td>{{ $user->distributor_address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Pengajuan</th>
                            <td>{{ $user->distributor_applied_at->format('d M Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Bergabung Sejak</th>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Reject Form -->
            <div class="box box-danger collapsed-box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-times"></i> Tolak Pengajuan</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
                <form action="{{ route('admin.distributors.reject', $user) }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label for="rejection_reason">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Yakin ingin menolak pengajuan ini?')">
                            <i class="fa fa-times"></i> Tolak Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Approve Form -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-check"></i> Setujui & Buat Hub Distributor</h3>
                </div>
                <form action="{{ route('admin.distributors.approve', $user) }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="callout callout-info">
                            <p><i class="fa fa-info-circle"></i> Setelah disetujui, sistem akan membuat Hub baru untuk distributor ini dan mengaktifkan akun mereka.</p>
                        </div>

                        <div class="form-group @error('hub_name') has-error @enderror">
                            <label for="hub_name">Nama Hub <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="hub_name" name="hub_name" 
                                   value="{{ old('hub_name', 'Hub Distributor ' . $user->name) }}" required>
                            @error('hub_name')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('province_id') has-error @enderror">
                                    <label for="province_id">Provinsi <span class="text-danger">*</span></label>
                                    <select class="form-control" id="province_id" name="province_id" required>
                                        <option value="">-- Pilih Provinsi --</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->id }}" {{ old('province_id', $user->distributor_province_id) == $province->id ? 'selected' : '' }}>
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('province_id')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('regency_id') has-error @enderror">
                                    <label for="regency_id">Kabupaten/Kota <span class="text-danger">*</span></label>
                                    <select class="form-control" id="regency_id" name="regency_id" required data-selected="{{ old('regency_id', $user->distributor_regency_id) }}">
                                        <option value="">-- Pilih Kabupaten/Kota --</option>
                                    </select>
                                    @error('regency_id')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('address') has-error @enderror">
                            <label for="address">Alamat Lengkap</label>
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Alamat lengkap hub">{{ old('address', $user->distributor_address) }}</textarea>
                            @error('address')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('hub_phone') has-error @enderror">
                            <label for="hub_phone">Nomor Telepon Hub</label>
                            <input type="text" class="form-control" id="hub_phone" name="hub_phone" 
                                   value="{{ old('hub_phone', $user->phone) }}" placeholder="Nomor telepon hub">
                            @error('hub_phone')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('price_level_id') has-error @enderror">
                            <label for="price_level_id">Level Harga</label>
                            <select class="form-control" id="price_level_id" name="price_level_id">
                                <option value="">-- Pilih Level Harga (Opsional) --</option>
                                @foreach($priceLevels as $priceLevel)
                                    <option value="{{ $priceLevel->id }}" {{ old('price_level_id') == $priceLevel->id ? 'selected' : '' }}>
                                        {{ $priceLevel->name }} (Diskon: {{ number_format($priceLevel->discount_percentage, 2) }}%)
                                    </option>
                                @endforeach
                            </select>
                            <span class="help-block">Pilih level harga untuk memberikan harga khusus pada distributor ini. Jika tidak dipilih, distributor akan menggunakan harga normal.</span>
                            @error('price_level_id')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Yakin ingin menyetujui pengajuan ini?')">
                            <i class="fa fa-check"></i> Setujui Pengajuan
                        </button>
                        <a href="{{ route('admin.distributors.applications') }}" class="btn btn-default btn-lg">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function loadRegencies(provinceId, selectedRegencyId) {
        var regencySelect = $('#regency_id');
        
        regencySelect.html('<option value="">Loading...</option>');
        
        if (provinceId) {
            $.ajax({
                url: '{{ route("admin.distributors.get-regencies") }}',
                type: 'GET',
                data: { province_id: provinceId },
                success: function(data) {
                    regencySelect.html('<option value="">-- Pilih Kabupaten/Kota --</option>');
                    $.each(data, function(index, regency) {
                        var selected = (selectedRegencyId && regency.id == selectedRegencyId) ? 'selected' : '';
                        regencySelect.append('<option value="' + regency.id + '" ' + selected + '>' + regency.name + '</option>');
                    });
                },
                error: function() {
                    regencySelect.html('<option value="">Error loading data</option>');
                }
            });
        } else {
            regencySelect.html('<option value="">-- Pilih Kabupaten/Kota --</option>');
        }
    }

    $('#province_id').change(function() {
        loadRegencies($(this).val(), null);
    });

    // Auto-load regencies if province is pre-selected
    var initialProvinceId = $('#province_id').val();
    var initialRegencyId = $('#regency_id').data('selected');
    if (initialProvinceId) {
        loadRegencies(initialProvinceId, initialRegencyId);
    }
});
</script>
@endpush

