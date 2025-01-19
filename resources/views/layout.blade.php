<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8">
    <title>Zaraz documentation</title>

    <!-- mobile responsive meta -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- theme meta -->
    <meta name="theme-name" content="godocs"/>

    <!-- ** Plugins Needed for the Project ** -->
    <!-- plugins -->
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/themify-icons/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/highlight/styles/default.min.css') }}">
    <!-- Main Stylesheet -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <!--Favicon-->
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

</head>

<body>

<header class="sticky-top navigation" style="z-index: 1024;">
    <div class=container>
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent">
            <a class=navbar-brand href="{{ route('home') }}}">
                <img style="max-width: 160px" class="img-fluid" src="{{ asset('zaraz-red.svg') }}" alt="godocs">
            </a>
            <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#navigation">
                <i class="ti-align-right h4 text-dark"></i></button>
            <div class="collapse navbar-collapse text-center" id=navigation>
                <ul class="navbar-nav mx-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('docs') }}">Docs</a></li>
                </ul>
                <a href="{{ route('moonshine.index') }}" class="btn btn-sm btn-outline-primary ml-lg-4">Login</a>
                <a href="https://github.com/kriptogenic/zaraz" target="_blank" class="btn btn-sm btn-primary ml-lg-4">
                    <i class="ti-github"></i>
                    Source code
                </a>
            </div>
        </nav>
    </div>
</header>
@yield('content')
<footer>
    <div class="container">
        <div class="row align-items-center py-5">
            <div class="col-lg-4">
                <ul class="list-inline footer-menu text-center text-lg-left">
                    <li class="list-inline-item"><a href="changelog.html">Changelog</a></li>
                    <li class="list-inline-item"><a href="contact.html">contact</a></li>
                    <li class="list-inline-item"><a href="search.html">Search</a></li>
                </ul>
            </div>
            <div class="col-lg-4 text-center mb-4 mb-lg-0">
            </div>
            <div class="col-lg-4">
                <ul class="list-inline social-icons text-lg-right text-center">
                    <li class="list-inline-item">
                        <a href="https://t.me/kriptonuz">
                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round"
                                 class="icon icon-tabler icons-tabler-outline icon-tabler-brand-telegram">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4"/>
                            </svg>
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="https://github.com/kriptogenic/zaraz">
                            <i class="ti-github"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>
<!-- plugins -->
<script src="{{ asset('plugins/jQuery/jquery.min.js') }}"></script>
<script src="{{ asset('plugins/bootstrap/bootstrap.min.js') }}"></script>
<script src="{{ asset('plugins/masonry/masonry.min.js') }}"></script>
<script src="{{ asset('plugins/clipboard/clipboard.min.js') }}"></script>
<script src="{{ asset('plugins/match-height/jquery.matchHeight-min.js') }}"></script>
<script src="{{ asset('plugins/highlight/highlight.js') }}"></script>

<!-- Main Script -->
<script src="{{ asset('js/script.js') }}"></script>

</body>
</html>
