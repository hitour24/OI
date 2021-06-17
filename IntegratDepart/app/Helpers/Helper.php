<?php
namespace App\Helpers;
class Helper {
    static public function timeDifference($unix, int $counthour) {
        $timestamp0 = $unix;
        $date_time_array = getdate($timestamp0);
        $hours = $date_time_array['hours']+$counthour;
        $minutes = 00;
        $seconds = 00;
        $month = $date_time_array['mon'];
        $day = $date_time_array['mday'];
        $year = $date_time_array['year'];

        // используйте mktime для обновления UNIX времени
        $timestamp = mktime($hours,$minutes,$seconds,$month,$day,$year);
        return [($timestamp - $timestamp0)/60, $timestamp];
    }
}