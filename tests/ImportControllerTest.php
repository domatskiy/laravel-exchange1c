<?php
namespace Domatskiy\Tests;

class ImportControllerTest extends ControllerTestCase
{

    public function test()
    {
        $response = $this->post('account@signup', array(
            'login' => 'admin',
            'password' => 'admin',
            ));

        $this->assertEquals('302', $response->foundation->getStatusCode());
        $session_errors = \Laravel\Session::instance()->get('errors');
        $this->assertNull($session_errors);
    }


}
