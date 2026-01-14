@extends('layouts.shop')

@section('title', 'Alamat Pengiriman')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('buyer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Alamat Pengiriman</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-geo-alt"></i> Alamat Pengiriman</h2>
        <a href="{{ route('buyer.addresses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Alamat
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($addresses->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-geo-alt text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Belum Ada Alamat</h5>
                <p class="text-muted">Tambahkan alamat pengiriman untuk memudahkan proses checkout.</p>
                <a href="{{ route('buyer.addresses.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Alamat Pertama
                </a>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($addresses as $address)
                <div class="col-md-6 mb-3">
                    <div class="card h-100 {{ $address->is_default ? 'border-primary' : '' }}">
                        <div class="card-header d-flex justify-content-between align-items-center {{ $address->is_default ? 'bg-primary text-white' : 'bg-light' }}">
                            <div>
                                <strong>{{ $address->label }}</strong>
                                @if($address->is_default)
                                    <span class="badge bg-light text-primary ms-2">Utama</span>
                                @endif
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm {{ $address->is_default ? 'btn-light' : 'btn-outline-secondary' }}" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('buyer.addresses.edit', $address) }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    </li>
                                    @if(!$address->is_default)
                                        <li>
                                            <form action="{{ route('buyer.addresses.set-default', $address) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-star"></i> Jadikan Utama
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('buyer.addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('Hapus alamat ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title mb-1">{{ $address->recipient_name }}</h6>
                            <p class="text-muted mb-2">
                                <i class="bi bi-telephone"></i> {{ $address->phone }}
                            </p>
                            <p class="card-text mb-2">
                                {{ $address->address_detail }}
                            </p>
                            <p class="card-text text-muted small mb-0">
                                <i class="bi bi-geo"></i> 
                                {{ $address->village?->name }}, 
                                Kec. {{ $address->district?->name }}, 
                                {{ $address->regency?->name }}, 
                                {{ $address->province?->name }}
                                @if($address->postal_code)
                                    {{ $address->postal_code }}
                                @endif
                            </p>
                            @if($address->notes)
                                <p class="card-text mt-2 small">
                                    <i class="bi bi-info-circle"></i> {{ $address->notes }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('buyer.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection

