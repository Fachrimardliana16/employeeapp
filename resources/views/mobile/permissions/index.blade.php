@extends('mobile.layouts.app')

@section('title', 'Izin & Cuti')
@section('header-title', 'Izin & Cuti')

@section('header-actions')
  <a href="{{ route('mobile.permissions.create') }}" class="header-btn" title="Ajukan Izin">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
  </a>
@endsection

@section('content')

{{-- Ringkasan --}}
<div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border: none;">
  <div class="card-body" style="padding: 1.5rem;">
    <div style="font-size: 0.75rem; font-weight: 700; color: rgba(255,255,255,0.8); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Ringkasan Izin</div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
      <div style="background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 16px;">
        <div style="font-size: 1.5rem; font-weight: 800; color: white;">{{ $permissions->where('approval_status', 'approved')->count() }}</div>
        <div style="font-size: 0.7rem; color: rgba(255,255,255,0.7); font-weight: 700;">Disetujui</div>
      </div>
      <div style="background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 16px;">
        <div style="font-size: 1.5rem; font-weight: 800; color: white;">{{ $permissions->where('approval_status', 'pending')->count() }}</div>
        <div style="font-size: 0.7rem; color: rgba(255,255,255,0.7); font-weight: 700;">Menunggu</div>
      </div>
    </div>
  </div>
</div>

{{-- New Submission Button --}}
<a href="{{ route('mobile.permissions.create') }}" class="btn btn-primary btn-full" style="margin-bottom: 2rem; border-radius: 20px; padding: 1rem; letter-spacing: 1px; font-weight: 800;">
  AJUKAN IZIN BARU
</a>

{{-- Permission List --}}
@if($permissions->isEmpty())
  <div class="card">
    <div class="card-body">
      <div class="empty-state">
        <div class="empty-state-icon">📅</div>
        <div class="empty-state-title">DATA KOSONG</div>
        <div class="empty-state-desc">Belum ada riwayat pengajuan izin ditemukan.</div>
      </div>
    </div>
  </div>
@else
  <div class="section-header">
    <span class="section-title">RIWAYAT PENGAJUAN</span>
  </div>
  <div class="card" style="margin-bottom: 2rem;">
    @foreach($permissions as $permission)
      @php
        $statusColors = ['approved' => 'success', 'rejected' => 'danger', 'pending' => 'warning'];
        $statusLabels = ['approved' => 'Disetujui', 'rejected' => 'Ditolak', 'pending' => 'Menunggu'];
      @endphp
      <a href="{{ route('mobile.permissions.show', $permission->id) }}" class="list-item">
        <div class="list-icon" style="background: var(--{{ $statusColors[$permission->approval_status] ?? 'gray' }}-light);">
          <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--{{ $statusColors[$permission->approval_status] ?? 'gray' }});"></div>
        </div>
        <div class="list-content">
          <div class="list-title" style="font-weight: 700; color: var(--gray-800);">{{ $permission->permission?->name ?? 'Izin' }}</div>
          <div class="list-subtitle">{{ \Carbon\Carbon::parse($permission->start_permission_date)->format('d/m/Y') }}</div>
        </div>
        <div class="list-meta text-right">
          <div class="badge badge-{{ $statusColors[$permission->approval_status] ?? 'gray' }}" style="font-size: 0.6rem; padding: 2px 8px;">
            {{ $statusLabels[$permission->approval_status] ?? $permission->approval_status }}
          </div>
        </div>
      </a>
    @endforeach
  </div>

  {{-- Pagination --}}
  @if($permissions->hasPages())
    <div style="display: flex; gap: 0.75rem; justify-content: center; margin-bottom: 2rem;">
      @if($permissions->onFirstPage())
        <span class="btn btn-ghost btn-sm" style="opacity: 0.2; background: rgba(255,255,255,0.02);">SEBELUMNYA</span>
      @else
        <a href="{{ $permissions->previousPageUrl() }}" class="btn btn-ghost btn-sm" style="background: rgba(255,255,255,0.05);">SEBELUMNYA</a>
      @endif
      
      <div style="padding: 0.5rem 1rem; background: rgba(14, 165, 233, 0.1); border-radius: 12px; font-size: 0.75rem; color: var(--primary); font-weight: 800; display: flex; align-items: center; border: 1px solid rgba(14, 165, 233, 0.2);">
        HALAMAN {{ $permissions->currentPage() }}
      </div>

      @if($permissions->hasMorePages())
        <a href="{{ $permissions->nextPageUrl() }}" class="btn btn-ghost btn-sm" style="background: rgba(255,255,255,0.05);">SELANJUTNYA</a>
      @else
        <span class="btn btn-ghost btn-sm" style="opacity: 0.2; background: rgba(255,255,255,0.02);">SELANJUTNYA</span>
      @endif
    </div>
  @endif
@endif

@endsection
