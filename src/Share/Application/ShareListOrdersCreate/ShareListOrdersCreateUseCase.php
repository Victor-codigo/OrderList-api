<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Share\Application\ShareListOrdersCreate\Dto\ShareListOrdersCreateInputDto;
use Share\Application\ShareListOrdersCreate\Dto\ShareListOrdersCreateOutputDto;
use Share\Application\ShareListOrdersCreate\Exception\ShareCreateListOrdersNotFoundException;
use Share\Domain\Service\ShareListOrdersCreate\Dto\ShareListOrderCreateDto;
use Share\Domain\Service\ShareListOrdersCreate\ShareListOrdersCreateService;

class ShareListOrdersCreateUseCase extends ServiceBase
{
    public function __construct(
        private ShareListOrdersCreateService $shareListOrdersCreateService,
        private ValidationInterface $validator,
    ) {
    }

    /**
     * @throws ValueObjectValidationException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ShareListOrdersCreateInputDto $input): ShareListOrdersCreateOutputDto
    {
        $this->validation($input);

        try {
            $sharedList = $this->shareListOrdersCreateService->__invoke(
                $this->createShareListOrdersCreateDto($input)
            );

            return $this->createShareListOrdersCreateOutputDto($sharedList->getId());
        } catch (DBNotFoundException) {
            throw ShareCreateListOrdersNotFoundException::fromMessage('List orders not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ShareListOrdersCreateInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createShareListOrdersCreateDto(ShareListOrdersCreateInputDto $input): ShareListOrderCreateDto
    {
        return new ShareListOrderCreateDto($input->listOrdersId, $input->userSession->getId());
    }

    private function createShareListOrdersCreateOutputDto(Identifier $sharedListId): ShareListOrdersCreateOutputDto
    {
        return new ShareListOrdersCreateOutputDto($sharedListId);
    }
}
