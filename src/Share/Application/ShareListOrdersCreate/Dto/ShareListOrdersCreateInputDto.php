<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersCreate\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

readonly class ShareListOrdersCreateInputDto implements ServiceInputDtoInterface
{
    public UserShared $userSession;
    public Identifier $listOrdersId;

    public function __construct(UserShared $userSession, ?string $listOrdersId)
    {
        $this->userSession = $userSession;
        $this->listOrdersId = ValueObjectFactory::createIdentifier($listOrdersId);
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'list_orders_id' => $this->listOrdersId,
        ]);
    }
}
