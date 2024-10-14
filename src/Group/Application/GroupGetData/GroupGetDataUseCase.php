<?php

declare(strict_types=1);

namespace Group\Application\GroupGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetData\Dto\GroupGetDataInputDto;
use Group\Application\GroupGetData\Dto\GroupGetDataOutputDto;
use Group\Application\GroupGetData\Exception\GroupGetDataUserNotBelongsToTheGroupException;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupGetData\Dto\GroupGetDataDto;
use Group\Domain\Service\GroupGetData\GroupGetDataService;

class GroupGetDataUseCase extends ServiceBase
{
    public function __construct(
        private GroupGetDataService $groupGetDataService,
        private UserGroupRepositoryInterface $userGroupRepository,
        private ValidationInterface $validator,
    ) {
    }

    public function __invoke(GroupGetDataInputDto $input): GroupGetDataOutputDto
    {
        try {
            $userGroupsValid = $this->getUserGroupsThatBelongsTo($input->userSession->getId(), $input->groupsId);
            $this->validation($input, $userGroupsValid);
            $userGroupsValidIds = array_map(
                fn (UserGroup $userGroup): Identifier => $userGroup->getGroupId(),
                $userGroupsValid
            );

            $groupsData = $this->groupGetDataService->__invoke(
                $this->createGroupGetDataDto($userGroupsValidIds, $input->userSession->getImage())
            );

            return $this->createGroupGetDataOutputDto($groupsData);
        } catch (ValueObjectValidationException|GroupGetDataUserNotBelongsToTheGroupException $e) {
            throw $e;
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @param UserGroup[] $userGroupsValid
     *
     * @throws ValueObjectValidationException
     * @throws GroupGetDataUserNotBelongsToTheGroupException
     */
    private function validation(GroupGetDataInputDto $input, array $userGroupsValid): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if (empty($userGroupsValid)) {
            throw GroupGetDataUserNotBelongsToTheGroupException::fromMessage('You not belong to the group');
        }
    }

    /**
     * @param Identifier[] $groupsId
     *
     * @return UserGroup[]
     *
     * @throws DBNotFoundException
     */
    private function getUserGroupsThatBelongsTo(Identifier $userId, array $groupsId): array
    {
        $userGroups = $this->userGroupRepository->findUserGroupsById($userId);

        return array_filter(
            iterator_to_array($userGroups),
            fn (UserGroup $userGroup): bool => in_array($userGroup->getGroupId(), $groupsId)
        );
    }

    /**
     * @param Identifier[] $groupsId
     */
    private function createGroupGetDataDto(array $groupsId, Path $userImage): GroupGetDataDto
    {
        return new GroupGetDataDto($groupsId, GROUP_TYPE::GROUP, $userImage);
    }

    /**
     * @param \Generator<int, array{
     *  group_id: string|null,
     *  type: string,
     *  name: string|null,
     *  description: string|null,
     *  image: string|null,
     *  created_on: string
     * }> $groupsData
     */
    private function createGroupGetDataOutputDto(\Generator $groupsData): GroupGetDataOutputDto
    {
        return new GroupGetDataOutputDto($groupsData);
    }
}
