<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    'prefix' => 'OTWSystems\\WpOidcLogin\\Vendor',

    'finders' => [
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/.*\\.dist|Makefile|scoper.inc.php|build.sh|public-signing-key.pub|composer.json|composer.lock/')
            ->exclude([
                'doc',
                'test',
                'test_old',
                'tests',
                'Tests',
                'vendor-bin',
            ])
			->in(['vendor', '.']),

        Finder::create()->append([
            'composer.json',
        ]),
    ],

    'exclude-constants' => [
        'ABSPATH',
        'WP_OIDC_LOGIN_VERSION',
    ],

    'exclude-namespaces' => [
        '/^OTWSystems\\\\WpOidcLogin\\\\/',
        '/^OTWSystems\\\\WpOidcLogin/',
        '/^Psr\\\/',
    ],

	'expose-global-constants' => false,
	'expose-global-classes'   => false,
	'expose-global-functions' => false,

    'patchers' => [
        static function (string $filePath, string $prefix, string $content): string {
            // Fix the string reference of a scoped dependency in the Math lib
            $escapedPrefix = str_replace('\\', '\\\\', $prefix);
            if (
                str_ends_with($filePath, 'vendor/phpseclib/phpseclib/phpseclib/Math/BigInteger.php') ||
                str_ends_with($filePath, 'vendor/phpseclib/phpseclib/phpseclib/Math/BigInteger/Engines/Engine.php')
            ) {
                $content = str_replace(
                    'phpseclib3\\Math\\BigInteger\\Engines\\\\',
                    "{$escapedPrefix}\\\\phpseclib3\\\\Math\\\\BigInteger\\\\Engines\\\\",
                    $content
                );
            }

            if (str_ends_with($filePath, 'vendor/phpseclib/phpseclib/phpseclib/Crypt/Common/AsymmetricKey.php')) {
                $content = str_replace(
                    'phpseclib3\\Crypt\\\\',
                    "{$escapedPrefix}\\\\phpseclib3\\\\Crypt\\\\",
                    $content
                );
            }

            // Remove the newly added namespace for the bcmath functions
            if (str_ends_with($filePath, 'vendor/phpseclib/bcmath_compat/lib/bcmath.php')) {
                $content = str_replace("namespace {$prefix};", '', $content);
            }

            return $content;
        }
    ],
];
