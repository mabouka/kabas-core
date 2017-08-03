<?php 

namespace Tests\Fields\Types;

use Kabas\Fields\Types\File;
use Tests\CreatesApplication;
use PHPUnit\Framework\TestCase;
use Kabas\Exceptions\TypeException;

class FileTest extends TestCase
{

    use CreatesApplication;

    protected $preserveGlobalState = false;
    protected $runTestInSeparateProcess = true;

    public function setUp()
    {
        $this->createApplication([
            'config' => \Kabas\Config\Container::class,
            'router' => \Kabas\Http\Router::class,
            'uploads' => \Kabas\Objects\Uploads\Container::class
        ]);
        $data = new \stdClass;
        $data->label = "File";
        $data->type = "file";
        $this->file = new File('File', null, $data);
        $this->file->set(__DIR__ . '/../../TestTheme/public/index.php');
    }

    /** @test */
    public function can_be_instantiated_properly()
    {
        $this->assertInstanceOf(File::class, $this->file);
    }

    /** @test */
    public function can_set_value_to_null_if_file_not_found()
    {
        $this->file->set('foo.php');
        $this->assertNull($this->file->get());
    }

    /** @test */
    public function can_return_information_about_the_file()
    {
        $this->assertContains('core/tests/TestTheme/public', $this->file->dirname);
        $this->assertSame('index.php', $this->file->basename);
        $this->assertSame('index', $this->file->filename);
        $this->assertSame('php', $this->file->extension);
        $this->assertSame('php', $this->file->extension());
        $this->assertSame(761, $this->file->size);
    }

    /** @test */
    public function returns_false_when_getting_attribute_that_does_not_exist()
    {
        $this->assertFalse($this->file->foo);
    }

}