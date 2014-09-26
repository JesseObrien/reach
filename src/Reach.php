<?php namespace Reach;

use Predis\Client;
use Reach\String;

class SearchableNamespaceException extends \RuntimeException {}
class SearchableAttributesException extends \RuntimeException {}

class Reach {

    /**
     * @var Predis\Client
     */
    private $client;

	/**
	 * Create a new instance of Reach
	 * 
	 * @param array $clients
	 * @return void
	 */
	public function __construct(Client $client, Transformer $transformer)
	{
        $this->setClient($client);
        $this->setTransformer($transformer);
	}

    public function setClient(Client $client)
    {
        $this->client = $client;
    }
    
    public function getclient()
    {
        return $this->client;
    }

    public function setTransformer(Transformer $transformer)
    {
        $this->transformer = $transformer;
    }

	/**
	 * Find some ids in the search index and return them
	 *
	 * @param string Namespace to search in
	 * @param string String to search for in the namespace
	 * @return array
	 */
	public function find($namespace, $text)
	{
		$string = $this->transformer->transform($text);
		$namespace = $this->validateNamespace($namespace);
		
		$searchkeys = []; 
		
		foreach($string as $term)
		{
			$searchkeys[] = "searchable:$namespace:$term";
		}

		// SINTER is an AND search
		// SUNION is an OR search
		return empty($searchkeys) ? [] : $this->redis->sinter($searchkeys);
	}

    /**
     * Ensure namespaces take on this:syntax
     *
     * @param string
     * @return bool
     */
    public function validateNamespace($namespace)
    {
        if ( ! preg_match('/\w+(:\w+)+/', $namespace))
        {
            throw new InvalidNamespaceException("Namespace [$namespace] is not a valid namespace. (valid:namespace)");
        }

        return true;
    }

	/**
	 * Search for a query in multiple namespaces
	 *
	 * @param array $namespaces
	 * @param string String to search for in the namespaces
	 * @return array
	 */
	public function findIn(array $namespaces, $string)
	{
		$results = [];
		foreach($namespaces as $index => $namespace)
		{
			$results[$namespace] = $this->find($namespace, $string);
		}

		return $results;
	}

	/**
	 * Add an items searchable fields to the search index
	 *
	 * @param mixed $object
	 * @return $this 
	 */
	public function add(Reachable $object)
	{
		$this->ensureSearchable($object);

		foreach($object->getReachableAttributes() as $attribute)
		{
			if (empty($object->{$attribute}))
			{
				$value = $object->{$attribute};

				$this->insert($object->getReachNamespace(), $value, $object->getReachId());
			}
		}

		# Return $this for monadic win
		return true;
	}
	
	/**
	 * Add an items searchable fields to the search index
	 *
	 * @param Searchable $object
	 * @return $this 
	 */
	public function remove($object)
	{
		$this->ensureSearchable($object);
		foreach($object->searchableAttributes as $field)
		{
			$value = $object->{$field};
			
			if (isset($value) and ! empty($value))
			{
				$this->delete($object->searchableNamespace, $value, $object->id);
			}
		}

		# Return $this for monadic win
		return true;
	}

	/**
	 * Insert a string item in the search index
	 *
	 * @param string $string
	 * @param int $id
	 * @return void
	 */
	protected function insert($namespace, $string, $id)
	{
		$string = $this->string->prepare($string);

		$namespace = $this->string->stripPunctuation($namespace);
		
		foreach ($string as $term)
		{
			// Redis SADD will return the number of elements added to 
			// the set. It's either 1 or 0. 0 does not mean failure,
			// it could mean the element is already part of the set.
			// Because of this behaviour there is no error checking.
			if ( ! empty($term))
			{
				$this->redis->sadd("searchable:$namespace:$term", $id);
			}
		}
	}

	/**
	 * Delete a string from the search index
	 *
	 * @param string $string
	 * @param int $id
	 * @return void
	 */
	protected function delete($namespace, $string, $id)
	{
		$string = $this->string->prepare($string);

		$namespace = $this->string->stripPunctuation($namespace);

		foreach($string as $term)
		{
			$member = false;

			if ( ! empty($term))
			{
				$member = $this->redis->sismember("searchable:$namespace:$term", $id);
			}
			
			if ($member)
			{
				// Redis SREM will return the number of elements deleted from 
				// the set. It's either 1 or 0. 0 does not mean failure it 
				// could mean the element not part of the set. There is 
				// no error checking here because of this behaviour.
				$this->redis->srem("searchable:$namespace:$term", $id); 
			}
		}
	}
	
	/**
	 * Ensure there's a namespace and searchable attributes
	 *
	 * @param object $object
	 * @return void
	 */
	public function ensureSearchable($object)
	{
		if ( ! isset($object->searchableNamespace) or empty($object->searchableNamespace))
		{
			throw new SearchableNamespaceException("You must specify a string searchableNamespace property on the object extending searchable.");
		}

		if ( ! isset($object->searchableAttributes) or ! is_array($object->searchableAttributes))
		{
			throw new SearchableAttributesException("Searchable attributes property must be an array of attribute names (strings).");
		}
	}

}



