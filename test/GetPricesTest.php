<?php declare(strict_types=1);

require '../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Simon77\Quicken\GetPrices;

class GetPricesTest extends TestCase
{
    public function testGetPrices()
    {
        new GetPrices();
    }
}