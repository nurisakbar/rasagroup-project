@extends('layouts.admin')

@section('title', 'Edit Armada Pengiriman')
@section('page-title', 'Edit Armada Pengiriman')

@section('breadcrumb')
    <li><a href="{{ route('admin.armadas.index') }}">Armada Pengiriman</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Edit Armada</h3>
                </div>
                
                <form action="{{ route('admin.armadas.update', $armada) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group @error('name') has-error @enderror">
                            <label for="name">Nama Armada <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $armada->name) }}" required placeholder="Contoh: Kurir Internal 1">
                            @error('name')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('hub_id') has-error @enderror">
                            <label for="hub_id">Hub / Gudang <span class="text-danger">*</span></label>
                            <select name="hub_id" id="hub_id" class="form-control select2" required style="width: 100%;">
                                <option value="">-- Pilih Hub --</option>
                                @foreach($hubs as $hub)
                                    <option value="{{ $hub->id }}" {{ old('hub_id', $armada->hub_id) == $hub->id ? 'selected' : '' }}>
                                        {{ $hub->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('hub_id')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('description') has-error @enderror">
                            <label for="description">Keterangan</label>
                            <textarea name="description" id="description" rows="3" class="form-control" placeholder="Opsional">{{ old('description', $armada->description) }}</textarea>
                            @error('description')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <!-- /.box-body -->

                    <div class="box-footer">
                        <a href="{{ route('admin.armadas.index') }}" class="btn btn-default">Batal</a>
                        <button type="submit" class="btn btn-primary pull-right">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('.select2').select2();
    });
</script>
@endpush
