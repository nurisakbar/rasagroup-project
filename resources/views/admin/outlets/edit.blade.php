@extends('layouts.admin')

@section('title', 'Edit Outlet')
@section('page-title', 'Edit Outlet')

@section('breadcrumb')
    <li><a href="{{ route('admin.outlets.index') }}">Data Outlet</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Form Edit Outlet</h3>
            </div>
            <form action="{{ route('admin.outlets.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="box-body">
                    <div class="form-group @error('qad_customer_code') has-error @enderror">
                        <label for="qad_customer_code">Kode Customer QAD (opsional)</label>
                        <input type="text" class="form-control" id="qad_customer_code" name="qad_customer_code" value="{{ old('qad_customer_code', $user->qad_customer_code) }}" placeholder="Masukkan kode QAD">
                        @error('qad_customer_code') <span class="help-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group @error('name') has-error @enderror">
                        <label for="name">Nama Outlet</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required placeholder="Masukkan nama outlet">
                        @error('name') <span class="help-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group @error('email') has-error @enderror">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="email@example.com">
                        @error('email') <span class="help-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group @error('phone') has-error @enderror">
                        <label for="phone">No. Telepon</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                        @error('phone') <span class="help-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group @error('password') has-error @enderror">
                        <label for="password">Password Baru (opsional)</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Isi hanya jika ingin mengubah password">
                            <span class="input-group-addon" style="cursor: pointer;" onclick="togglePasswordVisibility('password', 'icon-password')">
                                <i class="fa fa-eye" id="icon-password"></i>
                            </span>
                        </div>
                        @error('password') <span class="help-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru">
                            <span class="input-group-addon" style="cursor: pointer;" onclick="togglePasswordVisibility('password_confirmation', 'icon-password-confirmation')">
                                <i class="fa fa-eye" id="icon-password-confirmation"></i>
                            </span>
                        </div>
                    </div>

                    <div class="form-group @error('sales_code') has-error @enderror">
                        <label for="sales_code">Nama Sales (opsional)</label>
                        <select class="form-control select2" style="width: 100%;" id="sales_code" name="sales_code">
                            <option value="">-- Pilih Sales --</option>
                            @foreach($salesList as $sales)
                                <option value="{{ $sales->sales_code }}" {{ old('sales_code', $user->sales_code) == $sales->sales_code ? 'selected' : '' }}>
                                    {{ $sales->name }} ({{ $sales->sales_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('sales_code') <span class="help-block">{{ $message }}</span> @enderror
                    </div>
                </div>

                    <div class="callout callout-warning" style="margin-bottom: 0;">
                        <h4><i class="icon fa fa-warning"></i> Penting</h4>
                        <p>Mengubah data ini akan berdampak pada hak akses pengguna tersebut di dalam sistem.</p>
                    </div>
                </div>

                <div class="box-footer">
                    <a href="{{ route('admin.outlets.index') }}" class="btn btn-default">Kembali</a>
                    <button type="submit" class="btn btn-warning pull-right">Perbarui User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, iconId) {
        var input = document.getElementById(inputId);
        var icon = document.getElementById(iconId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Pilih Sales",
            allowClear: true
        });
    });
</script>
@endpush
