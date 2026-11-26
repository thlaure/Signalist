<?php

declare(strict_types=1);

use App\Kernel;
use App\Tests\Behat\ApiContext;
use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\MinkExtension\ServiceContainer\MinkExtension;
use FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension;

return new Config()->withProfile(
    new Profile('default')
        ->withSuite(
            new Suite('api')
                ->withPaths('%paths.base%/features/api')
                ->withContexts(ApiContext::class),
        )
        ->withExtension(new Extension(SymfonyExtension::class, [
            'bootstrap' => 'tests/bootstrap.php',
            'kernel' => [
                'class' => Kernel::class,
                'environment' => 'test',
                'debug' => true,
            ],
        ]))
        ->withExtension(new Extension(MinkExtension::class, [
            'base_url' => 'http://localhost',
            'sessions' => [
                'default' => ['symfony' => null],
            ],
        ])),
);
