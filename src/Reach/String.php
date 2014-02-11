<?php namespace Reach;

class String {

	/**
	 * Stop words
	 *
	 * @var array
	 */
	protected $stopWords = ["the", "of", "to", "and", "a", "in", "is", "it", "you", "that"];

	/**
	 * Minimal word length to be included in the index
	 *
	 * @var int
	 */
	protected $minimalWordLength = 3;

	/**
	 * Prepare a string to have it's pieces inserted into the search index
	 *
	 * @param string $term
	 * @return array
	 */
	public function prepare($string)
	{
		$string = strtolower($this->stripPunctuation($string));
		$string = str_replace("-", " ", $string);
		$terms = explode(" ", $string);
		
		$prepared = [];
		foreach($terms as &$t)
		{
			var_dump($t);
			if ( ! $this->isStopWord($t) and strlen($t) >= 3)
			{
				$prepared[] = metaphone($t); 
			}
		}

		return $prepared;
	}

	/**
	 * Determine if the word is a stop word, not to be included in the index
	 *
	 * @param string $string
	 * @return bool
	 */
	public function isStopWord($string)
	{
		return in_array($string, $this->stopWords);
	}

	/**
	 * Strip Puncutation out of the words
	 *
	 * @param string $string
	 * @return string
	 */
	public function stripPunctuation($string)
	{
		return trim(preg_replace('#[^a-zA-Z0-9- ]#', '', $string));
	}



}
