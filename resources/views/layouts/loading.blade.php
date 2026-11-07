@php
    $enableLoader = \App\Models\Setting::boolean(\App\Models\Setting::THEME_ENABLE_LOADER, 'global', true);
    $loaderSpeed  = (int) \App\Models\Setting::get(\App\Models\Setting::THEME_LOADER_SPEED, 'global', 3000);
@endphp

@if ($enableLoader)
<style>
    /* Styling for the loading overlay */
    #global-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        /* Efek Glassmorphism: Transparan dan Blur */
        background-color: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        z-index: 999999;
        display: flex;
        justify-content: center;
        align-items: center;
        /* Efek fade out saat loading selesai */
        transition: opacity 0.4s ease-out, visibility 0.4s ease-out;
    }

    html[data-bs-theme="dark"] #global-loader {
        background-color: rgba(26, 27, 46, 0.6);
    }

    #global-loader.hidden {
        opacity: 0;
        visibility: hidden;
    }

    /* Style untuk card putih melingkar */
    .loader-card {
        background-color: rgba(255, 255, 255, 0.85);
        padding: 2rem 3rem;
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: center;
        align-items: center;
        transition: background-color 0.3s ease;
    }

    html[data-bs-theme="dark"] .loader-card {
        background-color: rgba(30, 31, 53, 0.9);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
    }

    /* Style for the Logo */
    .loader-logo {
        width: 150px;
        height: auto;
        animation: pulse 1.5s infinite ease-in-out alternate;
    }

    /* Keyframes untuk efek detak jantung (pulsing) */
    @keyframes pulse {
        0% {
            transform: scale(0.9);
            opacity: 0.9;
        }

        100% {
            transform: scale(1.1);
            opacity: 1;
        }
    }
</style>

<div id="global-loader">
    <div class="loader-card">
        <img src="{{ asset('images/logo/SIMLAB_logo1.svg') }}" alt="Loading Logo" class="loader-logo">
    </div>
</div>

<script>
    (function() {
        const hideLoader = function() {
            const loader = document.getElementById('global-loader');
            if (loader) {
                setTimeout(() => {
                    loader.classList.add('hidden');
                }, 200);
            }
        };

        if (document.readyState === 'complete') {
            hideLoader();
        } else {
            window.addEventListener('load', hideLoader);
        }

        // Failsafe dengan durasi konfigurasi pengaturan sistem
        setTimeout(hideLoader, {{ $loaderSpeed }});
    })();
</script>
@endif
