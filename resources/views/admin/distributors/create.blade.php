@extends('layouts.admin')

@section('title', 'Tambah Distributor')
@section('page-title', 'Tambah Distributor')
@section('page-description', 'Tambah distributor baru beserta hub-nya')

@section('breadcrumb')
    <li><a href="{{ route('admin.distributors.index') }}">Distributor</a></li>
    <li class="active">Tambah</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <form role="form" action="{{ route('admin.distributors.store') }}" method="POST">
                @csrf
                
                <!-- Hub Info -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-building"></i> Data Hub Distributor</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group @error('hub_name') has-error @enderror">
                            <label for="hub_name">Nama Hub <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="hub_name" name="hub_name" value="{{ old('hub_name') }}" placeholder="Contoh: Hub Distributor Jakarta" required>
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
                                            <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
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
                                    <select class="form-control" id="regency_id" name="regency_id" required>
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
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Alamat lengkap hub">{{ old('address') }}</textarea>
                            @error('address')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('hub_phone') has-error @enderror">
                            <label for="hub_phone">Nomor Telepon Hub</label>
                            <input type="text" class="form-control" id="hub_phone" name="hub_phone" value="{{ old('hub_phone') }}" placeholder="Contoh: 021-12345678">
                            @error('hub_phone')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- User Account -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-user"></i> Akun Distributor</h3>
                    </div>
                    <div class="box-body">
                        <div class="callout callout-info">
                            <p><i class="fa fa-info-circle"></i> Akun ini akan digunakan untuk login ke panel Distributor.</p>
                        </div>

                        <div class="form-group @error('user_name') has-error @enderror">
                            <label for="user_name">Nama Distributor <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_name" name="user_name" value="{{ old('user_name') }}" placeholder="Nama lengkap" required>
                            @error('user_name')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('user_email') has-error @enderror">
                                    <label for="user_email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="user_email" name="user_email" value="{{ old('user_email') }}" placeholder="Email untuk login" required>
                                    @error('user_email')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('user_phone') has-error @enderror">
                                    <label for="user_phone">No. HP</label>
                                    <input type="text" class="form-control" id="user_phone" name="user_phone" value="{{ old('user_phone') }}" placeholder="Nomor HP">
                                    @error('user_phone')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('user_password') has-error @enderror">
                                    <label for="user_password">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="user_password" name="user_password" placeholder="Minimal 8 karakter" minlength="8" required>
                                    @error('user_password')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_password_confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="user_password_confirmation" name="user_password_confirmation" placeholder="Ulangi password" required>
                                </div>
                            </div>
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
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> Simpan Distributor
                        </button>
                        <a href="{{ route('admin.distributors.index') }}" class="btn btn-default btn-lg">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi</h3>
                </div>
                <div class="box-body">
                    <p><strong>Distributor</strong> adalah mitra yang mengelola stock dan order di wilayahnya.</p>
                    <hr>
                    <p><i class="fa fa-check text-green"></i> Dapat login ke sistem</p>
                    <p><i class="fa fa-check text-green"></i> Mengelola stock produk</p>
                    <p><i class="fa fa-check text-green"></i> Memiliki hub sendiri</p>
                    <hr>
                    <p><i class="fa fa-link text-blue"></i> <strong>URL Login:</strong></p>
                    <code>{{ url('/distributor/login') }}</code>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#province_id').change(function() {
        var provinceId = $(this).val();
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
                        regencySelect.append('<option value="' + regency.id + '">' + regency.name + '</option>');
                    });
                },
                error: function() {
                    regencySelect.html('<option value="">Error loading data</option>');
                }
            });
        } else {
            regencySelect.html('<option value="">-- Pilih Kabupaten/Kota --</option>');
        }
    });
});
</script>
@endpush

