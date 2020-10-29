<?php

namespace lucenarenato\laraApiMagento\Tests;

use lucenarenato\ModelFields\Tests\User;

class initialTest extends TestCase
{
    /** @test */
    public function test_trait()
    {
        //Get User fields
        $resposta = User::metodoTeste();
        $this->assertSame($resposta, 'tudo ok');
    }
}