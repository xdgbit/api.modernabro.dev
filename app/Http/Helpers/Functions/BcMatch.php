<?php

if (!function_exists('determine_decimal_places'))
{
    function determine_decimal_places($number): int
    {
        $string_number = sprintf('%.53f', $number);

        return strlen(substr(strrchr($string_number, "."), 1));
    }
}

if (!function_exists('determine_decimal_scale'))
{
    function determine_decimal_scale($num_1, $num_2, $type = 'max'): int
    {
        $decimal_places_1 = determine_decimal_places($num_1);
        $decimal_places_2 = determine_decimal_places($num_2);

        if ($type === 'max')
        {
            return max($decimal_places_1, $decimal_places_2);
        }
        elseif ($type === 'min')
        {
            return min($decimal_places_1, $decimal_places_2);
        }
        elseif ($type === 'plus')
        {
            return $decimal_places_1 + $decimal_places_2;
        }
        elseif ($type === 'mines')
        {
            return $decimal_places_1 - $decimal_places_2;
        }
        else
        {
            return max($decimal_places_1, $decimal_places_2);
        }
    }
}

if (!function_exists('convert_scientific_to_decimal'))
{
    function convert_scientific_to_decimal($number, $precision = 30): string
    {
        if (stripos($number, 'e') === false)
        {
            return (string)$number;
        }

        [$base, $exp] = explode('e', strtolower($number));
        $exp = (int)$exp;

        return bcmul($base, bcpow('10', $exp, $precision), $precision);
    }
}

if (!function_exists('trim_trailing_zeros'))
{
    function trim_trailing_zeros($number): string
    {
        return rtrim(rtrim((string)$number, '0'), '.');
    }
}

if (!function_exists('precise_add'))
{
    function precise_add($a, $b): string
    {
        $a_string = convert_scientific_to_decimal((string)$a);
        $b_string = convert_scientific_to_decimal((string)$b);

        $result = bcadd($a_string, $b_string, 1000);

        return trim_trailing_zeros($result);
    }
}

if (!function_exists('precise_sub'))
{
    function precise_sub($a, $b): string
    {
        $a_string = convert_scientific_to_decimal((string)$a);
        $b_string = convert_scientific_to_decimal((string)$b);

        $result = bcsub($a_string, $b_string, 1000);

        return trim_trailing_zeros($result);
    }
}

if (!function_exists('precise_mul'))
{
    function precise_mul($a, $b): string
    {
        $a_string = convert_scientific_to_decimal((string)$a);
        $b_string = convert_scientific_to_decimal((string)$b);

        $result = bcmul($a_string, $b_string, 1000);

        return trim_trailing_zeros($result);
    }
}

if (!function_exists('precise_div'))
{
    function precise_div($a, $b): string
    {
        $a_string = convert_scientific_to_decimal((string)$a);
        $b_string = convert_scientific_to_decimal((string)$b);

        if ($b_string == 0)
        {
            throw new InvalidArgumentException("تقسیم بر صفر مجاز نیست.");
        }

        $result = bcdiv($a_string, $b_string, 1000);

        return trim_trailing_zeros($result);
    }
}

if (!function_exists('precise_compare'))
{
    function precise_compare($a, $b, $scale = 1000): int
    {
        $a_string = convert_scientific_to_decimal((string)$a);
        $b_string = convert_scientific_to_decimal((string)$b);

        return bccomp($a_string, $b_string, $scale);
    }
}

if (!function_exists('precise_equals'))
{
    function precise_equals($a, $b): bool
    {
        return precise_compare($a, $b) === 0;
    }
}

if (!function_exists('precise_less_than'))
{
    function precise_less_than($a, $b): bool
    {
        return precise_compare($a, $b) < 0;
    }
}

if (!function_exists('precise_greater_than'))
{
    function precise_greater_than($a, $b, $scale = 1000): bool
    {
        return precise_compare($a, $b, $scale) > 0;
    }
}

if (!function_exists('precise_less_than_or_equal'))
{
    function precise_less_than_or_equal($a, $b): bool
    {
        return precise_compare($a, $b) <= 0;
    }
}

if (!function_exists('precise_greater_than_or_equal'))
{
    function precise_greater_than_or_equal($a, $b, $scale = 1000): bool
    {
        return precise_compare($a, $b, $scale) >= 0;
    }
}
