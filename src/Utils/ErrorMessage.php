<?php 

namespace Moonlight\Utils;

use Config;
use Exception;
use Mail;
use Moonlight\Mail\Error;
use Carbon\Carbon;

class ErrorMessage {

	const TIME_DELAY = 60;

	public static function send(Exception $e)
	{
		if (
			! Config::get('mail.from.address')
			|| ! Config::get('mail.buglover.address')
		) {
			return false;
		}

		$to = Config::get('mail.buglover.address') ?: Config::get('mail.from.address');

		$server =
			isset($_SERVER['HTTP_HOST'])
			? $_SERVER['HTTP_HOST']
			: (defined('HTTP_HOST') ? HTTP_HOST : '');

		$uri =
			isset($_SERVER['REQUEST_URI'])
			? $server.$_SERVER['REQUEST_URI']
			: $_SERVER['PHP_SELF'];

		$ip =
			isset($_SERVER['HTTP_X_REAL_IP'])
			? $_SERVER['HTTP_X_REAL_IP']
			: isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

		$ip2 =
			isset($_SERVER['HTTP_X_FORWARDED_FOR'])
			? $_SERVER['HTTP_X_FORWARDED_FOR']
			: isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

		$useragent =
			isset($_SERVER['HTTP_USER_AGENT'])
			? $_SERVER['HTTP_USER_AGENT']
			: '';

		$referer =
			isset($_SERVER['HTTP_REFERER'])
			? $_SERVER['HTTP_REFERER']
			: '';

		$method =
			isset($_SERVER['REQUEST_METHOD'])
			? $_SERVER['REQUEST_METHOD']
			: '';

		$exception = get_class($e);

		$get = var_export($_GET, true);
		$post = var_export($_POST, true);
		$cookie = var_export($_COOKIE, true);

		$filename = md5(
			$exception.' - '.$e->getMessage().' - '.$e->getTraceAsString()
		);

		$send = false;
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
				$send = true;
			} else {
				static::increment($filepath);
			}
		} else {
			static::reset($filepath);

			$send = true;
		}

		if (! $send) return;

		$date = Carbon::now();

		$subject = $uri.' - '.$exception.' - '.$e->getMessage();

		$scope = [
			'e' => $e,
			'server' => $server,
			'uri' => $uri,
			'ip' => $ip,
			'ip2' => $ip2,
			'useragent' => $useragent,
			'referer' => $referer,
			'method' => $method,
			'exception' => $exception,
			'get' => $get,
			'post' => $post,
			'cookie' => $cookie,
			'count' => $count,
			'diff' => $diff,
			'date' => $date,
			'subject' => $subject,
			'to' => $to,
		];

		Mail::send(new Error($scope));
	}

	protected static function reset($filepath)
	{
		$count = 0;

		if (
			file_exists($filepath) 
			&& $f = fopen($filepath, 'r')
		) {
			$count = (int)fread($f, 4096);
			fclose($f);
		}

		if ($f = fopen($filepath, 'w')) {
			fwrite($f, 1);
			fclose($f);
		}

		return $count;
	}

	protected static function increment($filepath)
	{
		$count = 0;

		if (
			file_exists($filepath) 
			&& $f = fopen($filepath, 'r')
		) {
			$count = (int)fread($f, 4096);
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