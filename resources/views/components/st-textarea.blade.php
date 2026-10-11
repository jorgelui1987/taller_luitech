@props(['label' => '', 'name' => '', 'value' => '', 'required' => false, 'rows' => 2, 'placeholder' => ''])
<div>
    @if($label)<label class="st-label" for="{{ $name }}">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>@endif
    <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}" class="st-input"
              @if($required) required @endif @if($placeholder) placeholder="{{ $placeholder }}" @endif>{{ $value }}</textarea>
    {{ $slot }}
</div>
