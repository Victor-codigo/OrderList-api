<?php

declare(strict_types=1);

namespace Test\Unit\User\Application\GetUsers\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use User\Application\GetUsers\Dto\GetUsersInputDto;
use User\Domain\Model\User;

class GetUsersInputDtoTest extends TestCase
{
    private const int NUM_MAX_USERS = 50;

    private GetUsersInputDto $object;
    private ValidationInterface $validator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    private function createGetUsersInputDto(array|null $usersId): GetUsersInputDto
    {
        $user = $this->createMock(User::class);

        return new GetUsersInputDto($user, $usersId);
    }

    private function getIds(int $numIds): array
    {
        $ids = [
            '9831dc42-50bb-4230-a1c1-701dc2d74a36',
            '672e761b-1ea1-45b9-bd06-5d451d6240ac',
            '000bcc70-8244-4926-b608-6890a729b74c',
            'a441818f-0743-4d1c-9544-9966b0bb302a',
            '3707c6aa-a008-403d-b7a3-42e07e3a7741',
            '9c72ba59-29ff-4cbc-adfb-01786653df6a',
            'a5890853-6683-4a3a-a1ac-115af7ce7856',
            '499dab30-8d6e-44c6-9217-6759f2a20b3d',
            '2a9e349f-a6d7-482d-94ab-ec42d71435e5',
            '8ded3878-3021-4805-babc-8f1a7f90df6a',
            '6a5cbe62-2b4e-47af-bb75-69860dc92177',
            '008a9a8e-c16f-49a8-ad8d-7cce75aa6e9c',
            '1b5150c1-84a8-465e-83a8-1500e85af7dd',
            'e74b03fb-2f73-429b-8313-a42b28fb75a3',
            'e3d576a6-9fba-4f29-9f82-9bd9f65a6f28',
            '277f4331-c9e6-44b6-aa47-821070de5892',
            'bebfe54a-b430-4972-88d9-d749a5498263',
            '8177696c-c551-4830-a430-f170f262d67f',
            '5d429738-e2bb-4869-ad6d-2b76bbcb56bf',
            '6b8e6d75-ef73-448e-9ff6-ead0a248c546',
            '06d59908-5584-454e-ba4a-441d3d7f9843',
            '8681e962-2a88-483f-857e-4520876237e3',
            'c1ca970e-f58a-4886-8d8c-986ebf1b6599',
            '758fcebe-4eef-4e95-ad09-06210bcc7742',
            'b7c924e2-397b-44b8-9518-6782b7e8cd5a',
            '47091c7b-65b4-4e1f-b0e7-746d3c310342',
            'bf0d869b-0eca-4c04-b818-f9841a7ce0ea',
            '865bd977-01c2-4216-ac03-9b7df3857d7e',
            '7fb4dc06-eb6b-4eca-b5dd-b0f23a3aabde',
            '7420833f-09f6-4f62-bf68-0b7901e90ad1',
            'ec629b59-44c2-48d2-9704-1e35919f1f3e',
            '046cf544-0f4f-471b-94f2-5af9edf64b76',
            'ebb39122-1b19-438b-807e-114394caa771',
            'e1250d5d-89d0-461e-b306-b632c7031760',
            '4fdd4259-f10f-4fb9-a8fc-e9982104091d',
            '45532865-6fef-4dbf-a889-8087735517f7',
            'a984ac46-78e0-4d6c-831d-b3d82e947af9',
            '24e7f94b-bc70-4685-9349-d02c2bd97260',
            '6f8533a0-c34b-45ad-8cde-dc6230801854',
            'beb0b4de-5620-4ce0-888f-7fc89397326b',
            '7011528d-e805-4bf9-b944-99a6ffa6ec17',
            '242e696f-6610-4de9-809e-207a92bde8d3',
            '360ab406-f24b-4d99-81f0-84d195c6c26f',
            '698fe112-a76d-49c1-b7de-8a84fb81cf76',
            '1407bcf4-21c7-4c50-ba2f-5ea04741ef14',
            '73c3b48f-3cc6-4387-8986-191eb3ece70d',
            '56b6352f-6b53-4a82-8ee6-1fec4d13a977',
            '2bbe1b55-2a21-4279-963d-7c4aa86eb49f',
            'dbf389c3-4bcd-4fc0-9f4b-460cfb2c79ce',
            '895a1a4b-718d-46c4-80db-3d1ee3f8af8e',
            '33ca7fff-3074-4a01-8722-bc5dbf6eb729',
            'ed9fe04c-60c2-4ddc-80de-664baed98b53',
            '96292e27-a81a-4529-a35d-8cb68d44e7d4',
            'f2366dc0-c277-4f9f-bd1e-2e11645ea801',
            '02e87051-5f56-4cd2-bf86-a874b49007d7',
            '401a7b7e-5027-4100-ae67-58c229102111',
            '3554b2f3-27df-466d-9092-9368e15573f9',
            '39ccbbc1-74b1-492e-af79-e87a1367fbde',
            '6555162a-2ced-4de4-a050-6d9b2ad93fe9',
            '83eaebe2-71cb-4058-b9a3-0e25d86976f3',
            '855abc22-2671-40f0-b264-dd905855cf7d',
            '916a2830-53f3-4c02-9e03-9ff29c9f8aab',
            '52e1fc03-7e82-4a65-83e0-4aad29757bfd',
            'f6e01fd6-e066-40e7-8a44-bdcfd67301ea',
            '60f2fd90-3569-4e4e-a1b4-08832fea7238',
            '9414d62d-9767-49f6-947e-338e2f12a7ae',
            '90cbfb2e-4866-41e6-9b4d-553bab8bf329',
            '5fe4972a-5baa-4e7d-aebc-b45358f5343d',
            'ff8affc4-4b5f-42c1-ab32-c5c99cc01105',
            'b1b0b960-8a89-4df0-b52f-b624e6f55585',
            '28399d95-caa6-49ff-894f-42ef7875c4d1',
            '5c2bd8eb-efbc-402a-aaa6-5687eb1d24d5',
            '137b9628-f567-41df-9d76-81d59441818e',
            '9d97a277-83ab-49a4-b20b-9d9f1154ada3',
            '793d999c-6434-43b5-8c77-a8650cf8b1d2',
        ];

        return array_slice($ids, 0, $numIds);
    }

    /** @test */
    public function itShouldValidateUsersId(): void
    {
        $usersId = $this->getIds(self::NUM_MAX_USERS - 1);
        $this->object = $this->createGetUsersInputDto($usersId);
        $return = $this->object->validate($this->validator);

        $this->assertCount(self::NUM_MAX_USERS - 1, $this->object->usersId);
        $this->assertContainsOnlyInstancesOf(Identifier::class, $this->object->usersId);
        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailIdsMissing(): void
    {
        $this->object = $this->createGetUsersInputDto(null);
        $return = $this->object->validate($this->validator);

        $this->assertEquals([VALIDATION_ERRORS::NOT_NULL, VALIDATION_ERRORS::NOT_BLANK], $return);
    }

    /** @test */
    public function itShouldFailIdsEmpty(): void
    {
        $this->object = $this->createGetUsersInputDto([]);
        $return = $this->object->validate($this->validator);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK], $return);
    }

    /** @test */
    public function itShouldFailIdsIsWrong(): void
    {
        $usersId = $this->getIds(5);
        $usersId[] = 'wrong id';
        $this->object = $this->createGetUsersInputDto($usersId);
        $return = $this->object->validate($this->validator);

        $this->assertEquals([[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
