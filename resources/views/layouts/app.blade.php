<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <link rel="shortcut icon" href="{{ asset('images/logo/SIMLAB_logo1.png') }}" type="image/x-icon" />
    <link rel="shortcut icon" href="{{ asset('images/logo/SIMLAB_logo1.png') }}" type="image/png" />
    @include('layouts.styles')
    @yield('styles')
</head>

<body>
    <script src="{{ asset('mazer/static/js/initTheme.js') }}"></script>
    @include('layouts.loading')

    <div id="app">
        @include('layouts.sidebar')
        <div id="main">
            <header class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <a href="#" class="burger-btn d-block d-xl-none">
                            <i class="bi bi-justify fs-3"></i>
                        </a>
                        <h3 class="mb-0 ms-3">{{ $title }}</h3>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        {{-- Notification Bell --}}
                        <div class="dropdown" id="notificationDropdown">
                            <a href="#" class="position-relative" data-bs-toggle="dropdown"
                                aria-expanded="false" id="notificationBell">
                                <i class="bi bi-bell fs-4"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
                                    id="notificationBadge" style="font-size: 0.65rem;">
                                    0
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow-lg p-0"
                                style="width: 360px; max-height: 420px; overflow-y: auto; border-radius: 12px;">
                                <div
                                    class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                    <h6 class="mb-0 fw-bold">Notifikasi</h6>
                                    <button class="btn btn-sm btn-link text-decoration-none p-0"
                                        id="markAllReadBtn" style="font-size: 0.8rem;">
                                        Tandai semua dibaca
                                    </button>
                                </div>
                                <div id="notificationList">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-4 px-3">
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                                            style="width:56px; height:56px; background: rgba(79,70,229,0.06); color:#4f46e5;">
                                            <i class="bi bi-bell-slash fs-4"></i>
                                        </div>
                                        <h6 class="mb-1 fw-semibold">Belum ada notifikasi terbaru</h6>
                                        <p class="mb-0 small text-muted">Notifikasi terbaru akan muncul di sini.</p>
                                    </div>
                                </div>
                                <div class="border-top text-center py-2">
                                    <a href="{{ route('notifications.index') }}"
                                        class="text-decoration-none small fw-semibold">
                                        Lihat Semua Notifikasi
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Push Notification Toggle --}}
                        <button id="pushToggleBtn"
                            class="btn btn-sm d-flex align-items-center gap-1"
                            title="Toggle Notifikasi Push"
                            style="font-size: 0.8rem; padding: 4px 10px; border-radius: 20px; border: 1.5px solid #ccc; background: transparent; transition: all 0.2s;">
                            <i class="bi bi-bell-slash" id="pushToggleIcon" style="font-size: 1rem; display: inline-flex; align-items: center; justify-content: center; line-height: 1;"></i>
                            <span id="pushToggleLabel" class="d-none d-md-inline">Push</span>
                        </button>

                        <div class="d-none d-xl-flex align-items-center gap-2">
                            <div class="user-img d-flex align-items-center">
                                <div class="avatar avatar-md">
                                    <img src="{{ Auth::user()->photo_url }}" alt="Profile Photo"
                                        srcset="">
                                </div>
                            </div>
                            <div class="profile-text">
                                <h6 class="mb-0">{{ Auth::user()->nama_asli ?? 'User' }}</h6>
                                <small class="text-muted">{{ Auth::user()->role->nama_role ?? 'Role' }}</small>
                            </div>
                        </div>

                        <div class="dropdown d-xl-none">
                            <a href="#" class="d-flex align-items-center" id="mobileProfileToggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="avatar avatar-md">
                                    <img src="{{ Auth::user()->photo_url }}" alt="Profile Photo" srcset="">
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm p-3" style="min-width: 220px;">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="avatar avatar-md">
                                        <img src="{{ Auth::user()->photo_url }}" alt="Profile Photo" srcset="">
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ Auth::user()->nama_asli ?? 'User' }}</h6>
                                        <small class="text-muted">{{ Auth::user()->role->nama_role ?? 'Role' }}</small>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">Logout</button>
                                </form>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="d-none d-xl-block">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                        </form>
                    </div>
                </div>
            </header>
            <div class="page-content">
                @yield('content')
            </div>
            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.scripts')
    @yield('scripts')

    {{-- Notification AJAX Script --}}
    <script>
        $(document).ready(function() {
            function loadNotifications() {
                $.ajax({
                    url: '{{ route("notifications.unread") }}',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            let badge = $('#notificationBadge');
                            let list = $('#notificationList');

                            // Update badge
                            if (response.count > 0) {
                                badge.text(response.count > 99 ? '99+' : response.count);
                                badge.removeClass('d-none');
                            } else {
                                badge.addClass('d-none');
                            }

                            // Update notification list
                            if (response.notifications.length > 0) {
                                let html = '';
                                response.notifications.forEach(function(notif) {
                                    html += `
                                        <form action="/notifications/${notif.id}/read" method="POST" class="notification-item-form">
                                            @csrf
                                            <button type="submit" class="dropdown-item d-flex align-items-start gap-3 px-3 py-3 border-bottom" style="white-space: normal;">
                                                <div class="flex-shrink-0">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-light-${notif.icon_color}" style="width: 40px; height: 40px; min-width: 40px; line-height: 1; padding: 0;">
                                                        <i class="bi ${notif.icon} text-${notif.icon_color}" style="font-size: 1rem; display: inline-flex; align-items: center; justify-content: center;"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="mb-0 fw-semibold" style="font-size: 0.85rem;">${notif.title}</p>
                                                    <p class="mb-0 text-muted" style="font-size: 0.78rem;">${notif.message.length > 80 ? notif.message.substring(0, 80) + '...' : notif.message}</p>
                                                    <small class="text-muted">${notif.time_ago}</small>
                                                </div>
                                            </button>
                                        </form>
                                    `;
                                });
                                list.html(html);
                            } else {
                                list.html(`
                                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-4 px-3">
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                                            style="width:56px; height:56px; background: rgba(79,70,229,0.06); color:#4f46e5;">
                                            <i class="bi bi-bell-slash fs-4"></i>
                                        </div>
                                        <h6 class="mb-1 fw-semibold">Belum ada notifikasi terbaru</h6>
                                        <p class="mb-0 small text-muted">Notifikasi terbaru akan muncul di sini.</p>
                                    </div>
                                `);
                            }
                        }
                    }
                });
            }

            // Load on page ready
            loadNotifications();

            // Polling setiap 30 detik
            setInterval(loadNotifications, 30000);

            // Refresh saat dropdown dibuka
            $('#notificationBell').on('click', function() {
                loadNotifications();
            });

            // Mark all as read
            $('#markAllReadBtn').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $.ajax({
                    url: '{{ route("notifications.read_all") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            loadNotifications();
                            Toastify({
                                text: response.message,
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                style: { background: "linear-gradient(to right, #00b09b, #96c93d)" }
                            }).showToast();
                        }
                    }
                });
            });
        });
    </script>
    {{-- Push Notification — Service Worker & Subscribe --}}
    @if(config('services.vapid.public_key'))
    <script>
    (function () {
        const VAPID_PUBLIC_KEY = '{{ config('services.vapid.public_key') }}';
        const SUBSCRIBE_URL    = '{{ route('notifications.push.subscribe') }}';
        const UNSUBSCRIBE_URL  = '{{ route('notifications.push.unsubscribe') }}';
        const CSRF_TOKEN       = '{{ csrf_token() }}';

        const btn     = document.getElementById('pushToggleBtn');
        const icon    = document.getElementById('pushToggleIcon');
        const label   = document.getElementById('pushToggleLabel');

        // ─── Helpers ──────────────────────────────────────────────────────────
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const raw     = window.atob(base64);
            return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
        }

        function setButtonActive(active) {
            if (active) {
                icon.className  = 'bi bi-bell-fill text-primary';
                label.textContent = 'Push ON';
                btn.style.borderColor = '#0d6efd';
                btn.style.color       = '#0d6efd';
            } else {
                icon.className  = 'bi bi-bell-slash';
                label.textContent = 'Push';
                btn.style.borderColor = '#ccc';
                btn.style.color       = '';
            }
        }

        async function postJson(url, data) {
            const res = await fetch(url, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body:    JSON.stringify(data),
            });
            return res.json();
        }

        // ─── Subscribe ────────────────────────────────────────────────────────
        async function subscribe(reg) {
            const sub = await reg.pushManager.subscribe({
                userVisibleOnly:      true,
                applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
            });

            const json = sub.toJSON();
            await postJson(SUBSCRIBE_URL, {
                endpoint:        json.endpoint,
                keys:            json.keys,
                contentEncoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
            });

            setButtonActive(true);
            showToast('Notifikasi push diaktifkan!', '#0d6efd');
        }

        // ─── Unsubscribe ──────────────────────────────────────────────────────
        async function unsubscribe(sub) {
            const endpoint = sub.endpoint;
            await sub.unsubscribe();
            await postJson(UNSUBSCRIBE_URL, { endpoint });
            setButtonActive(false);
            showToast('Notifikasi push dinonaktifkan.', '#6c757d');
        }

        // ─── Toast helper ─────────────────────────────────────────────────────
        function showToast(text, bg) {
            if (typeof Toastify !== 'undefined') {
                Toastify({ text, duration: 3000, gravity: 'top', position: 'right',
                    style: { background: bg } }).showToast();
            }
        }

        // ─── Init: cek status subscription saat load ──────────────────────────
        async function init() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                btn.style.display = 'none'; // sembunyikan jika browser tidak support
                return;
            }

            const reg = await navigator.serviceWorker.register('/sw.js');
            const sub = await reg.pushManager.getSubscription();
            setButtonActive(!!sub);

            // ─── Tombol toggle ────────────────────────────────────────────────
            btn.addEventListener('click', async () => {
                btn.disabled = true;
                try {
                    const currentSub = await reg.pushManager.getSubscription();

                    if (currentSub) {
                        await unsubscribe(currentSub);
                    } else {
                        const permission = await Notification.requestPermission();
                        if (permission === 'granted') {
                            await subscribe(reg);
                        } else {
                            showToast('Izin notifikasi ditolak oleh browser.', '#dc3545');
                        }
                    }
                } catch (err) {
                    console.error('Push toggle error:', err);
                    showToast('Gagal mengubah status push notifikasi.', '#dc3545');
                } finally {
                    btn.disabled = false;
                }
            });

            // ─── Auto-minta izin di kunjungan pertama (jika belum pernah) ────
            if (Notification.permission === 'default') {
                setTimeout(async () => {
                    const perm = await Notification.requestPermission();
                    if (perm === 'granted') {
                        await subscribe(reg);
                    }
                }, 3000); // tunda 3 detik agar tidak langsung popup
            }
        }

        init().catch(console.error);
    })();
    </script>
    @endif
</body>

</html>
