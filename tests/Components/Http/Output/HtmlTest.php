<?php

namespace Parable\Tests\Components\Output;

class HtmlTest extends \Parable\Tests\Base
{
    /** @var \Parable\Http\Output\Json */
    protected $json;

    /** @var \Parable\Http\Output\Html */
    protected $html;

    protected function setUp()
    {
        parent::setUp();

        $this->json = new \Parable\Http\Output\Json();
        $this->html = new \Parable\Http\Output\Html();
    }

    public function testInit()
    {
        $response = \Parable\DI\Container::createAll(\Parable\Http\Response::class);

        // Default is text/html
        $this->assertSame('text/html', $response->getContentType());

        $response->setOutput($this->json);

        // Now it should be application/json
        $this->assertSame('application/json', $response->getContentType());

        $response->setOutput($this->html);

        // Now it should be text/html again
        $this->assertSame('text/html', $response->getContentType());

        $response->setOutput($this->json);

        $this->assertSame('application/json', $response->getContentType());
    }

    public function testPrepare()
    {
        $response = \Parable\DI\Container::createAll(\Parable\Http\Response::class);
        $response->setOutput($this->html);

        $response->setContent("this is content");

        $content = $this->html->prepare($response);

        $this->assertSame("this is content", $content);
    }

    /**
     * @dataProvider dpInvalidContentTypes
     * @param $data
     */
    public function testPrepareThrowsExceptionOnInvalidDataType($data)
    {
        $this->expectException(\Parable\Http\Exception::class);
        $this->expectExceptionMessage("Can only work with string or null content");

        $response = \Parable\DI\Container::createAll(\Parable\Http\Response::class);
        $response->setOutput($this->html);

        $response->setContent($data);

        $this->html->prepare($response);
    }

    public function dpInvalidContentTypes()
    {
        return [
            [[]],
            [new \stdClass()],
            [true],
        ];
    }

    /**
     * @dataProvider dpDataTypes
     */
    public function testAcceptsContentSaysYesStringIntFloatAndNullOnly($type, $expectedBoolValue)
    {
        $this->assertSame($expectedBoolValue, $this->html->acceptsContent($type));
    }

    public function dpDataTypes()
    {
        return [
            [null, true],
            ["string", true],
            [1337, true],
            [13.37, true],
            [true, false],
            [new \stdClass(), false],
            [["array"], false],
        ];
    }
}
