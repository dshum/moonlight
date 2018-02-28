<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, height=device-height, user-scalable=no, initial-scale=1.0">
<meta name="msapplication-tap-highlight" content="no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title')</title>
<link rel="shortcut icon" href="/packages/moonlight/img/moonlight16.png" type="image/x-icon">
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/font-awesome.min.css">
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/default.css">
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/loader.css">
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/login.css">
@section('css')
@show
<style>
body {background-image: url(/assets/background.jpg?{{ date('Y-m-d-H-00') }});}
</style>
<script src="/packages/moonlight/js/jquery-3.2.1.min.js"></script>
<script src="/packages/moonlight/js/jquery.form.min.js"></script>
<script src="/packages/moonlight/js/common.js"></script>
@section('js')
@show
</head>
<body>
@section('content')
@show
    <div class="block-ui">
        <div class="container">
            <div class="wrapper">
                <div class="cssload-loader"></div>
            </div>
        </div>
    </div>
</body>
</html>