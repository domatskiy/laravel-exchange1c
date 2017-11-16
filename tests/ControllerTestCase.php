<?php

namespace Domatskiy\Tests;
use Illuminate\Http\Request;

abstract class ControllerTestCase extends \PHPUnit_Framework_TestCase
{

    public function call($destination, $parameters = array(), $method = 'GET')
    {
        $old_method = Request::foundation()->getMethod();
        \Laravel\Request::foundation()->setMethod($method);
        $response = Controller::call($destination, $parameters);
        Request::foundation()->setMethod($old_method);

        return $response;
    }

    public function get($destination, $parameters = array())
    {
        return $this->call($destination, $parameters, 'GET');
    }

    public function post($destination, $post_data, $parameters = array())
    {
        $this->clean_request();
        \Laravel\Request::foundation()->request->add($post_data);

        return $this->call($destination, $parameters, 'POST');
    }

    private function clean_request()
    {
        $request = \Laravel\Request::foundation()->request;

        foreach ($request->keys() as $key)
        {
            $request->remove($key);
        }
    }

}