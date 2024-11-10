<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use Share\Application\ShareListOrdersCreate\Dto\ShareListOrdersCreateInputDto;
use Share\Application\ShareListOrdersCreate\Dto\ShareListOrdersCreateOutputDto;
use Share\Application\ShareListOrdersCreate\Exception\ShareCreateListOrdersNotFoundException;
use Share\Application\ShareListOrdersCreate\Exception\ShareCreateListOrdersNotificationException;
use Share\Domain\Service\ShareListOrdersCreate\Dto\ShareListOrderCreateDto;
use Share\Domain\Service\ShareListOrdersCreate\ShareListOrdersCreateService;

class ShareListOrdersCreateUseCase extends ServiceBase
{
    public function __construct(
        private ShareListOrdersCreateService $shareListOrdersCreateService,
        private ValidationInterface $validator,
        private ModuleCommunicationInterface $moduleCommunication,
        private ListOrdersRepositoryInterface $listOrdersRepository,
        private string $systemKey,
    ) {
    }

    /**
     * @throws ValueObjectValidationException
     * @throws DomainInternalErrorException
     * @throws ShareCreateListOrdersNotificationException
     * @throws Error400Exception
     * @throws ModuleCommunicationException
     * @throws DBNotFoundException
     */
    public function __invoke(ShareListOrdersCreateInputDto $input): ShareListOrdersCreateOutputDto
    {
        $this->validation($input);

        try {
            $sharedList = $this->shareListOrdersCreateService->__invoke(
                $this->createShareListOrdersCreateDto($input)
            );

            $listOrders = $this->getListOrdersData($input->listOrdersId);

            $this->createNotificationListOrdersCreated(
                $input->userSession->getId(),
                $sharedList->getId(),
                $listOrders->getName(),
                $this->systemKey
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

    /**
     * @throws ShareCreateListOrdersNotificationException
     * @throws Error400Exception
     * @throws ModuleCommunicationException
     */
    private function createNotificationListOrdersCreated(Identifier $userId, Identifier $sharedRecourseId, NameWithSpaces $listOrdersName, string $systemKey): void
    {
        $responseData = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::notificationShareListOrdersCreated(
                $userId,
                $sharedRecourseId,
                $listOrdersName,
                $systemKey
            )
        );
        throw new DomainExceptionOutput('este es el error: '.$responseData->getMessage(), $responseData->getErrors(), RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
        if (RESPONSE_STATUS::OK !== $responseData->getStatus()) {
            throw ShareCreateListOrdersNotificationException::fromMessage('An error was ocurred when trying to send the notification: shared list orders created');
        }
    }

    /**
     * @throws DBNotFoundException
     */
    private function getListOrdersData(Identifier $listOrdersId): ListOrders
    {
        $listOrdersPagination = $this->listOrdersRepository->findListOrderByIdOrFail([$listOrdersId], null);
        $listOrdersPagination->setPagination(1, 1);

        return iterator_to_array($listOrdersPagination)[0];
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
