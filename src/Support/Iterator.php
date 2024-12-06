<?php

namespace AlexQiu\Sdkit\Support;

class Iterator
{
    /**
     * @param callable $handle
     * @param callable|null $catch
     * @param int $page
     * @param int $tries
     *
     * @return int
     */
    public static function each(callable $handle, ?callable $catch = null, int $page = 1, int $tries = 3)
    {
        $triesCount = 0;
        do {
            try {
                $next = $handle($page);
                $page++;
            } catch (\Exception $e) {
                if (!$catch || $catch($e, $page, $triesCount) !== false) {
                    if ($triesCount >= $tries) {
                        $next = false;
                        $triesCount = 0;
                        $page++;
                    } else {
                        $next = true;
                        $triesCount++;
                    }
                }
            }
        } while ($next !== false);

        return $page;
    }
}