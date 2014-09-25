<?php namespace Acme\Library;

class Book implements Reachable {

	public function getReachId()
	{
		return $this->isbn;
	}

	public function getReachNamespace()
	{
		return 'books';
	}

	public function getReacahableAttributes()
	{
		return [
			'title',
			'author',
			'synopsis'
		];
	}

	public function afterSave()
	{
		$reach = new Reach();

		$reach->add($this);
	}

	public function afterUpdate()
	{
		$reach = new Reach();

		$reach->update($this);
	}
}
