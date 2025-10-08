<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Unit\DTO;

use Factorial\TwentyCrm\DTO\Phone;
use Factorial\TwentyCrm\DTO\PhoneCollection;
use Factorial\TwentyCrm\Tests\TestCase;

class PhoneTest extends TestCase
{
    public function testCreatePhone(): void
    {
        $phone = new Phone('1234567890', 'US', '+1');

        $this->assertEquals('1234567890', $phone->getNumber());
        $this->assertEquals('US', $phone->getCountryCode());
        $this->assertEquals('+1', $phone->getCallingCode());
    }

    public function testPhoneFromArray(): void
    {
        $data = [
            'number' => '9876543210',
            'countryCode' => 'DE',
            'callingCode' => '+49',
        ];

        $phone = Phone::fromArray($data);

        $this->assertEquals('9876543210', $phone->getNumber());
        $this->assertEquals('DE', $phone->getCountryCode());
        $this->assertEquals('+49', $phone->getCallingCode());
    }

    public function testPhoneFromArrayWithPrimaryFields(): void
    {
        $data = [
            'primaryPhoneNumber' => '5551234567',
            'primaryPhoneCountryCode' => 'GB',
            'primaryPhoneCallingCode' => '+44',
        ];

        $phone = Phone::fromArray($data);

        $this->assertEquals('5551234567', $phone->getNumber());
        $this->assertEquals('GB', $phone->getCountryCode());
        $this->assertEquals('+44', $phone->getCallingCode());
    }

    public function testPhoneToArray(): void
    {
        $phone = new Phone('1234567890', 'US', '+1');
        $array = $phone->toArray();

        $this->assertEquals('1234567890', $array['number']);
        $this->assertEquals('US', $array['countryCode']);
        $this->assertEquals('+1', $array['callingCode']);
    }

    public function testPhoneGetFormatted(): void
    {
        $phone = new Phone('1234567890', 'US', '+1');
        $this->assertEquals('+11234567890', $phone->getFormatted());

        $phoneWithoutCode = new Phone('9876543210');
        $this->assertEquals('9876543210', $phoneWithoutCode->getFormatted());

        $nullPhone = new Phone();
        $this->assertNull($nullPhone->getFormatted());
    }

    public function testPhoneSetters(): void
    {
        $phone = new Phone();

        $phone->setNumber('1112223333')
            ->setCountryCode('FR')
            ->setCallingCode('+33');

        $this->assertEquals('1112223333', $phone->getNumber());
        $this->assertEquals('FR', $phone->getCountryCode());
        $this->assertEquals('+33', $phone->getCallingCode());
    }

    public function testPhoneCollectionCreate(): void
    {
        $primaryPhone = new Phone('1234567890', 'US', '+1');
        $additionalPhone = new Phone('9876543210', 'DE', '+49');

        $collection = new PhoneCollection($primaryPhone, [$additionalPhone]);

        $this->assertSame($primaryPhone, $collection->getPrimaryPhone());
        $this->assertCount(1, $collection->getAdditionalPhones());
        $this->assertCount(2, $collection->getAllPhones());
    }

    public function testPhoneCollectionFromArray(): void
    {
        $data = [
            'primaryPhoneNumber' => '5551234567',
            'primaryPhoneCountryCode' => 'US',
            'primaryPhoneCallingCode' => '+1',
            'additionalPhones' => [
                ['number' => '5559876543', 'countryCode' => 'CA', 'callingCode' => '+1'],
            ],
        ];

        $collection = PhoneCollection::fromArray($data);

        $this->assertEquals('5551234567', $collection->getPrimaryNumber());
        $this->assertCount(1, $collection->getAdditionalPhones());
    }

    public function testPhoneCollectionToArray(): void
    {
        $primaryPhone = new Phone('1234567890', 'US', '+1');
        $collection = new PhoneCollection($primaryPhone);

        $array = $collection->toArray();

        $this->assertEquals('1234567890', $array['primaryPhoneNumber']);
        $this->assertEquals('US', $array['primaryPhoneCountryCode']);
        $this->assertEquals('+1', $array['primaryPhoneCallingCode']);
    }

    public function testPhoneCollectionIsEmpty(): void
    {
        $emptyCollection = new PhoneCollection();
        $this->assertTrue($emptyCollection->isEmpty());

        $collection = new PhoneCollection(new Phone('1234567890'));
        $this->assertFalse($collection->isEmpty());
    }

    public function testPhoneCollectionGetPrimaryFormatted(): void
    {
        $phone = new Phone('1234567890', 'US', '+1');
        $collection = new PhoneCollection($phone);

        $this->assertEquals('+11234567890', $collection->getPrimaryFormatted());

        $emptyCollection = new PhoneCollection();
        $this->assertNull($emptyCollection->getPrimaryFormatted());
    }

    public function testPhoneCollectionAddAdditionalPhone(): void
    {
        $collection = new PhoneCollection(new Phone('1234567890'));

        $collection->addAdditionalPhone(new Phone('9876543210'));

        $this->assertCount(1, $collection->getAdditionalPhones());
        $this->assertCount(2, $collection->getAllPhones());
    }
}
