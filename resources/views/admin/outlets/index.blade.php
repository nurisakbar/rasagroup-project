@extends($layout ?? 'layouts.admin')

@section('title', 'Outlet dan Reseller')
@section('page-title', 'Outlet dan Reseller')
@section('page-description', 'Outlet, reseller, dan ecom')

@section('breadcrumb')
    <li class="active">Outlet dan Reseller</li>
@endsection

@push('styles')
{{-- DataTables 3 + ColumnControl (urut & cari per kolom di judul kolom), tema Bootstrap 3. --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs@3.1.5/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-columncontrol-bs@2.1.3/css/columnControl.bootstrap.min.css">
<style>
    #users-table thead th { white-space: nowrap; }
</style>
@endpush

@section('content')
    @php
        // Tab ECOM: tanpa kolom Hub dan tanpa tombol Edit/Hapus.
        $showHub = ($canChooseHub ?? true) && $tab !== 'buyer';
        $showAction = $tab !== 'buyer';
        $tabHelp = [
            'buyer' => 'User ecom (pembeli biasa) yang mendaftar tanpa kode sales.',
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
                    @if(! empty($tabHelp[$tab]))
                        <p class="text-muted">{{ $tabHelp[$tab] }}</p>
                    @endif
                    <table id="users-table" class="table table-bordered table-striped" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Phone</th>
                                @if($showHub)
                                    <th>Hub</th>
                                @endif
                                @if($tab === 'outlet')
                                    <th>Nama Sales</th>
                                @endif
                                <th>Tanggal Daftar</th>
                                @if($tab === 'reseller')
                                    <th>Reseller Sejak</th>
                                @endif
                                @if($showAction)
                                    <th>Action</th>
                                @endif
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
<script src="https://cdn.jsdelivr.net/npm/datatables.net@3.1.5/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs@3.1.5/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-columncontrol@2.1.3/js/dataTables.columnControl.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-columncontrol-bs@2.1.3/js/columnControl.bootstrap.min.js"></script>
<script>
    $(function () {
        // Kontrol di judul kolom: tombol urut + menu (urut naik/turun & cari teks).
        var textControl = ['order', ['orderAsc', 'orderDesc', 'orderClear', 'spacer', 'searchText']];
        var searchOnly = [['searchText']];
        var orderOnly = ['order', ['orderAsc', 'orderDesc', 'orderClear']];

        var table = $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: @json(route(($routePrefix ?? 'admin.outlets') . '.index', ['tab' => $tab]))
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, columnControl: []},
                {data: 'name', name: 'name', columnControl: textControl},
                {data: 'email', name: 'email', columnControl: textControl},
                {data: 'phone', name: 'phone', type: 'string', className: 'dt-left', columnControl: textControl},
                @if($showHub)
                {data: 'hub', name: 'hub', orderable: false, columnControl: searchOnly},
                @endif
                @if($tab === 'outlet')
                {data: 'sales_name', name: 'salesPerson.name', orderable: false, searchable: false, columnControl: searchOnly},
                @endif
                {data: 'created_at', name: 'created_at', columnControl: orderOnly},
                @if($tab === 'reseller')
                {data: 'reseller_marked', name: 'reseller_marked_at', searchable: false, columnControl: orderOnly},
                @endif
                @if($showAction)
                {data: 'action', name: 'action', orderable: false, searchable: false, columnControl: []},
                @endif
            ],
            order: [[{{ ($tab === 'outlet' ? 5 : 4) + ($showHub ? 1 : 0) }}, 'desc']],
            ordering: {indicators: false, handler: false},
            language: {
                processing: "Memproses...",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ data keseluruhan)",
                zeroRecords: "Data tidak ditemukan",
                emptyTable: "Belum ada data",
                paginate: {
                    first: "Pertama",
                    previous: "Sebelumnya",
                    next: "Selanjutnya",
                    last: "Terakhir"
                },
                columnControl: {
                    order: "Urutkan",
                    orderAsc: "Urutkan A-Z / terlama",
                    orderDesc: "Urutkan Z-A / terbaru",
                    orderClear: "Hapus urutan",
                    dropdown: "Menu kolom",
                    searchClear: "Hapus pencarian",
                    buttons: {searchClear: "Hapus pencarian"},
                    search: {
                        text: {
                            contains: "Mengandung",
                            notContains: "Tidak mengandung",
                            equal: "Sama dengan",
                            notEqual: "Tidak sama dengan",
                            starts: "Diawali",
                            ends: "Diakhiri",
                            empty: "Kosong",
                            notEmpty: "Tidak kosong"
                        }
                    }
                }
            }
        });

        $(document).on('submit', '.delete-form', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
