<div class="modal fade" id="syncModal" tabindex="-1" role="dialog" aria-labelledby="syncModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document" style="top: 25%;">
        <div class="modal-content text-center" style="border-radius: 10px; padding: 20px;">
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <i class="fa fa-refresh fa-spin fa-3x text-info" id="syncModalIcon"></i>
                </div>
                <h4 style="font-weight: 600;" id="syncModalTitle">Sinkronisasi Sedang Berjalan</h4>
                <p class="text-muted" id="syncModalMessage">Harap tunggu, proses sinkronisasi sedang berjalan di background.</p>
                <div class="progress" style="margin-top: 20px; height: 22px; border-radius: 4px; margin-bottom: 0;">
                    <div id="syncProgressBar" class="progress-bar progress-bar-striped active progress-bar-info" role="progressbar" style="width: 0%; min-width: 2em;">
                        <span id="syncProgressText">0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
var masterSyncPollTimer = null;

function showMasterSyncModal(title, message) {
    $('#syncModalTitle').text(title || 'Sinkronisasi Sedang Berjalan');
    $('#syncModalMessage').text(message || 'Harap tunggu, proses sinkronisasi sedang berjalan di background.');
    $('#syncModalIcon').removeClass('fa-check text-success fa-times text-danger').addClass('fa-refresh fa-spin text-info');
    updateMasterSyncProgressBar(0, 'Menunggu antrian...');
    $('#syncModal').modal('show');
}

function updateMasterSyncProgressBar(progress, message) {
    var rounded = Math.round(progress || 0);
    $('#syncProgressBar')
        .removeClass('progress-bar-danger progress-bar-success')
        .addClass('progress-bar-info active progress-bar-striped')
        .css('width', rounded + '%');
    $('#syncProgressText').text(rounded + '%');

    if (message) {
        $('#syncModalMessage').text(message);
    }
}

function stopMasterSyncPolling() {
    if (masterSyncPollTimer) {
        clearInterval(masterSyncPollTimer);
        masterSyncPollTimer = null;
    }
}

function finishMasterSyncSuccess(summary, options) {
    stopMasterSyncPolling();
    updateMasterSyncProgressBar(100, 'Selesai');
    $('#syncProgressBar').removeClass('progress-bar-info progress-bar-striped active').addClass('progress-bar-success');
    $('#syncModalIcon').removeClass('fa-refresh fa-spin text-info').addClass('fa-check text-success');

    setTimeout(function() {
        $('#syncModal').modal('hide');

        swal({
            title: 'Berhasil!',
            text: summary || 'Sinkronisasi selesai.',
            type: 'success',
            timer: 5000,
            showConfirmButton: true
        });

        if (typeof options.onComplete === 'function') {
            options.onComplete();
        } else if ($('#warehouses-table').length && $.fn.DataTable.isDataTable('#warehouses-table')) {
            $('#warehouses-table').DataTable().draw(false);
        } else if ($('#products-table').length && $.fn.DataTable.isDataTable('#products-table')) {
            $('#products-table').DataTable().draw(false);
        }
    }, 600);
}

function finishMasterSyncError(errorMessage) {
    stopMasterSyncPolling();
    $('#syncProgressBar').removeClass('progress-bar-info progress-bar-striped active').addClass('progress-bar-danger');
    $('#syncModalIcon').removeClass('fa-refresh fa-spin text-info').addClass('fa-times text-danger');

    setTimeout(function() {
        $('#syncModal').modal('hide');

        swal({
            title: 'Gagal!',
            text: errorMessage || 'Sinkronisasi gagal.',
            type: 'error'
        });
    }, 600);
}

function pollMasterSyncStatus(syncId, options) {
    stopMasterSyncPolling();

    var statusUrl = @json(route('admin.master-sync.status', ['syncId' => '__SYNC_ID__'])).replace('__SYNC_ID__', syncId);

    masterSyncPollTimer = setInterval(function() {
        $.ajax({
            url: statusUrl,
            method: 'GET',
            success: function(data) {
                if (data.status === 'queued' || data.status === 'running') {
                    updateMasterSyncProgressBar(data.progress || 0, data.message || 'Sedang memproses...');
                    return;
                }

                if (data.status === 'completed') {
                    finishMasterSyncSuccess(data.summary, options || {});
                    return;
                }

                if (data.status === 'failed') {
                    finishMasterSyncError(data.error || 'Sinkronisasi gagal.');
                }
            },
            error: function() {
                finishMasterSyncError('Gagal membaca status sinkronisasi.');
            }
        });
    }, 1500);
}

function startMasterSync(type, options) {
    options = options || {};

    showMasterSyncModal(options.title, options.message);

    $.ajax({
        url: @json(route('admin.master-sync.dispatch')),
        method: 'POST',
        data: {
            type: type,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (!response.sync_id) {
                finishMasterSyncError('Respons server tidak valid.');
                return;
            }

            pollMasterSyncStatus(response.sync_id, options);
        },
        error: function(xhr) {
            var message = 'Gagal memulai sinkronisasi.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            finishMasterSyncError(message);
        }
    });
}

// Alias untuk partial lama
function showSyncModal(title, message) {
    showMasterSyncModal(title, message);
}

function confirmSyncQadJubelio() {
    swal({
        title: "Sinkronisasi Gabungan QAD + Jubelio",
        text: "Proses ini akan mensinkronkan hub, produk, deskripsi & foto, serta stok dari Jubelio dan QAD. Proses berjalan di background dan bisa memakan waktu beberapa menit.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f39c12",
        confirmButtonText: "Ya, Sinkronkan!",
        cancelButtonText: "Batal",
        closeOnConfirm: true
    }, function() {
        startMasterSync('qad_jubelio', {
            title: 'Sinkronisasi QAD + Jubelio',
            message: 'Memulai sinkronisasi hub, produk, deskripsi & foto, dan stok di background...'
        });
    });
}
</script>
@endpush
@endonce
