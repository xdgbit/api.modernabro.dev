<?php


use App\Http\Helpers\Classes\jDateTime;

if (!function_exists('JDate'))
{
    function JDate($format = "Y/m/d-H:i", $numberType = "fa", $stamp = false, $convert = null, $jalali = false, $timezone = "Asia/Tehran")
    {
        if ($stamp == false)
        {
            $stamp = time();
        }
        $date = new jDateTime();
        $res = $date->date($format, $stamp);
        if ($numberType != "fa")
        {
            $res = ConvertNumbersFatoEn($res);
        }

        return $res;
    }
}

if (!function_exists('Date_GtoJ'))
{
    function Date_GtoJ($GDate, $Format = "ر", $convert = true)
    {
        $date = new jDateTime($convert, true, 'Asia/Tehran');
        $time = is_numeric($GDate) ? strtotime(date('Y-m-d H:i:s', $GDate)) : strtotime($GDate);

        return $date->date($Format, $time);
    }
}

if (!function_exists('Date_JtoG'))
{
    function Date_JtoG($jDate, $delimiter = '/', $to_string = false, $with_time = false, $input_format = 'Y/m/d H:i')
    {


        $jDate = ConvertNumbersFatoEn($jDate);
        // return $jDate;
        $parseDateTime = jDateTime::parseFromFormat($input_format, $jDate);
        // dd($parseDateTime);
        $r = jDateTime::toGregorian($parseDateTime['year'], $parseDateTime['month'], $parseDateTime['day']);

        if ($to_string)
        {
            $year_m_d_str = $r[0] . $delimiter . str_pad($r[1], 2, '0', STR_PAD_LEFT) . $delimiter . str_pad($r[2], 2, '0', STR_PAD_LEFT);
            if ($with_time)
            {
                // $r = $year_m_d_str . ' ' . $parseDateTime['hour'] . ':' . $parseDateTime['minute'] . ':' . $parseDateTime['second'];
                $r = $year_m_d_str . ' ' . $parseDateTime['hour'] . ':' . $parseDateTime['minute'];

            }
            else
            {
                $r = $year_m_d_str;
            }
        }

        return ($r);
    }
}

/*if (!function_exists('Date_JtoG'))
{
    function Date_JtoG($jDate, $delimiter = '/', $to_string = false)
    {
        $jDate = ConvertNumbersFatoEn($jDate);
        $date = explode($delimiter, $jDate);
        $r = \App\Http\Helpers\Classes\jDateTime::toGregorian($date[0], $date[1], $date[2]);
        if ($to_string)
        {
            $r = $r[0] . $delimiter . $r[1] . $delimiter . $r[2];
        }

        return ($r);
    }
}*/

if (!function_exists('prepare_date'))
{
    function prepare_date($value, $output_delimiter = '-', $output_with_time = false, $input_format = 'Y/m/d')
    {
        $value = ConvertNumbersFatoEn($value);

        if (isset($value) && !is_null($value))
        {
            $date = substr($value, 0, 10);
            $parameters[0] = '\/';
            $parameters[1] = '-';
            $matches_0 = [];
            $matches_1 = [];
            preg_match('/^((?:13|14)\d\d)[' . $parameters[0] . '](0[1-9]|1[012])[' . $parameters[0] . '](0[1-9]|[12][0-9]|3[01])$/i', $date, $matches_0);
            preg_match('/^((?:13|14)\d\d)[' . $parameters[1] . '](0[1-9]|1[012])[' . $parameters[1] . '](0[1-9]|[12][0-9]|3[01])$/i', $date, $matches_1);
            if (isset($matches_0[0]) || isset($matches_1[0]))
            {
                //return $date;
                $res = Date_JtoG($value, $output_delimiter, true, $output_with_time, $input_format);

                return $res;
            }
            else
            {
                $res = null;
            }
        }
        else
        {
            $res = null;
        }

        return $res;
    }
}

if (!function_exists('createDateRange'))
{
    function createDateRange($startDate, $endDate, $format = "Y-m-d")
    {
        $begin = new jDateTime($startDate);
        $end = new jDateTime($endDate);
        $interval = new DateInterval('P1D'); // 1 Day
        $dateRange = new DatePeriod($begin, $interval, $end);
        $range = [];
        foreach ($dateRange as $date)
        {
            $range[] = $date->format($format);
        }

        return $range;
    }
}

if (!function_exists('human_date'))
{
    function human_date($timestamp, $past = true)
    {
        $t = abs(time() - $timestamp) / 60;
        switch (true)
        {
            case $t > 548 * 24 * 60:
                $r = JDate('Y-m-d', 'fa', $timestamp);
                break;
            case $t > 12 * 30 * 24 * 60:
                $r = '1 ' . trans('helper_human_date.year'); //'1 سال';
                break;
            case $t > 9 * 30 * 24 * 60:
                $r = '9 ' . trans('helper_human_date.month'); //'9 ماه';
                break;
            case $t > 6 * 30 * 24 * 60:
                $r = '6 ' . trans('helper_human_date.month'); //'6 ماه';
                break;
            case $t > 3 * 30 * 24 * 60:
                $r = '3 ' . trans('helper_human_date.month'); //'3 ماه';
                break;
            case $t > 30 * 24 * 60:
                $r = '1 ' . trans('helper_human_date.month'); //'1 ماه';
                break;
            case $t > 4 * 7 * 24 * 60:
                $r = '4 ' . trans('helper_human_date.week'); //'1 هفته';
                break;
            case $t > 3 * 7 * 24 * 60:
                $r = '3 ' . trans('helper_human_date.week'); //'1 هفته';
                break;
            case $t > 2 * 7 * 24 * 60:
                $r = '2 ' . trans('helper_human_date.week'); //'1 هفته';
                break;
            case $t > 7 * 24 * 60:
                $r = '1 ' . trans('helper_human_date.week'); //'1 هفته';
                break;
            case $t > 6 * 24 * 60:
                $r = '6 ' . trans('helper_human_date.day'); //'1 روز';
                break;
            case $t > 5 * 24 * 60:
                $r = '5 ' . trans('helper_human_date.day'); //'1 روز';
                break;
            case $t > 4 * 24 * 60:
                $r = '4 ' . trans('helper_human_date.day'); //'1 روز';
                break;
            case $t > 3 * 24 * 60:
                $r = '3 ' . trans('helper_human_date.day'); //'1 روز';
                break;
            case $t > 2 * 24 * 60:
                $r = '2 ' . trans('helper_human_date.day'); //'1 روز';
                break;
            case $t > 24 * 60:
                $r = '1 ' . trans('helper_human_date.day'); //'1 روز';
                break;
            case $t > 12 * 60:
                $r = '12 ' . trans('helper_human_date.hour'); //'12 ساعت';
                break;
            case $t > 11 * 60:
                $r = '11 ' . trans('helper_human_date.hour'); //'ساعت';
                break;
            case $t > 10 * 60:
                $r = '10 ' . trans('helper_human_date.hour'); //'ساعت';
                break;
            case $t > 9 * 60:
                $r = '9 ' . trans('helper_human_date.hour'); //'ساعت';
                break;
            case $t > 8 * 60:
                $r = '8 ' . trans('helper_human_date.hour'); //'ساعت';
                break;
            case $t > 7 * 60:
                $r = '7 ' . trans('helper_human_date.hour'); //'ساعت';
                break;
            case $t > 6 * 60:
                $r = '6 ' . trans('helper_human_date.hour'); //'ساعت';
                break;
            case $t > 5 * 60:
                $r = '5 ' . trans('helper_human_date.hour'); //'ساعت';
                break;
            case $t > 4 * 60:
                $r = '4 ' . trans('helper_human_date.hour'); //'ساعت';
                break;
            case $t > 3 * 60:
                $r = '3 ' . trans('helper_human_date.hour'); //'ساعت';
                break;
            case $t > 2 * 60:
                $r = '2 ' . trans('helper_human_date.hour'); //'ساعت';
                break;
            case $t > 60:
                $r = '1 ' . trans('helper_human_date.hour'); //'ساعت';
                break;
            case $t > 30:
                $r = trans('helper_human_date.half_hour'); //'نیم ساعت';
                break;
            case $t > 15:
                $r = trans('helper_human_date.quarter'); //'یک ربع';
                break;
            case $t > 1:
                $r = trans('helper_human_date.minutes'); //'دقایقی';
                break;
            case $t > 0:
                $r = trans('helper_human_date.moments'); //'لحظاتی';
                break;
        }
        if ($past)
        {
            $r = $r . ' ' . trans('helper_human_date.past');
        }

        return ($r);
    }
}

