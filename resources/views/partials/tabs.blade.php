@props([
    'tabs' => [], // [['id' => 'equipo', 'label' => '📱 Equipo', 'icon' => 'fas fa-mobile-alt'], ...]
    'active' => null,
])

@php
    $activeId = $active ?? ($tabs[0]['id'] ?? 'tab-1');
@endphp

<div class="card mb-4">
    <div class="card-header p-0" style="background:#fff; border-bottom:1px solid #e5e7eb;">
        <ul class="nav nav-tabs card-header-tabs" id="tabs-{{ $activeId }}" role="tablist" style="border-bottom:none; padding:0 8px;">
            @foreach($tabs as $tab)
            <li class="nav-item">
                <button class="nav-link {{ $tab['id'] === $activeId ? 'active' : '' }}"
                        id="tab-{{ $tab['id'] }}-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-{{ $tab['id'] }}"
                        type="button"
                        role="tab"
                        aria-controls="tab-{{ $tab['id'] }}"
                        aria-selected="{{ $tab['id'] === $activeId ? 'true' : 'false' }}"
                        style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                    <i class="{{ $tab['icon'] ?? 'fas fa-circle' }} me-1" style="color:#a855f7;"></i>{{ $tab['label'] }}
                </button>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="card-body p-4">
        <div class="tab-content" id="tabs-{{ $activeId }}-content">
            {{ $slot }}
        </div>
    </div>
</div>