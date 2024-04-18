<?php

namespace App\Tests\Functional;

use App\Service\TagManager;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Support\FunctionalTester;

class TagCest
{
    private EntityManagerInterface $entityManager;
    private UserManager $userManager;
    private TagManager $tagManager;

    public function _before(FunctionalTester $I)
    {
        $this->entityManager = $I->grabService(EntityManagerInterface::class);
        $this->userManager = $I->grabService(UserManager::class);
        $this->tagManager = $I->grabService(TagManager::class);
    }

    public function testCreateTag(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('post', '/api/admin/tags', [
            'name' => 'Launch',
            'colorCode' => '#FF99CC'
        ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson([
            'name' => 'Launch',
            'colorCode' => '#FF99CC'
        ]);
    }

    public function testUpdateTag(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('patch', '/api/admin/tags/1', [
            'name' => 'Test',
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'name' => 'Test',
        ]);
    }

    public function testDeleteTag(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('delete', '/api/admin/tags/2');
        $I->seeResponseCodeIs(200);

        $I->sendRequest('delete', '/api/admin/tags/2222');
        $I->seeResponseCodeIs(404);
    }

    public function testGetAllTags(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('get', '/api/admin/tags');
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            ['name' => 'Backend', 'name' => 'Frontend']
        ]);
    }

    public function testGetOneTag(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('get', '/api/admin/tags/1');
        $I->seeResponseCodeIs(200);
    }
}
