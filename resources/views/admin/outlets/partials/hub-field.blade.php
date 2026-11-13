{{-- Hub asal outlet/reseller. Admin pusat bisa memilih; Admin Hub hanya melihat (selalu hub-nya sendiri). --}}
@php $currentHubId = old('hub_id', $user->hub_id ?? null); @endphp
@if($canChooseHub ?? true)
    <div class="form-group @error('hub_id') has-error @enderror">
        <label for="hub_id">Hub</label>
        <select class="form-control" id="hub_id" name="hub_id">
            <option value="">- Otomatis dari hub sales -</option>
            @foreach($hubOptions ?? [] as $hubOption)
                <option value="{{ $hubOption->id }}" {{ (string) $currentHubId === (string) $hubOption->id ? 'selected' : '' }}>{{ $hubOption->name }}</option>
            @endforeach
            @if(isset($user) && $user->hub && ! collect($hubOptions ?? [])->contains('id', $user->hub_id))
                <option value="{{ $user->hub_id }}" selected>{{ $user->hub->name }}</option>
            @endif
        </select>
        <span class="help-block">Admin hub hanya melihat outlet &amp; reseller dari hub-nya. Kosongkan untuk mengikuti hub sales yang dipilih.</span>
        @error('hub_id') <span class="help-block">{{ $message }}</span> @enderror
    </div>
@else
    <div class="form-group">
        <label>Hub</label>
        <p class="form-control-static">{{ $user->hub->name ?? auth()->user()->warehouse->name ?? '-' }}</p>
    </div>
@endif
