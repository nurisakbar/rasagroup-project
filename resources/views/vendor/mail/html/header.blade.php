@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="{{ asset('logorasa.png') }}" class="logo" alt="Rasa Group Logo" style="height: 50px;">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
