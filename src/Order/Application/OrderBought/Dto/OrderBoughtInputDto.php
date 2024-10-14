<?php

declare(strict_types=1);

namespace Order\Application\OrderBought\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

class OrderBoughtInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $orderId;
    public readonly Identifier $groupId;
    public readonly bool $bought;

    public function __construct(UserShared $userSession, ?string $orderId, ?string $groupId, bool $bought)
    {
        $this->userSession = $userSession;
        $this->orderId = ValueObjectFactory::createIdentifier($orderId);
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->bought = $bought;
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'order_id' => $this->orderId,
            'group_id' => $this->groupId,
        ]);
    }
}
