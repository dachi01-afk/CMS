@props(['color' => 'red'])
 
<button {{ $attributes->merge(['class' => 'bg-'.$color.'-200']) }}>
    {{ $slot }}
</button>