<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductCreate;

use Common\Domain\Config\AppConfig;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Object\ProductImage;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Ports\Image\ImageInterface;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductCreate\Dto\ProductCreateDto;
use Product\Domain\Service\ProductCreate\Exception\ProductCreateNameAlreadyExistsException;

class ProductCreateService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private FileUploadInterface $fileUpload,
        private ImageInterface $image,
        private string $productImagePath
    ) {
    }

    /**
     * @throws ProductCreateNameAlreadyExistsException
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     * @throws FileUploadCanNotWriteException
     * @throws FileUploadExtensionFileException
     * @throws FileUploadException
     * @throws FormSizeFileException
     * @throws FileUploadIniSizeException
     * @throws FileUploadNoFileException
     * @throws FileUploadTmpDirFileException
     * @throws FileUploadPartialFileException
     * @throws FileException
     * @throws ImageResizeException
     */
    public function __invoke(ProductCreateDto $input): Product
    {
        try {
            $this->productRepository->findProductsByGroupAndNameOrFail($input->groupId, $input->name);

            throw ProductCreateNameAlreadyExistsException::fromMessage('Product name already exists');
        } catch (DBNotFoundException) {
            $product = $this->createProduct($input->groupId, $input->name, $input->description, $input->image);
            $this->productRepository->save($product);

            return $product;
        }
    }

    /**
     * @throws FileUploadCanNotWriteException
     * @throws FileUploadExtensionFileException
     * @throws FileUploadException
     * @throws FormSizeFileException
     * @throws FileUploadIniSizeException
     * @throws FileUploadNoFileException
     * @throws FileUploadTmpDirFileException
     * @throws FileUploadPartialFileException
     * @throws FileException
     * @throws ImageResizeException
     */
    private function createProduct(Identifier $groupId, NameWithSpaces $name, Description $description, ProductImage $image): Product
    {
        $productId = ValueObjectFactory::createIdentifier($this->productRepository->generateId());

        return new Product(
            $productId,
            $groupId,
            $name,
            $description,
            $this->productImageUpload($image)
        );
    }

    /**
     * @throws FileUploadCanNotWriteException
     * @throws FileUploadExtensionFileException
     * @throws FileUploadException
     * @throws FormSizeFileException
     * @throws FileUploadIniSizeException
     * @throws FileUploadNoFileException
     * @throws FileUploadTmpDirFileException
     * @throws FileUploadPartialFileException
     * @throws FileException
     * @throws ImageResizeException
     */
    private function productImageUpload(ProductImage $image): Path
    {
        if ($image->isNull()) {
            return new Path(null);
        }

        $this->fileUpload->__invoke($image->getValue(), $this->productImagePath);

        $this->image->resizeToAFrame(
            ValueObjectFactory::createPath("{$this->productImagePath}/{$this->fileUpload->getFileName()}"),
            AppConfig::PRODUCT_IMAGE_FRAME_SIZE_WIDTH,
            AppConfig::PRODUCT_IMAGE_FRAME_SIZE_HEIGHT
        );

        return ValueObjectFactory::createPath($this->fileUpload->getFileName());
    }
}
