
<!-- Modal Ganti Metode Pembayaran -->
<div class="modal fade" id="changePaymentModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px;">
      <div class="modal-header">
        <h5 class="modal-title">Ganti Metode Pembayaran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('orders.change-payment', $order) }}" method="POST">
        @csrf
        <div class="modal-body p-4">
            @php $activeGateway = config('services.active_payment_gateway', 'xendit'); @endphp
            <div class="form-check mb-3 p-3 border rounded">
                <input class="form-check-input ms-1" type="radio" name="payment_method" value="manual_transfer" id="pm_manual" {{ in_array($order->payment_method, ['manual_transfer', 'transfer']) ? 'checked' : '' }}>
                <label class="form-check-label ms-2 w-100" style="cursor: pointer;" for="pm_manual">
                    <strong>Transfer Bank Manual</strong>
                </label>
            </div>

            @if($activeGateway === 'xendit')
            <div class="form-check mb-3 p-3 border rounded">
                <input class="form-check-input ms-1" type="radio" name="payment_method" value="xendit" id="pm_xendit" {{ $order->payment_method === 'xendit' ? 'checked' : '' }}>
                <label class="form-check-label ms-2 w-100" style="cursor: pointer;" for="pm_xendit">
                    <strong>Pembayaran Online (Otomatis)</strong>
                </label>
            </div>
            @elseif($activeGateway === 'faspay')
            <div class="form-check mb-3 p-3 border rounded">
                <input class="form-check-input ms-1" type="radio" name="payment_method" value="faspay_bca_va" id="pm_bca" {{ $order->payment_method === 'faspay_bca_va' ? 'checked' : '' }}>
                <label class="form-check-label ms-2 w-100" style="cursor: pointer;" for="pm_bca"><strong>BCA Virtual Account</strong></label>
            </div>
            <div class="form-check mb-3 p-3 border rounded">
                <input class="form-check-input ms-1" type="radio" name="payment_method" value="faspay_mandiri_va" id="pm_mandiri" {{ $order->payment_method === 'faspay_mandiri_va' ? 'checked' : '' }}>
                <label class="form-check-label ms-2 w-100" style="cursor: pointer;" for="pm_mandiri"><strong>Mandiri Virtual Account</strong></label>
            </div>
            <div class="form-check mb-3 p-3 border rounded">
                <input class="form-check-input ms-1" type="radio" name="payment_method" value="faspay_bri_va" id="pm_bri" {{ $order->payment_method === 'faspay_bri_va' ? 'checked' : '' }}>
                <label class="form-check-label ms-2 w-100" style="cursor: pointer;" for="pm_bri"><strong>BRI Virtual Account</strong></label>
            </div>
            <div class="form-check mb-3 p-3 border rounded">
                <input class="form-check-input ms-1" type="radio" name="payment_method" value="faspay_qris" id="pm_qris" {{ $order->payment_method === 'faspay_qris' ? 'checked' : '' }}>
                <label class="form-check-label ms-2 w-100" style="cursor: pointer;" for="pm_qris"><strong>QRIS</strong></label>
            </div>
            @endif

            <p class="text-danger font-sm mt-3 mb-0"><i class="fi-rs-info mr-5"></i><strong>Perhatian:</strong> Mengganti metode pembayaran akan membatalkan kode bayar yang lama dan membuat instruksi bayar baru.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            <button type="submit" class="btn btn-brand">Ubah Sekarang</button>
        </div>
      </form>
    </div>
  </div>
</div>
