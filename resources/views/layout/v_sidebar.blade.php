<div class="sidebar-wrapper">
    <nav class="mt-2">
        <!--begin::Sidebar Menu-->
        <ul
            class="nav sidebar-menu flex-column"
            data-lte-toggle="treeview"
            role="navigation"
            aria-label="Main navigation"
            data-accordion="false"
            id="navigation">

            {{-- Dashboard --}}
            <li class="nav-item">
                <a href="{{ route('dashboard') }}"
                   class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-speedometer"></i>
                    <p>Dashboard</p>
                </a>
            </li>

            {{-- Paket Kegiatan per Satker --}}
            <li class="nav-item {{ request()->routeIs('satker.show') ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->routeIs('satker.show') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-diagram-3"></i>
                    <p>
                        Paket per Satker
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @foreach ($satkerGroups as $slug => $group)
                        <li class="nav-item">
                            <a href="{{ route('satker.show', $slug) }}"
                               class="nav-link {{ request()->routeIs('satker.show') && request()->route('slug') === $slug ? 'active' : '' }}">
                                <i class="nav-icon bi bi-dot"></i>
                                <p>
                                    {{ $group['singkatan'] }}
                                    @if (($satkerCounts[$slug] ?? 0) > 0)
                                        <span class="nav-badge badge text-bg-secondary">{{ $satkerCounts[$slug] }}</span>
                                    @endif
                                </p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>

            {{-- Kelola Revisi Pagu --}}
            <li class="nav-item">
                <a href="{{ route('pagu.index') }}"
                   class="nav-link {{ request()->routeIs('pagu.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-bar-chart-steps"></i>
                    <p>Kelola Revisi Pagu</p>
                </a>
            </li>

            {{-- Kelola PPK --}}
            <li class="nav-item">
                <a href="{{ route('ppk.index') }}"
                   class="nav-link {{ request()->routeIs('ppk.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-person-badge"></i>
                    <p>Kelola PPK</p>
                </a>
            </li>

            {{-- Kelola Akun (Super Admin Only) --}}
            @if (Auth::user()->isAdmin())
                <li class="nav-item">
                    <a href="{{ route('users.index') }}"
                       class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-people"></i>
                        <p>Kelola Akun</p>
                    </a>
                </li>
            @endif

        </ul>
        <!--end::Sidebar Menu-->
    </nav>
</div>
