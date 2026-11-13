@extends($layout ?? 'layouts.admin')

@section('title', 'Edit Outlet')
@section('page-title', 'Edit Outlet')

@section('breadcrumb')
    <li><a href="{{ route(($routePrefix ?? 'admin.outlets') . '.index') }}">Outlet dan Reseller</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Form Edit {{ \App\Models\UserLevelChange::label($user->customerLevel()) }}</h3>
            </div>
            <form action="{{ route(($routePrefix ?? 'admin.outlets') . '.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="box-body">
                    <div class="form-group @error('qad_customer_code') has-error @enderror">
                        <label for="qad_customer_code">Kode Customer QAD (opsional)</label>
                        <input type="text" class="form-control" id="qad_customer_code" name="qad_customer_code" value="{{ old('qad_customer_code', $user->qad_customer_code) }}" placeholder="Masukkan kode QAD">
                        @error('qad_customer_code') <span class="help-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group @error('name') has-error @enderror">
                        <label for="name">Nama</label>
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

                    @php $isOutletUser = $user->role === \App\Models\User::ROLE_OUTLET; @endphp
                    <div class="form-group" style="padding: 10px; background: #fcf8e3; border: 1px solid #faebcc; border-radius: 4px;">
                            <div class="checkbox" style="margin: 0;">
                                <label>
                                    <input type="hidden" name="is_reseller" value="0">
                                    <input type="checkbox" name="is_reseller" value="1" id="is_reseller" {{ old('is_reseller', $user->is_reseller) ? 'checked' : '' }}>
                                    <strong>Tandai sebagai Reseller</strong>
                                </label>
                            </div>
                            <p class="help-block" style="margin-bottom: 0;">
                                Reseller pindah ke tab Reseller dan mendapat diskon reseller tanpa perlu kode sales: lebih dari 50 karton per grup, 15% (Syrup &amp; Sauce) dan 8% (Pulp, Powder, Cocoa). 50 karton atau kurang tidak mendapat diskon.
                                @if($isOutletUser)
                                    Sebagai reseller, diskon karton outlet dan diskon dari distributor perekrut tidak berlaku lagi; pesanan dikirim dari hub outlet. Hapus centang untuk mengembalikan ke level Outlet.
                                @endif
                                @if($user->reseller_marked_at)
                                    Ditandai sejak {{ $user->reseller_marked_at->timezone(\App\Support\Wib::TZ)->format('d M Y H:i') }} WIB.
                                @endif
                            </p>
                        </div>

                    @php $isResellerNow = (bool) old('is_reseller', $user->is_reseller); @endphp
                    @if($isOutletUser && $user->sales_code)
                        <div class="form-group" id="origin-sales-info" style="{{ $isResellerNow ? '' : 'display: none;' }}">
                            <label>Sales / Distributor Perekrut</label>
                            <p class="form-control-static">
                                {{ $user->salesPerson?->name ?? $user->referringDistributor?->name ?? '-' }} ({{ $user->sales_code }})
                                <br><small class="text-muted">Tetap tersimpan sebagai catatan, tidak dipakai untuk diskon selama berstatus reseller.</small>
                            </p>
                        </div>
                    @endif
                    @include('admin.outlets.partials.hub-field')

                    <div class="form-group @error('sales_code') has-error @enderror" id="sales-code-group" style="{{ $isResellerNow ? 'display: none;' : '' }}">
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

                    @if($levelChanges->isNotEmpty())
                        <div class="form-group">
                            <label>Riwayat Perubahan Level</label>
                            <table class="table table-condensed table-bordered" style="margin-bottom: 0;">
                                <thead><tr><th>Waktu</th><th>Perubahan</th><th>Oleh</th></tr></thead>
                                <tbody>
                                    @foreach($levelChanges as $change)
                                        <tr>
                                            <td>{{ $change->created_at?->timezone(\App\Support\Wib::TZ)->format('d M Y H:i') }} WIB</td>
                                            <td>{{ \App\Models\UserLevelChange::label($change->from_level) }} &rarr; {{ \App\Models\UserLevelChange::label($change->to_level) }}</td>
                                            <td>{{ $change->changer?->name ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="box-footer">
                    <a href="{{ route(($routePrefix ?? 'admin.outlets') . '.index', ['tab' => $user->customerLevel()]) }}" class="btn btn-default">Kembali</a>
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

        // Reseller tidak memakai kode sales: sembunyikan pilihan sales saat ditandai reseller.
        $('#is_reseller').on('change', function () {
            var reseller = $(this).is(':checked');
            $('#sales-code-group').toggle(!reseller);
            $('#origin-sales-info').toggle(reseller);
            $('#sales_code').prop('disabled', reseller);
        });
        if ($('#is_reseller').is(':checked')) {
            $('#sales_code').prop('disabled', true);
        }
    });
</script>
@endpush
