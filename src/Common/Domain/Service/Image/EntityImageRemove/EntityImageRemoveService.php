<?php

declare(strict_types=1);

namespace Common\Domain\Service\Image\EntityImageRemove;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\Image\EntityImageModifyInterface;

class EntityImageRemoveService
{
    /**
     * @throws DomainInternalErrorException
     */
    public function __invoke(EntityImageModifyInterface $entity, Path $imagesPathToStorage): void
    {
        if ($entity->getImage()->isNull() || $imagesPathToStorage->isNull()) {
            return;
        }

        $this->removeImage($imagesPathToStorage, $entity->getImage());
        $entity->setImage(ValueObjectFactory::createPath(null));
    }

    /**
     * @throws DomainInternalErrorException
     */
    private function removeImage(Path $imagePathToStorage, Path $imageName): void
    {
        $imagePath = "{$imagePathToStorage->getValue()}/{$imageName->getValue()}";

        if (!file_exists($imagePath)) {
            return;
        }

        if (!unlink($imagePath)) {
            throw DomainInternalErrorException::fromMessage('The image cannot be deleted');
        }
    }
}
