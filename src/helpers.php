<?php

if (!function_exists('str_overlap')) {
    function str_overlap(string $needle, string $haystack, bool $caseSensitive = false): string
    {
        $searchNeedle = $caseSensitive ? $needle : mb_strtolower($needle);
        $searchHayStack = $caseSensitive ? $haystack : mb_strtolower($haystack);
        $result = '';
        for ($i = 0; $i < strlen($searchNeedle); $i++) {
            if (substr($searchNeedle, $i, 1) === substr($searchHayStack, $i, 1)) {
                $result .= substr($needle, $i, 1);
            } else {
                break;
            }
        }

        return $result;
    }
}

if (!function_exists('str_match_humps')) {
    function str_match_humps(string $search, string $haystack): bool
    {
        $segments = explode(
            ' ',
            trim(
                preg_replace(
                    '/(?<! )[A-Z]/',
                    ' $0',
                    str_replace('\\', '', $haystack)
                )
            )
        );
        foreach ($segments as $segment) {
            $lowerSeg = mb_strtolower($segment);
            $overlap = str_overlap($lowerSeg, $search);
            if (0 < strlen($overlap)) {
                $search = substr($search, strlen($overlap));
            }
        }

        return 1 > strlen($search);
    }
}
