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
        transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
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
    }

    /* Style for the Logo */
    .loader-logo {
        width: 150px;
        /* Sesuaikan ukuran logo */
        height: auto;
        /* Menambahkan animasi CSS murni */
        animation: pulse 1.5s infinite ease-in-out alternate;
    }

    /* Keyframes untuk efek detak jantung (pulsing) */
    @keyframes pulse {
        0% {
            transform: scale(0.9);
            opacity: 1;
        }

        100% {
            transform: scale(1.1);
            opacity: 1;
        }
    }
</style>

<div id="global-loader">
    <!-- Memuat SVG logo bawaan Anda di dalam card melingkar -->
    <div class="loader-card">
        <img src="{{ asset('images/logo/SIMLAB_logo1.svg') }}" alt="Loading Logo" class="loader-logo">
    </div>
</div>

<script>
    // Menggunakan Vanilla JS murni agar aman dan dieksekusi secepat mungkin
    (function() {
        const hideLoader = function() {
            const loader = document.getElementById('global-loader');
            if (loader) {
                // Tambahkan delay kecil agar animasi terlihat mulus sebelum hilang
                setTimeout(() => {
                    loader.classList.add('hidden');
                }, 300);
            }
        };

        // Tunggu sampai seluruh resource halaman (gambar, css) selesai dimuat
        if (document.readyState === 'complete') {
            hideLoader();
        } else {
            window.addEventListener('load', hideLoader);
        }

        // Failsafe: Jika ada gambar atau koneksi lambat, paksakan hilang setelah 5 detik
        setTimeout(hideLoader, 5000);
    })();
</script>
