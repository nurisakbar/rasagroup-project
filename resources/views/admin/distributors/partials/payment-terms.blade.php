@php
    $currentPaymentMethod = old('payment_method', $currentPaymentMethod ?? null);
    $selectedCreditTerm = old(
        'credit_terms_code',
        \App\Support\QadCreditTerms::codeFromDays($currentTermDays ?? null)
    );
@endphp
<div class="row">
    <div class="col-md-6">
        <div class="form-group @error('payment_method') has-error @enderror">
            <label for="payment_method">Cara Bayar</label>
            <select class="form-control" id="payment_method" name="payment_method">
                <option value="">-- Pilih Cara Bayar --</option>
                <option value="TOP" {{ $currentPaymentMethod == 'TOP' ? 'selected' : '' }}>TOP</option>
                <option value="CIA" {{ $currentPaymentMethod == 'CIA' ? 'selected' : '' }}>CIA</option>
            </select>
            @error('payment_method')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6" id="top_container" style="{{ $currentPaymentMethod == 'TOP' ? '' : 'display: none;' }}">
        <div class="form-group @error('credit_terms_code') has-error @enderror">
            <label for="credit_terms_code">Term Of Payment (Hari)</label>
            <select class="form-control" id="credit_terms_code" name="credit_terms_code">
                <option value="">-- Pilih jumlah hari --</option>
                @foreach(\App\Support\QadCreditTerms::forTop() as $code => $term)
                    <option value="{{ $code }}" {{ $selectedCreditTerm === $code ? 'selected' : '' }}>
                        {{ \App\Support\QadCreditTerms::optionLabel($code, $term) }}
                    </option>
                @endforeach
            </select>
            @error('credit_terms_code')
                <span class="help-block text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
