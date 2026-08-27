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
    {{-- Push Notification & Browser Recommendation Prompt --}}
    <!-- Floating Notification Recommendation Card -->
    <div id="notificationRecommendationPrompt" class="card shadow-lg border-0 border-start border-4 border-primary position-fixed d-none"
        style="bottom: 24px; right: 24px; z-index: 9999; max-width: 380px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.18) !important; transition: all 0.3s ease;">
        <div class="card-body p-3">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bi bi-bell-fill fs-5"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-bold text-dark">Aktifkan Notifikasi Browser</h6>
                    <p class="mb-2 small text-muted">Dapatkan pembaruan langsung terkait status persetujuan peminjaman lab & presensi harian di browser Anda.</p>
                    <div class="d-flex gap-2">
                        <button id="btnAllowNotificationNow" class="btn btn-primary btn-sm px-3 fw-semibold">
                            <i class="bi bi-bell me-1"></i> Izinkan
                        </button>
                        <button id="btnDismissNotificationPrompt" class="btn btn-light btn-sm text-muted">
                            Nanti Saja
                        </button>
                    </div>
                </div>
                <button type="button" class="btn-close ms-1 flex-shrink-0" style="font-size: 0.7rem;" id="btnCloseNotificationPrompt"></button>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const VAPID_PUBLIC_KEY = '{{ config('services.vapid.public_key') }}';
        const SUBSCRIBE_URL    = '{{ route('notifications.push.subscribe') }}';
        const UNSUBSCRIBE_URL  = '{{ route('notifications.push.unsubscribe') }}';
        const CSRF_TOKEN       = '{{ csrf_token() }}';

        const btnToggle   = document.getElementById('pushToggleBtn');
        const iconToggle  = document.getElementById('pushToggleIcon');
        const labelToggle = document.getElementById('pushToggleLabel');
        const promptCard  = document.getElementById('notificationRecommendationPrompt');
        const btnAllow    = document.getElementById('btnAllowNotificationNow');
        const btnDismiss  = document.getElementById('btnDismissNotificationPrompt');
        const btnClose    = document.getElementById('btnCloseNotificationPrompt');

        // ─── Helpers ──────────────────────────────────────────────────────────
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const raw     = window.atob(base64);
            return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
        }

        function setButtonActive(active) {
            if (!btnToggle) return;
            if (active) {
                iconToggle.className  = 'bi bi-bell-fill text-primary';
                labelToggle.textContent = 'Push ON';
                btnToggle.style.borderColor = '#0d6efd';
                btnToggle.style.color       = '#0d6efd';
            } else {
                iconToggle.className  = 'bi bi-bell-slash';
                labelToggle.textContent = 'Push';
                btnToggle.style.borderColor = '#ccc';
                btnToggle.style.color       = '';
            }
        }

        function showToast(text, bg) {
            if (typeof Toastify !== 'undefined') {
                Toastify({
                    text: text,
                    duration: 3500,
                    gravity: "top",
                    position: "right",
                    style: { background: bg }
                }).showToast();
            }
        }

        function hidePrompt() {
            if (promptCard) {
                promptCard.classList.add('d-none');
            }
        }

        function dismissPromptFor24Hours() {
            hidePrompt();
            const expireTime = Date.now() + (24 * 60 * 60 * 1000); // 24 jam
            localStorage.setItem('notif_prompt_dismissed_until', expireTime);
        }

        async function postJson(url, data) {
            const res = await fetch(url, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body:    JSON.stringify(data),
            });
            return res.json();
        }

        // ─── Subscribe Web Push ───────────────────────────────────────────────
        async function subscribe(reg) {
            try {
                if (VAPID_PUBLIC_KEY && VAPID_PUBLIC_KEY.length > 0) {
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
                }

                setButtonActive(true);
                hidePrompt();
                showToast('Notifikasi browser berhasil diaktifkan!', 'linear-gradient(to right, #00b09b, #96c93d)');

                // Test desktop notification feedback
                if (Notification.permission === 'granted') {
                    new Notification('SIMLAB — Notifikasi Aktif', {
                        body: 'Anda akan menerima pemberitahuan otomatis seputar peminjaman dan presensi lab.',
                        icon: '/images/logo/SIMLAB_logo1.png'
                    });
                }
            } catch (err) {
                console.error('Subscription error:', err);
                showToast('Notifikasi diaktifkan (mode standar browser).', '#0d6efd');
                setButtonActive(true);
                hidePrompt();
            }
        }

        // ─── Unsubscribe Web Push ─────────────────────────────────────────────
        async function unsubscribe(sub) {
            const endpoint = sub.endpoint;
            await sub.unsubscribe();
            await postJson(UNSUBSCRIBE_URL, { endpoint });
            setButtonActive(false);
            showToast('Notifikasi push dinonaktifkan.', '#6c757d');
        }

        // ─── Request Permission & Activate ────────────────────────────────────
        async function requestAndActivateNotification(reg) {
            try {
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    await subscribe(reg);
                } else if (permission === 'denied') {
                    hidePrompt();
                    showToast('Izin notifikasi diblokir oleh browser. Anda dapat mengaktifkannya via ikon gembok di URL.', '#dc3545');
                } else {
                    hidePrompt();
                }
            } catch (err) {
                console.error('Permission request error:', err);
            }
        }

        // ─── Main Init ────────────────────────────────────────────────────────
        async function init() {
            if (!('Notification' in window)) {
                if (btnToggle) btnToggle.style.display = 'none';
                return;
            }

            let reg = null;
            if ('serviceWorker' in navigator) {
                try {
                    reg = await navigator.serviceWorker.register('/sw.js');
                } catch (e) {
                    console.warn('Service Worker registration skipped:', e);
                }
            }

            // Cek status subscription saat ini
            if (reg && 'PushManager' in window) {
                try {
                    const currentSub = await reg.pushManager.getSubscription();
                    setButtonActive(!!currentSub || Notification.permission === 'granted');
                } catch (e) {
                    setButtonActive(Notification.permission === 'granted');
                }
            } else {
                setButtonActive(Notification.permission === 'granted');
            }

            // ─── Tampilkan Rekomendasi jika Belum Diaktifkan ──────────────────
            if (Notification.permission === 'default') {
                const dismissedUntil = localStorage.getItem('notif_prompt_dismissed_until');
                const isDismissed = dismissedUntil && Date.now() < parseInt(dismissedUntil, 10);

                if (!isDismissed && promptCard) {
                    // Tampilkan rekomendasi setelah delay singkat 1.5 detik
                    setTimeout(() => {
                        promptCard.classList.remove('d-none');
                    }, 1500);
                }
            }

            // ─── Event Listener Tombol Rekomendasi ─────────────────────────────
            if (btnAllow) {
                btnAllow.addEventListener('click', () => {
                    requestAndActivateNotification(reg);
                });
            }

            if (btnDismiss) {
                btnDismiss.addEventListener('click', () => {
                    dismissPromptFor24Hours();
                });
            }

            if (btnClose) {
                btnClose.addEventListener('click', () => {
                    dismissPromptFor24Hours();
                });
            }

            // ─── Event Listener Tombol Navbar Toggle ──────────────────────────
            if (btnToggle) {
                btnToggle.addEventListener('click', async () => {
                    btnToggle.disabled = true;
                    try {
                        if (reg && 'PushManager' in window) {
                            const currentSub = await reg.pushManager.getSubscription();
                            if (currentSub) {
                                await unsubscribe(currentSub);
                            } else {
                                await requestAndActivateNotification(reg);
                            }
                        } else {
                            await requestAndActivateNotification(reg);
                        }
                    } catch (err) {
                        console.error('Push toggle error:', err);
                    } finally {
                        btnToggle.disabled = false;
                    }
                });
            }
        }

        // Jalankan saat DOM siap
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    </script>
</body>

</html>
