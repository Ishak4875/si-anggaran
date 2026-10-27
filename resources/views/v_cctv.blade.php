@extends('layout.v_layout')
@section('title', 'CCTV Biota')
@section('content')

@php
    $singkatan = collect(\App\Support\Satker::groups())->map(fn ($g) => $g['singkatan']);
@endphp

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h3 class="mb-0">CCTV Biota</h3>
                <small class="text-secondary">
                    @if (auth()->user()->role === 'ppk')
                        Kamera di paket {{ auth()->user()->ppk?->jabatan ?? 'PPK Anda' }}.
                    @else
                        Siaran langsung kamera per paket, dikelompokkan per satker dan PPK.
                    @endif
                </small>
            </div>
            @can('kelola-cctv')
                <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                    <a href="{{ route('kelola-cctv.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-camera-reels"></i> Kelola CCTV
                    </a>
                </div>
            @endcan
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @unless ($configured)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-1"></i>
                CCTV belum dikonfigurasi. Isi <code>TUYA_ACCESS_ID</code>, <code>TUYA_ACCESS_SECRET</code>,
                dan <code>TUYA_REGION</code> di <code>.env</code>.
            </div>
        @endunless

        @if (! $terpilih)
            <div class="card">
                <div class="card-body text-center py-5 text-secondary">
                    <i class="bi bi-camera-video-off fs-1 d-block mb-2"></i>
                    @if (auth()->user()->role === 'ppk' && ! auth()->user()->ppk_id)
                        Akun ini belum ditautkan ke PPK. Hubungi admin.
                    @else
                        Belum ada kamera yang bisa ditampilkan.
                        @can('kelola-cctv')
                            <div class="mt-2"><a href="{{ route('kelola-cctv.index') }}">Pasang kamera ke paket di Kelola CCTV</a>.</div>
                        @endcan
                    @endif
                </div>
            </div>
        @else
        <div class="row g-3">
            {{-- Daftar kamera: satker → PPK → kamera --}}
            <div class="col-lg-4 col-xl-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="bi bi-list-ul me-1"></i> Kamera ({{ $jumlah }})</h3>
                    </div>
                    <div class="card-body p-0 daftar-kamera" style="max-height: 75vh; overflow-y: auto;">
                        {{-- list-group .active bawaan Bootstrap biru terang; ikuti palet tema --}}
                        <style>
                            .daftar-kamera .list-group-item.active { background-color: var(--bs-primary); border-color: var(--bs-primary); }
                        </style>
                        @foreach ($kelompok as $slug => $perPpk)
                            <div class="px-3 py-2 bg-light border-bottom fw-bold small">
                                {{ $singkatan[$slug] ?? 'Lainnya' }}
                                <span class="fw-normal text-secondary">· {{ $namaSatker[$slug] ?? 'Paket tidak ditemukan' }}</span>
                            </div>
                            @foreach ($perPpk as $ppk => $kamera)
                                <div class="px-3 pt-2 small text-secondary">{{ $ppk }}</div>
                                <div class="list-group list-group-flush">
                                    @foreach ($kamera as $c)
                                        <a href="{{ route('cctv.index', ['kamera' => $c->id]) }}"
                                           class="list-group-item list-group-item-action py-2 {{ $c->id === $terpilih->id ? 'active' : '' }}"
                                           title="{{ $c->packet?->nmpaket ?? $c->kdpaket }}">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-circle-fill status-titik text-secondary" data-cctv="{{ $c->id }}"
                                                   style="font-size: .55em;" title="Memeriksa…"></i>
                                                <span class="fw-semibold text-truncate">{{ $c->nama }}</span>
                                            </div>
                                            <div class="small text-truncate opacity-75">{{ $c->lokasi ?: \Illuminate\Support\Str::limit($c->packet?->nmpaket ?? $c->kdpaket, 60) }}</div>
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Pemutar kamera terpilih --}}
            <div class="col-lg-8 col-xl-9">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <h3 class="card-title mb-0"><i class="bi bi-camera-video me-1"></i> {{ $terpilih->nama }}</h3>
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
                <div class="mb-1">
                    <i class="bi bi-box-seam me-1"></i>
                    @if ($terpilih->packet)
                        <span class="badge text-bg-secondary">{{ $singkatan[$terpilih->packet->satker_group] ?? '' }}</span>
                        {{ $terpilih->kdpaket }} — {{ $terpilih->packet->nmpaket }}
                        · <strong>{{ $terpilih->packet->ppk?->jabatan ?? 'Belum ada PPK' }}</strong>
                    @else
                        {{ $terpilih->kdpaket }} (paket tidak ada lagi di data API)
                    @endif
                </div>
                @if ($terpilih->lokasi)<div class="mb-1"><i class="bi bi-geo-alt me-1"></i>{{ $terpilih->lokasi }}</div>@endif
                @if ($terpilih->keterangan)<div class="mb-1"><i class="bi bi-info-circle me-1"></i>{{ $terpilih->keterangan }}</div>@endif
                Siaran memakai kuota SIM dan baterai kamera selama diputar; tekan Hentikan bila sudah selesai menonton.
            </div>
        </div>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection

@if ($terpilih)
@push('scripts')
<script>
(() => {
    // Titik status online di daftar kamera: semua kamera dari satu panggilan Tuya
    const URL_SEMUA = @json(route('cctv.status-semua'));
    const INTERVAL_MS = @json(\App\Services\TuyaCameraService::STATUS_CACHE_SECONDS * 1000);
    if (!@json($configured)) return;

    const cek = async () => {
        try {
            const res = await fetch(URL_SEMUA, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message);
            document.querySelectorAll('.status-titik').forEach(el => {
                const on = data.online[el.dataset.cctv];
                el.classList.remove('text-secondary', 'text-success', 'text-danger');
                el.classList.add(on ? 'text-success' : 'text-danger');
                el.title = on ? 'Online' : 'Offline';
            });
        } catch (e) {
            document.querySelectorAll('.status-titik').forEach(el => el.title = 'Status tidak diketahui');
        }
    };

    cek();
    setInterval(() => { if (!document.hidden) cek(); }, INTERVAL_MS);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) cek(); });
})();
</script>
<script>
(() => {
    const STATUS_URL = @json(route('cctv.status', $terpilih));
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
    const STREAM_URL = @json(route('cctv.stream', $terpilih));
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
@endif
