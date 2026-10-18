@extends('layouts.warehouse')

@section('title', 'Jadwal Operasional Hub')
@section('page-title', 'Jadwal Operasional Hub')
@section('page-description', 'Atur jam operasional Hub/Warehouse Anda untuk setiap hari')

@section('breadcrumb')
    <li><a href="{{ route('warehouse.dashboard') }}">Dashboard</a></li>
    <li class="active">Jadwal Operasional</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o"></i> Atur Jam Operasional</h3>
            </div>
            
            <form action="{{ route('warehouse.profile.operational-hours.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr class="bg-gray">
                                    <th width="150">Hari</th>
                                    <th width="150">Status</th>
                                    <th>Jam Buka</th>
                                    <th>Jam Tutup</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($operationalHours as $hour)
                                    <tr>
                                        <td>
                                            <input type="hidden" name="hours[{{ $hour->day }}][day]" value="{{ $hour->day }}">
                                            <strong>{{ $hour->day_name }}</strong>
                                        </td>
                                        <td>
                                            <select name="hours[{{ $hour->day }}][is_open]" class="form-control input-sm status-select">
                                                <option value="1" {{ $hour->is_open ? 'selected' : '' }}>BUKA</option>
                                                <option value="0" {{ !$hour->is_open ? 'selected' : '' }}>TUTUP</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="time" name="hours[{{ $hour->day }}][open_time]" 
                                                   value="{{ old("hours.{$hour->day}.open_time", $hour->open_time ? $hour->open_time->format('H:i') : '08:00') }}" 
                                                   class="form-control input-sm time-input" 
                                                   {{ !$hour->is_open ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <input type="time" name="hours[{{ $hour->day }}][close_time]" 
                                                   value="{{ old("hours.{$hour->day}.close_time", $hour->close_time ? $hour->close_time->format('H:i') : '20:00') }}" 
                                                   class="form-control input-sm time-input" 
                                                   {{ !$hour->is_open ? 'disabled' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan Jadwal Operasional
                    </button>
                    <a href="{{ route('warehouse.dashboard') }}" class="btn btn-default">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        function updateRowState(selectElem) {
            var row = $(selectElem).closest('tr');
            var timeInputs = row.find('.time-input');
            
            if ($(selectElem).val() == '0') {
                timeInputs.prop('disabled', true).prop('required', false).css('opacity', '0.5');
            } else {
                timeInputs.prop('disabled', false).prop('required', true).css('opacity', '1');
            }
        }

        $('.status-select').change(function() {
            updateRowState(this);
        });

        // Initialize state on page load
        $('.status-select').each(function() {
            updateRowState(this);
        });

        // Re-enable inputs before form submit so values are included in POST
        $('form').on('submit', function() {
            $('.time-input').prop('disabled', false);
        });
    });
</script>
@endpush
@endsection
