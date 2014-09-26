<?php namespace Reach\Transformers;

use Reach\Transformer;

class Metaphone implements Transformer {

	/**
	 * Stop words
	 *
	 * @var array
	 */
	protected $stopWords = ["the", "of", "to", "and", "a", "in", "is", "it", "you", "that"];

	/**
	 * Minimum length for a word to be included in the index
	 *
	 * @var int
	 */
	protected $minimumWordLength = 3;

    /**
     * Transform text and return an array of storable indexes
     *
     * @param string $text
     * @return array
     */
    public function transform($text)
    {
        $this->clean($text);

        return $this->aggregateMetaphones($text);
    }

    /**
     * Clean any puntuation and spacing from text
     *
     * @param string $text
     * @return string
     */
    public function clean($text)
    {
        $text = strtolower($this->stripPunctuation($text));
        $text = trim($text);
		$text = str_replace("-", " ", $text);

        return $text;
    }

    /**
     * Aggregate a string of words into metaphones
     *
     * @param string
     * @return array
     */
    public function aggregateMetaphones($text)
    {
		$terms = explode(" ", $text);

       	$metaphones = [];

		foreach($terms as &$t)
		{
			if ( ! $this->isStopWord($t) and strlen($t) >= $this->minimumWordLength)
			{
				$metaphones[] = metaphone($t); 
			}
		}

		return $metaphones;
    }

	/**
	 * Determine if the word is a stop word, not to be included in the index
	 *
	 * @param string $string
	 * @return bool
	 */
	protected function isStopWord($string)
	{
		return in_array($string, $this->stopWords);
	}

    /**
     * Strip punctuation from the text
     */
    protected function stripPunctuation(&$text)
    {
		return preg_replace("/[^a-z\d']+/i", ' ', $text);
    }

}
