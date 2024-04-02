<?php

namespace App\Tests\Functional;

use App\DTO\VacationDTO;
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

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'dateFrom' => (new \DateTimeImmutable('2024-04-12 00:00:00'))->format(\DateTimeImmutable::ATOM),
            'dateTo' => (new \DateTimeImmutable('2024-04-17 23:59:59'))->format(\DateTimeImmutable::ATOM),
            'note' => 'Keiciasi planai'
        ]);

        $I->sendRequest('patch', '/api/update-vacation/' . $vacation->getId(), [
            'dateFrom' => null,
            'dateTo' => '2024-04-15',
            'note' => 'Keiciasi planai'
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'dateFrom' => (new \DateTimeImmutable('2024-04-12 00:00:00'))->format(\DateTimeImmutable::ATOM),
            'dateTo' => (new \DateTimeImmutable('2024-04-15 23:59:59'))->format(\DateTimeImmutable::ATOM),
            'confirmed' => false
        ]);
    }

    public function testIfUpdatingConfirmedVacationResetsConfirmedStatus(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('vacationtest@test.com');
        $I->amBearerAuthenticated($token);

        $user = $this->userManager->getUserByEmail('vacationtest@test.com');

        /** @var \App\Repository\VacationRepository $repository */
        $repository = $this->entityManager->getRepository(Vacation::class);
        /** @var Vacation $vacation */
        $vacation = $repository->findOneBy(['requestedBy' => $user->getId()]);

        $I->sendRequest('patch', '/api/admin/confirm-vacation/' . $vacation->getId());

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'requestedBy' => ['email' => 'vacationtest@test.com'],
            'confirmed' => true,
            'reviewedBy' => ['email' => 'vacationtest@test.com']
        ]);

        $I->sendRequest('patch', '/api/update-vacation/' . $vacation->getId(), [
            'dateFrom' => '2024-04-21',
            'dateTo' => '2024-04-27',
            'note' => 'Keiciasi planai'
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'dateFrom' => (new \DateTimeImmutable('2024-04-21 00:00:00'))->format(\DateTimeImmutable::ATOM),
            'dateTo' => (new \DateTimeImmutable('2024-04-27 23:59:59'))->format(\DateTimeImmutable::ATOM),
            'note' => 'Keiciasi planai',
            'confirmed' => false
        ]);

        $I->sendRequest('patch', '/api/update-vacation/22222', [
            'dateFrom' => '2024-04-14',
            'dateTo' => '2024-04-15',
            'note' => 'Keiciasi planai'
        ]);

        $I->seeResponseCodeIs(404);
    }

    public function testUpdateVacationRequestWhenDateFromAndDateToIsInThePast(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('vacationtest@test.com');
        $I->amBearerAuthenticated($token);

        $user = $this->userManager->getUserByEmail('vacationtest@test.com');

        /** @var \App\Repository\VacationRepository $repository */
        $repository = $this->entityManager->getRepository(Vacation::class);
        /** @var Vacation $vacation */
        $vacation = $repository->findOneBy(['requestedBy' => $user->getId()]);

        $I->sendRequest('patch', '/api/update-vacation/' . $vacation->getId(), [
            'dateFrom' => '2024-03-01',
            'dateTo' => '2024-03-10',
            'note' => 'Negalima updatint praeityje'
        ]);

        $I->seeResponseCodeIs(400);
    }

    public function testUpdateVacationRequestOnReservedDays(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('vacationtest@test.com');
        $I->amBearerAuthenticated($token);

        $user = $this->userManager->getUserByEmail('vacationtest@test.com');

        /** @var \App\Repository\VacationRepository $repository */
        $repository = $this->entityManager->getRepository(Vacation::class);
        /** @var Vacation $vacation */
        $vacation = $repository->findOneBy(['requestedBy' => $user->getId()]);

        $I->sendRequest('post', '/api/admin/reserved-day', [
            'dateFrom' => '2024-04-19',
            'dateTo' => '2024-04-20',
            'note' => 'Important launch'
        ]);

        $I->sendRequest('patch', '/api/update-vacation/' . $vacation->getId(), [
            'dateFrom' => '2024-04-18',
            'dateTo' => '2024-04-25',
            'note' => 'Yra rezervuotu dienu'
        ]);

        $I->seeResponseCodeIs(400);
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

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'requestedBy' => ['email' => 'vacationtest@test.com'],
            'rejectionNote' => 'Tomis dienomis iseiti negalima',
            'confirmed' => false,
            'reviewedBy' => ['email' => 'apitest@test.com']
        ]);

        $I->seeResponseCodeIs(200);

        $I->sendRequest('patch', '/api/admin/reject-vacation/2222', [
            'rejectionNote' => 'Tomis dienomis iseiti negalima'
        ]);
        $I->seeResponseCodeIs(404);
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

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'requestedBy' => ['email' => 'vacationtest@test.com'],
            'confirmed' => true,
            'reviewedBy' => ['email' => 'apitest@test.com']
        ]);

        $I->seeResponseCodeIs(200);

        $I->sendRequest('patch', '/api/admin/confirm-vacation/22222');
        $I->seeResponseCodeIs(404);
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

    public function testGetRequestedVacationById(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $userRequested = $this->userManager->getUserByEmail('vacationtest@test.com');

        /** @var \App\Repository\VacationRepository $repository */
        $repository = $this->entityManager->getRepository(Vacation::class);
        /** @var Vacation $vacation */
        $vacation = $repository->findOneBy(['requestedBy' => $userRequested->getId()]);

        $I->sendRequest('get', '/api/vacations/' . $vacation->getId());
        $I->seeResponseCodeIs(200);

        $I->sendRequest('get', '/api/vacations/2222');
        $I->seeResponseCodeIs(404);
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

    public function testGetVacationsInTimePeriod(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('vacationtest@test.com');

        $I->amBearerAuthenticated($token);
        $I->sendRequest('get', '/api/vacations', [
            'startDate' => '2024-03-01',
            'endDate' => '2024-03-31'
        ]);

        $I->seeResponseCodeIs(200);
    }

    public function testIfOverlappingVacationsAreAddedToTheBucketsInTimePeriod(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('vacationtest@test.com');
        $I->amBearerAuthenticated($token);

        $user = $this->userManager->getUserByEmail('vacationtest@test.com');

        $vacationDTO1 = new VacationDTO('2024-04-04', '2024-04-07');
        $vacationDTO2 = new VacationDTO('2024-04-30', '2024-05-01');
        $this->vacationManager->requestVacation($user, $vacationDTO1);
        $this->vacationManager->requestVacation($user, $vacationDTO2);

        $I->sendRequest('get', '/api/vacations', [
            'startDate' => '2024-04-05',
            'endDate' => '2024-04-30'
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            '2024-04-12' => ['requestedBy' => ['email' => 'vacationtest@test.com']],
            '2024-04-13' => ['requestedBy' => ['email' => 'vacationtest@test.com']],
            '2024-04-29' => [],
            '2024-04-30' => ['requestedBy' => ['email' => 'vacationtest@test.com']]
        ]);
    }
}
