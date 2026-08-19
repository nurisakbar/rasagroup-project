@extends('layouts.admin')

@section('title', 'Edit Admin')
@section('page-title', 'Edit Admin')

@section('breadcrumb')
    <li><a href="{{ route('admin.users.index') }}">Admin Users</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Form Edit User</h3>
            </div>
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="box-body">
                    <div class="form-group @error('name') has-error @enderror">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required placeholder="Masukkan nama lengkap">
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

                    <div class="form-group @error('role') has-error @enderror">
                        <label for="role">Role User</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="ecommerce" {{ old('role', $user->role) == 'ecommerce' ? 'selected' : '' }}>eCommerce</option>
                            <option value="brand_marketing" {{ old('role', $user->role) == 'brand_marketing' ? 'selected' : '' }}>Brand Marketing</option>
                            <option value="finance" {{ old('role', $user->role) == 'finance' ? 'selected' : '' }}>Finance</option>
                            <option value="sales_admin" {{ old('role', $user->role) == 'sales_admin' ? 'selected' : '' }}>Sales Admin</option>
                            <option value="customer_service" {{ old('role', $user->role) == 'customer_service' ? 'selected' : '' }}>Customer Service</option>
                            <option value="it_application" {{ old('role', $user->role) == 'it_application' ? 'selected' : '' }}>IT Application</option>
                            <option value="inventory_manager" {{ old('role', $user->role) == 'inventory_manager' ? 'selected' : '' }}>Inventory Manager</option>
                        </select>
                        @error('role') <span class="help-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="callout callout-warning" style="margin-bottom: 0;">
                        <h4><i class="icon fa fa-warning"></i> Penting</h4>
                        <p>Mengubah data ini akan berdampak pada hak akses pengguna tersebut di dalam sistem.</p>
                    </div>
                </div>

                <div class="box-footer">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-default">Kembali</a>
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
</script>
@endpush
