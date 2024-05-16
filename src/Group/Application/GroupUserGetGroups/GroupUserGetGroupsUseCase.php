<?php

declare(strict_types=1);

namespace Group\Application\GroupUserGetGroups;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserGetGroups\Dto\GroupUserGetGroupsInputDto;
use Group\Application\GroupUserGetGroups\Dto\GroupUserGetGroupsOutputDto;
use Group\Application\GroupUserGetGroups\Exception\GroupUserGetGroupsNoGroupsFoundException;
use Group\Domain\Service\GroupUserGetGroups\Dto\GroupUserGetGroupsDto;
use Group\Domain\Service\GroupUserGetGroups\Dto\GroupUserGetGroupsOutputDto as GroupUserGetGroupsOutputServiceDto;
use Group\Domain\Service\GroupUserGetGroups\GroupUserGetGroupsService;

class GroupUserGetGroupsUseCase extends ServiceBase
{
    public function __construct(
        private GroupUserGetGroupsService $groupUserGetGroupsService,
        private ValidationInterface $validator
    ) {
    }

    /**
     * @throws ValueObjectValidationException
     */
    public function __invoke(GroupUserGetGroupsInputDto $input): GroupUserGetGroupsOutputDto
    {
        $this->validation($input);

        try {
            $userGroups = $this->groupUserGetGroupsService->__invoke(
                $this->createGroupUserGetGroupsDto($input)
            );

            return $this->createGroupUserGetGroupsOutputDto($input->page, $userGroups);
        } catch (DBNotFoundException $e) {
            throw GroupUserGetGroupsNoGroupsFoundException::fromMessage('No groups found');
        } catch (\Exception $e) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(GroupUserGetGroupsInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Errors', $errorList);
        }
    }

    private function createGroupUserGetGroupsDto(GroupUserGetGroupsInputDto $input): GroupUserGetGroupsDto
    {
        return new GroupUserGetGroupsDto($input->userSession->getId(), $input->page, $input->pageItems, $input->groupType, $input->filterSection, $input->filterText, $input->orderAsc);
    }

    private function createGroupUserGetGroupsOutputDto(PaginatorPage $page, GroupUserGetGroupsOutputServiceDto $userGroups): GroupUserGetGroupsOutputDto
    {
        return new GroupUserGetGroupsOutputDto($page, $userGroups->pagesTotal, $userGroups->groups);
    }
}
