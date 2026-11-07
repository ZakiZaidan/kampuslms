@extends('layouts.app')

@section('title', 'Notifikasi Anda')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h1 class="h2 fw-bold text-gray-800 mb-1"><i class="bi bi-bell me-2 text-primary"></i> Notifikasi Anda</h1>
        <p class="text-muted mb-0">Pemberitahuan aktivitas, tugas baru, dan pengumuman sistem kampus.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        @if ($unreadCount > 0)
            <form action="{{ route('notifications.readAll') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-check2-all me-1"></i> Tandai Semua Sudah Dibaca
                </button>
            </form>
        @endif
    </div>
</div>

@if ($notifications->isEmpty())
    <div class="card shadow-sm border-0 text-center py-5">
        <div class="card-body">
            <i class="bi bi-bell-slash fs-1 d-block mb-3 text-muted"></i>
            <h5 class="fw-bold text-dark">Tidak Ada Notifikasi</h5>
            <p class="text-muted mb-0">Saat ini Anda tidak memiliki pemberitahuan baru.</p>
        </div>
    </div>
@else
    <div class="card shadow-sm border-0">
        <div class="list-group list-group-flush">
            @foreach ($notifications as $notification)
                <div class="list-group-item p-3 d-flex justify-content-between align-items-center {{ $notification->read_at ? 'bg-white' : 'bg-primary-subtle' }}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="mt-1">
                            @if (! $notification->read_at)
                                <i class="bi bi-circle-fill text-primary" style="font-size: 8px;"></i>
                            @else
                                <i class="bi bi-check2 text-muted"></i>
                            @endif
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h6 class="fw-bold mb-0 text-dark">{{ $notification->data['title'] ?? 'Pemberitahuan Sistem' }}</h6>
                                @if (! $notification->read_at)
                                    <span class="badge bg-primary">Baru</span>
                                @endif
                            </div>
                            <p class="text-secondary mb-1 small">{{ $notification->data['message'] ?? '' }}</p>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i> {{ $notification->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>

                    @if (! $notification->read_at)
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="ms-3 flex-shrink-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                Tandai Dibaca
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
        @if (method_exists($notifications, 'hasPages') && $notifications->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endif
@endsection
