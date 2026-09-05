<?php
$files = [
    '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/checkout/success.blade.php' => 'buyer.orders.change-payment',
    '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/buyer/distributor/orders/success.blade.php' => 'distributor.orders.change-payment',
    '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/buyer/orders/show.blade.php' => 'buyer.orders.change-payment',
];

$modalHtml = <<<HTML
<!-- Modal Ganti Metode Pembayaran -->
<div class="modal fade" id="changePaymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">Ganti Metode Pembayaran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('ROUTE_PLACEHOLDER', \$order) }}" method="POST">
        @csrf
        <div class="modal-body p-4">
            @php \$activeGateway = config('services.active_payment_gateway', 'xendit'); @endphp
            
            @if(\$activeGateway === 'xendit')
                <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_xendit" value="xendit" {{ \$order->payment_method === 'xendit' ? 'checked' : '' }} required>
                    <label class="form-check-label fw-bold w-100 ms-2" for="pay_xendit" style="cursor: pointer;">
                        <i class="fi-rs-credit-card mr-5"></i> Xendit (Virtual Account, QRIS, E-Wallet)
                    </label>
                </div>
            @endif

            @if(\$activeGateway === 'faspay')
                <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_faspay_qris" value="faspay_qris" {{ \$order->payment_method === 'faspay_qris' ? 'checked' : '' }} required>
                    <label class="form-check-label fw-bold w-100 ms-2" for="pay_faspay_qris" style="cursor: pointer;">
                        <i class="fi-rs-smartphone mr-5"></i> QRIS (Faspay)
                    </label>
                </div>
                <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_faspay_bca" value="faspay_bca_va" {{ \$order->payment_method === 'faspay_bca_va' ? 'checked' : '' }} required>
                    <label class="form-check-label fw-bold w-100 ms-2" for="pay_faspay_bca" style="cursor: pointer;">
                        <i class="fi-rs-bank mr-5"></i> BCA Virtual Account (Faspay)
                    </label>
                </div>
                <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_faspay_mandiri" value="faspay_mandiri_va" {{ \$order->payment_method === 'faspay_mandiri_va' ? 'checked' : '' }} required>
                    <label class="form-check-label fw-bold w-100 ms-2" for="pay_faspay_mandiri" style="cursor: pointer;">
                        <i class="fi-rs-bank mr-5"></i> Mandiri Virtual Account (Faspay)
                    </label>
                </div>
                <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_faspay_bri" value="faspay_bri_va" {{ \$order->payment_method === 'faspay_bri_va' ? 'checked' : '' }} required>
                    <label class="form-check-label fw-bold w-100 ms-2" for="pay_faspay_bri" style="cursor: pointer;">
                        <i class="fi-rs-bank mr-5"></i> BRI Virtual Account (Faspay)
                    </label>
                </div>
                <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                    <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_faspay_bni" value="faspay_bni_va" {{ \$order->payment_method === 'faspay_bni_va' ? 'checked' : '' }} required>
                    <label class="form-check-label fw-bold w-100 ms-2" for="pay_faspay_bni" style="cursor: pointer;">
                        <i class="fi-rs-bank mr-5"></i> BNI Virtual Account (Faspay)
                    </label>
                </div>
            @endif

            <div class="form-check mb-3 p-3 border rounded" style="border-color: #ECECEC !important; border-radius: 15px !important;">
                <input class="form-check-input ms-1" type="radio" name="payment_method" id="pay_manual" value="manual_transfer" {{ in_array(\$order->payment_method, ['manual_transfer', 'transfer']) ? 'checked' : '' }} required>
                <label class="form-check-label fw-bold w-100 ms-2" for="pay_manual" style="cursor: pointer;">
                    <i class="fi-rs-document-text mr-5"></i> Transfer Bank Manual (Upload Bukti)
                </label>
            </div>
            
            <p class="text-danger font-sm mt-3 mb-0"><i class="fi-rs-info mr-5"></i><strong>Perhatian:</strong> Mengganti metode pembayaran akan membatalkan kode bayar yang lama dan membuat instruksi bayar baru.</p>
        </div>
        <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-brand rounded-pill">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>
HTML;

foreach ($files as $file => $route) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);

    // Remove existing modal if any
    if (preg_match('/<!-- (Modal Ganti|Change Payment Modal).*?<\/div>\s*<\/div>\s*<\/div>/s', $content)) {
        $content = preg_replace('/<!-- (Modal Ganti|Change Payment Modal).*?<\/div>\s*<\/div>\s*<\/div>/s', '', $content);
    }
    
    // Add new unified modal at the end before @endsection
    $finalModal = str_replace('ROUTE_PLACEHOLDER', $route, $modalHtml);
    $content = str_replace('@endsection', "\n" . $finalModal . "\n@endsection", $content);
    
    file_put_contents($file, $content);
}
echo "Unified modal styling.\n";
