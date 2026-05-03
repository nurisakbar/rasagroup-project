@extends('layouts.admin')

@section('title', 'Tambah Promo')
@section('page-title', 'Tambah Promo')
@section('page-description', 'Tambah data promo baru')

@section('breadcrumb')
    <li><a href="{{ route('admin.promos.index') }}">Promo</a></li>
    <li class="active">Tambah</li>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <style>
        .note-editable { background: #fff !important; }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Form Tambah Promo</h3>
                </div>
                <form action="{{ route('admin.promos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="box-body">
                        <div class="form-group @error('kode_promo') has-error @enderror">
                            <label for="kode_promo">Kode Promo</label>
                            <input type="text" class="form-control" id="kode_promo" name="kode_promo" value="{{ old('kode_promo') }}" placeholder="Contoh: PROMO10K">
                            @error('kode_promo')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('judul_promo') has-error @enderror">
                            <label for="judul_promo">Judul Promo</label>
                            <input type="text" class="form-control" id="judul_promo" name="judul_promo" value="{{ old('judul_promo') }}" placeholder="Contoh: Promo Gajian">
                            <p class="help-block"><small>Slug URL dibuat otomatis dari judul.</small></p>
                            @error('judul_promo')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('image') has-error @enderror">
                            <label for="image">Gambar Promo</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <p class="help-block">Format: JPG, PNG, GIF, SVG. Max: 2MB.</p>
                            @error('image')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('deskripsi') has-error @enderror">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control summernote" id="deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi promo...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('harga') has-error @enderror">
                            <label for="harga">Harga / Potongan</label>
                            <div class="input-group">
                                <span class="input-group-addon">Rp</span>
                                <input type="number" class="form-control" id="harga" name="harga" value="{{ old('harga', 0) }}">
                            </div>
                            @error('harga')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('awal') has-error @enderror">
                                    <label for="awal">Mulai (tanggal &amp; jam)</label>
                                    <input type="datetime-local" class="form-control" id="awal" name="awal" step="60" value="{{ old('awal') }}">
                                    @error('awal')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('akhir') has-error @enderror">
                                    <label for="akhir">Berakhir (tanggal &amp; jam)</label>
                                    <input type="datetime-local" class="form-control" id="akhir" name="akhir" step="60" value="{{ old('akhir') }}">
                                    @error('akhir')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                        <a href="{{ route('admin.promos.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
@endpush
