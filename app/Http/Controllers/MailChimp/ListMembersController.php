<?php

declare(strict_types=1);

namespace App\Http\Controllers\MailChimp;

use App\Database\Entities\MailChimp\MailChimpList;
use App\Database\Entities\MailChimp\MailChimpListMember;
use App\Exceptions\EntityValidationException;
use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mailchimp\Mailchimp;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListMembersController extends Controller
{
    /** @var Mailchimp */
    private $mailChimp;


    public function __construct(EntityManagerInterface $entityManager, Mailchimp $mailchimp)
    {
        parent::__construct($entityManager);

        $this->mailChimp = $mailchimp;
    }

    public function create(string $listId, Request $request): JsonResponse
    {
        $member = new MailChimpListMember();

        $validator = $this->getValidationFactory()->make(
            $request->all(),
            $member->getValidationRules()
        );

        if ($validator->fails()) {
            throw new EntityValidationException($validator->errors()->toArray());
        }

        $member->fill($request->all());

        $list = $this->getList($listId);

        try {
            $response = $this->mailChimp->post(
                "lists/{$list->getMailChimpId()}/members",
                $member->toMailChimpArray()
            );

            // @todo Fill the entity with other data from mailchimp API

            $this->saveEntity($member->setMailChimpId($response->get('id')));
        } catch (Exception $exception) {
            // @todo Write real message to log
            throw new RuntimeException('Unknown error happened');
        }

        return $this->successfulResponse($member->toArray());
    }

    public function remove(string $listId, string $memberId): JsonResponse
    {
        $list = $this->getList($listId);
        $member = $this->getListMember($memberId);

        try {
            // NOTE: Remote first!
            // Remove list from MailChimp
            $this->mailChimp->delete("lists/{$list->getMailChimpId()}/members/{$member->getMailChimpId()}");

            // Remove list from database
            $this->removeEntity($member);
        } catch (Exception $exception) {
            // @todo Write exception message to log file
            throw new RuntimeException('Unknown error happened');
        }

        return $this->successfulResponse([]);
    }

    public function show(string $memberId): JsonResponse
    {
        return $this->successfulResponse(
            $this->getListMember($memberId)->toArray()
        );
    }

    public function update(Request $request, string $listId, string $memberId): JsonResponse
    {
        $list = $this->getList($listId);
        $member = $this->getListMember($memberId);

        $validator = $this->getValidationFactory()->make(
            $request->all(),
            $member->getValidationRules()
        );

        if ($validator->fails()) {
            throw new EntityValidationException($validator->errors()->toArray());
        }

        $member->fill($request->all());

        try {
            $this->mailChimp->patch(
                "lists/{$list->getMailChimpId()}/members/{$member->getMailChimpId()}",
                $member->toMailChimpArray()
            );

            $this->saveEntity($member);
        } catch (Exception $exception) {
            // @todo Write real error  message to log file
            throw new RuntimeException('Unknown error happened');
        }

        return $this->successfulResponse($member->toArray());
    }

    public function getList(string $id): MailChimpList
    {
        /** @var MailChimpList|null $list */
        if (!$list = $this->entityManager->getRepository(MailChimpList::class)->find($id)) {
            throw new NotFoundHttpException("MailChimpList[$id] not found");
        }

        return $list;
    }

    public function getListMember(string $id): MailChimpListMember
    {
        /** @var MailChimpListMember|null $member */
        if (!$member = $this->entityManager->getRepository(MailChimpListMember::class)->find($id)) {
            throw new NotFoundHttpException("MailChimpListMember[$id] not found");
        }

        return $member;
    }
}
