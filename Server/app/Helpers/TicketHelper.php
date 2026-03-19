<?php

use Illuminate\Support\Str;

if (!function_exists('generateLotteryTicket')) {
    function generateLotteryTicket(): string
    {
        $part1 = rand(1000, 9999);              
        $part2 = strtoupper(Str::random(4));   
        $part3 = rand(10, 99) . strtoupper(Str::random(2));

        return $part1 . '-' . $part2 . '-' . $part3;
    }
}
