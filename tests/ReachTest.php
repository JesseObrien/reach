<?php

use Mockery as m;
use Reach\Reach;

class ReachTest extends PHPUnit_Framework_TestCase {

	public function setUp()
	{
		$this->reach = new Reach(m::mock('Illuminate\Redis\Database'),  m::mock('Reach\String'));
	}

	public function tearDown()
	{
		m::close();
	}

	public function testInsertString()
	{
		$testObj = new stdClass;
		$testObj->searchableAttributes = ["title"];
		$testObj->searchableNamespace = "books";
		$testObj->id = 1;
		$testObj->title = "The Story Jesse";

		$redis = m::mock('Illuminate\Redis\Database');

		$redis->shouldReceive('sadd')->times(3);

		$string = m::mock('Reach\String');
		
		$string->shouldReceive('prepare')
			->times(1)
			->andReturn([
			metaphone('the'), 
			metaphone('story'),
			metaphone('jesse')
		]);

		$string->shouldReceive('stripPunctuation')
			->times(1)
			->andReturn('books');

		$reach = new Reach($redis, $string);

		$this->assertEquals($reach->add($testObj), true);
	}

	public function testEnsureSearchableNoNamespaceFailure()
	{
		$this->setExpectedException('Reach\SearchableNamespaceException');
		$obj = new stdClass;
		$this->reach->ensureSearchable($obj);
	}

	public function testEnsureSearchableNoAttributesFailure() 
	{
		$this->setExpectedException('Reach\SearchableAttributesException');
		$obj = new stdClass;
		$obj->searchableNamespace = 'test';
		$this->reach->ensureSearchable($obj);
	}
}
