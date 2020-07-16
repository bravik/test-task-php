<?php
declare(strict_types=1);

namespace Tests\App\Functional\Http\Controllers\MailChimp;

use Tests\App\TestCases\MailChimp\ListMemberTestCase;

class ListMembersControllerTest extends ListMemberTestCase
{
    public function testCreateListMemberSuccessfully(): void
    {
        $this->post("/mailchimp/lists/{$this->listId}/members", static::$memberData);

        $content = json_decode($this->response->getContent(), true);

        $this->assertResponseOk();
        $this->seeJson(static::$memberData);
        self::assertArrayHasKey('mail_chimp_id', $content);
        self::assertNotNull($content['mail_chimp_id']);
    }

    public function testCreateListMemberForMissingList(): void
    {
        $this->post("/mailchimp/lists/invalid_list_id/members", self::$memberData);

        $this->assertListNotFoundResponse('invalid_list_id');
    }

    public function testCreateListMemberWithoutRequiredFields(): void
    {
        $this->post("/mailchimp/lists/{$this->listId}/members");

        $content = json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(400);
        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('errors', $content);
        self::assertEquals('Invalid data given', $content['message']);

        foreach (array_keys(static::$memberData) as $key) {
            if (in_array($key, static::$notRequired, true)) {
                continue;
            }

            self::assertArrayHasKey($key, $content['errors']);
        }
    }

    public function testCreateListMemberWithInvalidData(): void
    {
        $invalidData = [
            'email_address' => 'invalid_email',
            'status' => 'invalid_status',
            // @todo Other fields should be added here, but since it is a test task I'm skipping
        ];

        $this->post("/mailchimp/lists/{$this->listId}/members", $invalidData);

        $content = json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(400);
        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('errors', $content);
        self::assertEquals('Invalid data given', $content['message']);

        foreach (array_keys($invalidData) as $key) {
            self::assertArrayHasKey($key, $content['errors']);
        }
    }

    public function testRemoveMemberInMissingList(): void
    {
        $this->delete("/mailchimp/lists/invalid_list_id/members/invalid_member_id");

        $this->assertListNotFoundResponse('invalid_list_id');
    }

    public function testRemoveMissingMember(): void
    {
        $this->delete("/mailchimp/lists/{$this->listId}/members/invalid_member_id");

        $this->assertListMemberNotFoundResponse('invalid_member_id');
    }


    public function testRemoveListMemberSuccessfully(): void
    {
        $this->post("/mailchimp/lists/{$this->listId}/members", static::$memberData);

        $content = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('id', $content, 'Failed to create a list member');

        $this->delete("/mailchimp/lists/{$this->listId}/members/{$content['id']}");

        $this->assertResponseOk();
        self::assertEmpty(json_decode($this->response->content(), true));
    }

    public function testShowListMemberNotFoundException(): void
    {
        $this->get("/mailchimp/lists/{$this->listId}/members/invalid_member_id");

        $this->assertListMemberNotFoundResponse('invalid_member_id');
    }


    public function testShowListSuccessfully(): void
    {
        $this->post("/mailchimp/lists/{$this->listId}/members", static::$memberData);

        $content = json_decode($this->response->getContent(), true);

        self::assertArrayHasKey('id', $content, 'Failed to create a list member');

        $this->get("/mailchimp/lists/{$this->listId}/members/{$content['id']}");

        $content = json_decode($this->response->content(), true);

        $this->assertResponseOk();

        foreach (static::$memberData as $key => $value) {
            self::assertArrayHasKey($key, $content);
            self::assertEquals($value, $content[$key]);
        }
    }

    public function testUpdateListMemberNotFoundException(): void
    {
        $this->put("/mailchimp/lists/{$this->listId}/members/invalid_member_id");

        $this->assertListMemberNotFoundResponse('invalid_member_id');
    }

    public function testUpdateListMemberSuccessfully(): void
    {
        $this->post("/mailchimp/lists/{$this->listId}/members", static::$memberData);
        $member = json_decode($this->response->content(), true);

        self::assertArrayHasKey('id', $member, "Failed to create a list member");

        $updatedData = [
            'unsubscribe_reason' => 'Test unsubscribe reason'
        ];

        $this->put("/mailchimp/lists/{$this->listId}/members/{$member['id']}", $updatedData);

        $content = json_decode($this->response->content(), true);

        $this->assertResponseOk();

        self::assertArrayHasKey('unsubscribe_reason', $content);
        self::assertEquals('Test unsubscribe reason', $content['unsubscribe_reason']);

        foreach (array_keys($updatedData) as $key) {
            self::assertArrayHasKey($key, $content);
            self::assertEquals($updatedData[$key], $content[$key]);
        }
    }

    /**
     * Test application returns error response with errors when list validation fails.
     *
     * @return void
     */
    public function testUpdateListValidationFailed(): void
    {
        $this->post("/mailchimp/lists/{$this->listId}/members", static::$memberData);
        $member = json_decode($this->response->content(), true);

        self::assertArrayHasKey('id', $member, "Failed to create a list member");

        $invalidUpdatedData = [
            'email_type' => 'invalid_email_type'
        ];

        $this->put("/mailchimp/lists/{$this->listId}/members/{$member['id']}", $invalidUpdatedData);

        $content = json_decode($this->response->content(), true);

        $this->assertResponseStatus(400);

        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('errors', $content);

        foreach (array_keys($invalidUpdatedData) as $key) {
            self::assertArrayHasKey($key, $content['errors']);
        }
    }
}
