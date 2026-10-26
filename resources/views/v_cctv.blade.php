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
                <h3 class="card-title mb-0"><i class="bi bi-camera-video me-1"></i> Live View</h3>
                <div class="d-flex flex-wrap gap-1 me-auto" id="kameraInfo">
                    <span id="kameraOnline" class="badge text-bg-secondary" title="Status kamera menurut Tuya Cloud">
                        <i class="bi bi-circle-fill me-1" style="font-size: .55em; vertical-align: middle;"></i>
                        <span>Memeriksa…</span>
                    </span>
                    <span id="kameraBaterai" class="badge text-bg-secondary d-none">
                        <i class="bi bi-battery me-1"></i><span></span>
                    </span>                </div>
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
<script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.13/dist/hls.min.js" crossorigin="anonymous"></script>
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
<script>
(() => {
    const STATUS_URL = @json(route('cctv.status'));
    // Sama dengan lama cache status di server; lebih sering pun hanya mendapat hasil yang sama
    const INTERVAL_MS = @json(\App\Services\TuyaCameraService::STATUS_CACHE_SECONDS * 1000);
    if (!@json($configured)) { document.getElementById('kameraInfo').classList.add('d-none'); return; }

    const elOnline = document.getElementById('kameraOnline');
    const elBaterai = document.getElementById('kameraBaterai');

    const tampil = (el, teks, kelas, judul, ikon = null) => {
        el.className = 'badge ' + kelas;
        el.lastElementChild.textContent = teks;
        el.title = judul;
        if (ikon) el.firstElementChild.className = 'bi ' + ikon + ' me-1';
    };

    const ikonBaterai = (persen, isi) => {
        if (isi) return 'bi-battery-charging';
        if (persen >= 75) return 'bi-battery-full';
        if (persen >= 25) return 'bi-battery-half';
        return 'bi-battery';
    };

    const cek = async () => {
        try {
            const res = await fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message);
            const jam = new Date((data.diperiksa || Date.now() / 1000) * 1000).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            // Saat offline, baterai adalah laporan terakhir kamera
            const terakhir = data.dilaporkan
                ? ' Laporan terakhir kamera: ' + new Date(data.dilaporkan * 1000).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) + '.'
                : '';
            const catatan = data.online ? '' : ' (terakhir)';

            data.online
                ? tampil(elOnline, 'Online', 'text-bg-success', 'Kamera terhubung ke Tuya Cloud (dicek ' + jam + ')')
                : tampil(elOnline, 'Offline', 'text-bg-danger', 'Kamera tidak terhubung ke Tuya Cloud (dicek ' + jam + '); siaran tidak bisa diputar');

            if (data.baterai !== null) {
                const lemah = !data.mengisi_daya && data.baterai <= data.baterai_lemah;
                tampil(elBaterai,
                    data.baterai + '%' + (data.mengisi_daya ? ' · mengisi' : '') + catatan,
                    lemah ? 'text-bg-danger' : (data.baterai < 25 ? 'text-bg-warning' : 'text-bg-secondary'),
                    'Baterai kamera' + (data.mengisi_daya ? ', sedang dicolok/diisi daya' : ', memakai baterai')
                        + (lemah ? '; baterai lemah' : '') + '.' + terakhir,
                    ikonBaterai(data.baterai, data.mengisi_daya));
            } else {
                elBaterai.classList.add('d-none');
            }        } catch (e) {
            tampil(elOnline, 'Status tidak diketahui', 'text-bg-secondary', e.message || 'Gagal memeriksa status kamera');
            elBaterai.classList.add('d-none');
        }
    };

    cek();
    setInterval(() => { if (!document.hidden) cek(); }, INTERVAL_MS);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) cek(); });
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

    // FLV (jeda ±1–3 detik) bila browser mendukung; HLS (±10 detik) sebagai cadangan
    let mode = (window.mpegts && mpegts.getFeatureList().mseLivePlayback) ? 'flv' : 'hls';
    let player = null;
    let ulang = 0;
    let pernahTayang = false;
    let berjalan = false;

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
        if (player) { player.destroy(); player = null; }
        video.removeAttribute('src');
        video.load();
    };

    // Siaran putus (URL kedaluwarsa, kamera tidur, jaringan): sambung ulang,
    // atau pindah ke HLS bila FLV belum pernah berhasil tayang (mis. port diblokir).
    const gagal = () => {
        if (!berjalan) return;
        if (mode === 'flv' && !pernahTayang) {
            mode = 'hls';
            setStatus('FLV tidak bisa diputar, beralih ke HLS…', 'text-warning');
            putar();
        } else if (ulang++ < MAKS_ULANG) {
            setStatus('Siaran terputus, menyambung ulang…', 'text-warning');
            putar();
        } else {
            hentikan('Siaran gagal dimuat. Pastikan kamera online, lalu coba lagi.', 'text-danger');
        }
    };

    const pasangFlv = (url) => {
        const p = mpegts.createPlayer({ type: 'flv', isLive: true, url }, {
            enableStashBuffer: false,
            lazyLoad: false,
            liveBufferLatencyChasing: true,
            liveBufferLatencyMaxLatency: 1.5,
            liveBufferLatencyMinRemain: 0.3,
        });
        p.attachMediaElement(video);
        p.on(mpegts.Events.ERROR, gagal);
        p.on(mpegts.Events.LOADING_COMPLETE, gagal); // server menutup siaran
        p.load();
        p.play();
        return { destroy: () => { p.pause(); p.unload(); p.detachMediaElement(); p.destroy(); } };
    };

    const pasangHls = (url) => {
        if (window.Hls && Hls.isSupported()) {
            const h = new Hls({
                liveDurationInfinity: true,
                liveSyncDurationCount: 1,       // putar dekat ujung siaran
                liveMaxLatencyDurationCount: 3,
                maxLiveSyncPlaybackRate: 1.5,   // kejar ketertinggalan dengan sedikit mempercepat
            });
            h.loadSource(url);
            h.attachMedia(video);
            h.on(Hls.Events.MANIFEST_PARSED, () => video.play().catch(() => {}));
            h.on(Hls.Events.ERROR, (_e, d) => { if (d.fatal) gagal(); });
            return h;
        }
        if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = url; // Safari / iOS
            video.play().catch(() => {});
            return null;
        }
        throw new Error('Browser tidak mendukung pemutaran siaran.');
    };

    const putar = async () => {
        berjalan = true;
        btnPutar.disabled = true;
        setStatus('Menghubungkan ke kamera…');
        try {
            const url = await ambilUrl();
            if (!berjalan) return; // keburu dihentikan
            lepas();
            player = mode === 'flv' ? pasangFlv(url) : pasangHls(url);
            btnStop.classList.remove('d-none');
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
        ulang = 0;
        pernahTayang = pernahTayang || mode === 'flv';
        setStatus('Siaran langsung (' + mode.toUpperCase() + ')', 'text-success');
    });
})();
</script>
@endpush
