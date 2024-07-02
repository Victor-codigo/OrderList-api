<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Command\HexFileStructure;

use Common\Adapter\Command\HexFilesStructure\Exception\TemplateErrorException;
use Common\Adapter\Command\HexFilesStructure\PLACEHOLDER;
use Common\Adapter\Command\HexFilesStructure\Template;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    private const TEMPLATE_PATH = __DIR__.'/Fixtures/Output/Template/Template/Template.php.template';
    private const TEMPLATE_EXPECTED_PATH = __DIR__.'/Fixtures/Output/Template/Expected/TemplateExpected.php';
    private const TEMPLATE_OUTPUT_PATH = __DIR__.'/Fixtures/Output/Template/Output/TemplateOutput.php';

    private Template $object;
    private array $templatePlaceholder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->templatePlaceholder = [
            PLACEHOLDER::ENDPOINT->value => 'EndpointName',
            PLACEHOLDER::NAMESPACE->value => 'NamespaceName',
            PLACEHOLDER::NAMESPACE_INNER_LAYER->value => 'NamespaceInnerLayerName',
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (file_exists(self::TEMPLATE_OUTPUT_PATH)) {
            unlink(self::TEMPLATE_OUTPUT_PATH);
        }
    }

    /** @test */
    public function itShouldCreateATemplateInDestiny(): void
    {
        $this->object = new Template(self::TEMPLATE_PATH, self::TEMPLATE_OUTPUT_PATH);
        $this->object->createDestiny($this->templatePlaceholder);

        $this->assertFileExists(self::TEMPLATE_OUTPUT_PATH);
        $this->assertFileEquals(self::TEMPLATE_EXPECTED_PATH, self::TEMPLATE_OUTPUT_PATH);
    }

    /** @test */
    public function itShouldFailTemplateNotFound(): void
    {
        $this->expectException(TemplateErrorException::class);

        $this->object = new Template(self::TEMPLATE_PATH.'-', self::TEMPLATE_OUTPUT_PATH);
        $this->object->createDestiny($this->templatePlaceholder);
    }

    /** @test */
    public function itShouldFailTemplateOutputNotFound(): void
    {
        $this->expectException(TemplateErrorException::class);

        $this->object = new Template(self::TEMPLATE_PATH, self::TEMPLATE_OUTPUT_PATH.'/no/existe');
        $this->object->createDestiny($this->templatePlaceholder);
    }
}
