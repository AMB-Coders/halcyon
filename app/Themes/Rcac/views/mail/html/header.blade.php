<tr>
	<td class="header">
		<a href="{{ $url }}">
			<img src="{{ asset('themes/rcac/images/RCAC_SIG_Logo_RGB__PU-H-Full-RGB_Black_white.png') }}" width="395" height="35" class="logo" alt="{{ $slot }}">
		</a>
	</td>
</tr>
@if ($alert)
	<tr>
		<td class="alert alert-{{ $alert }}">
		</td>
	</tr>
@endif