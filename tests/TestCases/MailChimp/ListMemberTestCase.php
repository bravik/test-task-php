<?php

declare(strict_types=1);

namespace Tests\App\TestCases\MailChimp;

use Illuminate\Http\JsonResponse;
use Mailchimp\Mailchimp;
use Tests\App\TestCases\WithDatabaseTestCase;

abstract class ListMemberTestCase extends WithDatabaseTestCase
{
    protected const MAILCHIMP_EXCEPTION_MESSAGE = 'MailChimp exception';

    /** @var string */
    protected $listId;

    /** @var string */
    protected $listMailchimpId;

    /** @var array */
    protected static $listData = [
        'name' => 'New list',
        'permission_reminder' => 'You signed up for updates on Greeks economy.',
        'email_type_option' => false,
        'contact' => [
            'company' => 'Doe Ltd.',
            'address1' => 'DoeStreet 1',
            'address2' => '',
            'city' => 'Doesy',
            'state' => 'Doedoe',
            'zip' => '1672-12',
            'country' => 'US',
            'phone' => '55533344412'
        ],
        'campaign_defaults' => [
            'from_name' => 'John Doe',
            'from_email' => 'john@doe.com',
            'subject' => 'My new campaign!',
            'language' => 'US'
        ],
        'visibility' => 'prv',
        'use_archive_bar' => false,
        'notify_on_subscribe' => 'notify@loyaltycorp.com.au',
        'notify_on_unsubscribe' => 'notify@loyaltycorp.com.au'
    ];

    /** @var array */
    protected static $memberData = [
        'email_address' => 'set email in setUp method',
        'email_type' => 'text',
        'status' => 'subscribed',
        'vip' => false,
        'timestamp_opt' => '2020-07-16T12:27:16+00:00',
        'location' => [
            'latitude' => 42.123123,
            'longitude' => 132.123123,
        ],
    ];

    /** @var array */
    protected static $notRequired = [
        'email_type',
        'unsubscribe_reason',
        'merge_fields',
        'interests',
        'language',
        'vip',
        'location',
        'marketing_permissions',
        'ip_signup',
        'timestamp_signup',
        'ip_opt',
        'timestamp_opt',
        'tags',
    ];

    public function setUp(): void
    {
        parent::setUp();

        /*
         * Mailchimp blocks email if too many signups.
         * Since project tests uses real API - we have to cheat with such hacks
         */
        self::$memberData['email_address'] = bin2hex(random_bytes(5)) . '@eonx.com';

        // Create test list

        // I would rather mock data locally, but since these functional tests require calling mailchimp API
        // it doesn't make much sense
        $this->post('/mailchimp/lists', static::$listData);

        $content = json_decode($this->response->getContent(), true);

        $this->assertResponseOk();
        $this->seeJson(static::$listData);

        $this->listId = $content['list_id'];
        $this->listMailchimpId = $content['mail_chimp_id'];
    }


    public function tearDown(): void
    {
        /** @var Mailchimp $mailChimp */
        $mailChimp = $this->app->make(Mailchimp::class);

        // Clear test list
        $mailChimp->delete("lists/{$this->listMailchimpId}");

        parent::tearDown();
    }

    protected function assertListNotFoundResponse(string $listId): void
    {
        $content = json_decode($this->response->content(), true);

        $this->assertResponseStatus(404);
        self::assertArrayHasKey('message', $content);
        self::assertEquals(sprintf('MailChimpList[%s] not found', $listId), $content['message']);
    }

    protected function assertListMemberNotFoundResponse(string $listId): void
    {
        $content = json_decode($this->response->content(), true);

        $this->assertResponseStatus(404);
        self::assertArrayHasKey('message', $content);
        self::assertEquals(sprintf('MailChimpListMember[%s] not found', $listId), $content['message']);
    }

    /**
     * Asserts error response when MailChimp exception is thrown.
     *
     * @param JsonResponse $response
     *
     * @return void
     */
    protected function assertMailChimpExceptionResponse(JsonResponse $response): void
    {
        $content = json_decode($response->content(), true);

        self::assertEquals(400, $response->getStatusCode());
        self::assertArrayHasKey('message', $content);
        self::assertEquals(self::MAILCHIMP_EXCEPTION_MESSAGE, $content['message']);
    }
}
