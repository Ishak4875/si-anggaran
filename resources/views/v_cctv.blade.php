@extends('layout.v_layout')
@section('title', 'CCTV Biota')
@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0">CCTV Biota</h3>
        <small class="text-secondary">Siaran langsung kamera BIOTA Smart Camera Edge 4G.</small>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @unless ($configured)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-1"></i>
                CCTV belum dikonfigurasi. Isi <code>TUYA_ACCESS_ID</code>, <code>TUYA_ACCESS_SECRET</code>,
                <code>TUYA_REGION</code>, dan <code>TUYA_DEVICE_ID</code> di <code>.env</code>.
            </div>
        @endunless

        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h3 class="card-title mb-0 me-auto"><i class="bi bi-camera-video me-1"></i> Live View</h3>
                <span id="cctvStatus" class="text-secondary small">Siaran belum diputar.</span>
                <button type="button" id="btnPutar" class="btn btn-primary btn-sm" @disabled(! $configured)>
                    <i class="bi bi-play-fill"></i> Putar
                </button>
                <button type="button" id="btnStop" class="btn btn-outline-secondary btn-sm d-none">
                    <i class="bi bi-stop-fill"></i> Hentikan
                </button>
            </div>
            <div class="card-body p-0 bg-black">
                <video id="cctvVideo" class="w-100 d-block" style="max-height: 75vh; aspect-ratio: 16 / 9;"
                       muted playsinline controls></video>
            </div>
            <div class="card-body border-top">
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-4">
                    <div class="ptz-pad" aria-label="Kontrol arah kamera">
                        @foreach ([[null, null, null], [0, 'bi-arrow-up', 'Atas'], [null, null, null],
                                   [6, 'bi-arrow-left', 'Kiri'], ['tengah', null, null], [2, 'bi-arrow-right', 'Kanan'],
                                   [null, null, null], [4, 'bi-arrow-down', 'Bawah'], [null, null, null]] as [$arah, $ikon, $label])
                            @if ($arah === 'tengah')
                                <span class="ptz-tengah"><i class="bi bi-camera-video"></i></span>
                            @elseif ($arah === null)
                                <span></span>
                            @else
                                <button type="button" class="btn btn-outline-secondary ptz-btn" data-arah="{{ $arah }}"
                                        title="{{ $label }}" aria-label="{{ $label }}" @disabled(! $configured)>
                                    <i class="bi {{ $ikon }}"></i>
                                </button>
                            @endif
                        @endforeach
                    </div>
                    <div class="small text-secondary" style="max-width: 22rem;">
                        <strong>Gerakkan kamera:</strong> tekan dan tahan tombol arah; kamera berhenti saat tombol dilepas.
                        Putar siaran terlebih dahulu untuk melihat hasil gerakannya.
                        <div id="ptzStatus" class="mt-1"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer small text-secondary">
                Siaran memakai kuota SIM dan baterai kamera selama diputar; tekan Hentikan bila sudah selesai menonton.
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<style>
    .ptz-pad { display: grid; grid-template-columns: repeat(3, 3rem); gap: .35rem; }
    .ptz-btn, .ptz-tengah { width: 3rem; height: 3rem; display: flex; align-items: center; justify-content: center; }
    .ptz-btn { padding: 0; font-size: 1.25rem; touch-action: none; user-select: none; }
    .ptz-tengah { color: var(--bs-secondary-color); }
</style>
<script>
(() => {
    const PTZ_URL = @json(route('cctv.ptz'));
    const CSRF = @json(csrf_token());
    const BATAS_TAHAN_MS = 5000; // pengaman: berhenti sendiri bila tombol tertahan terlalu lama
    const status = document.getElementById('ptzStatus');

    let aktif = null;
    let pengaman = null;

    const kirim = async (arah) => {
        const res = await fetch(PTZ_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ arah: String(arah) }),
        });
        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            throw new Error(data.message || 'Gagal menggerakkan kamera.');
        }
    };

    const tampil = (teks, kelas) => {
        status.className = 'mt-1 ' + kelas;
        status.textContent = teks;
    };

    const mulai = (btn) => {
        if (aktif) return;
        aktif = btn;
        btn.classList.add('active');
        tampil('Menggerakkan kamera…', 'text-secondary');
        kirim(btn.dataset.arah).catch((e) => tampil(e.message, 'text-danger'));
        pengaman = setTimeout(berhenti, BATAS_TAHAN_MS);
    };

    const berhenti = () => {
        if (!aktif) return;
        clearTimeout(pengaman);
        aktif.classList.remove('active');
        aktif = null;
        kirim('stop')
            .then(() => tampil('', ''))
            .catch((e) => tampil(e.message, 'text-danger'));
    };

    document.querySelectorAll('.ptz-btn').forEach((btn) => {
        btn.addEventListener('pointerdown', (e) => { e.preventDefault(); mulai(btn); });
        ['pointerup', 'pointerleave', 'pointercancel'].forEach((ev) => btn.addEventListener(ev, berhenti));
        btn.addEventListener('contextmenu', (e) => e.preventDefault());
    });
    window.addEventListener('blur', berhenti);
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/mpegts.js@1.7.3/dist/mpegts.min.js" crossorigin="anonymous"></script>
<script>
(() => {
    const STREAM_URL = @json(route('cctv.stream'));
    const MAKS_ULANG = 3;

    const video = document.getElementById('cctvVideo');
    const status = document.getElementById('cctvStatus');
    const btnPutar = document.getElementById('btnPutar');
    const btnStop = document.getElementById('btnStop');

    // FLV lewat mpegts.js. HLS hanya untuk browser tanpa MSE (Safari iPhone lama) yang
    // memutarnya secara native: hls.js tidak bisa dipakai karena server Tuya tidak mengirim
    // header CORS untuk playlist HLS, sedangkan <video src> native tidak terkena CORS.
    const mode = (window.mpegts && mpegts.getFeatureList().mseLivePlayback) ? 'flv' : 'hls';
    const BATAS_TUNGGU_MS = 15000; // gambar pertama biasanya datang 2–7 detik setelah Putar
    let player = null;
    let ulang = 0;
    let berjalan = false;
    let pengawas = null;

    const setStatus = (teks, kelas = 'text-secondary') => {
        status.className = 'small ' + kelas;
        status.textContent = teks;
    };

    const ambilUrl = async () => {
        const res = await fetch(STREAM_URL + '?type=' + mode, { headers: { 'Accept': 'application/json' } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.url) throw new Error(data.message || 'Gagal memuat siaran.');
        return data.url;
    };

    const lepas = () => {
        clearTimeout(pengawas);
        if (player) { player.destroy(); player = null; }
        video.removeAttribute('src');
        video.load();
    };

    // Siaran putus atau tidak kunjung tayang (URL kedaluwarsa, kamera tidur, jaringan):
    // minta URL baru beberapa kali, lalu menyerah dengan pesan yang jelas.
    const gagal = () => {
        if (!berjalan) return;
        if (ulang++ < MAKS_ULANG) {
            setStatus('Siaran terputus, menyambung ulang…', 'text-warning');
            putar();
        } else {
            hentikan('Video belum diterima dari kamera. Tekan Putar lagi; bila tetap gagal, '
                + 'periksa kamera di aplikasi Smart Life (online / sinyal 4G).', 'text-danger');
        }
    };

    const pasangFlv = (url) => {
        const p = mpegts.createPlayer({ type: 'flv', isLive: true, url }, {
            // Sisakan cadangan ±3 detik supaya naik-turunnya sinyal 4G kamera tidak
            // membuat video tersendat; ketertinggalan dikejar dengan sedikit mempercepat
            // pemutaran (liveSync), bukan melompat (latency chasing).
            enableStashBuffer: true,
            lazyLoad: false,
            liveBufferLatencyChasing: false,
            liveSync: true,
            liveSyncTargetLatency: 3,
            liveSyncMaxLatency: 6,
            liveSyncPlaybackRate: 1.1,
        });
        p.attachMediaElement(video);
        p.on(mpegts.Events.ERROR, gagal);
        p.on(mpegts.Events.LOADING_COMPLETE, gagal); // server menutup siaran
        p.load();
        Promise.resolve(p.play()).catch(() => {}); // dibatalkan saat Hentikan/sambung ulang: wajar
        return { destroy: () => { p.pause(); p.unload(); p.detachMediaElement(); p.destroy(); } };
    };

    const pasangHlsNative = (url) => {
        if (!video.canPlayType('application/vnd.apple.mpegurl')) {
            throw new Error('Browser tidak mendukung pemutaran siaran.');
        }
        video.src = url;
        video.play().catch(() => {});
        return null;
    };

    const putar = async () => {
        berjalan = true;
        btnPutar.disabled = true;
        setStatus(ulang ? 'Menyambung ulang…' : 'Menghubungkan ke kamera… (biasanya ±5–10 detik)');
        try {
            const url = await ambilUrl();
            if (!berjalan) return; // keburu dihentikan
            lepas();
            player = mode === 'flv' ? pasangFlv(url) : pasangHlsNative(url);
            btnStop.classList.remove('d-none');
            // Tidak ada gambar sama sekali dalam batas waktu: anggap gagal (biasanya kamera tidur)
            pengawas = setTimeout(() => { if (video.readyState < 3) gagal(); }, BATAS_TUNGGU_MS);
        } catch (err) {
            hentikan(err.message, 'text-danger');
        } finally {
            btnPutar.disabled = false;
        }
    };

    const hentikan = (pesan = 'Siaran dihentikan.', kelas = 'text-secondary') => {
        berjalan = false;
        lepas();
        btnStop.classList.add('d-none');
        setStatus(pesan, kelas);
    };

    btnPutar.addEventListener('click', () => { ulang = 0; putar(); });
    btnStop.addEventListener('click', () => hentikan());
    video.addEventListener('playing', () => {
        clearTimeout(pengawas);
        ulang = 0;
        setStatus('Siaran langsung', 'text-success');
    });
})();
</script>
@endpush
