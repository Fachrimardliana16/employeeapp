@extends('mobile.layouts.app')

@section('title', 'Data Keluarga')
@section('header-title', 'Keluarga')

@section('content')

{{-- Data Keluarga --}}
<div class="section-header">
  <span class="section-title">DATA KELUARGA</span>
</div>

@if($families->isNotEmpty())
  <div class="card">
    @foreach($families as $family)
      <div class="list-item">
        <div class="list-icon" style="background: var(--primary-light);">
          <span style="font-size: 1.25rem;">👨‍👩‍👧</span>
        </div>
        <div class="list-content">
          <div class="list-title" style="font-weight: 700; color: var(--gray-800);">{{ $family->family_name }}</div>
          <div class="list-subtitle">{{ $family->masterFamily?->name ?? 'Keluarga' }}</div>
          @if($family->family_date_birth)
            <div style="font-size: 0.7rem; color: var(--gray-500); margin-top: 0.25rem; font-weight: 600;">
              📅 {{ \Carbon\Carbon::parse($family->family_date_birth)->format('d F Y') }}
            </div>
          @endif
        </div>
        <div class="list-meta">
          <div class="badge badge-gray" style="font-size: 0.65rem;">
            {{ $family->family_gender == 'L' ? 'Pria' : 'Wanita' }}
          </div>
        </div>
      </div>
    @endforeach
  </div>
@else
  <div class="card" style="padding: 4rem 1.5rem; text-align: center;">
    <div style="font-size: 3rem; margin-bottom: 1rem; filter: grayscale(1); opacity: 0.5;">👨‍👩‍👦</div>
    <div class="empty-state-title">Belum ada data keluarga</div>
    <div class="empty-state-desc">Data pasangan atau anak Anda akan muncul di sini.</div>
  </div>
@endif

@endsection
