<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Helpers;

use Factorial\TwentyCrm\DTO\Company;

/**
 * Factory for creating Company test instances.
 */
class CompanyFactory
{
    /**
     * Create a Company with test data.
     */
    public static function create(array $overrides = []): Company
    {
        $defaults = [
            'name' => 'Test Company',
            'domainName' => [
                'primaryLinkUrl' => 'https://testcompany.com',
            ],
            'address' => [
                'addressCity' => 'Test City',
            ],
        ];

        $data = array_merge($defaults, $overrides);

        return Company::fromArray($data);
    }

    /**
     * Create a Company with minimal required data.
     */
    public static function createMinimal(array $overrides = []): Company
    {
        $defaults = [
            'name' => 'Test Company',
        ];

        $data = array_merge($defaults, $overrides);

        return Company::fromArray($data);
    }

    /**
     * Create a Company with unique test identifier.
     */
    public static function createUnique(string $prefix = 'TEST_'): Company
    {
        $id = uniqid();
        return self::create([
            'name' => $prefix . $id . ' Company',
            'domainName' => [
                'primaryLinkUrl' => "https://test-{$id}.com",
            ],
        ]);
    }

    /**
     * Create API response array for testing (matches real Twenty CRM structure).
     */
    public static function createApiResponse(array $overrides = []): array
    {
        $defaults = [
            'data' => [
                'company' => [
                    'id' => 'test-company-id-' . uniqid(),
                    'name' => 'Test Company',
                    'domainName' => [
                        'primaryLinkLabel' => 'testcompany.com',
                        'primaryLinkUrl' => 'https://testcompany.com',
                        'secondaryLinks' => [],
                    ],
                    'address' => [
                        'addressStreet1' => '',
                        'addressStreet2' => '',
                        'addressCity' => 'Test City',
                        'addressPostcode' => '',
                        'addressState' => '',
                        'addressCountry' => '',
                        'addressLat' => null,
                        'addressLng' => null,
                    ],
                    'linkedinLink' => [
                        'primaryLinkLabel' => null,
                        'primaryLinkUrl' => null,
                        'secondaryLinks' => [],
                    ],
                    'xLink' => [
                        'primaryLinkLabel' => null,
                        'primaryLinkUrl' => null,
                        'secondaryLinks' => [],
                    ],
                    'facebook' => [
                        'primaryLinkLabel' => '',
                        'primaryLinkUrl' => '',
                        'secondaryLinks' => [],
                    ],
                    'phones' => [
                        'primaryPhoneNumber' => '',
                        'primaryPhoneCountryCode' => '',
                        'primaryPhoneCallingCode' => '',
                        'additionalPhones' => null,
                    ],
                    'annualRecurringRevenue' => [
                        'amountMicros' => null,
                        'currencyCode' => '',
                    ],
                    'annualRevenue' => [
                        'amountMicros' => null,
                        'currencyCode' => 'USD',
                    ],
                    'webTechnologies' => [],
                    'industry' => '',
                    'hubspotId' => '',
                    'yearFounded' => null,
                    'description' => '',
                    'timezone' => '',
                    'linkedinBio' => '',
                    'isPublic' => false,
                    'lifecycleStage' => null,
                    'originalTrafficSource' => null,
                    'employees' => null,
                    'idealCustomerProfile' => false,
                    'position' => 0,
                    'accountOwnerId' => null,
                    'lastActivityDate' => null,
                    'createdAt' => date('c'),
                    'updatedAt' => date('c'),
                    'deletedAt' => null,
                    'createdBy' => [
                        'source' => 'API',
                        'workspaceMemberId' => null,
                        'name' => 'test',
                        'context' => [],
                    ],
                    'searchVector' => "'test':1 'company':2",
                ],
            ],
        ];

        return array_merge_recursive($defaults, $overrides);
    }

    /**
     * Create collection API response (matches real Twenty CRM structure).
     */
    public static function createCollectionApiResponse(int $count = 3): array
    {
        $companies = [];
        for ($i = 0; $i < $count; $i++) {
            $companies[] = [
                'id' => 'test-company-id-' . $i,
                'name' => "Test Company {$i}",
                'domainName' => [
                    'primaryLinkLabel' => "test{$i}.com",
                    'primaryLinkUrl' => "https://test{$i}.com",
                    'secondaryLinks' => [],
                ],
                'address' => [
                    'addressStreet1' => '',
                    'addressStreet2' => '',
                    'addressCity' => '',
                    'addressPostcode' => '',
                    'addressState' => '',
                    'addressCountry' => '',
                    'addressLat' => null,
                    'addressLng' => null,
                ],
                'linkedinLink' => [
                    'primaryLinkLabel' => null,
                    'primaryLinkUrl' => null,
                    'secondaryLinks' => [],
                ],
                'xLink' => [
                    'primaryLinkLabel' => null,
                    'primaryLinkUrl' => null,
                    'secondaryLinks' => [],
                ],
                'facebook' => [
                    'primaryLinkLabel' => '',
                    'primaryLinkUrl' => '',
                    'secondaryLinks' => [],
                ],
                'phones' => [
                    'primaryPhoneNumber' => '',
                    'primaryPhoneCountryCode' => '',
                    'primaryPhoneCallingCode' => '',
                    'additionalPhones' => null,
                ],
                'annualRecurringRevenue' => [
                    'amountMicros' => null,
                    'currencyCode' => '',
                ],
                'annualRevenue' => [
                    'amountMicros' => null,
                    'currencyCode' => 'USD',
                ],
                'webTechnologies' => [],
                'industry' => '',
                'hubspotId' => '',
                'yearFounded' => null,
                'description' => '',
                'timezone' => '',
                'linkedinBio' => '',
                'isPublic' => false,
                'lifecycleStage' => null,
                'originalTrafficSource' => null,
                'employees' => null,
                'idealCustomerProfile' => false,
                'position' => $i,
                'accountOwnerId' => null,
                'lastActivityDate' => null,
                'createdAt' => date('c'),
                'updatedAt' => date('c'),
                'deletedAt' => null,
                'createdBy' => [
                    'source' => 'API',
                    'workspaceMemberId' => null,
                    'name' => 'test',
                    'context' => [],
                ],
                'searchVector' => "'test':1 'company':2 '{$i}':3",
            ];
        }

        return [
            'data' => [
                'companies' => $companies,
            ],
            'pageInfo' => [
                'hasNextPage' => false,
                'startCursor' => 'test-cursor-start',
                'endCursor' => 'test-cursor-end',
            ],
            'totalCount' => $count,
        ];
    }
}
