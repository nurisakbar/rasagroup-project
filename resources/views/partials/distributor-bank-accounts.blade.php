{{-- Daftar rekening distributor dengan tombol salin nomor rekening. Variabel: $accounts (koleksi DistributorBankAccount). --}}
<div class="distributor-bank-accounts">
    @foreach($accounts as $account)
        <div class="d-flex justify-content-between align-items-center gap-2 p-2 mb-2" style="background: #FAF6F1; border: 1px solid #E3D6C8; border-radius: 10px;">
            <div class="font-sm" style="min-width: 0;">
                <strong style="color: #6A1B1B;">{{ $account->bank_name }}</strong>
                <span class="d-block" style="font-size: 15px; font-weight: 700; color: #253D4E; letter-spacing: .5px;">{{ $account->account_number }}</span>
                <span class="d-block font-xs text-muted">a.n. {{ $account->account_name }}</span>
            </div>
            <button type="button" class="btn btn-xs py-1 px-2 flex-shrink-0" style="background-color: #6A1B1B; color: #fff;"
                    onclick="event.stopPropagation(); var b=this; navigator.clipboard && navigator.clipboard.writeText('{{ $account->account_number }}').then(function(){ var t=b.innerHTML; b.innerHTML='Tersalin!'; setTimeout(function(){ b.innerHTML=t; }, 1500); });">
                <i class="fi-rs-copy me-1"></i>Salin
            </button>
        </div>
    @endforeach
</div>
