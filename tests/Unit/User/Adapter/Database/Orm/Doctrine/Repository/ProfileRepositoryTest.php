<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Database\Orm\Doctrine\Repository;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Test\Unit\DataBaseTestCase;
use User\Adapter\Database\Orm\Doctrine\Repository\ProfileRepository;
use User\Domain\Model\Profile;

class ProfileRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private ProfileRepository $object;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(Profile::class);
    }

    #[Test]
    public function itShouldReturnThreeProfiles(): void
    {
        $usersId = [
            ValueObjectFactory::createIdentifier('2606508b-4516-45d6-93a6-c7cb416b7f3f'),
            ValueObjectFactory::createIdentifier('b11c9be1-b619-4ef5-be1b-a1cd9ef265b7'),
            ValueObjectFactory::createIdentifier('1befdbe2-9c14-42f0-850f-63e061e33b8f'),
        ];

        $return = $this->object->findProfilesOrFail($usersId);

        $dbProfilesIds = array_map(
            fn (Profile $profile): Identifier => $profile->getId(),
            $return
        );

        $this->assertCount(count($usersId), $return);
        $this->assertContainsOnlyInstancesOf(Profile::class, $return);

        foreach ($dbProfilesIds as $dbProfileId) {
            $this->assertContainsEquals($dbProfileId, $usersId);
        }
    }

    #[Test]
    public function itShouldFailNoIdsPassed(): void
    {
        $this->expectException(DBNotFoundException::class);

        $this->object->findProfilesOrFail([]);
    }

    #[Test]
    public function itShouldFailNotFoundIds(): void
    {
        $this->expectException(DBNotFoundException::class);

        $usersId = [
            ValueObjectFactory::createIdentifier('0b13e52d-b058-32fb-8507-10dec634a07A'),
            ValueObjectFactory::createIdentifier('0b17ca3e-490b-3ddb-aa78-35b4ce668dcA'),
            ValueObjectFactory::createIdentifier('1befdbe2-9c14-42f0-850f-63e061e33b8A'),
        ];
        $this->object->findProfilesOrFail($usersId);
    }
}
