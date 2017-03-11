<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @version $Id: FatalErrorHandlerTest.php 629 2013-11-08 12:34:31Z glenn $
 */

namespace CodeaciousTest\Stdlib;

use Codeacious\Stdlib\FatalErrorHandler;
use PHPUnit_Framework_TestCase as TestCase;

class FatalErrorHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function register()
    {
        $this->assertFalse(FatalErrorHandler::started());
        FatalErrorHandler::start();
        $this->assertTrue(FatalErrorHandler::started());
    }
}
