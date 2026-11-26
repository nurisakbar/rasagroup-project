@php $bonus = $bonus ?? []; @endphp
@if(count($bonus))
    <div class="mb-30" style="border: 1px solid #E3D6C8; border-radius: 14px; background: #fffaf3; padding: 16px 18px;">
        <h5 class="mb-15" style="color: #6A1B1B; font-weight: 800;"><i class="fi-rs-gift mr-5"></i>Bonus Pembelian</h5>
        @foreach($bonus as $e)
            @php $rule = $e['rule']; @endphp
            <div class="mb-15 pb-15" style="{{ $loop->last ? '' : 'border-bottom: 1px dashed #E3D6C8;' }}">
                <div class="fw-bold">{{ $rule->name }}</div>
                @if($e['qualified'])
                    <div class="text-success mb-5"><i class="fi-rs-check mr-5"></i>Selamat! Anda mendapat {{ $e['gift_units'] }} {{ $rule->unitLabel() }} bonus.</div>
                    @if($rule->isFixed())
                        <div>Bonus: <strong>{{ $e['selected']?->display_name }}</strong> × {{ $e['gift_units'] }} {{ $rule->unitLabel() }}@if($e['gift_pcs'] != $e['gift_units']) ({{ $e['gift_pcs'] }} pcs)@endif</div>
                    @else
                        <label class="d-block mb-5" style="font-size: 14px;">Pilih produk bonus Anda:</label>
                        <select class="form-select js-bonus-choice" data-rule="{{ $rule->id }}" style="max-width: 460px;">
                            <option value="">-- Pilih produk bonus --</option>
                            @foreach($e['options'] as $opt)
                                <option value="{{ $opt->id }}" {{ $e['selected']?->id === $opt->id ? 'selected' : '' }}>{{ $opt->display_name }}</option>
                            @endforeach
                        </select>
                        @if($e['needs_choice'])
                            <div class="text-danger mt-5" style="font-size: 13px;">Pilih produk bonus sebelum checkout.</div>
                        @elseif($e['selected'])
                            <div class="mt-5" style="font-size: 13px;">Bonus: {{ $e['gift_units'] }} {{ $rule->unitLabel() }}@if($e['gift_pcs'] != $e['gift_units']) ({{ $e['gift_pcs'] }} pcs)@endif</div>
                        @endif
                    @endif
                    @if($rule->is_multiple && $e['remaining'] > 0 && (! $rule->max_gift_qty || $e['gift_units'] < $rule->max_gift_qty))
                        <div class="text-muted mt-5" style="font-size: 13px;">Tambah {{ $e['remaining'] }} {{ $rule->unitLabel() }} lagi untuk bonus tambahan.</div>
                    @endif
                @else
                    <div class="text-muted">Beli {{ $e['remaining'] }} {{ $rule->unitLabel() }} lagi untuk mendapat {{ $rule->gift_qty }} {{ $rule->unitLabel() }} bonus
                        ({{ $rule->isFixed() ? $rule->giftProducts->first()?->display_name : 'pilih produk bebas' }}).</div>
                @endif
            </div>
        @endforeach
    </div>
@endif
