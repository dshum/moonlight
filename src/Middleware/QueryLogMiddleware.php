<?php

namespace Moonlight\Middleware;

use Log;
use Closure;
use Illuminate\Support\Facades\DB;

class QueryLogMiddleware
{
    public function handle($request, Closure $next)
    {
        DB::enableQueryLog();

        $response = $next($request);

        if ($request->has('log_sql_report')) {
            Log::info($request->fullUrl());

            $site = \App::make('site');
            $queries = \DB::getQueryLog();
            
            foreach ($queries as $index => $query) {
                Log::info($index.')'."\t".($query['time'] / 1000).' sec.'."\t".$query['query']);
            }

            Log::info('Total time:'."\t".$site->getMicroTime().' sec');
            Log::info('Memory usage:'."\t".$site->getMemoryUsage().' Mb');
        }

        return $response;
    }
}