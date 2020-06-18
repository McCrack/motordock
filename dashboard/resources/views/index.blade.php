<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'C-BBLe') }}</title>

    <!-- Styles -->
    <link href="{{ asset('fonts/icomoon/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components.css') }}" rel="stylesheet">
    <link href="{{ asset('css/layouts.css') }}" rel="stylesheet">
    <link href="{{ asset('css/themes/light.css') }}" rel="stylesheet">
    @yield('styles')
    
    <script src="{{ asset('js/C-BBLe.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    @yield('scripts')
</head>
<body>
    <section class="wrapper">
        <header class="card flex justify-between align-items-center px-1">
            <div>
                <span class="dark-txt font-size-18">
                    {{ config('app.name', 'Laravel') }}
                </span>
            </div>
            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                {{ csrf_field() }}

                <span class="font-size-13 dark-txt">{{ Auth::user()->name }}</span>
                <button class="btn btn-sm btn-dark" type="submit">Loagout</button>
            </form>
        </header>
        <aside>
            <div class="tabs p-1">
                <input id="modules-tab" type="radio" name="sidebar-tabs" hidden autocomplete="on">
                <label for="modules-tab" class="tab-btn dark-color">Modules</label>
                <div class="tab">
                    <fieldset class="card rounded-3 my-5 p-2">
                        <div class="card-title font-size-20 text-bold mb-20">Modules</div>
                        <a class="block @if($module == 'Users') active-txt @else dark-txt @endif font-size-14 text-bold my-5" href="/Users">Team</a>
                        <a class="block @if($module == 'eBay') active-txt @else dark-txt @endif font-size-14 text-bold my-5" href="/eBay">eBay</a>
                    </fieldset>
                </div>
                @yield('sidebar')
                <br clear="left">
            </div>
        </aside>
        <main class="p-1">
        @yield('layout')
        </main>
    </section>
    <form id="save-indicator" class="fixed right bottom w-64 h-64 light-bg text-center rounded-50 m-5">
        <input type="checkbox" name="indicator" autocomplete="off" hidden>
        <label class="light-txt icon font-size-30">💾</label>
        <script>
        var SaveIndicator;
        (function(form){
            SaveIndicator = form.indicator;
        })(document.currentScript.parentNode)
        </script>
    </form>
</body>
</html>
