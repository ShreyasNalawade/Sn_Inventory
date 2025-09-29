<div class="container-fluid">
    <!-- Sidebar toggle button for mobile on the left -->
    <button class="navbar-toggler d-lg-none me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar"
        aria-controls="sidebar" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fas fa-bars" style="color: white"></i>
    </button>
    <!-- Logo/Brand Name -->
    <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="/assets/icon/favicon-bg-remove.png" alt="Logo" width="40" height="40" class="me-2" /><span
            class="d-none d-lg-inline">Sandip Oil Depo</span>
    </a>
    <!-- Right-aligned nav items -->
    <div class="ms-auto d-flex align-items-center">
        <ul class="navbar-nav flex-row">
            <!-- Profile Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle me-1"></i> Profile
                </a>
                <ul class="dropdown-menu dropdown-menu-end position-absolute" aria-labelledby="profileDropdown">
                    <li>
                        <a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a>
                    </li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" id="logout-btn"><i
                                class="fas fa-sign-out-alt me-2"></i>Logout</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>