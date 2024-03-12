<?php

namespace App\Tests\Functional;

// use App\Entity\Vacation;

use App\Entity\Vacation;
use App\Service\UserManager;
use App\Service\VacationManager;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Support\FunctionalTester;

class VacationCest
{
    private EntityManagerInterface $entityManager;
    private UserManager $userManager;
    private VacationManager $vacationManager;

    public function _before(FunctionalTester $I)
    {
        $this->entityManager = $I->grabService(EntityManagerInterface::class);
        $this->userManager = $I->grabService(UserManager::class);
        $this->vacationManager = $I->grabService(VacationManager::class);
    }

    public function testRequestVacation(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('vacationtest@test.com');

        $I->amBearerAuthenticated($token);
        $I->sendRequest('post', '/api/request-vacation', [
            'dateFrom' => '2024-04-12',
            'dateTo' => '2024-04-15',
            'note' => 'Uzrasiukas testui'
        ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson([
            'requestedBy' => ['email' => 'vacationtest@test.com'],
            'note' => 'Uzrasiukas testui',
            'confirmed' => false,
            'dateFrom' => (new \DateTimeImmutable('2024-04-12 00:00:00'))->format('c'),
            'dateTo' => (new \DateTimeImmutable('2024-04-15 23:59:59'))->format(\DateTimeImmutable::ATOM),
            'reviewedBy' => null
        ]);
    }

    public function testUpdateVacationRequest(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('vacationtest@test.com');
        $I->amBearerAuthenticated($token);

        $user = $this->userManager->getUserByEmail('vacationtest@test.com');

        /** @var \App\Repository\VacationRepository $repository */
        $repository = $this->entityManager->getRepository(Vacation::class);
        /** @var Vacation $vacation */
        $vacation = $repository->findOneBy(['requestedBy' => $user->getId()]);

        $I->sendRequest('patch', '/api/update-vacation/' . $vacation->getId(), [
            'dateFrom' => '2024-04-12',
            'dateTo' => '2024-04-17',
            'note' => 'Keiciasi planai'
        ]);

        $I->seeResponseContainsJson([
            'dateFrom' => (new \DateTimeImmutable('2024-04-12 00:00:00'))->format(\DateTimeImmutable::ATOM),
            'dateTo' => (new \DateTimeImmutable('2024-04-17 23:59:59'))->format(\DateTimeImmutable::ATOM),
            'note' => 'Keiciasi planai'
        ]);

        $I->seeResponseCodeIs(200);
    }

    public function testUpdateOtherUserVacationRequestFailure(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $user = $this->userManager->getUserByEmail('vacationtest@test.com');

        /** @var \App\Repository\VacationRepository $repository */
        $repository = $this->entityManager->getRepository(Vacation::class);
        /** @var Vacation $vacation */
        $vacation = $repository->findOneBy(['requestedBy' => $user->getId()]);

        $I->sendRequest('patch', '/api/update-vacation/' . $vacation->getId(), [
            'dateFrom' => '2024-04-12',
            'dateTo' => '2024-04-17',
            'note' => 'Keiciasi planai'
        ]);

        $I->seeResponseCodeIs(400);
    }

    public function testRejectVacationRequest(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $userRequested = $this->userManager->getUserByEmail('vacationtest@test.com');

        /** @var \App\Repository\VacationRepository $repository */
        $repository = $this->entityManager->getRepository(Vacation::class);
        /** @var Vacation $vacation */
        $vacation = $repository->findOneBy(['requestedBy' => $userRequested->getId()]);

        $I->sendRequest('patch', '/api/admin/reject-vacation/' . $vacation->getId(), [
            'rejectionNote' => 'Tomis dienomis iseiti negalima'
        ]);

        $I->seeResponseContainsJson([
            'requestedBy' => ['email' => 'vacationtest@test.com'],
            'rejectionNote' => 'Tomis dienomis iseiti negalima',
            'confirmed' => false,
            'reviewedBy' => ['email' => 'apitest@test.com']
        ]);

        $I->seeResponseCodeIs(201);
    }

    public function testIfUserCanRejectVacationRequest(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('rejecttest@test.com');
        $I->amBearerAuthenticated($token);

        $userRequested = $this->userManager->getUserByEmail('vacationtest@test.com');

        /** @var \App\Repository\VacationRepository $repository */
        $repository = $this->entityManager->getRepository(Vacation::class);
        /** @var Vacation $vacation */
        $vacation = $repository->findOneBy(['requestedBy' => $userRequested->getId()]);

        $I->sendRequest('patch', '/api/admin/reject-vacation/' . $vacation->getId(), [
            'rejectionNote' => 'Tomis dienomis iseiti negalima'
        ]);

        $I->seeResponseCodeIs(403);
    }

    public function testConfirmVacationRequest(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $userRequested = $this->userManager->getUserByEmail('vacationtest@test.com');

        /** @var \App\Repository\VacationRepository $repository */
        $repository = $this->entityManager->getRepository(Vacation::class);
        /** @var Vacation $vacation */
        $vacation = $repository->findOneBy(['requestedBy' => $userRequested->getId()]);

        $I->sendRequest('patch', '/api/admin/confirm-vacation/' . $vacation->getId());

        $I->seeResponseContainsJson([
            'requestedBy' => ['email' => 'vacationtest@test.com'],
            'confirmed' => true,
            'reviewedBy' => ['email' => 'apitest@test.com']
        ]);

        $I->seeResponseCodeIs(201);
    }

    public function testIfUserCanConfirmVacationRequest(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('userconfirmtest@test.com');
        $I->amBearerAuthenticated($token);

        $userRequested = $this->userManager->getUserByEmail('vacationtest@test.com');

        /** @var \App\Repository\VacationRepository $repository */
        $repository = $this->entityManager->getRepository(Vacation::class);
        /** @var Vacation $vacation */
        $vacation = $repository->findOneBy(['requestedBy' => $userRequested->getId()]);

        $I->sendRequest('patch', '/api/admin/confirm-vacation/' . $vacation->getId());

        $I->seeResponseCodeIs(403);
    }

    public function testVacationRequestWhenItStartsInThePast(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('vacationtest@test.com');

        $I->amBearerAuthenticated($token);
        $I->sendRequest('post', '/api/request-vacation', [
            'dateFrom' => '2024-01-10',
            'dateTo' => '2024-03-15',
            'note' => ''
        ]);

        $I->seeResponseCodeIs(400);
    }

    public function testVacationRequestWhenItStartsAndEndsInThePast(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('vacationtest@test.com');

        $I->amBearerAuthenticated($token);
        $I->sendRequest('post', '/api/request-vacation', [
            'dateFrom' => '2024-01-10',
            'dateTo' => '2024-01-20',
            'note' => ''
        ]);

        $I->seeResponseCodeIs(400);
    }
}
