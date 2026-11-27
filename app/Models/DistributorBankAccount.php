<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rekening distributor untuk pembayaran subdist dan outlet milik distributor (metode distributor_transfer).
 * distributor_id tidak fillable: selalu diisi dari distributor yang login.
 */
class DistributorBankAccount extends Model
{
    use HasUuids;

    protected $fillable = ['bank_name', 'account_number', 'account_name', 'is_active'];

    /** Daftar bank di Indonesia untuk pilihan nama bank (urut abjad). */
    public const BANKS = [
        'Allo Bank',
        'Bank Aceh Syariah',
        'Bank Aladin Syariah',
        'Bank Amar',
        'Bank Artha Graha',
        'Bank Banten',
        'Bank Bengkulu',
        'Bank BJB',
        'Bank BJB Syariah',
        'Bank BPD DIY',
        'Bank Capital',
        'Bank DKI',
        'Bank Ganesha',
        'Bank Ina Perdana',
        'Bank Index Selindo',
        'Bank Jago',
        'Bank Jambi',
        'Bank Jateng',
        'Bank Jatim',
        'Bank Kalbar',
        'Bank Kalsel',
        'Bank Kalteng',
        'Bank Lampung',
        'Bank Maluku Malut',
        'Bank Mandiri Taspen',
        'Bank Mayapada',
        'Bank Mega',
        'Bank Mega Syariah',
        'Bank Mestika',
        'Bank Muamalat',
        'Bank Nagari',
        'Bank Neo Commerce',
        'Bank Nobu',
        'Bank NTB Syariah',
        'Bank NTT',
        'Bank Oke Indonesia',
        'Bank Papua',
        'Bank Raya',
        'Bank Resona Perdania',
        'Bank Riau Kepri Syariah',
        'Bank Shinhan Indonesia',
        'Bank Sinarmas',
        'Bank Sulselbar',
        'Bank Sulteng',
        'Bank Sultra',
        'Bank SulutGo',
        'Bank Sumsel Babel',
        'Bank Sumut',
        'Bank Victoria',
        'Bank Victoria Syariah',
        'Bank Woori Saudara',
        'Bankaltimtara',
        'BCA',
        'BCA Syariah',
        'blu by BCA Digital',
        'BNI',
        'BNI Syariah',
        'BPD Bali',
        'BRI',
        'BSI (Bank Syariah Indonesia)',
        'BTN',
        'BTPN / Jenius',
        'CIMB Niaga',
        'Citibank',
        'Danamon',
        'DBS Indonesia',
        'Hibank',
        'HSBC Indonesia',
        'J Trust Bank',
        'KB Bukopin',
        'Krom Bank',
        'Line Bank (KEB Hana)',
        'Mandiri',
        'Maybank Indonesia',
        'MNC Bank',
        'OCBC',
        'Panin',
        'Panin Dubai Syariah',
        'Permata',
        'QNB Indonesia',
        'SeaBank',
        'Standard Chartered',
        'Superbank',
        'UOB Indonesia',
    ];

    protected $casts = ['is_active' => 'boolean'];

    /** Teks ringkas rekening, mis. "BCA · 1234567890 · a.n. PT Contoh" (disalin ke order saat konfirmasi bayar). */
    public function label(): string
    {
        return $this->bank_name . ' · ' . $this->account_number . ' · a.n. ' . $this->account_name;
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}
