<?php

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Response;
use App\Jobs\WhatsAppMessageJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendFirebaseNotificationJob;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;



if (!function_exists('array_duplicate_values')) {
    function array_duplicate_values(array $array)
    {
        $valueCounts = array_count_values($array);

        // Filter duplicates (values with count > 1)
        $duplicates = array_filter($valueCounts, function ($count) {
            return $count > 1;
        });

        // Get only the values (keys)
        return array_keys($duplicates);
    }
}

if (!function_exists('convertArrayToStrings')) {
    function convertArrayToStrings(array $data = null)
    {
        if (!$data)
            return null;

        return array_map(function ($value) {
            if (is_scalar($value)) {
                return strval($value); // Convert scalar values to strings
            }
            return json_encode($value); // Convert arrays/objects to JSON strings
        }, $data);
    }
}

if (!function_exists(' pushWhatsAppMessage')) {
    function pushWhatsAppMessage($message, $recipient, $receiver_id = null)
    {
        WhatsAppMessageJob::dispatch([
            'recipient' => $recipient,
            'message' => $message,
            'receiver_id' => $receiver_id,
        ])
            ->onQueue('default');
    }
}

if (!function_exists('order_by_fields')) {
    function order_by_fields(&$query, array $fields)
    {
        foreach ($fields as $field => $direction) {
            $query->orderBy($field, $direction);
        }

        return $query;
    }
}

if (!function_exists('pushFirebaseNotification')) {
    function pushFirebaseNotification(string|array $fcmTokens, string $title, string $description, array $data = [], string $queue = 'default', $connection = null)
    {
        if (!is_array($fcmTokens)) {
            $fcmTokens = [$fcmTokens];
        }

        if (!app()->isProduction()) {
            $connection = 'sync';
        }

        foreach ($fcmTokens as $token) {
            dispatch(new SendFirebaseNotificationJob($token, $title, $description, $data))
                ->onConnection($connection ?? config('queue.default') ?? 'database')
                ->onQueue($queue);
        }
    }
}



if (!function_exists('getConfig')) {
    function getConfig()
    {
        return config("lms_systems." . trim(strtolower(config('app.name'))));
    }
}

if (!function_exists('toCamelCase')) {
    function toCamelCase($string)
    {
        $string = str_replace('_', ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
        $string = lcfirst($string);

        return $string;
    }
}

if (!function_exists('sec_to_date_time')) {
    function sec_to_date_time($total_sec, $withDays = false)
    {
        if (!$total_sec or !is_numeric($total_sec) or !is_int($total_sec))
            return $total_sec;

        try {
            $days = floor($total_sec / 86400);
            $hours = floor(($total_sec % 86400) / 3600);
            $minutes = floor(($total_sec % 3600) / 60);
            $seconds = $total_sec % 60;
            if ($withDays)
                return sprintf('%d days %02d:%02d:%02d', $days, $hours, $minutes, $seconds);
            return sprintf('%02d:%02d:%02d', $hours + $days * 24, $minutes, $seconds);
        } catch (\Throwable $th) {
            return $total_sec;
        }
    }
}

if (!function_exists('sumTime')) {
    function sumTime($column)
    {
        $connectionName = config('database.default');
        $connectionConfig = config("database.connections.{$connectionName}");
        $driver = $connectionConfig['driver'] ?? null;

        switch ($driver) {
            case 'sqlite':
                return "strftime('%s', {$column}) - strftime('%s', '00:00:00')";
            case 'mysql':
                return "TIME_TO_SEC({$column})";
            case 'pgsql':
                return "EXTRACT(EPOCH FROM {$column})";
            default:
                Log::emergency("Unsupported database driver: {$driver}");
                return DB::raw('0'); // Return zero if unsupported
        }
    }
}

if (!function_exists('error')) {
    function error(string $message = null, $errors = null, $code = 401)
    {
        if (is_string($code)) {
            $code = (int) $code;
        }
        return response()->json([
            'message' => $message,
            'errors' => $errors ?? [$message],
            'code' => (int) (($code <= 503 and $code >= 400) ? $code : 500),
        ], (int) (($code <= 503 and $code >= 400) ? $code : 500));
    }
}
if (!function_exists('success')) {
    function success($data = null, int $code = Response::HTTP_OK, $additionalData = [])
    {
        return response()->json(
            array_merge([
                'data' => $data ?? ['success' => true],
                'code' => $code,
                'server_time' => now()->toDateTimeString(),
            ], $additionalData),
            $code
        );
    }
}
if (!function_exists('throwError')) {
    function throwError($message, $errors = null, int $code = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        throw new HttpResponseException(response()->json(
            [
                'message' => $message,
                'errors' => $errors ?? [$message],
                'server_time' => now()->toDateTimeString(),
            ],
            $code
        ));
    }
}


if (!function_exists('paginate')) {
    function paginate(&$data, $paginationLimit = null)
    {
        $paginationLimit = $paginationLimit ?? config('app.pagination_limit');
        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginatedStudents = collect($data)->forPage($page, $paginationLimit);

        // Create a LengthAwarePaginator-like structure
        $paginator = new LengthAwarePaginator(
            $paginatedStudents,
            count($data),
            $paginationLimit,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        // Convert the paginator to an array with numerically indexed data
        $data = $paginator->toArray();
        $data['data'] = collect($data['data'])->values()->all();

        return $data;
    }
}
if (!function_exists('diffForHumans')) {
    function diffForHumans($time)
    {
        return Carbon::parse($time)->diffForHumans(Carbon::now(), [
            'long' => true,
            'parts' => 2,
            'join' => true,
        ]);
    }
}


if (!function_exists('interpolateQuery')) {
    /**
     * Interpolates query bindings into the SQL query.
     *
     * @param string $query The SQL query with placeholders.
     * @param array $bindings The array of bindings.
     * @return string The interpolated SQL query.
     */
    function interpolateQuery($query, $bindings)
    {
        $pdo = DB::getPdo();
        foreach ($bindings as $binding) {
            // Determine the type of the binding
            if (is_numeric($binding)) {
                $value = $binding;
            } elseif (is_null($binding)) {
                $value = 'NULL';
            } else {
                // Escape single quotes in the binding
                $escaped = str_replace("'", "''", $binding);
                $value = "'{$escaped}'";
            }

            // Replace the first occurrence of the placeholder with the binding
            $query = preg_replace('/\?/', $value, $query, 1);
        }

        return $query;
    }


    if (!function_exists('convertTimeToSeconds')) {

        /**
         * Converts a time string in the format H:i:s to the total number of seconds.
         *
         * @param string $time Time string to convert
         * @return int Total seconds
         */
        function convertTimeToSeconds($time)
        {
            sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
            return $hours * 3600 + $minutes * 60 + $seconds;
        }
    }

    if (!function_exists('convertSecondsToTime')) {

        /**
         * Converts a number of seconds into a time string formatted as H:i:s.
         *
         * @param int $seconds Total number of seconds to convert
         * @return string Formatted time string
         */
        function convertSecondsToTime($seconds)
        {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = $seconds % 60;

            return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        }
    }
}



if (!function_exists('getVideoStreams')) {
    function getVideoStreams($videoId)
    {
        $pythonExecutable = '/usr/bin/python3';
        $scriptPath = base_path('python/youtube_streams.py');

        $process = new Process([$pythonExecutable, $scriptPath, $videoId]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();

        return json_decode($output);
    }
}
