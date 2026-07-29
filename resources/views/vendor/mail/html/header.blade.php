@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (in_array(trim($slot), ['Laravel', 'Rasa Group', 'RasaGroup', 'Rasaconnect']))
<img src="https://rasaconnect.com/logo/RASA%20Group%20-%20Logo%20-%20R-02.png" class="logo" alt="Rasaconnect Logo" style="height: 50px;">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
