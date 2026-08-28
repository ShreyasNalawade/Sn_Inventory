<div class="sidebar-inner">
    <div class="sidebar-heading">
        <i class="fas fa-compass" aria-hidden="true"></i>
        <span>Navigation</span>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-item">
            <a class="sidebar-link {{ request()->routeIs('admin.listofPrice') ? 'active' : '' }}"
                href="{{ route('admin.listofPrice') }}"
                @if (request()->routeIs('admin.listofPrice')) aria-current="page" @endif>
                <i class="fa-solid fa-file-contract" aria-hidden="true"></i>
                <span class="sidebar-link-label">Price List</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="sidebar-link {{ request()->routeIs('vashi-market.*') ? 'active' : '' }}"
                href="{{ route('vashi-market.index') }}"
                @if (request()->routeIs('vashi-market.*')) aria-current="page" @endif>
                <i class="fas fa-box-open" aria-hidden="true"></i>
                <span class="sidebar-link-label">Vashi Market Bill Details</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="sidebar-link" href="#">
                <i class="fas fa-users" aria-hidden="true"></i>
                <span class="sidebar-link-label">Oil Bill Details</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="sidebar-link" href="dailyCollections.html">
                <i class="fas fa-chart-line" aria-hidden="true"></i>
                <span class="sidebar-link-label">Daily Report</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="sidebar-link" href="#">
                <i class="fas fa-cogs" aria-hidden="true"></i>
                <span class="sidebar-link-label">Settings</span>
            </a>
        </li>
    </ul>
</div>