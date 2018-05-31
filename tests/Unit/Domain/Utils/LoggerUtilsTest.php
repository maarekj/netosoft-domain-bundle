<?php

namespace Tests\Unit\Netosoft\DomainBundle\Domain\Utils;

use Netosoft\DomainBundle\Domain\Logger\ExpressionLanguageProvider;
use Netosoft\DomainBundle\Domain\Utils\LoggerUtils;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tests\Unit\Netosoft\DomainBundle\Domain\Utils\Fixtures\ObjectWithDoubleNested;
use Tests\Unit\Netosoft\DomainBundle\Domain\Utils\Fixtures\ObjectWithError;
use Tests\Unit\Netosoft\DomainBundle\Domain\Utils\Fixtures\ObjectWithNested;
use Tests\Unit\Netosoft\DomainBundle\Domain\Utils\Fixtures\SimpleObject;

class LoggerUtilsTest extends \PHPUnit_Framework_TestCase
{
    /** @var LoggerUtils */
    protected $logger;

    /** @var AnnotationReader */
    private $annotationReader;

    public function setUp()
    {
        $this->annotationReader = new AnnotationReader();
        $this->logger = new LoggerUtils(new ArrayAdapter(), $this->annotationReader, new ExpressionLanguageProvider());
    }

    /**
     * @dataProvider provideLogCommand
     */
    public function testLogCommand($command, $expected)
    {
        $result = $this->logger->logCommand($command);

        $this->assertEquals($expected, $result);
    }

    public function provideLogCommand()
    {
        yield [
            new SimpleObject('field1', 'field2'), [
                'field1' => 'field1',
                'field2' => 'field2',
            ],
        ];

        yield [
            new SimpleObject(null, 'field2'), [
                'field1' => null,
                'field2' => 'field2',
            ],
        ];

        yield [
            new ObjectWithNested(new SimpleObject('simple field1', 'simple field2'), 'field1', 'field2'), [
                'field1' => 'field1',
                'field2' => 'field2',
                'simpleObject' => [
                    'field1' => 'simple field1',
                    'field2' => 'simple field2',
                ],
            ],
        ];

        yield [
            new ObjectWithDoubleNested(
                new ObjectWithNested(
                    new SimpleObject('simple field1', 'simple field2'),
                    'nested field1', 'nested field2'
                ),
                'field1', 'field2'
            ), [
                '__command_message__' => 'object_with_double_nested',
                'field1' => 'field1',
                'field2' => 'field2',
                'object' => [
                    'field1' => 'nested field1',
                    'simpleObject.field1' => 'simple field1',
                ],
            ],
        ];

        yield [
            new ObjectWithDoubleNested(
                new ObjectWithNested(
                    null,
                    'nested field1', 'nested field2'
                ),
                'field1', 'field2'
            ), [
                '__command_message__' => 'object_with_double_nested',
                'field1' => 'field1',
                'field2' => 'field2',
                'object' => [
                    'field1' => 'nested field1',
                    'simpleObject.field1' => null,
                ],
            ],
        ];

        yield [
            new ObjectWithDoubleNested(
                null,
                'field1', 'field2'
            ), [
                '__command_message__' => 'object_with_double_nested',
                'field1' => 'field1',
                'field2' => 'field2',
                'object' => null,
            ],
        ];

        yield [
            new ObjectWithError(
                new ObjectWithNested(null, 'nested field1', 'nested field2'),
                'field1', 'field2'
            ), [
                '__command_message__' => 'object_with_error',
                'field1' => 'field1',
                'field2' => 'field2',
                'object' => [
                    'field1' => 'nested field1',
                    'erroronpath.field1' => null,
                ],
            ],
        ];
    }
}
