<?php

namespace Moonlight\Middleware;

use Log;
use Closure;
use Session;
use Moonlight\Main\LoggedUser;
use Moonlight\Models\User;

class MobileMiddleware
{
    public function handle($request, Closure $next)
    {   
        if (
            strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry") 
            || strpos($_SERVER['HTTP_USER_AGENT'],"Mobile") 
            || strpos($_SERVER['HTTP_USER_AGENT'],"Opera M") 
            || strpos($_SERVER['HTTP_USER_AGENT'],"iPhone") 
            || strpos($_SERVER['HTTP_USER_AGENT'],"iPad") 
            || strpos($_SERVER['HTTP_USER_AGENT'],"iPod")
            || strpos($_SERVER['HTTP_USER_AGENT'],"Android") 
            || strpos($_SERVER['HTTP_USER_AGENT'],"WindowsPhone") 
            || strpos($_SERVER['HTTP_USER_AGENT'],"WP7") 
            || strpos($_SERVER['HTTP_USER_AGENT'],"WP8")
        ) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}