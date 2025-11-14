<?php declare(strict_types=1);

namespace OTWSystems\WpOidcLogin\Utilities;

use function count;
use function is_string;
use function strlen;

final class Sanitize
{
    /**
     * @param array $array
     *
     * @return mixed[]
     */
    public static function arrayUnique(array $array): array
    {
        if ([] === $array) {
            return [];
        }

        return array_values(array_filter(array_unique(array_map('trim', $array))));
    }

    public static function boolean(string $string): ?string
    {
        $string = trim(sanitize_text_field($string));

        if ('' === $string) {
            return null;
        }

        if ('true' === $string) {
            return 'true';
        }

        if ('1' === $string) {
            return 'true';
        }

        return 'false';
    }

    public static function url(string $path): ?string
    {
        $path = self::string($path);

        if (is_string($path) && '' === $path) {
            return null;
        }

        if (null === $path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL) === FALSE) {
            return null;
        }

        return $path;
    }

    public static function string(string $string): ?string
    {
        $string = trim(sanitize_text_field($string));

        if ('' === $string) {
            return null;
        }

        return $string;
    }

    public static function textarea(string $string): ?string
    {
        $string = trim(sanitize_textarea_field($string));

        if ('' === $string) {
            return null;
        }

        return $string;
    }
}
