@extends('mobile.layouts.app')

@section('title', 'Riwayat Pelatihan')
@section('header-title', 'Pelatihan')

@section('content')

{{-- Daftar Pelatihan --}}
<div class="section-header">
  <span class="section-title">RIWAYAT PELATIHAN</span>
</div>

@if($trainings->isNotEmpty())
  <div class="card">
    @foreach($trainings as $training)
      <div class="list-item">
        <div class="list-icon" style="background: var(--primary-light);">
          <span style="font-size: 1.25rem;">🎓</span>
        </div>
        <div class="list-content">
          <div class="list-title" style="font-weight: 700; color: var(--gray-800);">{{ $training->training_name }}</div>
          <div class="list-subtitle">{{ $training->organizer }}</div>
          @if($training->training_date)
            <div style="font-size: 0.7rem; color: var(--gray-500); margin-top: 0.25rem; font-weight: 600;">
              📅 {{ \Carbon\Carbon::parse($training->training_date)->format('d F Y') }}
            </div>
          @endif
        </div>
        <div class="list-meta">
          <div style="font-size: 0.7rem; font-weight: 800; color: var(--primary);">{{ $training->year }}</div>
        </div>
      </div>
    @endforeach
  </div>
@else
  <div class="card" style="padding: 4rem 1.5rem; text-align: center;">
    <div style="font-size: 3rem; margin-bottom: 1rem; filter: grayscale(1); opacity: 0.5;">🎖️</div>
    <div class="empty-state-title">Belum ada pelatihan</div>
    <div class="empty-state-desc">Sertifikat dan riwayat pelatihan Anda akan muncul di sini.</div>
  </div>
@endif

@endsection
