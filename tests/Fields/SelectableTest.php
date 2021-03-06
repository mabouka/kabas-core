<?php 

namespace Tests\Fields;

use Kabas\Fields\Option;
use Kabas\Fields\Types\Select;
use PHPUnit\Framework\TestCase;
use Kabas\Fields\Types\Checkbox;

class SelectableTest extends TestCase
{

    public function setUp()
    {
        if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
        $data = new \stdClass;
        $data->label = 'Checkbox';
        $data->type = 'checkbox';
        $data->options = ['foo' => 'foo', 'bar' => 'bar'];
        $this->selectable = new Checkbox('Checkbox', null, $data);
        $this->selectable->set('foo');
    }

    /** @test */
    public function can_be_echoed()
    {
        $this->expectOutputString('foo');
        echo $this->selectable;
    }

    /** @test */
    public function can_format_its_value()
    {
        $this->assertTrue(is_array($this->selectable->format(['foo' => 'bar', 'bar' => 'baz'])));
        $this->assertSame('true', $this->selectable->format(true));
        $this->assertSame('5', $this->selectable->format(5));
    }

    /** @test */
    public function can_return_all_options()
    {
        $all = $this->selectable->all();
        $this->assertCount(2, $all);
        $this->assertInstanceOf(Option::class, $all[0]);
        $this->assertSame('foo', $all[0]->label());
    }

    /** @test */
    public function returns_false_when_trying_to_get_undefined_option()
    {
        $this->assertFalse($this->selectable->get('test'));
    }

    /** @test */
    public function can_return_the_labels_of_the_selected_options()
    {
        $this->selectable->set(['foo', 'bar']);
        $this->assertEquals('foo', $this->selectable->labels()[0]);
        $this->assertEquals('bar', $this->selectable->labels()[1]);
    }

    /** @test */
    public function can_return_the_label_of_the_first_selected_option()
    {
        $this->selectable->set(['foo', 'bar']);
        $this->assertSame('foo', $this->selectable->label());
    }

    /** @test */
    public function can_return_the_values_of_the_selected_options()
    {
        $this->selectable->set(['foo', 'bar']);
        $this->assertSame('foo', $this->selectable->keys()[0]);
        $this->assertSame('bar', $this->selectable->keys()[1]);
    }

    /** @test */
    public function can_return_the_key_of_the_first_selected_option()
    {
        $this->selectable->set(['foo', 'bar']);
        $this->assertSame('foo', $this->selectable->key());
    }

    /** @test */
    public function can_be_used_as_an_array()
    {
        foreach($this->selectable as $item) {
            $this->assertInstanceOf(Option::class, $item);
        }
    }

    /** @test */
    public function condition_returns_false_if_no_value()
    {
        $data = new \stdClass;
        $data->label = 'Select';
        $data->type = 'select';
        $data->multiple = false;
        $data->options = ['foo' => 'foo', 'bar' => 'bar'];
        $selectable = new Select('select', null, $data);
        $this->assertFalse($selectable->condition());
    }

    /** @test */
    public function can_set_a_value()
    {
        $data = new \stdClass;
        $data->label = 'Select';
        $data->type = 'select';
        $data->multiple = false;
        $data->options = ['foo' => 'foo', 'bar' => 'bar'];
        $selectable = new Select('select', null, $data);
        $selectable->set('foo');
        $this->assertSame('foo', $selectable->key());
    }

}