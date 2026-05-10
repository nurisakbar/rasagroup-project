@extends('layouts.shop')

@section('title', 'Jadwal Operasional')

@section('content')
<div class="page-header breadcrumb-wrap">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}" rel="nofollow"><i class="fi-rs-home mr-5"></i>Beranda</a>
            <span></span> Akun Saya
            <span></span> Jadwal Operasional
        </div>
    </div>
</div>

<div class="page-content pt-50 pb-80" style="background-color: #F2EAE1;">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm border-radius-20 overflow-hidden" style="background-color: #ffffff;">
                    <div class="card-header bg-white border-bottom-0 p-30 pb-0">
                        <h3 class="mb-0" style="font-family: 'Fira Sans', sans-serif; font-weight: 700; color: #253D4E;">Jadwal Operasional</h3>
                        <p class="text-muted mt-10">Atur jam operasional distributor Anda agar pembeli mengetahui kapan Anda aktif.</p>
                    </div>
                    <div class="card-body p-30">
                        @if(session('success'))
                            <div class="alert alert-success border-radius-12 border-0 mb-30" style="background-color: #f0fff4; color: #2f855a;">
                                <i class="fi-rs-check mr-5"></i> {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger border-radius-12 border-0 mb-30">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('buyer.profile.operational-hours.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="table-responsive">
                                <table class="table table-borderless align-middle">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #F2EAE1;">
                                            <th class="py-3 ps-4" style="color: #6A1B1B; font-weight: 700;">Hari</th>
                                            <th class="py-3" style="color: #6A1B1B; font-weight: 700;">Status</th>
                                            <th class="py-3" style="color: #6A1B1B; font-weight: 700;">Jam Buka</th>
                                            <th class="py-3" style="color: #6A1B1B; font-weight: 700;">Jam Tutup</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($operationalHours as $hour)
                                            <tr style="border-bottom: 1px solid #F2EAE1;">
                                                <td class="py-4 ps-4">
                                                    <input type="hidden" name="hours[{{ $hour->day }}][day]" value="{{ $hour->day }}">
                                                    <span class="fw-bold" style="color: #253D4E;">{{ $hour->day_name }}</span>
                                                </td>
                                                <td class="py-4">
                                                    <select name="hours[{{ $hour->day }}][is_open]" class="form-select border-radius-10 status-select" style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;">
                                                        <option value="1" {{ $hour->is_open ? 'selected' : '' }}>BUKA</option>
                                                        <option value="0" {{ !$hour->is_open ? 'selected' : '' }}>TUTUP</option>
                                                    </select>
                                                </td>
                                                <td class="py-4">
                                                    <input type="time" name="hours[{{ $hour->day }}][open_time]" 
                                                           value="{{ old("hours.{$hour->day}.open_time", $hour->open_time->format('H:i')) }}" 
                                                           class="form-control border-radius-10 time-input" 
                                                           style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;"
                                                           {{ !$hour->is_open ? 'disabled' : '' }} required>
                                                </td>
                                                <td class="py-4">
                                                    <input type="time" name="hours[{{ $hour->day }}][close_time]" 
                                                           value="{{ old("hours.{$hour->day}.close_time", $hour->close_time->format('H:i')) }}" 
                                                           class="form-control border-radius-10 time-input" 
                                                           style="background-color: #F8F9FA; border: 1.5px solid #ECECEC;"
                                                           {{ !$hour->is_open ? 'disabled' : '' }} required>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-40">
                                <button type="submit" class="btn btn-lg rounded-pill px-40" style="background-color: #6A1B1B; color: white;">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.status-select').change(function() {
            var row = $(this).closest('tr');
            var timeInputs = row.find('.time-input');
            
            if ($(this).val() == '0') {
                timeInputs.prop('disabled', true).prop('required', false);
                timeInputs.css('opacity', '0.5');
            } else {
                timeInputs.prop('disabled', false).prop('required', true);
                timeInputs.css('opacity', '1');
            }
        });
        
        // Initial state
        $('.status-select').each(function() {
            if ($(this).val() == '0') {
                $(this).closest('tr').find('.time-input').css('opacity', '0.5');
            }
        });
    });
</script>
@endpush
@endsection
