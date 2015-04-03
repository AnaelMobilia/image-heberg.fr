<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of imageObjectTest
 *
 * @author Anael
 */
class imageObjectTest extends PHPUnit_Framework_TestCase {

	    public function testPushAndPop()
    {
        $stack = array();
        $this->assertEquals(1, count($stack));

        array_push($stack, 'foo');
        $this->assertEquals('foo', $stack[count($stack)-1]);
        $this->assertEquals(1, count($stack));

        $this->assertEquals('foo', array_pop($stack));
        $this->assertEquals(0, count($stack));
    }
	
}
