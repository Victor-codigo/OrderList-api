<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersCreate\Dto;

use Common\Domain\Model\ValueObject\Date\DateNowToFuture;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

class ListOrdersCreateInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    public readonly NameWithSpaces $name;
    public readonly Description $description;
    public readonly DateNowToFuture $dateToBuy;

    public function __construct(UserShared $userSession, ?string $groupId, ?string $name, ?string $description, ?string $dateToBuy)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->name = ValueObjectFactory::createNameWithSpaces($name);
        $this->description = ValueObjectFactory::createDescription($description);
        $this->dateToBuy = ValueObjectFactory::createDateNowToFuture(
            $this->stringDateToDateTime($dateToBuy)
        );
    }

    private function stringDateToDateTime(?string $date): ?\DateTime
    {
        if (null === $date) {
            return null;
        }

        $dateTimeToBuy = \DateTime::createFromFormat('Y-m-d H:i:s', $date);

        if ($dateTimeToBuy instanceof \DateTime) {
            return $dateTimeToBuy;
        }

        return null;
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
            'name' => $this->name,
            'description' => $this->description,
            'date_to_buy' => $this->dateToBuy,
        ]);
    }
}
