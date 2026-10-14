@extends('layouts.warehouse')

@section('title', 'Kelola Stock')
@section('page-title', 'Kelola Stock')
@section('page-description', 'Kelola stock produk di hub Anda')

@section('breadcrumb')
    <li class="active">Kelola Stock</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap.min.css">
<style>
    .filter-stock-box {
        background: #fdfdfd;
        border-bottom: 1px solid #f4f4f4;
        padding: 12px 15px;
    }
    #stock-table_length { margin-top: 5px; margin-bottom: 12px; }
    .dataTables_filter { float: right; margin-top: 5px; margin-bottom: 12px; }
    .dataTables_filter input { width: 250px !important; display: inline-block; margin-left: 6px; }
    #stock-table th { background: #fafafa; vertical-align: middle !important; }
    #stock-table td { vertical-align: middle !important; }
    .table-responsive { border: none; }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Stock Produk - {{ $warehouse->name }}</h3>
                    <div class="box-tools">
                        @if($usesQadStock)
                            <span class="label label-primary" style="margin-right: 8px;">
                                <i class="fa fa-database"></i> QAD {{ $qadLocationCode }}
                            </span>
                        @endif
                        <button type="button" id="btnExportStock" class="btn btn-success btn-sm" style="margin-right: 5px;" title="Download Stock dan Batch ke Excel (Realtime Jubelio & QAD)">
                            <i class="fa fa-file-excel-o"></i> Download Excel
                        </button>
                        @if($usesQadStock)
                        <form action="{{ route('warehouse.stock.sync-qad') }}" method="POST" style="display: inline-block; margin-right: 5px;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Menyinkronkan...';">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm" title="Tarik data stok & batch terkini dari QAD/WMS">
                                <i class="fa fa-refresh"></i> Sinkron QAD
                            </button>
                        </form>
                        @endif
                        @if(!empty($warehouse->kode_hub))
                        <form action="{{ route('warehouse.stock.sync-jubelio') }}" method="POST" style="display: inline-block; margin-right: 5px;" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fa fa-spinner fa-spin&quot;></i> Menyinkronkan...';">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm" title="Tarik data stok terkini dari Jubelio">
                                <i class="fa fa-refresh"></i> Sinkron Jubelio
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('warehouse.stock.sync') }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Sync semua produk aktif dengan stock 0?');">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm" title="Tambahkan produk aktif baru ke hub dengan stok 0">
                                <i class="fa fa-plus-circle"></i> Sync Produk
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="box-body filter-stock-box">
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <label style="font-size: 12px; margin-bottom: 4px; color: #555;">Filter Stok Produk:</label>
                            <select id="filter-stock-status" class="form-control input-sm">
                                <option value="">Semua Stok</option>
                                <option value="low" {{ request('filter') == 'low' ? 'selected' : '' }}>Stok Rendah (≤10)</option>
                                <option value="available">Stok Tersedia (>0)</option>
                                <option value="empty">Stok Habis (0)</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6" style="padding-top: 22px;">
                            <button type="button" id="btn-reset-filter" class="btn btn-default btn-sm">
                                <i class="fa fa-refresh"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                @if($usesQadStock)
                    <div class="box-body" style="padding-bottom: 0;">
                        <div class="callout callout-info" style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                            <p style="margin: 0;">
                                Hub terhubung ke kode lokasi QAD <strong>{{ $qadLocationCode }}</strong>.
                                Kolom stok fisik menampilkan qty batch dari QAD/WMS.
                            </p>
                            @if(!empty($lastSyncAt))
                                <span class="text-muted" style="font-size: 12px; background: rgba(255,255,255,0.7); padding: 3px 8px; border-radius: 4px; color: #31708f;">
                                    <i class="fa fa-clock-o"></i> Terakhir sinkron QAD: <strong>{{ \Carbon\Carbon::parse($lastSyncAt)->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="box-body table-responsive" style="padding: 15px;">
                    <table id="stock-table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                <th width="45" class="text-center">No</th>
                                <th width="65" class="text-center">Gambar</th>
                                <th>Nama Produk</th>
                                <th width="120">Kode (SKU)</th>
                                <th width="120">Harga</th>
                                @if($usesQadStock)
                                    <th width="110" class="text-center">Stok Jubelio</th>
                                    <th width="130" class="text-center">Stok QAD</th>
                                @else
                                    <th width="110" class="text-center">Stok Jubelio</th>
                                @endif
                                <th width="130" class="text-center">Terakhir Update</th>
                                @if(! $usesQadStock)
                                    <th width="120" class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stocks as $stock)
                                @php
                                    $qadQty = (int) ($stock->qad_qty ?? 0);
                                    $qadBatches = $stock->qad_batches ?? [];
                                    $displayQty = $usesQadStock ? $qadQty : (int) $stock->stock;
                                    $productImageUrl = $stock->product->image
                                        ? $stock->product->image_url
                                        : ($stock->product->images->first()?->image_url ?: $stock->product->image_url);
                                @endphp
                                <tr class="{{ $displayQty <= 10 ? 'danger' : '' }}" data-qty="{{ $displayQty }}">
                                    <td class="text-center"></td>
                                    <td class="text-center">
                                        <img src="{{ $productImageUrl }}" alt="{{ $stock->product->display_name }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 4px;" onerror="this.onerror=null;this.src='{{ asset('logo/Rasa Connect - Logo 2_Maroon 1.png') }}';">
                                    </td>
                                    <td>
                                        <strong>{{ $stock->product->display_name }}</strong>
                                        @if($usesQadStock && count($qadBatches) > 0)
                                            <div style="margin-top: 6px; font-size: 11px; color: #555;">
                                                @foreach($qadBatches as $batch)
                                                    <div>
                                                        <em>Batch: {{ $batch['lot_serial'] }}
                                                        @if(!empty($batch['expired']))
                                                            - Exp: {{ $batch['expired'] }}
                                                        @endif
                                                        </em>
                                                        - Jml: {{ number_format($batch['qty']) }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td><code>{{ $stock->product->code ?: '-' }}</code></td>
                                    <td data-order="{{ (float) $stock->product->price }}">Rp {{ number_format($stock->product->price, 0, ',', '.') }}</td>
                                    <td class="text-center" data-order="{{ (int) $stock->stock }}">
                                        @if($stock->stock <= 10)
                                            <span class="badge bg-red" style="font-size: 13px;" title="Stok Jubelio">{{ number_format($stock->stock) }}</span>
                                            @if(! $usesQadStock)
                                                <br><small class="text-red" style="font-size: 10px;"><i class="fa fa-warning"></i> Rendah</small>
                                            @endif
                                        @elseif($stock->stock <= 50)
                                            <span class="badge bg-yellow" style="font-size: 13px;" title="Stok Jubelio">{{ number_format($stock->stock) }}</span>
                                        @else
                                            <span class="badge bg-green" style="font-size: 13px;" title="Stok Jubelio">{{ number_format($stock->stock) }}</span>
                                        @endif
                                    </td>
                                    @if($usesQadStock)
                                        <td class="text-center" data-order="{{ (int) $qadQty }}">
                                            @if($qadQty > 0)
                                                <span class="badge bg-purple" style="font-size: 13px;">{{ number_format($qadQty) }}</span>
                                                @if((int) $stock->stock !== $qadQty)
                                                    <br><small class="text-danger" style="font-size: 10px;"><i class="fa fa-warning"></i> Selisih</small>
                                                @endif
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="text-center" data-order="{{ $stock->updated_at ? $stock->updated_at->timestamp : 0 }}">
                                        {{ $stock->updated_at ? $stock->updated_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    @if(! $usesQadStock)
                                    <td class="text-center">
                                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#updateStockModal{{ $stock->id }}">
                                            <i class="fa fa-edit"></i> Update
                                        </button>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if(! $usesQadStock)
        @foreach($stocks as $stock)
            @php
                $productImageUrl = $stock->product->image
                    ? $stock->product->image_url
                    : ($stock->product->images->first()?->image_url ?: $stock->product->image_url);
            @endphp
            <!-- Update Stock Modal -->
            <div class="modal fade" id="updateStockModal{{ $stock->id }}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="{{ route('warehouse.stock.update', $stock) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Update Stock - {{ $stock->product->display_name }}</h4>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <img src="{{ $productImageUrl }}" alt="{{ $stock->product->display_name }}" class="img-responsive" style="border-radius: 5px;" onerror="this.onerror=null;this.src='{{ asset('logo/Rasa Connect - Logo 2_Maroon 1.png') }}';">
                                    </div>
                                    <div class="col-md-8">
                                        <p><strong>{{ $stock->product->display_name }}</strong></p>
                                        <p class="text-muted">Harga: Rp {{ number_format($stock->product->price, 0, ',', '.') }}</p>
                                        <hr>
                                        <div class="form-group">
                                            <label for="stock{{ $stock->id }}">Jumlah Stock Baru</label>
                                            <input type="number" class="form-control input-lg" id="stock{{ $stock->id }}" name="stock" value="{{ $stock->stock }}" min="0" required autofocus>
                                            <p class="help-block">Stock saat ini: <strong>{{ number_format($stock->stock) }}</strong> unit</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <!-- Modal Progress Export Excel -->
    <div class="modal fade" id="exportProgressModal" tabindex="-1" role="dialog" aria-labelledby="exportProgressModalLabel" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-sm" role="document" style="top: 20%; width: 420px; max-width: 90%;">
            <div class="modal-content text-center" style="border-radius: 8px; padding: 25px 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.3);">
                <div class="modal-body" style="padding: 0;">
                    <div style="margin-bottom: 15px;">
                        <i class="fa fa-refresh fa-spin fa-3x text-info" id="exportProgressIcon"></i>
                    </div>
                    <h4 style="font-weight: 600; margin-bottom: 8px;" id="exportProgressTitle">Menyiapkan Data Stok...</h4>
                    <p class="text-muted" id="exportProgressMessage" style="font-size: 13px; min-height: 40px; margin-bottom: 15px;">
                        Harap tunggu, sistem sedang memproses sinkronisasi realtime Jubelio & QAD di background...
                    </p>
                    <div class="progress" style="height: 22px; border-radius: 4px; margin-bottom: 10px; background-color: #e9ecef;">
                        <div id="exportProgressBar" class="progress-bar progress-bar-striped active progress-bar-info" role="progressbar" style="width: 5%; min-width: 2em; line-height: 22px; font-weight: bold; font-size: 12px;">
                            <span id="exportProgressPercent">5%</span>
                        </div>
                    </div>
                    <div id="exportProgressActions" style="display: none; margin-top: 15px;">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    // Custom filter stock status
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'stock-table') {
                return true;
            }
            var filterVal = $('#filter-stock-status').val();
            if (!filterVal) return true;

            var rowNode = table.row(dataIndex).node();
            var qty = parseInt($(rowNode).attr('data-qty'), 10) || 0;

            if (filterVal === 'low') {
                return qty <= 10;
            } else if (filterVal === 'available') {
                return qty > 0;
            } else if (filterVal === 'empty') {
                return qty === 0;
            }
            return true;
        }
    );

    var table = $('#stock-table').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        columnDefs: [
            { orderable: false, targets: [0, 1{{ !$usesQadStock ? ', 8' : '' }}] },
            { searchable: false, targets: [0, 1{{ !$usesQadStock ? ', 8' : '' }}] }
        ],
        order: [[2, 'asc']],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>',
            search: "Cari Produk / Kode / Batch:",
            searchPlaceholder: "Ketik untuk mencari...",
            lengthMenu: "Tampilkan _MENU_ produk",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ produk",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 produk",
            infoFiltered: "(difilter dari _MAX_ total produk)",
            zeroRecords: "Tidak ada produk yang cocok",
            emptyTable: "Tidak ada data produk tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });

    // Auto-numbering column 0
    table.on('order.dt search.dt', function () {
        let i = 1;
        table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
            this.data(i++);
        });
    }).draw();

    // Trigger initial filter if requested in query string
    @if(request('filter'))
        table.draw();
    @endif

    $('#filter-stock-status').on('change', function() {
        table.draw();
    });

    $('#btn-reset-filter').on('click', function() {
        $('#filter-stock-status').val('');
        table.search('').draw();
    });

    // Export progress handling
    var exportPollTimer = null;

    $('#btnExportStock').click(function(e) {
        e.preventDefault();
        startStockExport();
    });

    function startStockExport() {
        $('#exportProgressIcon').removeClass('fa-check text-success fa-times text-danger').addClass('fa-refresh fa-spin text-info');
        $('#exportProgressTitle').text('Memulai Export Stok...');
        $('#exportProgressMessage').text('Menghubungkan ke antrian server...');
        $('#exportProgressBar').removeClass('progress-bar-success progress-bar-danger').addClass('progress-bar-info progress-bar-striped active').css('width', '5%');
        $('#exportProgressPercent').text('5%');
        $('#exportProgressActions').hide();
        $('#exportProgressModal').modal('show');

        if (exportPollTimer) {
            clearInterval(exportPollTimer);
            exportPollTimer = null;
        }

        var exportParams = {
            _token: '{{ csrf_token() }}',
            search: table.search() || '',
            filter: $('#filter-stock-status').val() || ''
        };

        $.ajax({
            url: '{{ route("warehouse.stock.export-job") }}',
            type: 'POST',
            data: exportParams,
            success: function(res) {
                if (res && res.progress_id) {
                    pollExportProgress(res.progress_id);
                } else {
                    handleExportError('Gagal mendapatkan ID proses export.');
                }
            },
            error: function(xhr) {
                var errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menghubungi server.';
                handleExportError(errMsg);
            }
        });
    }

    function pollExportProgress(progressId) {
        var progressUrl = '{{ url("/warehouse/stock/export-progress") }}/' + progressId;

        exportPollTimer = setInterval(function() {
            $.ajax({
                url: progressUrl,
                type: 'GET',
                cache: false,
                success: function(data) {
                    if (!data) return;

                    var pct = Math.round(data.progress || 0);
                    $('#exportProgressBar').css('width', pct + '%');
                    $('#exportProgressPercent').text(pct + '%');

                    if (data.message) {
                        $('#exportProgressMessage').text(data.message);
                    }

                    if (data.status === 'completed') {
                        clearInterval(exportPollTimer);
                        exportPollTimer = null;

                        $('#exportProgressBar').removeClass('progress-bar-info progress-bar-striped active').addClass('progress-bar-success').css('width', '100%');
                        $('#exportProgressPercent').text('100%');
                        $('#exportProgressIcon').removeClass('fa-refresh fa-spin text-info').addClass('fa-check text-success');
                        $('#exportProgressTitle').text('Download Selesai!');
                        $('#exportProgressMessage').text('Mengunduh file Excel...');

                        if (data.file_url) {
                            window.location.href = data.file_url;
                        }

                        setTimeout(function() {
                            $('#exportProgressModal').modal('hide');
                        }, 1800);
                    } else if (data.status === 'failed') {
                        clearInterval(exportPollTimer);
                        exportPollTimer = null;
                        handleExportError(data.error || 'Terjadi kesalahan saat memproses data.');
                    }
                },
                error: function() {
                    // Continue polling on transient error
                }
            });
        }, 1000);
    }

    function handleExportError(msg) {
        $('#exportProgressBar').removeClass('progress-bar-info progress-bar-striped active').addClass('progress-bar-danger');
        $('#exportProgressIcon').removeClass('fa-refresh fa-spin text-info').addClass('fa-times text-danger');
        $('#exportProgressTitle').text('Export Gagal');
        $('#exportProgressMessage').text(msg);
        $('#exportProgressActions').show();
    }
});
</script>
@endpush
