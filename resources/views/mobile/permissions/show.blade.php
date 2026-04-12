@extends('mobile.layouts.app')

@section('title', 'Detail Izin')
@section('header-title', 'Detail Izin')

@section('content')

@php
  $statusColors = ['approved' => 'success', 'rejected' => 'danger', 'pending' => 'warning'];
  $statusLabels = ['approved' => 'Disetujui', 'rejected' => 'Ditolak', 'pending' => 'Menunggu'];
@endphp

<div class="card" style="margin-bottom: 2rem;">
  <div style="padding: 1.75rem; background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(20px); border-bottom: 1px solid var(--glass-border);">
    <div style="font-size: 0.7rem; color: var(--primary); font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 0.75rem;">
      ID Izin: #{{ str_pad($permission->id, 6, '0', STR_PAD_LEFT) }}
    </div>
    <div style="font-size: 1.25rem; font-weight: 900; color: white; margin-bottom: 1rem; letter-spacing: -0.5px;">
      {{ $permission->permission?->permission_type_name ?? 'Entri Izin' }}
    </div>
    <span class="badge badge-{{ $statusColors[$permission->approval_status] ?? 'gray' }}" style="font-size: 0.65rem; padding: 4px 12px; font-weight: 900; letter-spacing: 1px;">
      {{ $statusLabels[$permission->approval_status] ?? $permission->approval_status }}
    </span>
  </div>
  <div class="card-body" style="padding: 0.5rem 1.75rem;">
    <div class="info-row">
      <div class="info-key">Tanggal Mulai</div>
      <div class="info-value">{{ \Carbon\Carbon::parse($permission->start_permission_date)->format('d/m/Y') }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Tanggal Selesai</div>
      <div class="info-value">{{ \Carbon\Carbon::parse($permission->end_permission_date)->format('d/m/Y') }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Durasi</div>
      <div class="info-value">
        {{ \Carbon\Carbon::parse($permission->start_permission_date)->diffInDays($permission->end_permission_date) + 1 }} Hari
      </div>
    </div>
    <div class="info-row">
      <div class="info-key">Keterangan</div>
      <div class="info-value">{{ $permission->permission_desc }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Disetujui Oleh</div>
      <div class="info-value">{{ $permission->approver?->name ?? '-' }}</div>
    </div>
    @if($permission->approved_at)
    <div class="info-row">
      <div class="info-key">Waktu Persetujuan</div>
      <div class="info-value">{{ $permission->approved_at->format('d/m/Y, H:i') }}</div>
    </div>
    @endif
    @if($permission->approval_notes)
    <div class="info-row">
      <div class="info-key">Catatan</div>
      <div class="info-value">{{ $permission->approval_notes }}</div>
    </div>
    @endif
    @if($permission->scan_doc)
    <div class="info-row">
      <div class="info-key">ATTACHED_EVIDENCE</div>
      <div class="info-value">
        <a href="{{ asset('storage/' . $permission->scan_doc) }}" target="_blank" class="btn btn-primary btn-sm" style="font-size: 0.65rem; border-radius: 8px;">
          VIEW_ATTACHMENT
        </a>
      </div>
    </div>
    @endif
    <div class="info-row">
      <div class="info-key">INITIALIZED</div>
      <div class="info-value">{{ $permission->created_at->format('d/m/Y, H:i') }}</div>
    </div>
  </div>
</div>

<a href="{{ route('mobile.permissions') }}" class="btn btn-ghost btn-full" style="margin-bottom: 3rem; font-size: 0.75rem; font-weight: 800; opacity: 0.6;">
  ← RETURN_TO_LIST
</a>

@endsection
