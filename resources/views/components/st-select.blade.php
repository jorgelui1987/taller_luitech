@props(['label' => '', 'name' => '', 'value' => '', 'required' => false, 'options' => []])
<div>
    @if($label)<label class="st-label" for="{{ $name }}">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>@endif
    <select name="{{ $name }}" id="{{ $name }}" class="st-input" @if($required) required @endif>
        {{ $slot }}
    </select>
</div>
