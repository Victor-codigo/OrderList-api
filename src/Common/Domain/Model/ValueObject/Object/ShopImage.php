<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\ConstraintFactory;

class ShopImage extends ObjectValueObject
{
    #[\Override]
    public function getValidationValue(): mixed
    {
        if (null === $this->value) {
            return null;
        }

        return $this->value->getFile();
    }

    #[\Override]
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::image(
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_MAX_FILE_SIZE,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_MIME_TYPES,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_MIN_WITH,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_MAX_WITH,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_MIN_HEIGH,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_MAX_HEIGH,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_MIN_PIXELS,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_MAX_PIXELS,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_MIN_ASPECT_RATIO,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_MAX_ASPECT_RATIO,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_ALLOW_LANDSCAPE,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_ALLOW_PORTRAIT,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_ALLOW_SQUARE_IMAGE,
                VALUE_OBJECTS_CONSTRAINTS::FILE_SHOP_IMAGE_DETECT_CORRUPTED
            ));
    }
}
