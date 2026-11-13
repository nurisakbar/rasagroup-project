@extends($layout ?? 'layouts.admin')

@section('title', 'Outlet dan Reseller')
@section('page-title', 'Outlet dan Reseller')
@section('page-description', 'Outlet, reseller, dan ecom')

@section('breadcrumb')
    <li class="active">Outlet dan Reseller</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0;
        margin: 0;
    }
</style>
@endpush

@section('content')
    @php
        $tabHelp = [
            'outlet' => 'User yang saat mendaftar memasukkan kode sales. Edit lalu centang "Tandai sebagai Reseller" untuk mengubah levelnya menjadi reseller.',
            'reseller' => 'Outlet atau user ecom yang ditandai sebagai reseller. Diskon reseller (tanpa perlu kode sales): lebih dari 50 karton per grup mendapat 15% untuk DRiPP Syrup & Sauce dan 8% untuk DRiPP Pulp, Powder, Cocoa.',
            'buyer' => 'User ecom (pembeli biasa) yang mendaftar tanpa kode sales. Edit lalu centang "Tandai sebagai Reseller" untuk memindahkannya ke tab RESELLER.',
        ];
    @endphp
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    @foreach($tabLabels as $key => $label)
                        <li class="{{ $tab === $key ? 'active' : '' }}">
                            <a href="{{ route(($routePrefix ?? 'admin.outlets') . '.index', ['tab' => $key]) }}">{{ $label }} <span class="badge">{{ number_format($counts[$key] ?? 0, 0, ',', '.') }}</span></a>
                        </li>
                    @endforeach
                    @if($tab === 'outlet')
                        <li class="pull-right" style="padding: 6px 10px 0 0;">
                            <a href="{{ route(($routePrefix ?? 'admin.outlets') . '.create') }}" class="btn btn-primary btn-sm" style="color: #fff; padding: 5px 10px;">
                                <i class="fa fa-plus"></i> Tambah Outlet
                            </a>
                        </li>
                    @endif
                </ul>
                <div class="tab-content">
                    <p class="text-muted">{{ $tabHelp[$tab] }}</p>
                    @if($tab === 'outlet')
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-4">
                            <label for="filter_sales">Filter by Sales:</label>
                            <select id="filter_sales" class="form-control select2" style="width: 100%;">
                                <option value="">Semua Sales</option>
                                <option value="none">-- Tanpa Sales --</option>
                                @foreach($salesList as $sales)
                                    <option value="{{ $sales->sales_code }}">{{ $sales->name }} ({{ $sales->sales_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif

                    <table id="users-table" class="table table-bordered table-striped" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Phone</th>
                                @if($canChooseHub ?? true)
                                    <th>Hub</th>
                                @endif
                                @if($tab === 'outlet')
                                    <th>Nama Sales</th>
                                @endif
                                @if($tab === 'reseller')
                                    <th>Asal</th>
                                @endif
                                <th>Tanggal Daftar</th>
                                @if($tab === 'reseller')
                                    <th>Reseller Sejak</th>
                                @endif
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script>
    $(function () {
        var table = $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: @json(route(($routePrefix ?? 'admin.outlets') . '.index', ['tab' => $tab])),
                data: function (d) {
                    d.sales_code = $('#filter_sales').val() || '';
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'phone', name: 'phone'},
                @if($canChooseHub ?? true)
                {data: 'hub', name: 'hub', orderable: false},
                @endif
                @if($tab === 'outlet')
                {data: 'sales_name', name: 'salesPerson.name', orderable: false, searchable: false},
                @endif
                @if($tab === 'reseller')
                {data: 'origin', name: 'role', searchable: false},
                @endif
                {data: 'created_at', name: 'created_at'},
                @if($tab === 'reseller')
                {data: 'reseller_marked', name: 'reseller_marked_at', searchable: false},
                @endif
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[{{ ($tab === 'buyer' ? 4 : 5) + (($canChooseHub ?? true) ? 1 : 0) }}, 'desc']],
            language: {
                processing: "Memproses...",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ data keseluruhan)",
                paginate: {
                    first: "Pertama",
                    previous: "Sebelumnya",
                    next: "Selanjutnya",
                    last: "Terakhir"
                }
            }
        });

        $('#filter_sales').select2({
            placeholder: "Pilih Sales",
            allowClear: true
        });

        $('#filter_sales').on('change', function() {
            table.draw();
        });

        $(document).on('submit', '.delete-form', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
