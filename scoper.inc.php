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

    'patchers' => [],
];
