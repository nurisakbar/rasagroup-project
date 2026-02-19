@extends('layouts.admin')

@section('title', 'Edit Promo')
@section('page-title', 'Edit Promo')
@section('page-description', 'Edit data promo')

@section('breadcrumb')
    <li><a href="{{ route('admin.promos.index') }}">Promo</a></li>
    <li class="active">Edit</li>
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
                    <h3 class="box-title">Form Edit Promo</h3>
                </div>
                <form action="{{ route('admin.promos.update', $promo) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        <div class="form-group @error('kode_promo') has-error @enderror">
                            <label for="kode_promo">Kode Promo</label>
                            <input type="text" class="form-control" id="kode_promo" name="kode_promo" value="{{ old('kode_promo', $promo->kode_promo) }}">
                            @error('kode_promo')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('judul_promo') has-error @enderror">
                            <label for="judul_promo">Judul Promo</label>
                            <input type="text" class="form-control" id="judul_promo" name="judul_promo" value="{{ old('judul_promo', $promo->judul_promo) }}">
                            @error('judul_promo')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('slug') has-error @enderror">
                            <label for="slug">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $promo->slug) }}">
                            @error('slug')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('image') has-error @enderror">
                            <label for="image">Gambar Promo</label>
                            @if($promo->image)
                                <div class="mb-10">
                                    <img src="{{ asset('storage/' . $promo->image) }}" class="img-thumbnail" style="height: 100px; display: block; margin-bottom: 10px;">
                                </div>
                            @endif
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <p class="help-block">Format: JPG, PNG, GIF, SVG. Max: 2MB. Biarkan kosong jika tidak ingin mengubah gambar.</p>
                            @error('image')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('deskripsi') has-error @enderror">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control summernote" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $promo->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group @error('harga') has-error @enderror">
                            <label for="harga">Harga / Potongan</label>
                            <div class="input-group">
                                <span class="input-group-addon">Rp</span>
                                <input type="number" class="form-control" id="harga" name="harga" value="{{ old('harga', $promo->harga) }}">
                            </div>
                            @error('harga')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group @error('awal') has-error @enderror">
                                    <label for="awal">Tanggal Awal</label>
                                    <input type="date" class="form-control" id="awal" name="awal" value="{{ old('awal', $promo->awal->format('Y-m-d')) }}">
                                    @error('awal')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group @error('akhir') has-error @enderror">
                                    <label for="akhir">Tanggal Akhir</label>
                                    <input type="date" class="form-control" id="akhir" name="akhir" value="{{ old('akhir', $promo->akhir->format('Y-m-d')) }}">
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

            $('#judul_promo').on('blur', function() {
                if ($('#slug').val() == '') {
                    var title = $(this).val();
                    var slug = title.toLowerCase()
                        .replace(/[^\w ]+/g, '')
                        .replace(/ +/g, '-');
                    $('#slug').val(slug);
                }
            });
        });
    </script>
@endpush
