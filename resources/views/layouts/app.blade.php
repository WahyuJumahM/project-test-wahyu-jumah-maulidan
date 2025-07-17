<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Suitmedia Ideas')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'Explore innovative ideas and insights from Suitmedia')">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="nav-brand">
                <img src="{{ asset('images/suitmedia-1.png') }}" alt="suitmedia">
            </div>
            <nav class="nav-menu">
                <ul>
                    <li><a href="#" class="nav-link">Work</a></li>
                    <li><a href="#" class="nav-link">About</a></li>
                    <li><a href="#" class="nav-link">Services</a></li>
                    <li><a href="{{ route('ideas.index') }}" class="nav-link {{ request()->routeIs('ideas.*') ? 'active' : '' }}">Ideas</a></li>
                    <li><a href="#" class="nav-link">Careers</a></li>
                    <li><a href="#" class="nav-link">Contact</a></li>
                </ul>
            </nav>


        </div>
    </header>

    <main>
        @if(isset($error))
            <div class="alert alert-error">
                {{ $error }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }}Project-Test-Wahyu. All rights reserved.</p>
        </div>
    </footer>

    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            baseUrl: '{{ url("/") }}',
            locale: '{{ app()->getLocale() }}'
        };
    </script>

    <script src="{{ asset('js/script.js') }}"></script>

    <!-- Additional Scripts -->
    @yield('scripts')
</body>
</html>
