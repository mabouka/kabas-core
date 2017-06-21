<?php

namespace Tests;

use Kabas\Utils\Meta;
use Tests\CreatesApplication;
use PHPUnit\Framework\TestCase;

class MetaTest extends TestCase
{

    use CreatesApplication;

    protected $preserveGlobalState = false;
    protected $runTestInSeparateProcess = true;

    public function setUp()
    {
        $this->createMinimalContentApplicationForRoute('/foo/bar');
    }

    /** @test */
    public function can_set_and_get_meta_information()
    {
        Meta::set('foo', 'bar');
        $this->assertSame('bar', Meta::get('foo'));
    }

    /** @test */
    public function returns_null_when_getting_undefined_meta_item()
    {
        $this->assertNull(Meta::get('bar'));
    }

    /** @test */
    public function can_return_an_array_containing_all_metadata()
    {
        Meta::set('foo', 'bar');
        Meta::set('bar', 'baz');
        $this->assertCount(2, Meta::all());
        $this->assertSame('bar', Meta::all()['foo']);
        $this->assertSame('baz', Meta::all()['bar']);
    }

    /** @test */
    public function can_output_html_meta_tags_for_the_currently_defined_data()
    {
        $this->expectOutputRegex('/<meta name="foo" content="bar">/');
        Meta::set('foo', 'bar');
        Meta::output();
    }

}