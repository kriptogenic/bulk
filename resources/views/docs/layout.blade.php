@extends('layout')

@section('content')
    <!-- details page -->
    <section class="pt-5 px-5">
        <div class="container-fluid shadow section-sm rounded">
            <div class="row">
                <!-- sidebar -->
                <div class="col-lg-3">
                    <ul class="sidenav sticky-top" style="top: 5rem">
                        <li class="sidelist {{ Route::is('docs') ? 'parent' : '' }}">
                            <a href="{{ route('docs') }}">REST API</a>
                            <ul>
                                <li class="sidelist">
                                    <a href="#create_task">Create task</a>
                                </li>
                                <li class="sidelist">
                                    <a href="#show_task">Show task</a>
                                </li>
                                <li class="sidelist">
                                    <a href="#cancel_task">Cancel task</a>
                                </li>
                            </ul>
                        </li>
                        <li class="sidelist {{ Route::is('docs.self-hosted') ? 'parent' : '' }}">
                            <a href="{{ route('docs.self-hosted') }}">Self hosted</a>
                            <ul>
                            </ul>
                        </li>
                        <li class="sidelist {{ Route::is('docs.chats-less') ? 'parent' : '' }}">
                            <a href="{{ route('docs.chats-less') }}">Broadcast without chat_id</a>
                            <ul>
                            </ul>
                        </li>
                    </ul>
                </div>

                <!-- body -->
                <div class="col-lg-8">
                    @yield('docs-content')
                </div>
            </div>
        </div>
    </section>
    <!-- /details page -->
@endsection
