<?php

namespace Moonlight\Utils;

use Illuminate\Support\Facades\Auth;
use Throwable;
use Illuminate\Support\Facades\Mail;
use Moonlight\Mail\Error;
use Carbon\Carbon;

/**
 * Class ErrorMessage
 *
 * @package Moonlight\Utils
 */
class ErrorMessage
{
    /**
     *
     */
    const TIME_DELAY = 60;

    /**
     * @param \Throwable $e
     * @return bool|void
     */
    public static function send(Throwable $e)
    {
        if (
            ! config('mail.from.address')
            || ! config('mail.buglover.address')
        ) {
            return false;
        }

        $exception = get_class($e);
        $filename = md5($exception.' - '.$e->getMessage().' - '.$e->getTraceAsString());

        $count = 0;
        $diff = 0;

        $folder = storage_path().'/framework/errors';

        if (! file_exists($folder)) {
            mkdir($folder, 0755);
        }

        $filepath = $folder.'/'.$filename;

        if (file_exists($filepath)) {
            $time = filemtime($filepath);

            if (time() - $time > static::TIME_DELAY) {
                $count = static::reset($filepath);
                $diff = time() - $time;
            } else {
                static::increment($filepath);

                return false;
            }
        } else {
            static::reset($filepath);
        }

        $admin = Auth::guard('moonlight')->user();
        $user = Auth::user();

        $server = self::getServer();
        $uri = self::getRequestUri();
        $get = var_export($_GET, true);
        $post = var_export($_POST, true);
        $cookie = var_export($_COOKIE, true);
        $date = Carbon::now();
        $to = config('mail.buglover.address') ?: config('mail.from.address');
        $subject = $uri.' - '.$exception.' - '.$e->getMessage();

        $scope = [
            'e' => $e,
            'server' => $server,
            'uri' => $uri,
            'method' => self::getRequestMethod(),
            'ip' => self::getIP(),
            'ip2' => self::getIP2(),
            'useragent' => self::getUserAgent(),
            'referer' => self::getReferer(),
            'exception' => $exception,
            'get' => $get,
            'post' => $post,
            'cookie' => $cookie,
            'admin' => $admin,
            'user' => $user,
            'count' => $count,
            'diff' => $diff,
            'date' => $date,
            'subject' => $subject,
            'to' => $to,
        ];

        Mail::send(new Error($scope));
    }

    /**
     * @return mixed|null
     */
    public static function getServer()
    {
        return $_SERVER['HTTP_HOST'] ?? (defined('HTTP_HOST') ? HTTP_HOST : null);
    }

    /**
     * @return mixed|null
     */
    public static function getRequestUri()
    {
        return $_SERVER['REQUEST_URI'] ?? $_SERVER['PHP_SELF'] ?? null;
    }

    /**
     * @return mixed|null
     */
    public static function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'] ?? null;
    }

    /**
     * @return mixed|null
     */
    public static function getReferer()
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }

    /**
     * @return mixed|null
     */
    public static function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    /**
     * @return mixed|null
     */
    public static function getIP()
    {
        return $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
    }

    /**
     * @return mixed|null
     */
    public static function getIP2()
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
    }

    /**
     * @param $filepath
     * @return int
     */
    protected static function reset($filepath)
    {
        $count = 0;

        if (
            file_exists($filepath)
            && $f = fopen($filepath, 'r')
        ) {
            $count = (int) fread($f, 4096);
            fclose($f);
        }

        if ($f = fopen($filepath, 'w')) {
            fwrite($f, 1);
            fclose($f);
        }

        return $count;
    }

    /**
     * @param $filepath
     * @return int
     */
    protected static function increment($filepath)
    {
        $count = 0;

        if (
            file_exists($filepath)
            && $f = fopen($filepath, 'r')
        ) {
            $count = (int) fread($f, 4096);
            fclose($f);
        }

        $count++;

        if ($f = fopen($filepath, 'w')) {
            fwrite($f, $count);
            fclose($f);
        }

        return $count;
    }
}
