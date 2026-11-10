@props(['data'])
{{-- JSON_HEX_TAG mencegah "</script>" di dalam data menutup tag lebih awal. --}}
<script type="application/ld+json">{!! json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
