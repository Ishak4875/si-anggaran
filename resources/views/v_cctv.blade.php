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
                    </span>
                </div>
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
            <div class="card-footer small text-secondary">
                Siaran memakai kuota SIM dan baterai kamera selama diputar; tekan Hentikan bila sudah selesai menonton.
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
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
                : tampil(elOnline, 'Offline', 'text-bg-danger', 'Kamera tidak terhubung ke Tuya Cloud (dicek ' + jam + '); siaran mungkin tidak bisa diputar');

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
            }
        } catch (e) {
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
