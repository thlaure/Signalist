<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use function array_key_exists;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Session;

use function count;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

use function gettype;
use function is_array;
use function is_scalar;
use function is_string;

use const JSON_ERROR_NONE;
use const JSON_THROW_ON_ERROR;

use RuntimeException;

use function sprintf;

use Symfony\Component\BrowserKit\AbstractBrowser;

