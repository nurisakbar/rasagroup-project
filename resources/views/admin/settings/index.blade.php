@extends('layouts.admin')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')
@section('page-description', 'Kelola pengaturan sistem')

@section('breadcrumb')
    <li class="active">Pengaturan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- DRiiPPreneur Point Rate Setting -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-cog"></i> Point Rate DRiiPPreneur</h3>
            </div>
            <div class="box-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.settings.update-driippreneur-point-rate') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="point_rate">Point per Item</label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="point_rate" 
                                   name="point_rate" 
                                   value="{{ old('point_rate', $driippreneurPointRate) }}" 
                                   min="0" 
                                   step="1" 
                                   required>
                            <span class="input-group-addon">point/item</span>
                        </div>
                        <p class="help-block">
                            Point yang akan diberikan kepada DRiiPPreneur untuk setiap item yang dibeli saat pesanan selesai.
                            <br>
                            <strong>Contoh:</strong> Jika di-set 1000 point/item, dan DRiiPPreneur membeli 5 item, maka akan mendapat 5000 point.
                        </p>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection








