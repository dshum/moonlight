<p>
Class: {{ $exception }}<br>
Message: {{ $e->getMessage() }}<br>
File: {{ $e->getFile() }}<br>
Line: {{ $e->getLine() }}<br>
Code: {{ $e->getCode() }}
</p>

<p><small>{!! nl2br($e->getTraceAsString()) !!}</small></p>

@if ($count)
<p>{{ $count }} error(s) per {{ $diff }} sec</p>
@endif

<p>
Server: {{ $server }}<br>
URI: {{ $uri }}<br>
IP: {{ $ip }}<br>
IP2: {{ $ip2 }}<br>
UserAgent: {{ $useragent }}<br>
Referer: {{ $referer }}<br>
Request method: {{ $method }}
</p>

<p>GET: <small>{!! nl2br($get) !!}</small></p>

<p>POST: <small>{!! nl2br($post) !!}</small></p>

<p>COOKIE: <small>{!! nl2br($cookie) !!}</small></p>

<p>Message sent: {{ $date->format('Y-m-d H:i:s') }}</p>