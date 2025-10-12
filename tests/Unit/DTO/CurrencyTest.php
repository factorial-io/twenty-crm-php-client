<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Unit\DTO;

use Factorial\TwentyCrm\DTO\Currency;
use Factorial\TwentyCrm\Tests\TestCase;

class CurrencyTest extends TestCase
{
    public function testCreateCurrency(): void
    {
        $currency = new Currency(50000000000, 'USD');

        $this->assertEquals(50000000000, $currency->getAmountMicros());
        $this->assertEquals(50000.00, $currency->getAmount());
        $this->assertEquals('USD', $currency->getCurrencyCode());
    }

    public function testCreateCurrencyDefaultCode(): void
    {
        $currency = new Currency(1000000);

        $this->assertEquals(1000000, $currency->getAmountMicros());
        $this->assertEquals(1.00, $currency->getAmount());
        $this->assertEquals('USD', $currency->getCurrencyCode());
    }

    public function testCurrencyFromArray(): void
    {
        $data = [
            'amountMicros' => 25000000000,
            'currencyCode' => 'EUR',
        ];

        $currency = Currency::fromArray($data);

        $this->assertEquals(25000000000, $currency->getAmountMicros());
        $this->assertEquals(25000.00, $currency->getAmount());
        $this->assertEquals('EUR', $currency->getCurrencyCode());
    }

    public function testCurrencyFromArrayDefaults(): void
    {
        $currency = Currency::fromArray([]);

        $this->assertEquals(0, $currency->getAmountMicros());
        $this->assertEquals(0.00, $currency->getAmount());
        $this->assertEquals('USD', $currency->getCurrencyCode());
    }

    public function testCurrencyFromAmount(): void
    {
        $currency = Currency::fromAmount(50000.00, 'USD');

        $this->assertEquals(50000000000, $currency->getAmountMicros());
        $this->assertEquals(50000.00, $currency->getAmount());
        $this->assertEquals('USD', $currency->getCurrencyCode());
    }

    public function testCurrencyFromAmountDefaultCode(): void
    {
        $currency = Currency::fromAmount(123.45);

        $this->assertEquals(123450000, $currency->getAmountMicros());
        $this->assertEquals(123.45, $currency->getAmount());
        $this->assertEquals('USD', $currency->getCurrencyCode());
    }

    public function testCurrencyFromAmountRounding(): void
    {
        // Test rounding - micros are rounded to nearest integer
        $currency = Currency::fromAmount(99.999999, 'USD');

        // 99.999999 * 1000000 = 99999999, rounds to 99999999
        $this->assertEquals(99999999, $currency->getAmountMicros());
        // Converting back: 99999999 / 1000000 = 99.999999
        $this->assertEquals(99.999999, $currency->getAmount());
    }

    public function testCurrencyToArray(): void
    {
        $currency = new Currency(50000000000, 'USD');
        $array = $currency->toArray();

        $this->assertEquals(50000000000, $array['amountMicros']);
        $this->assertEquals('USD', $array['currencyCode']);
    }

    public function testCurrencyGetFormatted(): void
    {
        $usd = new Currency(50000000000, 'USD');
        $this->assertEquals('$50,000.00 USD', $usd->getFormatted());

        $eur = new Currency(25000000000, 'EUR');
        $this->assertEquals('€25,000.00 EUR', $eur->getFormatted());

        $gbp = new Currency(10000000000, 'GBP');
        $this->assertEquals('£10,000.00 GBP', $gbp->getFormatted());

        $jpy = new Currency(1000000000, 'JPY');
        $this->assertEquals('¥1,000.00 JPY', $jpy->getFormatted());
    }

    public function testCurrencyGetFormattedWithDecimals(): void
    {
        $currency = new Currency(50123456789, 'USD');

        $this->assertEquals('$50,123.46 USD', $currency->getFormatted(2));
        $this->assertEquals('$50,123.457 USD', $currency->getFormatted(3));
        $this->assertEquals('$50,123 USD', $currency->getFormatted(0));
    }

    public function testCurrencyGetFormattedUnknownCurrency(): void
    {
        $currency = new Currency(1000000000, 'XYZ');

        $this->assertEquals('1,000.00 XYZ', $currency->getFormatted());
    }

    public function testCurrencyGetSymbol(): void
    {
        $this->assertEquals('$', (new Currency(0, 'USD'))->getCurrencySymbol());
        $this->assertEquals('€', (new Currency(0, 'EUR'))->getCurrencySymbol());
        $this->assertEquals('£', (new Currency(0, 'GBP'))->getCurrencySymbol());
        $this->assertEquals('¥', (new Currency(0, 'JPY'))->getCurrencySymbol());
        $this->assertEquals('CHF ', (new Currency(0, 'CHF'))->getCurrencySymbol());
        $this->assertEquals('C$', (new Currency(0, 'CAD'))->getCurrencySymbol());
        $this->assertEquals('A$', (new Currency(0, 'AUD'))->getCurrencySymbol());
        $this->assertEquals('₹', (new Currency(0, 'INR'))->getCurrencySymbol());
        $this->assertEquals('', (new Currency(0, 'UNKNOWN'))->getCurrencySymbol());
    }

    public function testCurrencySetAmountMicros(): void
    {
        $currency = new Currency(0, 'USD');

        $currency->setAmountMicros(25000000000);

        $this->assertEquals(25000000000, $currency->getAmountMicros());
        $this->assertEquals(25000.00, $currency->getAmount());
    }

    public function testCurrencySetAmount(): void
    {
        $currency = new Currency(0, 'USD');

        $currency->setAmount(50000.00);

        $this->assertEquals(50000000000, $currency->getAmountMicros());
        $this->assertEquals(50000.00, $currency->getAmount());
    }

    public function testCurrencySetCurrencyCode(): void
    {
        $currency = new Currency(1000000000, 'USD');

        $currency->setCurrencyCode('EUR');

        $this->assertEquals('EUR', $currency->getCurrencyCode());
    }

    public function testCurrencyFluentSetters(): void
    {
        $currency = new Currency(0, 'USD');

        $result = $currency
            ->setAmount(10000.00)
            ->setCurrencyCode('GBP');

        $this->assertSame($currency, $result);
        $this->assertEquals(10000.00, $currency->getAmount());
        $this->assertEquals('GBP', $currency->getCurrencyCode());
    }

    public function testCurrencyToString(): void
    {
        $currency = new Currency(50000000000, 'USD');

        $this->assertEquals('$50,000.00 USD', (string) $currency);
    }

    public function testCurrencyZeroAmount(): void
    {
        $currency = new Currency(0, 'USD');

        $this->assertEquals(0, $currency->getAmountMicros());
        $this->assertEquals(0.00, $currency->getAmount());
        $this->assertEquals('$0.00 USD', $currency->getFormatted());
    }

    public function testCurrencyNegativeAmount(): void
    {
        $currency = new Currency(-50000000000, 'USD');

        $this->assertEquals(-50000000000, $currency->getAmountMicros());
        $this->assertEquals(-50000.00, $currency->getAmount());
        $this->assertEquals('$-50,000.00 USD', $currency->getFormatted());
    }
}
