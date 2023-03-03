<?php

declare(strict_types=1);

namespace Common\Adapter\Validation\Validations;

use Common\Domain\Validation\VALIDATION_ERRORS;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

class ValidationFile extends ValidationConstraintBase
{
    public function file(mixed $maxSize, array|string|null $mimeTypes): ValidationConstraint
    {
        return $this->createConstraint(
            new File(null, $maxSize, null, $mimeTypes),
            [
                File::INVALID_MIME_TYPE_ERROR => VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE,
                File::NOT_FOUND_ERROR => VALIDATION_ERRORS::FILE_NOT_FOUND,
                File::NOT_READABLE_ERROR => VALIDATION_ERRORS::FILE_NOT_READABLE,
                FIle::TOO_LARGE_ERROR => VALIDATION_ERRORS::FILE_TOO_LARGE,
                File::EMPTY_ERROR => VALIDATION_ERRORS::FILE_EMPTY,
            ]
        );
    }

    public function image(
        mixed $maxSize,
        array|string|null $mimeTypes,
        int|null $minWith = null,
        int|null $maxWith = null,
        int|null $minHeigh = null,
        int|null $maxHeigh = null,
        int|null $minPixels = null,
        int|null $maxPixels = null,
        float|null $minAspectRatio = null,
        float|null $maxAspectRatio = null,
        bool $allowLandscape = true,
        bool $allowPortrait = true,
        bool $allowSquareImage = true,
        bool $detectCorrupted = false
    ): ValidationConstraint {
        return $this->createConstraint(
            new Image(
                null,
                $maxSize,
                null,
                $mimeTypes,
                $minWith,
                $maxWith,
                $maxHeigh,
                $minHeigh,
                $maxAspectRatio,
                $minAspectRatio,
                $minPixels,
                $maxPixels,
                $allowSquareImage,
                $allowLandscape,
                $allowPortrait,
                $detectCorrupted
            ),
            [
                Image::INVALID_MIME_TYPE_ERROR => VALIDATION_ERRORS::FILE_INVALID_MIME_TYPE,
                Image::NOT_FOUND_ERROR => VALIDATION_ERRORS::FILE_NOT_FOUND,
                Image::NOT_READABLE_ERROR => VALIDATION_ERRORS::FILE_NOT_READABLE,
                Image::EMPTY_ERROR => VALIDATION_ERRORS::FILE_EMPTY,
                Image::TOO_LARGE_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_TOO_LARGE,
                Image::TOO_NARROW_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_TOO_NARROW,
                Image::TOO_WIDE_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_TOO_WIDE,
                Image::TOO_LOW_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_TOO_LOW,
                Image::TOO_HIGH_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_TOO_HIGH,
                Image::TOO_FEW_PIXEL_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_TOO_FEW_PIXEL,
                Image::TOO_MANY_PIXEL_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_TOO_MANY_PIXEL,
                Image::RATIO_TOO_SMALL_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_RATIO_TOO_SMALL,
                Image::RATIO_TOO_BIG_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_RATIO_TOO_BIG,
                Image::LANDSCAPE_NOT_ALLOWED_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_LANDSCAPE_NOT_ALLOWED,
                Image::PORTRAIT_NOT_ALLOWED_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_PORTRAIT_NOT_ALLOWED,
                Image::SQUARE_NOT_ALLOWED_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_SQUARE_NOT_ALLOWED,
                Image::SIZE_NOT_DETECTED_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_SIZE_NOT_DETECTED,
                Image::CORRUPTED_IMAGE_ERROR => VALIDATION_ERRORS::FILE_USER_IMAGE_CORRUPTED_IMAGE,
                UPLOAD_ERR_FORM_SIZE => VALIDATION_ERRORS::FILE_UPLOAD_FORM_SIZE,
                UPLOAD_ERR_INI_SIZE => VALIDATION_ERRORS::FILE_UPLOAD_INIT_SIZE,
                UPLOAD_ERR_NO_FILE => VALIDATION_ERRORS::FILE_UPLOAD_NO_FILE,
                UPLOAD_ERR_PARTIAL => VALIDATION_ERRORS::FILE_UPLOAD_PARTIAL,
                UPLOAD_ERR_EXTENSION => VALIDATION_ERRORS::FILE_UPLOAD_EXTENSION,
                UPLOAD_ERR_CANT_WRITE => VALIDATION_ERRORS::FILE_UPLOAD_CANT_WRITE,
                UPLOAD_ERR_NO_TMP_DIR => VALIDATION_ERRORS::FILE_UPLOAD_NO_TMP_DIR,
            ]
        );
    }
}
