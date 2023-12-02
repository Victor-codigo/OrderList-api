<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductModify;

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
use Common\Domain\Model\ValueObject\Float\Money;
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
use Product\Domain\Service\ProductModify\Exception\ProductModifyProductNotFoundException;
use Product\Domain\Service\ProductModify\Exception\ProductModifyProductShopException;
use Product\Domain\Service\ProductModify\Exception\ProductModifyShopNotFoundException;
use Product\Domain\Service\ProductShop\Dto\ProductShopDto;
use Product\Domain\Service\ProductShop\ProductShopService;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;

class ProductModifyService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ShopRepositoryInterface $shopRepository,
        private ProductShopService $productShopService,
        private UploadImageService $uploadImageService,
        private string $productImagePath
    ) {
    }

    /**
     * @throws ProductModifyProductNotFoundException
     * @throws ProductModifyProductShopException
     * @throws ProductModifyShopNotFoundException
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
     */
    public function __invoke(ProductModifyDto $input): Product
    {
        try {
            $productToModify = $this->getProduct($input->productId, $input->groupId);
            $shop = $this->getShop($input->shopId, $input->groupId);

            $this->productShopService->__invoke(
                $this->createProductShopDto($productToModify, $shop, $input->price, $input->imageRemove)
            );
            $this->modifyProduct($productToModify, $input->name, $input->description, $input->image, $input->imageRemove);

            return $productToModify;
        } catch (ProductModifyShopNotFoundException) {
            if (!$input->shopId->isNull()) {
                throw ProductModifyShopNotFoundException::fromMessage('Shop not found');
            }

            $this->modifyProduct($productToModify, $input->name, $input->description, $input->image, $input->imageRemove);

            return $productToModify;
        } catch (DBNotFoundException $e) {
            throw ProductModifyProductNotFoundException::fromMessage($e->getMessage());
        } catch (DBConnectionException $e) {
            throw ProductModifyProductShopException::fromMessage($e->getMessage());
        } catch (FileException|FileUploadReplaceException $e) {
            throw $e;
        }
    }

    private function createProductShopDto(Product $product, Shop $shop, Money $price, bool $remove): ProductShopDto
    {
        return new ProductShopDto(
            $product,
            $shop,
            $price,
            $remove,
        );
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
            $remove
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
     * @throws ProductModifyShopNotFoundException
     */
    private function getShop(Identifier $shopId, Identifier $groupId): Shop|null
    {
        if ($shopId->isNull()) {
            throw ProductModifyShopNotFoundException::fromMessage('The Shop does not exists');
        }

        try {
            $shopPagination = $this->shopRepository->findShopsOrFail($groupId, [$shopId]);
            $shopPagination->setPagination(1, 1);

            return iterator_to_array($shopPagination)[0];
        } catch (\Throwable) {
            throw ProductModifyShopNotFoundException::fromMessage('The Shop does not exists');
        }
    }
}
