<?php

namespace App\Helpers;

use App\Researcher;

class Utilities
{


    public static function checkUniqueNickname($nickname)
    {
        $exists = Researcher::where("nickname", $nickname)->get();
        if ($exists->isEmpty()) {
            return true;
        }
        return false;
    }

    public static function generateUUID($length)
    {
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
        }
        return $random;
    }
}
