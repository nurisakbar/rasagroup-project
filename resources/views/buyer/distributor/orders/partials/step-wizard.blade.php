<style>
    .order-step-wizard { position: relative; padding-bottom: 20px; }
    .order-step-wizard .progress { position: absolute; left: 15%; width: 70%; top: 22px; height: 4px; z-index: 1; background-color: #e9ecef; }
    .order-step-wizard .step-item { flex: 1; z-index: 2; position: relative; }
    .order-step-wizard .step-icon { width: 48px; height: 48px; border-radius: 50%; font-size: 18px; font-weight: bold; line-height: 42px; margin: 0 auto; background-color: #fff; border: 3px solid #e9ecef; color: #adb5bd; transition: all 0.3s; }
    .order-step-wizard .step-item.active .step-icon, .order-step-wizard .step-item.completed .step-icon { background-color: #3BB77E; border-color: #3BB77E; color: #fff; }
    .order-step-wizard .step-title { margin-top: 10px; font-size: 14px; font-weight: 600; color: #adb5bd; }
    .order-step-wizard .step-item.active .step-title { color: #3BB77E; }
    .order-step-wizard .step-item.completed .step-title { color: #29A56C; }
</style>

@php
    $progressWidth = '0%';
    if ($step == 2) $progressWidth = '50%';
    if ($step == 3) $progressWidth = '100%';
@endphp

<div class="order-step-wizard mb-30">
    <div class="progress">
        <div class="progress-bar" role="progressbar" style="width: {{ $progressWidth }}; background-color: #3BB77E; transition: width 0.5s ease;"></div>
    </div>
    <div class="d-flex justify-content-between text-center">
        <!-- Step 1 -->
        <div class="step-item {{ $step >= 1 ? ($step == 1 ? 'active' : 'completed') : '' }}">
            <div class="step-icon"><i class="{{ $step > 1 ? 'fi-rs-check' : 'fi-rs-file-excel' }}"></i></div>
            <div class="step-title">Upload Excel</div>
        </div>
        <!-- Step 2 -->
        <div class="step-item {{ $step >= 2 ? ($step == 2 ? 'active' : 'completed') : '' }}">
            <div class="step-icon"><i class="{{ $step > 2 ? 'fi-rs-check' : 'fi-rs-shopping-cart' }}"></i></div>
            <div class="step-title">Keranjang Belanja</div>
        </div>
        <!-- Step 3 -->
        <div class="step-item {{ $step >= 3 ? 'active' : '' }}">
            <div class="step-icon"><i class="fi-rs-credit-card"></i></div>
            <div class="step-title">Selesaikan Transaksi</div>
        </div>
    </div>
</div>
