<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductModify;

use Common\Domain\Config\AppConfig;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\FileUpload\Exception\FileUploadCanNotWriteException;
use Common\Domain\FileUpload\Exception\FileUploadException;
use Common\Domain\FileUpload\Exception\FileUploadExtensionFileException;
use Common\Domain\FileUpload\Exception\FileUploadIniSizeException;
use Common\Domain\FileUpload\Exception\FileUploadNoFileException;
use Common\Domain\FileUpload\Exception\FileUploadPartialFileException;
use Common\Domain\FileUpload\Exception\FileUploadReplaceException;
use Common\Domain\FileUpload\Exception\FileUploadTmpDirFileException;
use Common\Domain\FileUpload\Exception\File\FileException;
use Common\Domain\Model\ValueObject\Object\ProductImage;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\Image\UploadImage\Dto\UploadImageDto;
use Common\Domain\Service\Image\UploadImage\UploadImageService;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductModify\Dto\ProductModifyDto;
use Product\Domain\Service\ProductModify\Exception\ProductModifyProductNameIsAlreadyInDataBaseException;
use Product\Domain\Service\ProductModify\Exception\ProductModifyProductNotFoundException;
use Product\Domain\Service\ProductModify\Exception\ProductModifyProductShopException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;

class ProductModifyService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private UploadImageService $uploadImageService,
        private string $productImagePath
    ) {
    }

    /**
     * @throws ProductModifyProductNameIsAlreadyInDataBaseException
     * @throws ProductModifyProductNotFoundException
     * @throws ProductModifyProductShopException
     * @throws FileUploadCanNotWriteException
     * @throws FileUploadExtensionFileException
     * @throws FileUploadException
     * @throws FormSizeFileException
     * @throws FileUploadIniSizeException
     * @throws FileUploadNoFileException
     * @throws FileUploadTmpDirFileException
     * @throws FileUploadPartialFileException
     * @throws FileException
     * @throws FileUploadReplaceException
     * @throws ImageResizeException
     */
    public function __invoke(ProductModifyDto $input): Product
    {
        try {
            $productToModify = $this->getProduct($input->productId, $input->groupId);

            if (!$input->name->isNull()
            && !$input->name->equalTo($productToModify->getName())) {
                $this->isValidProductName($input->groupId, $input->name);
            }

            $this->modifyProduct($productToModify, $input->name, $input->description, $input->image, $input->imageRemove);

            return $productToModify;
        } catch (DBNotFoundException $e) {
            throw ProductModifyProductNotFoundException::fromMessage($e->getMessage());
        } catch (DBConnectionException $e) {
            throw ProductModifyProductShopException::fromMessage($e->getMessage());
        }
    }

    /**
     * @throws FileUploadReplaceException
     * @throws FileUploadCanNotWriteException
     * @throws FileUploadExtensionFileException
     * @throws FileUploadReplaceException
     * @throws FormSizeFileException
     * @throws FileUploadIniSizeException
     * @throws FileUploadNoFileException
     * @throws FileUploadTmpDirFileException
     * @throws FileUploadPartialFileException
     * @throws FileException
     * @throws FileUploadReplaceException
     * @throws ImageResizeException
     * @throws DBConnectionException
     */
    private function modifyProduct(Product $product, NameWithSpaces $name, Description $description, ProductImage $image, bool $imageRemove): void
    {
        $name->isNull() ?: $product->setName($name);
        $description->isNull() ?: $product->setDescription($description);
        $this->uploadImageService->__invoke(
            $this->createUploadImageDto($product, $this->productImagePath, $image, $imageRemove)
        );

        $this->productRepository->save($product);
    }

    private function createUploadImageDto(Product $product, string $productImagePath, ProductImage $imageUploaded, bool $remove): UploadImageDto
    {
        return new UploadImageDto(
            $product,
            ValueObjectFactory::createPath($productImagePath),
            $imageUploaded,
            $remove,
            AppConfig::PRODUCT_IMAGE_FRAME_SIZE_WIDTH,
            AppConfig::PRODUCT_IMAGE_FRAME_SIZE_HEIGHT
        );
    }

    /**
     * @throws DBNotFoundException
     */
    private function getProduct(Identifier $productId, Identifier $groupId): Product
    {
        $productPagination = $this->productRepository->findProductsOrFail($groupId, [$productId]);
        $productPagination->setPagination(1, 1);

        return iterator_to_array($productPagination)[0];
    }

    /**
     * @throws ProductModifyProductNameIsAlreadyInDataBaseException
     */
    private function isValidProductName(Identifier $groupId, NameWithSpaces $productName): void
    {
        try {
            $this->productRepository->findProductsByGroupAndNameOrFail($groupId, $productName);

            throw ProductModifyProductNameIsAlreadyInDataBaseException::fromMessage('The product name is already in the database');
        } catch (DBNotFoundException) {
            return;
        }
    }
}
