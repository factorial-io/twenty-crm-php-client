<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Helpers;

use Factorial\TwentyCrm\DTO\Contact;

/**
 * Factory for creating Contact test instances.
 */
class ContactFactory
{
    /**
     * Create a Contact with test data.
     */
    public static function create(array $overrides = []): Contact
    {
        $defaults = [
            'name' => [
                'firstName' => 'Test',
                'lastName' => 'User',
            ],
            'emails' => [
                'primaryEmail' => 'test.user@example.com',
            ],
            'phones' => [
                'primaryPhoneNumber' => '+1234567890',
            ],
            'jobTitle' => 'Software Engineer',
            'city' => 'Test City',
        ];

        $data = array_merge($defaults, $overrides);

        return Contact::fromArray($data);
    }

    /**
     * Create a Contact with minimal required data.
     */
    public static function createMinimal(array $overrides = []): Contact
    {
        $defaults = [
            'name' => [
                'firstName' => 'Test',
                'lastName' => 'User',
            ],
        ];

        $data = array_merge($defaults, $overrides);

        return Contact::fromArray($data);
    }

    /**
     * Create a Contact with unique test identifier.
     */
    public static function createUnique(string $prefix = 'TEST_'): Contact
    {
        $id = uniqid();

        return self::create([
            'name' => [
                'firstName' => $prefix . $id,
                'lastName' => 'User',
            ],
            'emails' => [
                'primaryEmail' => "test_{$id}@example.com",
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
                'person' => [
                    'id' => 'test-id-' . uniqid(),
                    'name' => [
                        'firstName' => 'Test',
                        'lastName' => 'User',
                    ],
                    'emails' => [
                        'primaryEmail' => 'test.user@example.com',
                        'additionalEmails' => null,
                    ],
                    'phones' => [
                        'primaryPhoneNumber' => '+1234567890',
                        'primaryPhoneCountryCode' => '',
                        'primaryPhoneCallingCode' => '',
                        'additionalPhones' => null,
                    ],
                    'mobilePhones' => [
                        'primaryPhoneNumber' => '',
                        'primaryPhoneCountryCode' => '',
                        'primaryPhoneCallingCode' => '',
                        'additionalPhones' => null,
                    ],
                    'jobTitle' => 'Software Engineer',
                    'city' => 'Test City',
                    'country' => '',
                    'town' => '',
                    'avatarUrl' => '',
                    'position' => 0,
                    'contactAddress' => [
                        'addressStreet1' => '',
                        'addressStreet2' => null,
                        'addressCity' => null,
                        'addressPostcode' => null,
                        'addressState' => null,
                        'addressCountry' => null,
                        'addressLat' => null,
                        'addressLng' => null,
                    ],
                    'linkedinLink' => [
                        'primaryLinkLabel' => '',
                        'primaryLinkUrl' => '',
                        'secondaryLinks' => [],
                    ],
                    'xLink' => [
                        'primaryLinkLabel' => '',
                        'primaryLinkUrl' => '',
                        'secondaryLinks' => [],
                    ],
                    'hubspotId' => '',
                    'industry' => '',
                    'numberOfTimesContacted' => null,
                    'seniority' => null,
                    'ownerId' => null,
                    'leadSource' => null,
                    'lifecycleStage' => null,
                    'originalTrafficSource' => null,
                    'recordSource' => null,
                    'leadStatus' => null,
                    'campaignTmp' => null,
                    'outreachId' => null,
                    'companyId' => null,
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
                    'searchVector' => "'test':1 'user':2",
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
        $people = [];
        for ($i = 0; $i < $count; $i++) {
            $people[] = [
                'id' => 'test-id-' . $i,
                'name' => [
                    'firstName' => "Test{$i}",
                    'lastName' => 'User',
                ],
                'emails' => [
                    'primaryEmail' => "test{$i}@example.com",
                    'additionalEmails' => null,
                ],
                'phones' => [
                    'primaryPhoneNumber' => '',
                    'primaryPhoneCountryCode' => '',
                    'primaryPhoneCallingCode' => '',
                    'additionalPhones' => null,
                ],
                'mobilePhones' => [
                    'primaryPhoneNumber' => '',
                    'primaryPhoneCountryCode' => '',
                    'primaryPhoneCallingCode' => '',
                    'additionalPhones' => null,
                ],
                'jobTitle' => '',
                'city' => '',
                'country' => '',
                'town' => '',
                'avatarUrl' => '',
                'position' => $i,
                'contactAddress' => [
                    'addressStreet1' => '',
                    'addressStreet2' => null,
                    'addressCity' => null,
                    'addressPostcode' => null,
                    'addressState' => null,
                    'addressCountry' => null,
                    'addressLat' => null,
                    'addressLng' => null,
                ],
                'linkedinLink' => [
                    'primaryLinkLabel' => '',
                    'primaryLinkUrl' => '',
                    'secondaryLinks' => [],
                ],
                'xLink' => [
                    'primaryLinkLabel' => '',
                    'primaryLinkUrl' => '',
                    'secondaryLinks' => [],
                ],
                'hubspotId' => '',
                'industry' => '',
                'numberOfTimesContacted' => null,
                'seniority' => null,
                'ownerId' => null,
                'leadSource' => null,
                'lifecycleStage' => null,
                'originalTrafficSource' => null,
                'recordSource' => null,
                'leadStatus' => null,
                'campaignTmp' => null,
                'outreachId' => null,
                'companyId' => null,
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
                'searchVector' => "'test{$i}':1 'user':2",
            ];
        }

        return [
            'data' => [
                'people' => $people,
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
