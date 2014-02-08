<?php

use Reach\String;

class StringTest extends PHPUnit_Framework_TestCase {

	public function testStringPreparation()
	{
		$string = new String;

		$testString = "This is my-book title. BY Jesse.";

		$actual = $string->prepare($testString);

		$expected = [
			metaphone('this'),
			metaphone('book'),
			metaphone('title'),
			metaphone('jesse')
		];

		$this->assertEquals($actual, $expected);

	}

	public function testStripPunctuation()
	{
		$string = new String;

		$testString = "%^&Hello.,:;=!?+!@#$%^&*(){}|\/`";

		$result = $string->stripPunctuation($testString);
		
		$this->assertEquals($result, "Hello");
	}
}
