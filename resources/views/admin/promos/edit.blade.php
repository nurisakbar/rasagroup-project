@extends('layouts.admin')

@section('title', 'Edit Promo')
@section('page-title', 'Edit Promo')
@section('page-description', 'Edit data promo')

@section('breadcrumb')
    <li><a href="{{ route('admin.promos.index') }}">Promo</a></li>
    <li class="active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Edit Promo</h3>
                </div>
                <form action="{{ route('admin.promos.update', $promo) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        <div class="form-group @error('judul_promo') has-error @enderror">
                            <label for="judul_promo">Nama Promo</label>
                            <input type="text" class="form-control" id="judul_promo" name="judul_promo" value="{{ old('judul_promo', $promo->judul_promo) }}">
                            <p class="help-block"><small>Slug URL diperbarui otomatis dari nama promo saat menyimpan.</small></p>
                            @error('judul_promo')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('product_ids') has-error @enderror">
                            <label for="product_ids">Produk Promo</label>
                            <select class="form-control" id="product_ids" name="product_ids[]" multiple="multiple" data-placeholder="Ketik nama atau kode produk..." style="width: 100%;">
                                @foreach($selectedProducts as $product)
                                    <option value="{{ $product->id }}" selected>{{ $product->full_name }}</option>
                                @endforeach
                            </select>
                            <p class="help-block"><small>Ketik minimal 1 karakter untuk mencari produk. Satu promo dapat berisi satu atau lebih produk.</small></p>
                            @error('product_ids')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                            @error('product_ids.*')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('target_audience') has-error @enderror">
                            <label for="target_audience">Target Audiens</label>
                            @php
                                $selectedAudiences = is_array(old('target_audience', $promo->target_audience)) ? old('target_audience', $promo->target_audience) : [];
                            @endphp
                            <select class="form-control select2" id="target_audience" name="target_audience[]" multiple="multiple" data-placeholder="Pilih target audiens">
                                <option value="umum" {{ in_array('umum', $selectedAudiences) ? 'selected' : '' }}>Umum</option>
                                <option value="affiliator" {{ in_array('affiliator', $selectedAudiences) ? 'selected' : '' }}>Affiliator</option>
                                <option value="distributor" {{ in_array('distributor', $selectedAudiences) ? 'selected' : '' }}>Distributor</option>
                            </select>
                            @error('target_audience')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('awal') has-error @enderror">
                                    <label for="awal">Berlaku Dari</label>
                                    <input type="datetime-local" class="form-control" id="awal" name="awal" step="60" value="{{ old('awal', $promo->awal->format('Y-m-d\TH:i')) }}">
                                    @error('awal')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('akhir') has-error @enderror">
                                    <label for="akhir">Berlaku Sampai</label>
                                    <input type="datetime-local" class="form-control" id="akhir" name="akhir" step="60" value="{{ old('akhir', $promo->akhir->format('Y-m-d\TH:i')) }}">
                                    @error('akhir')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
                        <a href="{{ route('admin.promos.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@include('admin.promos.partials.product-select-scripts')
