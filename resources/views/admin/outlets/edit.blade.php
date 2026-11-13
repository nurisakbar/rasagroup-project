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
                    @php
                        $recruiter = $user->salesPerson?->name ?? $user->referringDistributor?->name;
                    @endphp
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" class="form-control" value="{{ $user->email }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" class="form-control" value="{{ $user->phone ?: '-' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Hub</label>
                        <input type="text" class="form-control" value="{{ $user->hub?->name ?? '-' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Sales / Distributor Perekrut</label>
                        <input type="text" class="form-control" value="{{ $user->sales_code ? (($recruiter ?? '-') . ' (' . $user->sales_code . ')') : '-' }}" readonly>
                    </div>

                    <div class="form-group" style="padding: 10px; background: #fcf8e3; border: 1px solid #faebcc; border-radius: 4px;">
                        <div class="checkbox" style="margin: 0;">
                            <label>
                                <input type="hidden" name="is_reseller" value="0">
                                <input type="checkbox" name="is_reseller" value="1" id="is_reseller" {{ old('is_reseller', $user->is_reseller) ? 'checked' : '' }}>
                                <strong>Tandai sebagai Reseller</strong>
                            </label>
                        </div>
                        @if($user->reseller_marked_at)
                            <p class="help-block" style="margin-bottom: 0;">
                                Ditandai sejak {{ $user->reseller_marked_at->timezone(\App\Support\Wib::TZ)->format('d M Y H:i') }} WIB.
                            </p>
                        @endif
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
