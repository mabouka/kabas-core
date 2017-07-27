<?php 

namespace Tests\Http;

use Kabas\Http\Route;
use Kabas\Http\UrlWorker;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function generateRoute($data)
    {
        $aggregate = [];
        foreach($data as $lang => $route) {
            $data = new \stdClass;
            $data->directory = 'templates';
            $data->route = $route;
            $data->meta = [];
            $data->title = 'Test page';
            $data->id = 'foo';
            $data->template = 'foo';
            $data->options = null;
            $data->fields = null;
            $data->data = null;
            $aggregate += [$lang => $data];
        }
        return new Route('foo', $aggregate);
    }

    /** @test */
    public function can_be_instanciated_properly()
    {
        $this->assertInstanceOf(Route::class, $this->generateRoute(['en-GB' => '/foo/{bar}']));
    }

    /** @test */
    public function can_check_if_route_matches_another()
    {
        $route = $this->generateRoute(['en-GB' => 'foo/{bar}']);
        $slashRoute = $this->generateRoute(['en-GB' => '/foo/{bar}/']);
        $multilangRoute = $this->generateRoute(['en-GB' => '/test', 'fr-FR' => '/foo/{bar}']);

        $lang = new \stdClass;
        $lang->original = 'en-GB';

        $this->assertFalse($route->matches('/test', $lang));
        $this->assertTrue($route->matches('/foo/bar', $lang));
        $this->assertTrue($route->matches('/foo/bar/', $lang));
        $this->assertTrue($route->matches('foo/bar', $lang));
        $this->assertTrue($slashRoute->matches('/foo/bar', $lang));
        $this->assertTrue($slashRoute->matches('/foo/bar/', $lang));
        $this->assertTrue($slashRoute->matches('foo/bar', $lang));
        $this->assertTrue($multilangRoute->matches('/test', $lang));
    }

    /** @test */
    public function can_return_parameters_for_the_current_route()
    {  
        $route = $this->generateRoute(['en-GB' => '/foo/{bar}']);
        $route->gatherParameters('/foo/hello', 'en-GB');
        $parameters = $route->getParameters();
        $this->assertEquals('hello', $parameters['bar']);
    }

}