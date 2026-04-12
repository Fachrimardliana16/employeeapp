@extends('mobile.layouts.app')

@section('title', 'Dokumen Saya')
@section('header-title', 'Dokumen Saya')

@section('content')

{{-- Dokumen Saya --}}
<div class="section-header">
  <span class="section-title">DOKUMEN SAYA</span>
</div>

@if($documents->isNotEmpty())
  <div class="card">
    @foreach($documents as $doc)
      <div class="list-item">
        <div class="list-icon" style="background: var(--primary-light);">
          <span style="font-size: 1.25rem;">📄</span>
        </div>
        <div class="list-content">
          <div class="list-title" style="font-weight: 700; color: var(--gray-800);">{{ $doc->document_name }}</div>
          <div class="list-subtitle">{{ $doc->created_at->format('d/m/Y') }}</div>
        </div>
        <div class="list-meta">
          <a href="{{ asset('storage/'.$doc->file_path) }}" class="btn-primary" style="padding: 4px 12px; font-size: 0.75rem; border-radius: 8px;">Lihat</a>
        </div>
      </div>
    @endforeach
  </div>
@else
  <div class="card" style="padding: 4rem 1.5rem; text-align: center;">
    <div style="font-size: 3rem; margin-bottom: 1rem; filter: grayscale(1); opacity: 0.5;">📁</div>
    <div class="empty-state-title">Belum ada dokumen</div>
    <div class="empty-state-desc">Dokumen resmi Anda akan muncul di sini.</div>
  </div>
@endif

@endsection
