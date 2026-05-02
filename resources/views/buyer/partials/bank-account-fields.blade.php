{{-- Field rekening bank (dipakai di halaman Afiliasi). $formIdPrefix opsional untuk menghindari bentrok ID jika beberapa form. --}}
@php
    $fid = $formIdPrefix ?? '';
    $selectedBank = old('bank_name', Auth::user()->bank_name);
    $banks = [
        'BCA', 'BRI', 'BNI', 'Bank Mandiri', 'CIMB Niaga', 'Bank Danamon', 'PermataBank', 'Bank BTN', 'BTPN',
        'Bank Mega', 'Bank Sinarmas', 'OCBC NISP', 'Maybank Indonesia', 'PaninBank', 'Bank Bukopin', 'Bank Jago',
        'Bank Muamalat', 'BSI (Bank Syariah Indonesia)', 'Bank Syariah Mandiri', 'BCA Syariah', 'BRI Syariah',
        'BNI Syariah', 'Bank Jatim', 'Bank Jabar Banten (bjb)', 'Bank DKI', 'Bank Sumut', 'Bank Nagari',
        'Bank Kaltimtara', 'Bank Kalsel', 'Bank Kalteng', 'Bank Sulselbar', 'Bank SulutGo', 'Bank Papua',
    ];
@endphp
<div class="row g-3">
    <div class="col-md-12">
        <div class="form-group mb-20">
            <label class="form-label-affiliate-bank">Nama Bank</label>
            <select name="bank_name" id="bank_name{{ $fid }}" class="form-control custom-input-affiliate-bank select2-bank-affiliate w-100" style="width: 100%;" data-placeholder="Pilih bank">
                <option value=""></option>
                @foreach($banks as $bank)
                    <option value="{{ $bank }}" {{ $selectedBank === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                @endforeach
                @if($selectedBank && !in_array($selectedBank, $banks, true))
                    <option value="{{ $selectedBank }}" selected>{{ $selectedBank }}</option>
                @endif
            </select>
            @error('bank_name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-20">
            <label class="form-label-affiliate-bank">Nomor Rekening</label>
            <input type="text" name="bank_account_number" id="bank_account_number{{ $fid }}" class="form-control custom-input-affiliate-bank" value="{{ old('bank_account_number', Auth::user()->bank_account_number) }}" placeholder="Nomor rekening">
            @error('bank_account_number')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-20">
            <label class="form-label-affiliate-bank">Atas Nama</label>
            <input type="text" name="bank_account_name" id="bank_account_name{{ $fid }}" class="form-control custom-input-affiliate-bank" value="{{ old('bank_account_name', Auth::user()->bank_account_name) }}" placeholder="Nama sesuai buku tabungan">
            @error('bank_account_name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
