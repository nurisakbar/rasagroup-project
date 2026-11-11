@extends('layouts.shop')

@section('title', 'Diskon Outlet - ' . $outlet->name)

@php
    $maxLabel = rtrim(rtrim(number_format($discountMax, 2, ',', '.'), '0'), ',');
    $canEdit = $discountMax > 0;
@endphp

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> <a href="{{ route('buyer.outlet-referral.index') }}">Rekrut Outlet</a>
            <span></span> Diskon {{ $outlet->name }}
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
                        <a href="{{ route('buyer.outlet-referral.index') }}" class="font-sm">&larr; Kembali ke daftar outlet</a>
                        <h3 class="mb-0 mt-10" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Diskon untuk {{ $outlet->name }}</h3>
                        <p class="text-muted font-sm mb-0">
                            Diskon per kategori ini berlaku saat {{ $outlet->name }} belanja dari hub Anda{{ $user->warehouse ? ' (' . $user->warehouse->name . ')' : '' }}.
                            Untuk item yang mendapat diskon ini, diskon pembelian per karton (MOQ) tidak berlaku.
                        </p>
                    </div>
                    <div class="card-body p-30">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        @if(! $canEdit)
                            <div class="alert alert-warning mb-0">Admin belum mengizinkan Anda memberi diskon ke outlet. Hubungi admin RasaConnect untuk mengaktifkannya.</div>
                        @else
                            <div class="p-20 border-radius-15 mb-25" style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;">
                                <span class="font-sm">Diskon maksimal yang boleh Anda berikan: <strong>{{ $maxLabel }}%</strong>. Kosongkan atau isi 0 untuk tanpa diskon.</span>
                            </div>

                            <form method="POST" action="{{ route('buyer.outlet-referral.discounts.update', $outlet) }}">
                                @csrf
                                @forelse($brands as $brand)
                                    @continue($brand->categories->isEmpty())
                                    <h5 class="mb-10 mt-20" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #6A1B1B;">{{ $brand->name }}</h5>
                                    <div class="table-responsive">
                                        <table class="table font-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Kategori</th>
                                                    <th style="width: 180px;">Diskon (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($brand->categories as $category)
                                                    @php
                                                        $key = $brand->id . '|' . $category->id;
                                                        $value = old('discounts.' . $brand->id . '.' . $category->id, $discounts[$key] ?? '');
                                                    @endphp
                                                    <tr>
                                                        <td class="align-middle">{{ $category->name }}</td>
                                                        <td>
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" step="0.01" min="0" max="{{ $discountMax }}" class="form-control"
                                                                       name="discounts[{{ $brand->id }}][{{ $category->id }}]"
                                                                       value="{{ $value !== '' ? rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') : '' }}"
                                                                       placeholder="0" aria-label="Diskon {{ $brand->name }} {{ $category->name }}">
                                                                <span class="input-group-text">%</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @empty
                                    <p class="text-muted font-sm">Belum ada brand aktif.</p>
                                @endforelse

                                <div class="mt-25">
                                    <button type="submit" class="btn px-30" style="background-color: #6A1B1B; color: #fff;">Simpan Diskon</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
