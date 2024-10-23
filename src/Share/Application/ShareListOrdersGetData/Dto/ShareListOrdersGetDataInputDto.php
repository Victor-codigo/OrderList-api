<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersGetData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

readonly class ShareListOrdersGetDataInputDto implements ServiceInputDtoInterface
{
    public UserShared $userSession;
    public Identifier $listOrdersId;

    public function __construct(UserShared $userSession, ?string $listOrdersId)
    {
        $this->userSession = $userSession;
        $this->listOrdersId = ValueObjectFactory::createIdentifier($listOrdersId);
    }

    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'shared_list_orders_id' => $this->listOrdersId,
        ]);
    }
}
