@extends('layouts.app')

@section('title', 'Notifikasi Anda')

@section('content')
<div style="max-width: 800px; margin: 20px auto; padding: 0 15px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Daftar Notifikasi</h2>
        @if ($unreadCount > 0)
            <form action="{{ route('notifications.readAll') }}" method="POST">
                @csrf
                <button type="submit" style="background: #3490dc; color: white; border: none; padding: 8px 14px; border-radius: 4px; cursor: pointer;">
                    Tandai Semua Sudah Dibaca
                </button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <p style="color: #666;">Tidak ada notifikasi saat ini.</p>
    @else
        <ul style="list-style: none; padding: 0;">
            @foreach ($notifications as $notification)
                <li style="border: 1px solid #ddd; border-radius: 6px; padding: 15px; margin-bottom: 12px; background: {{ $notification->read_at ? '#ffffff' : '#f0f9ff' }}; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: bold; margin-bottom: 4px;">
                            {{ $notification->data['title'] ?? 'Pemberitahuan Sistem' }}
                            @if (! $notification->read_at)
                                <span style="background: #e3342f; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 6px;">Baru</span>
                            @endif
                        </div>
                        <p style="margin: 0; color: #555;">
                            {{ $notification->data['message'] ?? '' }}
                        </p>
                        <small style="color: #888;">
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>

                    @if (! $notification->read_at)
                        {{-- BUG 4 (w10): Endpoint ini rentan IDOR jika controller tidak mengecek kepemilikan notifiable_id --}}
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" style="background: #38c172; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                Tandai Dibaca
                            </button>
                        </form>
                    @endif
                </li>
            @endforeach
        </ul>

        <div style="margin-top: 20px;">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
