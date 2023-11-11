<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\FileUpload\Exception\FileUploadReplaceException;
use Common\Domain\Model\ValueObject\Object\ShopImage;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\FileUpload\FileUploadInterface;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopModify\Dto\ShopModifyDto;
use Shop\Domain\Service\ShopModify\Exception\ShopModifyNameIsAlreadyInDataBaseException;

class ShopModifyService
{
    public function __construct(
        private ShopRepositoryInterface $shopRepository,
        private FileUploadInterface $fileUpload,
        private string $shopImagePath
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws ShopModifyNameIsAlreadyInDataBaseException
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
    public function __invoke(ShopModifyDto $input): void
    {
        $shopToModify = $this->getShopData($input->shopId, $input->groupId);

        if (!$input->name->isNull()
        && !$shopToModify->getName()->equalTo($input->name)) {
            $this->isValidShopName($input->groupId, $input->name);
            $shopToModify->setName($input->name);
        }

        $input->description->isNull() ?: $shopToModify->setDescription($input->description);
        $this->setShopImage($shopToModify, $input->image, $input->imageRemove);

        $this->shopRepository->save($shopToModify);
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
     * @throws FileUploadReplaceException
     */
    private function setShopImage(Shop $shop, ShopImage $imageNew, bool $imageRemove): void
    {
        $fileUploadedName = $this->shopImageUpload($imageNew, $shop->getImage());
        $fileUploadedName->isNull() ?: $shop->setImage($fileUploadedName);

        if ($imageRemove && $fileUploadedName->isNull()) {
            $this->removeImage($this->shopImagePath, $shop->getImage());
            $shop->setImage(ValueObjectFactory::createPath(null));
        }
    }

    /**
     * @throws DBNotFoundException
     */
    private function getShopData(Identifier $shopId, Identifier $groupId): Shop
    {
        $shopPagination = $this->shopRepository->findShopsOrFail([$shopId], $groupId);
        $shopPagination->setPagination(1, 1);

        return iterator_to_array($shopPagination)[0];
    }

    /**
     * @throws ShopModifyNameIsAlreadyInDataBaseException
     */
    private function isValidShopName(Identifier $groupId, NameWithSpaces $shopName): void
    {
        try {
            $this->shopRepository->findShopsOrFail(null, $groupId, null, $shopName);

            throw ShopModifyNameIsAlreadyInDataBaseException::fromMessage('Shop name is already in data base');
        } catch (DBNotFoundException) {
            return;
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
     * @throws FileUploadReplaceException
     */
    private function shopImageUpload(ShopImage $imageNew, Path $shopCurrentFileName): Path
    {
        if ($imageNew->isNull()) {
            return new Path(null);
        }

        $this->fileUpload->__invoke($imageNew->getValue(), $this->shopImagePath, $shopCurrentFileName->getValue());

        return new Path($this->fileUpload->getFileName());
    }

    /**
     * @throws FileUploadReplaceException
     */
    private function removeImage(string $imagePath, Path $fileName): void
    {
        if ($fileName->isNull()) {
            return;
        }

        $file = "{$imagePath}/{$fileName->getValue()}";

        if (!file_exists($file)) {
            return;
        }

        if (!unlink($file)) {
            throw FileUploadReplaceException::fromMessage(sprintf('File [%s] could not be Replaced', $file));
        }
    }
}
