@extends('layout.v_layout')
@section('title', 'Daftar Paket CCTV')
@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h3 class="mb-0">Daftar Paket CCTV</h3>
                <small class="text-secondary">
                    Paket pekerjaan yang sudah terpasang CCTV, beserta PPK-nya ({{ $jumlahPaket }} paket, {{ $jumlahKamera }} kamera).
                </small>
            </div>
            <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                <a href="{{ route('cctv.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-camera-video"></i> Lihat CCTV
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @if (empty($kelompok))
            <div class="card">
                <div class="card-body text-center py-5 text-secondary">
                    <i class="bi bi-camera-video-off fs-1 d-block mb-2"></i>
                    Belum ada paket yang terpasang CCTV.
                </div>
            </div>
        @else
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="cariPaket" class="form-control" autocomplete="off"
                               placeholder="Cari kode paket, nama paket, PPK, atau kamera…">
                    </div>
                </div>
            </div>

            @foreach ($kelompok as $slug => $daftar)
                <div class="card mb-3 seksi-satker">
                    <div class="card-header">
                        <h3 class="card-title mb-0">{{ $namaSatker[$slug] ?? $slug }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="min-width:820px">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px" class="text-center">No.</th>
                                        <th style="min-width:340px">Paket Pekerjaan</th>
                                        <th style="min-width:200px">PPK</th>
                                        <th style="min-width:220px">Kamera</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftar as $baris)
                                        @php
                                            $p = $baris['paket'];
                                            $cari = collect([$p->kdpaket, $p->nmpaket, $p->ppk?->jabatan, $p->ppk?->nama])
                                                ->merge($baris['kamera']->pluck('nama'))->implode(' ');
                                        @endphp
                                        <tr data-cari="{{ \Illuminate\Support\Str::lower($cari) }}">
                                            <td class="text-center nomor"></td>
                                            <td>
                                                <span class="small text-secondary">{{ $p->kdpaket }}</span>
                                                <div>{{ $p->nmpaket }}</div>
                                            </td>
                                            <td>
                                                @if ($p->ppk)
                                                    <div>{{ $p->ppk->jabatan }}</div>
                                                    <div class="small text-secondary">{{ $p->ppk->nama ?: '—' }}</div>
                                                @else
                                                    <span class="text-secondary">Belum ditetapkan</span>
                                                @endif
                                            </td>
                                            <td>
                                                @foreach ($baris['kamera'] as $c)
                                                    <a href="{{ route('cctv.index', ['kamera' => $c->id]) }}" class="d-block text-decoration-none">
                                                        <i class="bi bi-camera-video me-1"></i>{{ $c->nama }}
                                                    </a>
                                                    @if ($c->lokasi)<div class="small text-secondary ms-3">{{ $c->lokasi }}</div>@endif
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

            <div id="barisKosong" class="text-center text-secondary py-4 d-none">Tidak ada paket yang cocok dengan pencarian.</div>
        @endif

    </div>
</div>

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('cariPaket');
        if (!input) return;

        // Saring baris; nomor diurutkan ulang per satker, satker tanpa hasil disembunyikan
        function saring() {
            const q = input.value.trim().toLowerCase();
            let total = 0;
            document.querySelectorAll('.seksi-satker').forEach(seksi => {
                let n = 0;
                seksi.querySelectorAll('tbody tr').forEach(tr => {
                    const cocok = !q || tr.dataset.cari.includes(q);
                    tr.classList.toggle('d-none', !cocok);
                    if (cocok) tr.querySelector('.nomor').textContent = ++n;
                });
                seksi.classList.toggle('d-none', n === 0);
                total += n;
            });
            document.getElementById('barisKosong').classList.toggle('d-none', total > 0);
        }

        input.addEventListener('input', saring);
        saring();
    })();
</script>
@endpush

@endsection
