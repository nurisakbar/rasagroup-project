@extends('layouts.admin')

@section('title', 'Edit Hub')
@section('page-title', 'Edit Hub')
@section('page-description', 'Edit data hub')

@section('breadcrumb')
    <li><a href="{{ route('admin.warehouses.index') }}">Hub</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Edit Hub</h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <form role="form" action="{{ route('admin.warehouses.update', $warehouse) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        <div class="form-group">
                            <label for="id">ID (UUID)</label>
                            <input type="text" class="form-control" id="id" value="{{ $warehouse->id }}" disabled>
                            <p class="help-block">UUID tidak dapat diubah</p>
                        </div>

                        <div class="form-group @error('name') has-error @enderror">
                            <label for="name">Nama Hub <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $warehouse->name) }}" placeholder="Masukkan nama hub" required>
                            <p class="help-block">Contoh: Hub Jakarta Pusat, Hub Surabaya, dll</p>
                            @error('name')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('province_id') has-error @enderror">
                                    <label for="province_id">Provinsi <span class="text-danger">*</span></label>
                                    <select class="form-control" id="province_id" name="province_id" required>
                                        <option value="">-- Pilih Provinsi --</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->id }}" {{ old('province_id', $warehouse->province_id) == $province->id ? 'selected' : '' }}>
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('province_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('regency_id') has-error @enderror">
                                    <label for="regency_id">Kabupaten/Kota <span class="text-danger">*</span></label>
                                    <select class="form-control" id="regency_id" name="regency_id" required>
                                        <option value="">-- Pilih Kabupaten/Kota --</option>
                                        @foreach($regencies as $regency)
                                            <option value="{{ $regency->id }}" {{ old('regency_id', $warehouse->regency_id) == $regency->id ? 'selected' : '' }}>
                                                {{ $regency->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('regency_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group @error('address') has-error @enderror">
                            <label for="address">Alamat Lengkap</label>
                            <textarea class="form-control" id="address" name="address" rows="3" placeholder="Masukkan alamat lengkap hub">{{ old('address', $warehouse->address) }}</textarea>
                            @error('address')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('phone') has-error @enderror">
                            <label for="phone">Nomor Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $warehouse->phone) }}" placeholder="Contoh: 021-12345678">
                            @error('phone')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Deskripsi tambahan tentang hub">{{ old('description', $warehouse->description) }}</textarea>
                            @error('description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
                                Hub Aktif
                            </label>
                            <p class="help-block">Hub yang tidak aktif tidak akan ditampilkan dalam pilihan</p>
                        </div>
                    </div>
                    <!-- /.box-body -->

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update
                        </button>
                        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
            <!-- /.box -->
        </div>

        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi</h3>
                </div>
                <div class="box-body">
                    <p>Edit data hub sesuai kebutuhan.</p>
                    <hr>
                    <dl>
                        <dt>Dibuat Pada</dt>
                        <dd>{{ $warehouse->created_at->format('d M Y, H:i') }}</dd>
                        <dt>Diupdate Pada</dt>
                        <dd>{{ $warehouse->updated_at->format('d M Y, H:i') }}</dd>
                    </dl>
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
