<?php

namespace App\Tests\Functional;

use App\Entity\ReservedDay;
use App\Service\ReservedDayManager;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Support\FunctionalTester;

class ReservedDayCest
{
    private EntityManagerInterface $entityManager;
    private UserManager $userManager;
    private ReservedDayManager $reservedDayManager;

    public function _before(FunctionalTester $I)
    {
        $this->entityManager = $I->grabService(EntityManagerInterface::class);
        $this->userManager = $I->grabService(UserManager::class);
        $this->reservedDayManager = $I->grabService(ReservedDayManager::class);
    }

    public function testReserveDays(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $dateFrom = (new \DateTimeImmutable())->modify('+7 days');
        $dateTo = $dateFrom->modify('+1 day');

        $I->sendRequest('post', '/api/admin/reserved-day', [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'note' => 'Important launch'
        ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson([
            'reservedBy' => ['email' => 'apitest@test.com'],
            'dateFrom' => $dateFrom->setTime(0, 0, 0)->format(\DateTimeImmutable::ATOM),
            'dateTo' => $dateTo->setTime(23, 59, 59)->format(\DateTimeImmutable::ATOM),
            'note' => 'Important launch'
        ]);
    }

    public function testReserveDaysInThePast(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('post', '/api/admin/reserved-day', [
            'dateFrom' => '2024-03-10',
            'dateTo' => '2024-03-15',
            'note' => 'Important launch'
        ]);

        $I->seeResponseCodeIs(400);
    }

    public function testRequestVacationWhenThereIsReservedDays(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $dateFrom = (new \DateTimeImmutable())->modify('+12 days');
        $dateTo = $dateFrom->modify('+7 days');

        $I->sendRequest('post', '/api/request-vacation', [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'note' => 'Testuojamas vacation request kai yra rezervuota diena'
        ]);

        $I->seeResponseCodeIs(400);
    }

    public function testUpdateReservedDay(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $user = $this->userManager->getUserByEmail('apitest@test.com');

        /** @var \App\Repository\VacationRepository $repository */
        $repository = $this->entityManager->getRepository(ReservedDay::class);
        /** @var Vacation $vacation */
        $reservedDay = $repository->findOneBy(['reservedBy' => $user->getId()]);

        $dateFrom = (new \DateTimeImmutable())->modify('+8 days');
        $dateTo = $dateFrom->modify('+1 days');

        $I->sendRequest('patch', '/api/admin/reserved-day/' . $reservedDay->getId(), [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'note' => 'Keiciasi launch date',
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'reservedBy' => ['email' => 'apitest@test.com'],
            'dateFrom' => $dateFrom->setTime(0, 0, 0)->format(\DateTimeImmutable::ATOM),
            'dateTo' => $dateTo->setTime(23, 59, 59)->format(\DateTimeImmutable::ATOM),
            'note' => 'Keiciasi launch date'
        ]);

        $I->sendRequest('patch', '/api/admin/reserved-day/22222', [
            'dateFrom' => null,
            'dateTo' => null,
            'note' => '',
        ]);
        $I->seeResponseCodeIs(404);

        $I->sendRequest('patch', '/api/admin/reserved-day/' . $reservedDay->getId(), [
            'dateFrom' => '2024-03-01',
            'dateTo' => '2024-03-10',
            'note' => 'Keiciasi launch date',
        ]);
        $I->seeResponseCodeIs(400);
    }

    public function testDeleteReservedDays(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $user = $this->userManager->getUserByEmail('apitest@test.com');

        /** @var \App\Repository\ReservedDayRespository $repository */
        $repository = $this->entityManager->getRepository(ReservedDay::class);
        /** @var ReservedDay $reservedDay */
        $reservedDay = $repository->findOneBy(['reservedBy' => $user->getId()]);

        $I->sendRequest('delete', '/api/admin/reserved-day/' . $reservedDay->getId());
        $I->seeResponseCodeIs(200);

        $I->sendRequest('delete', '/api/admin/reserved-day/2222');
        $I->seeResponseCodeIs(404);
    }

    public function testGetReservedDaysInTimePeriod(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $I->sendRequest('get', '/api/reserved-day', [
            'startDate' => '2024-04-18',
            'endDate' => '2024-04-21'
        ]);

        $I->seeResponseCodeIs(200);
    }

    public function testGetReservedDaysById(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $user = $this->userManager->getUserByEmail('apitest@test.com');

        /** @var \App\Repository\ReservedDayRespository $repository */
        $repository = $this->entityManager->getRepository(ReservedDay::class);
        /** @var ReservedDay $reservedDay */
        $reservedDay = $repository->findOneBy(['reservedBy' => $user->getId()]);

        $this->reservedDayManager->getReservedDay($reservedDay->getId());
        $resDay = $repository->findBy(['id' => $reservedDay->getId()]);

        $I->assertEquals(1, count($resDay));
    }

    public function testCreateReservedDaysWithTags(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $dateFrom = (new \DateTimeImmutable())->modify('+7 days');
        $dateTo = $dateFrom->modify('+1 day');

        $I->sendRequest('post', '/api/admin/reserved-day', [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'note' => 'Important launch',
            'tags' => [['name' => 'Backend', 'colorCode' => '#990000']]
        ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson([
            'reservedBy' => ['email' => 'apitest@test.com'],
            'dateFrom' => $dateFrom->setTime(0, 0, 0)->format(\DateTimeImmutable::ATOM),
            'dateTo' => $dateTo->setTime(23, 59, 59)->format(\DateTimeImmutable::ATOM),
            'note' => 'Important launch',
            'tags' => [['name' => 'Backend', 'colorCode' => '#990000']]
        ]);
    }

    public function testUpdateReservedDayWithTags(FunctionalTester $I)
    {
        $token = $I->grabTokenForUser('apitest@test.com');
        $I->amBearerAuthenticated($token);

        $user = $this->userManager->getUserByEmail('apitest@test.com');

        /** @var \App\Repository\VacationRepository $repository */
        $repository = $this->entityManager->getRepository(ReservedDay::class);
        /** @var Vacation $vacation */
        $reservedDay = $repository->findOneBy(['reservedBy' => $user->getId()]);

        $dateFrom = (new \DateTimeImmutable())->modify('+8 days');
        $dateTo = $dateFrom->modify('+1 days');

        $I->sendRequest('patch', '/api/admin/reserved-day/' . $reservedDay->getId(), [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'note' => 'Keiciasi launch date',
            'tags' => [
                ['name' => 'Backend', 'colorCode' => '#808080'],
                ['name' => 'Frontend', 'colorCode' => '#808080']
            ],
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'reservedBy' => ['email' => 'apitest@test.com'],
            'dateFrom' => $dateFrom->setTime(0, 0, 0)->format(\DateTimeImmutable::ATOM),
            'dateTo' => $dateTo->setTime(23, 59, 59)->format(\DateTimeImmutable::ATOM),
            'note' => 'Keiciasi launch date',
            'tags' => [
                ['name' => 'Backend', 'colorCode' => '#990000'],
                ['name' => 'Frontend', 'colorCode' => '#FF9999']
            ]
        ]);

        $I->sendRequest('patch', '/api/admin/reserved-day/' . $reservedDay->getId(), [
            'tags' => [['name' => 'Frontend', 'colorCode' => '#808080']],
        ]);

        $I->seeResponseCodeIs(200);
        $I->dontSeeResponseContainsJson([
            'tags' => ['name' => 'Backend']
        ]);
    }
}
