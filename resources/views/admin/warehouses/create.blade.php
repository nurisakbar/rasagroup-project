@extends('layouts.admin')

@section('title', 'Tambah Hub')
@section('page-title', 'Tambah Hub')
@section('page-description', 'Tambah hub baru beserta akun pengelolanya')

@section('breadcrumb')
    <li><a href="{{ route('admin.warehouses.index') }}">Hub</a></li>
    <li class="active">Tambah</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <form role="form" action="{{ route('admin.warehouses.store') }}" method="POST">
                @csrf
                
                <!-- Hub Info -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-building"></i> Data Hub</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group @error('name') has-error @enderror">
                            <label for="name">Nama Hub <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama hub" required>
                            <p class="help-block">Contoh: Hub Jakarta Pusat, Hub Surabaya, dll</p>
                            @error('name')
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
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Masukkan alamat lengkap hub">{{ old('address') }}</textarea>
                            @error('address')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('phone') has-error @enderror">
                                    <label for="phone">Nomor Telepon Hub</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 021-12345678">
                                    @error('phone')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                            <strong>Hub Aktif</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Deskripsi tambahan tentang hub">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- User Account -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-user"></i> Akun Pengelola Hub</h3>
                    </div>
                    <div class="box-body">
                        <div class="callout callout-info">
                            <p><i class="fa fa-info-circle"></i> Akun ini akan digunakan untuk login ke panel hub dan mengelola stock.</p>
                        </div>

                        <div class="form-group @error('user_name') has-error @enderror">
                            <label for="user_name">Nama Pengelola <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_name" name="user_name" value="{{ old('user_name') }}" placeholder="Nama lengkap pengelola" required>
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
                                    <label for="user_phone">No. HP Pengelola</label>
                                    <input type="text" class="form-control" id="user_phone" name="user_phone" value="{{ old('user_phone') }}" placeholder="Nomor HP pengelola">
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
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> Simpan Hub & Akun
                        </button>
                        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-default btn-lg">
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
                    <p><strong>Hub</strong> digunakan untuk mengelola lokasi penyimpanan dan stock produk.</p>
                    <hr>
                    <p><i class="fa fa-user text-green"></i> <strong>Akun Pengelola:</strong></p>
                    <p>Setiap hub memiliki akun pengelola yang dapat:</p>
                    <ul>
                        <li>Login ke panel hub</li>
                        <li>Melihat dashboard stock</li>
                        <li>Update jumlah stock produk</li>
                    </ul>
                    <hr>
                    <p><i class="fa fa-link text-blue"></i> <strong>URL Login Hub:</strong></p>
                    <code>{{ url('/warehouse/login') }}</code>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load regencies when province changes
    $('#province_id').change(function() {
        var provinceId = $(this).val();
        var regencySelect = $('#regency_id');
        
        regencySelect.html('<option value="">Loading...</option>');
        
        if (provinceId) {
            $.ajax({
                url: '{{ route("admin.get-regencies") }}',
                type: 'GET',
                data: { province_id: provinceId },
                success: function(data) {
                    regencySelect.html('<option value="">-- Pilih Kabupaten/Kota --</option>');
                    $.each(data, function(index, regency) {
                        regencySelect.append('<option value="' + regency.id + '">' + regency.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
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
