<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Response;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Response\ResponseDto;
use PHPUnit\Framework\TestCase;

class ResponseDtoTest extends TestCase
{
    private ResponseDto $object;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new ResponseDto();
    }

    #[Test]
    public function itShouldValidateTheResponse(): void
    {
        $this->object->setErrors([]);
        $this->object->hasContent = true;

        $return = $this->object->validate();

        $this->assertTrue($return);
    }

    #[Test]
    public function itShouldValidateTheResponseNoContent(): void
    {
        $this->object->setErrors([]);
        $this->object->hasContent = false;

        $return = $this->object->validate(false);

        $this->assertTrue($return);
    }

    #[Test]
    public function itShouldFailValidatingTheResponseHasErrors(): void
    {
        $this->object->setErrors(['error']);
        $this->object->hasContent = true;

        $return = $this->object->validate();

        $this->assertFalse($return);
    }

    #[Test]
    public function itShouldFailValidatingTheResponseIsEmpty(): void
    {
        $this->object->setErrors([]);
        $this->object->hasContent = false;

        $return = $this->object->validate();

        $this->assertFalse($return);
    }

    #[Test]
    public function idtShouldCallCallbackOnNoneMultidimensional(): void
    {
        $data = ['id' => 15];
        $this->object->data = $data;

        $callback = function (array $dataResponse) use ($data): array {
            static $callCounter = 0;

            $this->assertEquals($data, $dataResponse);
            $this->assertEquals(1, ++$callCounter);

            return $data;
        };

        $return = $this->object->to($callback, false);

        $this->assertEquals($data, $return);
    }

    #[Test]
    public function idtShouldCallCallbackOnMultidimensional(): void
    {
        $data = [
            ['id' => 15],
            ['id' => 16],
            ['id' => 17],
        ];
        $this->object->data = $data;

        $callback = function (array $dataResponse) use ($data): array {
            static $callCounter = 0;

            $dataExpected = $data[$callCounter++];
            $this->assertEquals($dataExpected, $dataResponse);

            return $dataExpected;
        };

        $return = $this->object->to($callback, true);

        $this->assertEquals($data, $return);
    }
}
