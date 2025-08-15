<?php

namespace App\Models\Traits;

trait HasShortIdentifier
{

    /**
     * Get a short, unique 10-character ID from a 53-bit Snowflake.
     *
     * @return string
     */
    public function shortId(): string
    {
        $id = $this->getKey(); // Snowflake-ID als int/string
        return str_pad($this->base62Encode($id), 10, '0', STR_PAD_LEFT);
    }

    /**
     * Encode an integer into Base62 (0-9, a-z, A-Z).
     *
     * @param int|string $num
     * @return string
     */
    protected function base62Encode($num): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($chars);
        $result = '';

        // Integer zu Base62 konvertieren
        while ($num > 0) {
            $result = $chars[$num % $base] . $result;
            $num = intdiv($num, $base);
        }

        return $result ?: '0';
    }

}
