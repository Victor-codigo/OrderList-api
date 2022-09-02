<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Common\Domain\Validation\VALIDATION_ERRORS;
use Symfony\Component\Validator\Constraints\File;

class ValidationFile extends ValidationConstraintBase
{
    public function file(mixed $maxSize, bool|null $binaryFormat, array|string|null $mimeTypes): ValidationConstraint
    {
        return $this->createConstraint(
            new File(null, $maxSize, $binaryFormat, $mimeTypes),
            [
                File::INVALID_MIME_TYPE_ERROR => VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE,
                File::NOT_FOUND_ERROR => VALIDATION_ERRORS::FILE_NOT_FOUND,
                File::NOT_READABLE_ERROR => VALIDATION_ERRORS::FILE_NOT_READABLE,
                FIle::TOO_LARGE_ERROR => VALIDATION_ERRORS::FILE_TOO_LARGE,
                File::EMPTY_ERROR => VALIDATION_ERRORS::FILE_EMPTY,
            ]
        );
    }
}
