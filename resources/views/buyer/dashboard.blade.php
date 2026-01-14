@extends('layouts.shop')

@section('title', 'Dashboard')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Dashboard</h2>
    
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="bi bi-bag-check text-primary" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">Total Pesanan</h5>
                    <h2>{{ $totalOrders }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="bi bi-clock-history text-warning" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">Pesanan Pending</h5>
                    <h2>{{ $pendingOrders }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <!-- Addresses Card -->
            <a href="{{ route('buyer.addresses.index') }}" class="text-decoration-none">
                <div class="card text-center h-100 border-info">
                    <div class="card-body">
                        <i class="bi bi-geo-alt text-info" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2 text-dark">Alamat Pengiriman</h5>
                        <h2 class="text-info">{{ Auth::user()->addresses()->count() }}</h2>
                        <small class="text-muted">Kelola alamat</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <!-- DRiiPPreneur Application Card -->
            <div class="card text-center h-100 border-info">
                <div class="card-body">
                    <i class="bi bi-star text-info" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-2">Mendaftar DRiiPPreneur</h5>
                    @if(Auth::user()->canApplyAsDriippreneur())
                        <a href="{{ route('buyer.driippreneur.apply') }}" class="btn btn-info btn-sm">
                            <i class="bi bi-plus-circle"></i> Daftar Sekarang
                        </a>
                    @elseif(Auth::user()->hasDriippreneurApplicationPending())
                        <span class="badge bg-warning text-dark">Menunggu Verifikasi</span>
                        <br>
                        <a href="{{ route('buyer.driippreneur.status') }}" class="btn btn-outline-info btn-sm mt-2">Lihat Status</a>
                    @elseif(Auth::user()->isDriippreneurApproved())
                        <span class="badge bg-success">Disetujui</span>
                        <br>
                        <small class="text-muted d-block mt-2">Poin: {{ number_format(Auth::user()->points, 0, ',', '.') }}</small>
                        <a href="{{ route('buyer.point-withdrawals.index') }}" class="btn btn-success btn-sm mt-2">
                            <i class="bi bi-wallet2"></i> Penarikan Poin
                        </a>
                    @elseif(Auth::user()->isDriippreneurRejected())
                        <span class="badge bg-danger">Ditolak</span>
                        <br>
                        <a href="{{ route('buyer.driippreneur.status') }}" class="btn btn-outline-danger btn-sm mt-2">Lihat Detail</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3"><i class="bi bi-grid"></i> Menu Cepat</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('buyer.orders.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-bag"></i> Pesanan Saya
                        </a>
                        <a href="{{ route('buyer.addresses.index') }}" class="btn btn-outline-info">
                            <i class="bi bi-geo-alt"></i> Alamat
                        </a>
                        <a href="{{ route('buyer.profile') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-person"></i> Profil
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-success">
                            <i class="bi bi-shop"></i> Belanja
                        </a>
                        @if(Auth::user()->canApplyAsDriippreneur())
                        <a href="{{ route('buyer.driippreneur.apply') }}" class="btn btn-outline-info">
                            <i class="bi bi-star"></i> Daftar DRiiPPreneur
                        </a>
                        @elseif(Auth::user()->driippreneur_status !== null)
                        <a href="{{ route('buyer.driippreneur.status') }}" class="btn btn-outline-info">
                            <i class="bi bi-star"></i> Status DRiiPPreneur
                        </a>
                        @endif
                        @if(Auth::user()->isDriippreneurApproved())
                        <a href="{{ route('buyer.point-withdrawals.index') }}" class="btn btn-outline-success">
                            <i class="bi bi-wallet2"></i> Penarikan Poin
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-3"><i class="bi bi-clock-history"></i> Pesanan Terbaru</h4>
    <div class="card">
        <div class="card-body">
            <!-- Filter Box -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="form-label">Filter Status</label>
                        <select id="filter-status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="orders-table" class="table table-hover mb-0" style="width: 100%;">
                    <thead class="table-light">
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_filter { display: none; }
    #orders-table_length { margin-bottom: 15px; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('buyer.dashboard') }}",
            data: function(d) {
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'order_info', name: 'order_number' },
            { data: 'date_formatted', name: 'created_at' },
            { data: 'total_formatted', name: 'total_amount' },
            { data: 'status_badge', name: 'order_status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        language: {
            processing: '<i class="bi bi-hourglass-split"></i> Memuat data...',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Tidak ada data yang cocok",
            emptyTable: "Belum ada pesanan. <a href='{{ route('products.index') }}' class='btn btn-primary btn-sm mt-2'><i class='bi bi-shop'></i> Mulai Belanja</a>",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        },
        drawCallback: function(settings) {
            // Custom empty state
            if (settings.json.recordsTotal === 0) {
                $('#orders-table tbody').html(
                    '<tr><td colspan="5" class="text-center py-4">' +
                    '<i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>' +
                    '<p class="text-muted mb-0 mt-2">Belum ada pesanan</p>' +
                    '<a href="{{ route('products.index') }}" class="btn btn-primary btn-sm mt-2">' +
                    '<i class="bi bi-shop"></i> Mulai Belanja</a>' +
                    '</td></tr>'
                );
            }
        }
    });

    // Filter handler
    $('#filter-status').change(function() {
        table.draw();
    });
});
</script>
@endpush
