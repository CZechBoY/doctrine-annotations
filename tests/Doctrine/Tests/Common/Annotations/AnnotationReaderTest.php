<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedAtClassLevel;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedAtMethodLevel;
use Doctrine\Tests\Common\Annotations\Fixtures\IgnoredNamespaces\AnnotatedAtPropertyLevel;

class AnnotationReaderTest extends AbstractReaderTest
{
    /**
     * @param DocParser|null $parser
     * @return AnnotationReader
     */
    protected function getReader(DocParser $parser = null)
    {
        return new AnnotationReader($parser);
    }

    public function testMethodAnnotationFromTrait()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(Fixtures\ClassUsesTrait::class);

        $annotations = $reader->getMethodAnnotations($ref->getMethod('someMethod'));
        $this->assertInstanceOf(Bar\Autoload::class, $annotations[0]);

        $annotations = $reader->getMethodAnnotations($ref->getMethod('traitMethod'));
        $this->assertInstanceOf(Fixtures\Annotation\Autoload::class, $annotations[0]);
    }

    public function testMethodAnnotationFromOverwrittenTrait()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(Fixtures\ClassOverwritesTrait::class);

        $annotations = $reader->getMethodAnnotations($ref->getMethod('traitMethod'));
        $this->assertInstanceOf(Bar2\Autoload::class, $annotations[0]);
    }

    public function testPropertyAnnotationFromTrait()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(Fixtures\ClassUsesTrait::class);

        $annotations = $reader->getPropertyAnnotations($ref->getProperty('aProperty'));
        $this->assertInstanceOf(Bar\Autoload::class, $annotations[0]);

        $annotations = $reader->getPropertyAnnotations($ref->getProperty('traitProperty'));
        $this->assertInstanceOf(Fixtures\Annotation\Autoload::class, $annotations[0]);
    }

    public function testOmitNotRegisteredAnnotation()
    {
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);

        $reader = $this->getReader($parser);
        $ref = new \ReflectionClass(Fixtures\ClassWithNotRegisteredAnnotationUsed::class);

        $annotations = $reader->getMethodAnnotations($ref->getMethod('methodWithNotRegisteredAnnotation'));
        $this->assertEquals(array(), $annotations);
    }

    /**
     * @group 45
     *
     * @runInSeparateProcess
     */
    public function testClassAnnotationIsIgnored()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(AnnotatedAtClassLevel::class);

        $reader::addGlobalIgnoredNamespace('SomeClassAnnotationNamespace');

        self::assertEmpty($reader->getClassAnnotations($ref));
    }

    /**
     * @group 45
     *
     * @runInSeparateProcess
     */
    public function testMethodAnnotationIsIgnored()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(AnnotatedAtMethodLevel::class);

        $reader::addGlobalIgnoredNamespace('SomeMethodAnnotationNamespace');

        self::assertEmpty($reader->getMethodAnnotations($ref->getMethod('test')));
    }

    /**
     * @group 45
     *
     * @runInSeparateProcess
     */
    public function testPropertyAnnotationIsIgnored()
    {
        $reader = $this->getReader();
        $ref = new \ReflectionClass(AnnotatedAtPropertyLevel::class);

        $reader::addGlobalIgnoredNamespace('SomePropertyAnnotationNamespace');

        self::assertEmpty($reader->getPropertyAnnotations($ref->getProperty('property')));
    }
}
