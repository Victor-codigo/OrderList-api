<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopCreate;

use Common\Domain\Config\AppConfig;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Object\ShopImage;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Common\Domain\Ports\Image\ImageInterface;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopCreate\Dto\ShopCreateDto;
use Shop\Domain\Service\ShopCreate\Exception\ShopCreateNameAlreadyExistsException;

class ShopCreateService
{
    public function __construct(
        private ShopRepositoryInterface $shopRepository,
        private FileUploadInterface $fileUpload,
        private ImageInterface $image,
        private string $shopImagePath
    ) {
    }

    /**
     * @throws ShopCreateNameAlreadyExistsException
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
    public function __invoke(ShopCreateDto $input): Shop
    {
        try {
            $this->shopRepository->findShopsByGroupAndNameOrFail($input->groupId, $input->name);

            throw ShopCreateNameAlreadyExistsException::fromMessage('Shop name already exists');
        } catch (DBNotFoundException) {
            $shop = $this->createShop($input->groupId, $input->name, $input->description, $input->image);
            $this->shopRepository->save($shop);

            return $shop;
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
    private function createShop(Identifier $groupId, NameWithSpaces $name, Description $description, ShopImage $image): Shop
    {
        $shopId = ValueObjectFactory::createIdentifier($this->shopRepository->generateId());

        return new Shop(
            $shopId,
            $groupId,
            $name,
            $description,
            $this->shopImageUpload($image)
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
    private function shopImageUpload(ShopImage $image): Path
    {
        if ($image->isNull()) {
            return new Path(null);
        }

        $this->fileUpload->__invoke($image->getValue(), $this->shopImagePath);

        $this->image->resizeToAFrame(
            ValueObjectFactory::createPath("{$this->shopImagePath}/{$this->fileUpload->getFileName()}"),
            AppConfig::SHOP_IMAGE_FRAME_SIZE_WIDTH,
            AppConfig::SHOP_IMAGE_FRAME_SIZE_HEIGHT
        );

        return new Path($this->fileUpload->getFileName());
    }
}
