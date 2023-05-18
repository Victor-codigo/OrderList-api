<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Response;

use Common\Domain\Response\ResponseDto;
use PHPUnit\Framework\TestCase;

class ResponseDtoTest extends TestCase
{
    private ResponseDto $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new ResponseDto();
    }

    /** @test */
    public function itShouldValidateTheResponse(): void
    {
        $this->object->setErrors([]);
        $this->object->hasContent = true;

        $return = $this->object->validate();

        $this->assertTrue($return);
    }

    /** @test */
    public function itShouldValidateTheResponseNoContent(): void
    {
        $this->object->setErrors([]);
        $this->object->hasContent = false;

        $return = $this->object->validate(false);

        $this->assertTrue($return);
    }

    /** @test */
    public function itShouldFailValidatingTheResponseHasErrors(): void
    {
        $this->object->setErrors(['error']);
        $this->object->hasContent = true;

        $return = $this->object->validate();

        $this->assertFalse($return);
    }

    /** @test */
    public function itShouldFailValidatingTheResponseIsEmpty(): void
    {
        $this->object->setErrors([]);
        $this->object->hasContent = false;

        $return = $this->object->validate();

        $this->assertFalse($return);
    }

    /** @test */
    public function idtShouldCallCallbackOnNoneMultidimensional(): void
    {
        $data = ['id' => 15];
        $this->object->data = $data;

        $callback = function (array $dataResponse) use ($data) {
            static $callCounter = 0;

            $this->assertEquals($data, $dataResponse);
            $this->assertEquals(1, ++$callCounter);

            return $data;
        };

        $return = $this->object->to($callback, false);

        $this->assertEquals($data, $return);
    }

    /** @test */
    public function idtShouldCallCallbackOnMultidimensional(): void
    {
        $data = [
            ['id' => 15],
            ['id' => 16],
            ['id' => 17],
        ];
        $this->object->data = $data;

        $callback = function (array $dataResponse) use ($data) {
            static $callCounter = 0;

            $dataExpected = $data[$callCounter++];
            $this->assertEquals($dataExpected, $dataResponse);

            return $dataExpected;
        };

        $return = $this->object->to($callback, true);

        $this->assertEquals($data, $return);
    }
}
