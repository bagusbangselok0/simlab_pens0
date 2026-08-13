@extends('layouts.app')

@section('styles')
<style>
    .notification-card {
        border: none;
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .notification-card.unread {
        background: linear-gradient(135deg, rgba(67, 94, 190, 0.05), rgba(67, 94, 190, 0.02));
        border-left: 4px solid #435ebe !important;
    }

    .notification-card.read {
        opacity: 0.7;
    }

    .notification-icon-wrapper {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        line-height: 1;
        padding: 0;
    }

    .notification-icon-wrapper i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        line-height: 1;
    }

    .filter-tabs .btn {
        border-radius: 20px;
        padding: 6px 18px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .filter-tabs .btn.active {
        background-color: #435ebe;
        color: white;
        border-color: #435ebe;
    }

    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-state i {
        font-size: 4rem;
        color: #c0c0c0;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="card shadow-sm" style="border-radius: 16px;">
        <div class="card-body">
            {{-- Header & Filter --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div class="filter-tabs d-flex gap-2">
                    <button class="btn btn-outline-secondary active" id="filterAll">Semua</button>
                    <button class="btn btn-outline-secondary" id="filterUnread">Belum Dibaca</button>
                </div>
                <form action="{{ route('notifications.read_all') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary" style="border-radius: 20px;">
                        <i class="bi bi-check2-all"></i> Tandai Semua Dibaca
                    </button>
                </form>
            </div>

            {{-- Notification List --}}
            <div id="notificationContainer">
                @forelse ($notifications as $notification)
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="notification-card card mb-2 w-100 text-start {{ $notification->read_at ? 'read' : 'unread' }}"
                            style="cursor: pointer; border: 1px solid #eee;">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="notification-icon-wrapper bg-light-{{ $notification->data['icon_color'] ?? 'secondary' }}">
                                        <i class="bi {{ $notification->data['icon'] ?? 'bi-bell-fill' }} text-{{ $notification->data['icon_color'] ?? 'secondary' }} fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="mb-1 fw-bold" style="font-size: 0.9rem;">
                                                {{ $notification->data['title'] ?? 'Notifikasi' }}
                                                @if (!$notification->read_at)
                                                    <span class="badge bg-primary rounded-pill ms-1" style="font-size: 0.6rem;">Baru</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted flex-shrink-0 ms-2" style="font-size: 0.75rem;">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        <p class="mb-1 text-muted" style="font-size: 0.83rem;">
                                            {{ $notification->data['message'] ?? '' }}
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-person-fill"></i>
                                            {{ $notification->data['sender_name'] ?? 'Sistem' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </form>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-bell-slash d-block mb-3"></i>
                        <h5 class="text-muted">Belum Ada Notifikasi</h5>
                        <p class="text-muted">Notifikasi terkait peminjaman lab akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        // Client-side filter tabs
        $('#filterAll').on('click', function () {
            $(this).addClass('active');
            $('#filterUnread').removeClass('active');
            $('.notification-card').show();
        });

        $('#filterUnread').on('click', function () {
            $(this).addClass('active');
            $('#filterAll').removeClass('active');
            $('.notification-card.read').hide();
            $('.notification-card.unread').show();
        });
    });
</script>
@endsection
