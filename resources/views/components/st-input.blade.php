@props(['label' => '', 'name' => '', 'type' => 'text', 'value' => '', 'required' => false, 'placeholder' => '', 'step' => null, 'min' => null, 'extra' => '', 'id' => null])
<div>
    @if($label)<label class="st-label" for="{{ $id ?? $name }}">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>@endif
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $id ?? $name }}" value="{{ $value }}"
           class="st-input" @if($required) required @endif @if($placeholder) placeholder="{{ $placeholder }}" @endif
           @if($step !== null) step="{{ $step }}" @endif @if($min !== null) min="{{ $min }}" @endif {!! $extra !!}>
    {{ $slot }}
</div>
