{{-- Waktu tampil menu: tanggal + jam. Kosongkan keduanya = tampil kapan saja. --}}
@php
    $menu = $menu ?? null;
    $mulaiVal = old('tampil_mulai', $menu && $menu->tampil_mulai ? $menu->tampil_mulai->format('Y-m-d\TH:i') : '');
    $sampaiVal = old('tampil_sampai', $menu && $menu->tampil_sampai ? $menu->tampil_sampai->format('Y-m-d\TH:i') : '');
@endphp
<label class="control-label">Jadwal tampil</label>
<p class="help-block" style="margin-top: 0;"><small>Opsional — kosongkan keduanya jika menu tidak dibatasi waktu.</small></p>
<div class="row">
    <div class="col-sm-6">
        <div class="form-group @error('tampil_mulai') has-error @enderror">
            <label for="tampil_mulai">Mulai</label>
            <input type="datetime-local"
                   name="tampil_mulai"
                   id="tampil_mulai"
                   class="form-control"
                   step="60"
                   value="{{ $mulaiVal }}">
            @error('tampil_mulai')
                <span class="help-block">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group @error('tampil_sampai') has-error @enderror">
            <label for="tampil_sampai">Berakhir</label>
            <input type="datetime-local"
                   name="tampil_sampai"
                   id="tampil_sampai"
                   class="form-control"
                   step="60"
                   value="{{ $sampaiVal }}">
            @error('tampil_sampai')
                <span class="help-block">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
