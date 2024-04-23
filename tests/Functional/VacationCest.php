<?php

namespace App\Tests\Functional;

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

        $dateFrom = (new \DateTimeImmutable())->modify('+1 day');
        $dateTo = (new \DateTimeImmutable())->modify('+4 days');


        $I->amBearerAuthenticated($token);
        $I->sendRequest('post', '/api/request-vacation', [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'note' => 'Uzrasiukas testui'
        ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson([
            'requestedBy' => ['email' => 'vacationtest@test.com'],
            'note' => 'Uzrasiukas testui',
            'confirmed' => false,
            'dateFrom' => $dateFrom->setTime(0, 0, 0)->format('c'),
            'dateTo' => $dateTo->setTime(23, 59, 59)->format(\DateTimeImmutable::ATOM),
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

        $dateFrom = (new \DateTimeImmutable())->modify('+1 day');
        $dateTo = (new \DateTimeImmutable())->modify('+5 days');

        $I->sendRequest('patch', '/api/update-vacation/' . $vacation->getId(), [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'note' => 'Keiciasi planai'
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'dateFrom' => $dateFrom->setTime(0, 0, 0)->format(\DateTimeImmutable::ATOM),
            'dateTo' => $dateTo->setTime(23, 59, 59)->format(\DateTimeImmutable::ATOM),
            'note' => 'Keiciasi planai'
        ]);

        $newDateTo = $dateFrom->modify('+2 days');

        $I->sendRequest('patch', '/api/update-vacation/' . $vacation->getId(), [
            'dateFrom' => null,
            'dateTo' => $newDateTo->format('Y-m-d'),
            'note' => 'Keiciasi planai'
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'dateFrom' => $dateFrom->setTime(0, 0, 0)->format(\DateTimeImmutable::ATOM),
            'dateTo' => $newDateTo->setTime(23, 59, 59)->format(\DateTimeImmutable::ATOM),
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

        $dateFrom = (new \DateTimeImmutable())->modify('+9 days');
        $dateTo = $dateFrom->modify('+6 days');

        $I->sendRequest('patch', '/api/update-vacation/' . $vacation->getId(), [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'note' => 'Keiciasi planai'
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'dateFrom' => $dateFrom->setTime(0, 0, 0)->format(\DateTimeImmutable::ATOM),
            'dateTo' => $dateTo->setTime(23, 59, 59)->format(\DateTimeImmutable::ATOM),
            'note' => 'Keiciasi planai',
            'confirmed' => false
        ]);

        $I->sendRequest('patch', '/api/update-vacation/22222', [
            'dateFrom' => null,
            'dateTo' => null,
            'note' => ''
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

        $dateFrom = (new \DateTimeImmutable())->modify('+7 days');
        $dateTo = $dateFrom->modify('+1 day');

        $I->sendRequest('post', '/api/admin/reserved-day', [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'note' => 'Important launch'
        ]);

        $dateFrom = $dateFrom->modify('-1 day');
        $dateTo = $dateFrom->modify('+7 days');

        $I->sendRequest('patch', '/api/update-vacation/' . $vacation->getId(), [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
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
        $I->seeResponseContains('You are not authorized to update this vacation request.');
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

    public function testIfUserCantConfirmVacationRequest(FunctionalTester $I)
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

        $I->sendRequest('get', '/api/vacations', [
            'startDate' => '2024-04-01',
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

    public function testGetAllVacations(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('vacationtest@test.com');

        $I->amBearerAuthenticated($token);
        $I->sendRequest('get', '/api/admin/all-vacations');

        $I->seeResponseCodeIs(200);
    }

    public function testGetAllCurrentUserVacations(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('vacationtest@test.com');

        $I->amBearerAuthenticated($token);
        $I->sendRequest('get', '/api/user-vacations');

        $I->seeResponseCodeIs(200);
    }

    public function testGetAllConfirmedDaysInAYearCount(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('vacationtest@test.com');

        $I->amBearerAuthenticated($token);
        $I->sendRequest('get', '/api/admin/all-confirmed-days');

        $I->seeResponseCodeIs(200);
    }

    public function testRequestVacationWithTags(FunctionalTester $I)
    {
        $user = $this->userManager->getUserByEmail('vacationtest@test.com');

        $token = $I->grabTokenForUser('vacationtest@test.com');
        $I->amBearerAuthenticated($token);

        $dateFrom = (new \DateTimeImmutable('2050-01-07')); // friday
        $dateTo = (new \DateTimeImmutable('2050-01-11')); // tuesday

        $I->sendRequest('patch', '/api/admin/users/' . $user->getId(), [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phoneNumber' => '123456789',
            'tags' => [['name' => 'Backend']],
        ]);

        // reserve days
        $dateFromReserve = (new \DateTimeImmutable('2050-01-10')); // monday
        $dateToReserve = $dateFromReserve;
        $I->sendRequest('post', '/api/admin/reserved-day', [
            'dateFrom' => $dateFromReserve->format('Y-m-d'),
            'dateTo' => $dateToReserve->format('Y-m-d'),
            'note' => 'Important launch',
            'tags' => [['name' => 'Frontend']]
        ]);

        $I->sendRequest('post', '/api/request-vacation', [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'note' => 'Uzrasiukas testui'
        ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson([
            'requestedBy' => ['email' => 'vacationtest@test.com', 'availableDays' => $user->getAvailableDays() - 3],
            'note' => 'Uzrasiukas testui',
            'confirmed' => false,
            'dateFrom' => $dateFrom->setTime(0, 0, 0)->format('c'),
            'dateTo' => $dateTo->setTime(23, 59, 59)->format(\DateTimeImmutable::ATOM),
            'reviewedBy' => null,
        ]);
    }
}
