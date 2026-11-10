@props(['small' => false, 'as' => 'span'])
<{{ $as }} {{ $attributes->class(['plate', 'plate-sm' => $small]) }}>{{ $slot }}</{{ $as }}>
