{{--
    Daftar rekening distributor (checkout, halaman sukses, detail pesanan).
    Variabel: $accounts (koleksi DistributorBankAccount).
--}}
@php
    $bankLogos = [
        'BCA' => 'bca.png', 'BNI' => 'bni.png', 'BRI' => 'bri.png', 'Mandiri' => 'mandiri.png',
        'BSI (Bank Syariah Indonesia)' => 'bsi.png', 'CIMB Niaga' => 'cimb.png', 'Danamon' => 'danamon.svg',
        'Maybank Indonesia' => 'maybank.png', 'Permata' => 'permata.png', 'Bank Sinarmas' => 'sinarmas.png',
    ];
@endphp
<div class="dba-list">
    @foreach($accounts as $account)
        @php
            $logo = $bankLogos[$account->bank_name] ?? null;
            // Inisial untuk bank tanpa logo: satu kata → 3 huruf pertama (BJB, SUL), dua kata+ → huruf pertama 2 kata.
            $words = array_values(array_filter(preg_split('/[\s\/]+/', trim(preg_replace('/\(.*\)|^Bank\s+/i', '', $account->bank_name))), fn ($w) => preg_match('/[A-Za-z]/', $w)));
            $initials = strtoupper(count($words) >= 2 ? mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1) : mb_substr($words[0] ?? 'BK', 0, 3));
            $grouped = trim(chunk_split($account->account_number, 4, ' '));
        @endphp
        <div class="dba-card">
            <div class="dba-logo">
                @if($logo)
                    <img src="{{ asset('images/banks/' . $logo) }}" alt="{{ $account->bank_name }}">
                @else
                    <span>{{ $initials }}</span>
                @endif
            </div>
            <div class="dba-info">
                <div class="dba-bank">{{ $account->bank_name }}</div>
                <div class="dba-number">{{ $grouped }}</div>
                <div class="dba-name">a.n. {{ $account->account_name }}</div>
            </div>
            <button type="button" class="dba-copy" data-copy="{{ $account->account_number }}"
                    onclick="event.stopPropagation(); event.preventDefault(); var b=this; (navigator.clipboard ? navigator.clipboard.writeText(b.dataset.copy) : Promise.reject()).then(function(){ b.classList.add('is-copied'); b.querySelector('span').textContent='Tersalin'; setTimeout(function(){ b.classList.remove('is-copied'); b.querySelector('span').textContent='Salin'; }, 1600); });">
                <i class="fi-rs-copy"></i><span>Salin</span>
            </button>
        </div>
    @endforeach
</div>

@once
<style>
    .dba-list { display: flex; flex-direction: column; gap: 10px; }
    .dba-card { display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: #fff; border: 1px solid #EADFD4; border-radius: 14px; box-shadow: 0 2px 8px rgba(106, 27, 27, 0.04); transition: border-color .2s, box-shadow .2s; }
    .dba-card:hover { border-color: #D9C3B0; box-shadow: 0 6px 16px rgba(106, 27, 27, 0.08); }
    .dba-logo { flex: 0 0 52px; height: 52px; border-radius: 12px; background: #FAF6F1; border: 1px solid #F0E6DC; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .dba-logo img { max-width: 40px; max-height: 26px; object-fit: contain; }
    .dba-logo span { font-family: 'Fira Sans', sans-serif; font-weight: 800; font-size: 16px; color: #6A1B1B; letter-spacing: .5px; }
    .dba-info { flex: 1 1 auto; min-width: 0; }
    .dba-bank { font-size: 12px; font-weight: 700; color: #8A7B70; text-transform: uppercase; letter-spacing: .6px; }
    .dba-number { font-family: 'Fira Sans', sans-serif; font-size: 20px; font-weight: 700; color: #253D4E; letter-spacing: 1px; line-height: 1.3; word-break: break-all; }
    .dba-name { font-size: 13px; color: #6B7280; }
    .dba-copy { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 999px; border: 1.5px solid #6A1B1B; background: #fff; color: #6A1B1B; font-weight: 700; font-size: 13px; cursor: pointer; transition: all .2s; }
    .dba-copy:hover { background: #6A1B1B; color: #fff; }
    .dba-copy.is-copied, .dba-copy.is-copied span, .dba-copy.is-copied i { background: #3BB77E; border-color: #3BB77E; color: #fff !important; }
    .dba-copy.is-copied span, .dba-copy.is-copied i { background: transparent; }
    @media (max-width: 480px) {
        .dba-card { flex-wrap: wrap; }
        .dba-copy { width: 100%; justify-content: center; }
        .dba-number { font-size: 18px; }
    }
</style>
@endonce
