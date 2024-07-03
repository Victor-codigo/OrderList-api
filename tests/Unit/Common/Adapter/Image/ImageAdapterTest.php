<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Image;

use Override;
use RuntimeException;
use InvalidArgumentException;
use Common\Adapter\Image\BuiltInFunctionsReturn;
use Common\Adapter\Image\ImagineAdapter;
use Common\Domain\Image\Exception\ImageResizeException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once '/usr/src/tests/BuiltinFunctions/ImageAdapter.php';

class ImageAdapterTest extends TestCase
{
    private const int WIDTH_MAX = 1000;
    private const int HEIGHT_MAX = 1000;

    private ImagineAdapter $object;
    private MockObject|Imagine $imagine;
    private MockObject|ImageInterface $imageInterface;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->imagine = $this->createMock(Imagine::class);
        $this->imageInterface = $this->createMock(ImageInterface::class);
        $this->object = new ImagineAdapter($this->imagine);
    }

    /** @test */
    public function itShouldResizeImageWidthBiggerThanFrame(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->once())
            ->method('open')
            ->with($filePath->getValue())
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('resize')
            ->with($this->callback(function (Box $frame) {
                $this->assertEquals(1000, $frame->getWidth());
                $this->assertEquals(500, $frame->getHeight());

                return true;
            }))
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('save')
            ->with($filePath->getValue());

        BuiltInFunctionsReturn::$getimagesize = [2000, 1000];
        $this->object->resizeToAFrame(
            $filePath,
            self::WIDTH_MAX,
            self::HEIGHT_MAX
        );
    }

    /** @test */
    public function itShouldResizeImageHeightBiggerThanFrame(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->once())
            ->method('open')
            ->with($filePath->getValue())
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('resize')
            ->with($this->callback(function (Box $frame) {
                $this->assertEquals(500, $frame->getWidth());
                $this->assertEquals(1000, $frame->getHeight());

                return true;
            }))
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('save')
            ->with($filePath->getValue());

        BuiltInFunctionsReturn::$getimagesize = [1000, 2000];
        $this->object->resizeToAFrame(
            $filePath,
            self::WIDTH_MAX,
            self::HEIGHT_MAX
        );
    }

    /** @test */
    public function itShouldResizeImageWidthAndHeightBiggerThanFrame(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->once())
            ->method('open')
            ->with($filePath->getValue())
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('resize')
            ->with($this->callback(function (Box $frame) {
                $this->assertEquals(500, $frame->getWidth());
                $this->assertEquals(1000, $frame->getHeight());

                return true;
            }))
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('save')
            ->with($filePath->getValue());

        BuiltInFunctionsReturn::$getimagesize = [2000, 4000];
        $this->object->resizeToAFrame(
            $filePath,
            self::WIDTH_MAX,
            self::HEIGHT_MAX
        );
    }

    /** @test */
    public function itShouldResizeImageWidthAndHeightAreTheSameThanFrame(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->once())
            ->method('open')
            ->with($filePath->getValue())
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('resize')
            ->with($this->callback(function (Box $frame) {
                $this->assertEquals(1000, $frame->getWidth());
                $this->assertEquals(1000, $frame->getHeight());

                return true;
            }))
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('save')
            ->with($filePath->getValue());

        BuiltInFunctionsReturn::$getimagesize = [1000, 1000];
        $this->object->resizeToAFrame(
            $filePath,
            self::WIDTH_MAX,
            self::HEIGHT_MAX
        );
    }

    /** @test */
    public function itShouldResizeImageWidthIsSmallerThanFrame(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->once())
            ->method('open')
            ->with($filePath->getValue())
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('resize')
            ->with($this->callback(function (Box $frame) {
                $this->assertEquals(250, $frame->getWidth());
                $this->assertEquals(1000, $frame->getHeight());

                return true;
            }))
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('save')
            ->with($filePath->getValue());

        BuiltInFunctionsReturn::$getimagesize = [500, 2000];
        $this->object->resizeToAFrame(
            $filePath,
            self::WIDTH_MAX,
            self::HEIGHT_MAX
        );
    }

    /** @test */
    public function itShouldResizeImageHeightIsSmallerThanFrame(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->once())
            ->method('open')
            ->with($filePath->getValue())
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('resize')
            ->with($this->callback(function (Box $frame) {
                $this->assertEquals(1000, $frame->getWidth());
                $this->assertEquals(250, $frame->getHeight());

                return true;
            }))
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('save')
            ->with($filePath->getValue());

        BuiltInFunctionsReturn::$getimagesize = [2000, 500];
        $this->object->resizeToAFrame(
            $filePath,
            self::WIDTH_MAX,
            self::HEIGHT_MAX
        );
    }

    /** @test */
    public function itShouldResizeImageWidthAndHeightSmallerThanFrame(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->once())
            ->method('open')
            ->with($filePath->getValue())
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('resize')
            ->with($this->callback(function (Box $frame) {
                $this->assertEquals(500, $frame->getWidth());
                $this->assertEquals(700, $frame->getHeight());

                return true;
            }))
            ->willReturn($this->imageInterface);

        $this->imageInterface
            ->expects($this->once())
            ->method('save')
            ->with($filePath->getValue());

        BuiltInFunctionsReturn::$getimagesize = [500, 700];
        $this->object->resizeToAFrame(
            $filePath,
            self::WIDTH_MAX,
            self::HEIGHT_MAX
        );
    }

    /** @test */
    public function itShouldFailWidthMaxIsZero(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->never())
            ->method('open');

        $this->imageInterface
            ->expects($this->never())
            ->method('resize');

        $this->imageInterface
            ->expects($this->never())
            ->method('save');

        $this->expectException(ImageResizeException::class);
        $this->object->resizeToAFrame(
            $filePath,
            0,
            self::HEIGHT_MAX
        );
    }

    /** @test */
    public function itShouldFailHeightMaxIsZero(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->never())
            ->method('open');

        $this->imageInterface
            ->expects($this->never())
            ->method('resize');

        $this->imageInterface
            ->expects($this->never())
            ->method('save');

        $this->expectException(ImageResizeException::class);
        $this->object->resizeToAFrame(
            $filePath,
            self::WIDTH_MAX,
            0
        );
    }

    /** @test */
    public function itShouldFailGetImageSizeFails(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->never())
            ->method('open');

        $this->imageInterface
            ->expects($this->never())
            ->method('resize');

        $this->imageInterface
            ->expects($this->never())
            ->method('save');

        BuiltInFunctionsReturn::$getimagesize = false;
        $this->expectException(ImageResizeException::class);
        $this->object->resizeToAFrame(
            $filePath,
            self::WIDTH_MAX,
            self::HEIGHT_MAX
        );
    }

    /** @test */
    public function itShouldFailGetImageSizeReturnsWidthAndHeightAsZero(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->never())
            ->method('open');

        $this->imageInterface
            ->expects($this->never())
            ->method('resize');

        $this->imageInterface
            ->expects($this->never())
            ->method('save');

        BuiltInFunctionsReturn::$getimagesize = [0, 0];
        $this->expectException(ImageResizeException::class);
        $this->object->resizeToAFrame(
            $filePath,
            self::WIDTH_MAX,
            self::HEIGHT_MAX
        );
    }

    /** @test */
    public function itShouldFailOpenException(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->once())
            ->method('open')
            ->with($filePath->getValue())
            ->willThrowException(new RuntimeException());

        $this->imageInterface
            ->expects($this->never())
            ->method('resize');

        $this->imageInterface
            ->expects($this->never())
            ->method('save');

        BuiltInFunctionsReturn::$getimagesize = [500, 700];
        $this->expectException(ImageResizeException::class);
        $this->object->resizeToAFrame(
            $filePath,
            self::WIDTH_MAX,
            self::HEIGHT_MAX
        );
    }

    /** @test */
    public function itShouldFailResizeException(): void
    {
        $filePath = ValueObjectFactory::createPath('file/path');

        $this->imagine
            ->expects($this->once())
            ->method('open')
            ->with($filePath->getValue())
            ->willThrowException(new InvalidArgumentException());

        $this->imageInterface
            ->expects($this->never())
            ->method('resize');

        $this->imageInterface
            ->expects($this->never())
            ->method('save');

        BuiltInFunctionsReturn::$getimagesize = [500, 700];
        $this->expectException(ImageResizeException::class);
        $this->object->resizeToAFrame(
            $filePath,
            self::WIDTH_MAX,
            self::HEIGHT_MAX
        );
    }
}
