@extends('layouts.shop')

@section('title', 'Rekening Pembayaran')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.dashboard') }}">Akun Saya</a>
            <span></span> Rekening Pembayaran
        </div>
    </div>
</div>

<div class="page-content pt-50 pb-80" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm border-radius-20 overflow-hidden mb-40" style="background-color: #ffffff;">
                    <div class="card-header bg-white border-bottom-0 p-30 pb-0">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <h3 class="mb-0" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Rekening Pembayaran</h3>
                                <p class="text-muted font-sm mb-0">Rekening aktif tampil saat subdist dan outlet milik Anda membayar pesanan dengan transfer ke rekening distributor.</p>
                            </div>
                            <a href="{{ route('distributor.bank-accounts.create') }}" class="btn btn-sm px-20" style="background-color: #6A1B1B; color: #fff;">
                                <i class="fi-rs-plus mr-5"></i>Tambah Rekening
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-30">
                        @if($accounts->isEmpty())
                            <div class="alert alert-warning font-sm mb-0">Belum ada rekening. Subdist Anda belum bisa checkout sampai ada minimal satu rekening aktif.</div>
                        @else
                            @if($accounts->where('is_active', true)->isEmpty())
                                <div class="alert alert-warning font-sm">Tidak ada rekening aktif. Subdist Anda belum bisa checkout sampai ada minimal satu rekening aktif.</div>
                            @endif
                            <div class="bank-account-list">
                                @foreach($accounts as $account)
                                    <div class="bank-account-row">
                                        <div class="bank-account-info">
                                            <strong style="color: #6A1B1B;">{{ $account->bank_name }}</strong>
                                            <span class="d-block" style="font-size: 16px; font-weight: 700; color: #253D4E; letter-spacing: .5px;">{{ $account->account_number }}</span>
                                            <span class="d-block font-xs text-muted">a.n. {{ $account->account_name }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                            @if($account->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            @endif
                                            <a href="{{ route('distributor.bank-accounts.edit', $account) }}" class="btn btn-xs btn-outline-secondary py-1 px-2" title="Ubah"><i class="fi-rs-edit"></i></a>
                                            <form action="{{ route('distributor.bank-accounts.destroy', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus rekening {{ e($account->bank_name) }} {{ e($account->account_number) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-2" title="Hapus"><i class="fi-rs-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bank-account-list { border: 1px solid #ECECEC; border-radius: 12px; overflow: hidden; }
    .bank-account-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 14px 16px; }
    .bank-account-row + .bank-account-row { border-top: 1px solid #ECECEC; }
    .bank-account-info { min-width: 0; font-size: 14px; word-break: break-word; }
    @media (max-width: 575.98px) {
        .bank-account-row { flex-direction: column; align-items: flex-start; }
    }
</style>
@endsection
