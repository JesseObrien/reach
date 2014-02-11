<?php

use Mockery as m;
use Reach\Reach;

class ReachTest extends PHPUnit_Framework_TestCase {

	public function setUp()
	{
		$this->reach = new Reach(m::mock('Illuminate\Redis\Database'),  m::mock('Reach\String'));
		$this->testObj = new stdClass;
		$this->testObj->searchableNamespace = "books";
		$this->testObj->searchableAttributes = ["title"];
		$this->testObj->id = 1;
		$this->testObj->title = "The Story Jesse";

	}

	public function tearDown()
	{
		m::close();
	}

	public function testInsertString()
	{
		$redis = m::mock('Illuminate\Redis\Database');

		$redis->shouldReceive('sadd')->times(2);

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

		$this->assertEquals($reach->add($this->testObj), true);
	}

	public function testRemoveString()
	{
		$redis = m::mock('Illuminate\Redis\Database');

		$redis->shouldReceive('srem')->times(2);
		$redis->shouldReceive('sismember')
			->times(2)
			->andReturn( 1, 1);

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

		$this->assertEquals($reach->remove($this->testObj), true);
	}

	public function testFindString()
	{
		$redis = m::mock('Illuminate\Redis\Database');

		$redis->shouldReceive('sinter')
			->andReturn([1]);

		$string = m::mock('Reach\String');

		$string->shouldReceive('prepare')
			->andReturn([
				metaphone('the'),
				metaphone('story')
			]);

		$string->shouldReceive('stripPunctuation')
			->times(1)
			->andReturn('books');

		$reach = new Reach($redis, $string);
		$expected = [1];
		$this->assertEquals($reach->find('books', 'the story'), $expected);
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
