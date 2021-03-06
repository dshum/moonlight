<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no, initial-scale=1.0">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/font-awesome.min.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/default.min.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/loader.min.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/calendar.min.css') }}">
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/autocomplete.min.css') }}">
    @stack('styles')
    <script src="{{ asset('packages/moonlight/js/jquery/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/autocomplete/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/sortable/sortable.min.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/calendar/jquery.calendar.min.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/common.min.js') }}"></script>
    @stack('scripts')
</head>
<body>
@section('nav')
@show
@section('sidebar')
@show
@section('body')
@show
<div class="sidebar-block-ui"></div>
<div class="block-ui">
    <div class="container">
        <div class="wrapper">
            <div class="cssload-loader"></div>
        </div>
    </div>
</div>
<div class="alert">
    <div class="wrapper">
        <div class="container">
            <div class="hide">&#215;</div>
            <div class="content"></div>
        </div>
    </div>
</div>
</body>
</html>
