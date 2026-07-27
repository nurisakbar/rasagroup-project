@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (in_array(trim($slot), ['Laravel', 'Rasa Group', 'RasaGroup']))
<img src="{{ asset('logorasa.png') }}" class="logo" alt="Rasa Group Logo" style="height: 50px;">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
