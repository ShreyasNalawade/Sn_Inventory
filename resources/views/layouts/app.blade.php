<!DOCTYPE html>
<html lang="en">

@include('layouts.head')

<body>
    <!-- Header Bar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        @include('layouts.header')
    </nav>

    <div class="page-wrapper">
        <!-- Sidebar -->
        <nav class="sidebar collapse d-lg-block" id="sidebar">
            @include('layouts.sidebar')
        </nav>
        <!-- Sidebar overlay to close sidebar on click outside -->
        <div class="sidebar-overlay d-lg-none" onclick="$('#sidebar').removeClass('show');"></div>
        <!-- Main Content -->
        <div class="main-content">@yield('content')</div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->
    <script src="{{ asset('assets/js/main-new.js') }}"></script>
    @yield('scripts')

</body>

</html>