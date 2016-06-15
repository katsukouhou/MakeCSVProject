<?php
/* メモリ使用量表示 true=表示、false=非表示 */
$memory_usage_display = true;
$memory_usage_message = '';
/**
 * @memory_usage_start
 */
function memory_usage_start() {
    global $memory_usage_message, $memory_usage_display;
    if ($memory_usage_display) {
        $memory = (!function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
        $memory_usage_message .= $_SERVER['PHP_SELF'] . ' Memory usage <Start> ' . $memory . "\n";
    }
}
/**
 * @memory_usage_end
 */
function memory_usage_end() {
    global $memory_usage_message, $memory_usage_display;
    if ($memory_usage_display) {
        $memory = (!function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
        $memory_usage_message .= $_SERVER['PHP_SELF'] . ' Memory usage <End> ' . $memory . "\n";
        //
        $memory = (!function_exists('memory_get_usage')) ? '0' : round(memory_get_peak_usage()/1024/1024, 2).'MB';
        $memory_usage_message .= $_SERVER['PHP_SELF'] . ' Memory usage <Peak> ' . $memory . "\n";
    }

    print nl2br($memory_usage_message);
}

/* 実行時間表示 true=表示、false=非表示 */
$process_time_display = true;
$startTime = '';
/**
 * @start time
 */
function pro_start_time() {
    global $startTime, $process_time_display;
    if ($process_time_display) {
        $mtime1 = explode(" ", microtime());
        $startTime = $mtime1[1] + $mtime1[0];
    }
}

/**
 * @End time
 */
function pro_end_time() {
    global $startTime, $process_time_display;
    if ($process_time_display) {
        $mtime2 = explode(" ", microtime());
        $endtime = $mtime2[1] + $mtime2[0];
        $totaltime = ($endtime - $startTime);
        $totaltime = number_format($totaltime, 7);

        print nl2br("process time: " . $totaltime . "s\n");
    }
}