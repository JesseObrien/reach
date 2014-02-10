<?php namespace Reach;

use Illuminate\Redis\Database as Redis;
use Reach\String;

class SearchableNamespaceException extends \RuntimeException {}
class SearchableAttributesException extends \RuntimeException {}

class Reach {


	/**
	 * Create a new instance of Reach
	 * 
	 * @param array $connections
	 * @return void
	 */
	public function __construct(Redis $redis = null, String $string = null, array $connections = [])
	{
		if ( ! is_array($connections) or empty($connections))
		{
			$connections = [
				'default' => [
					'host'     => '127.0.0.1',
					'port'     => 6379,
					'database' => 0, 
				]
			];
		}

		$this->redis = $redis ?: new Redis;
		$this->string = $string ?: new String;
	}

    /**
     * Find some ids in the search index and return them
     *
     * @param string
     * @return array
     */
    public function find($namespace, $string)
    {
        $string = $this->string->prepare($string);
        $namespace = $this->string->stripPunctuation($namespace);
        
        $searchkeys = []; 
        
        foreach($string as $term)
        {
           	$searchkeys[] = "searchable:$namespace:$term";
        }

		// SINTER is an AND search
		// SUNION is an OR search
        return $this->redis->sinter($searchkeys);
    }

    /**
     * Add an items searchable fields to the search index
     *
     * @param mixed $object
     * @return $this 
     */
    public function add($object)
    {
        $this->ensureSearchable($object);

        foreach($object->searchableAttributes as $field)
        {
            if (isset($object->{$field}))
            {
                $this->insert($object->searchableNamespace, $object->{$field}, $object->id);
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
            if (isset($object->{$field}))
            {
                $this->delete($object->searchableNamespace, $object->{$field}, $object->id);
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
           	$this->redis->sadd("searchable:$namespace:$term", $id);
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
            $member = $this->redis->sismember("searchable:$namespace:$term", $id);
            
            if ($member)
            {
				// Redis SREM will return the number of elements deleted from 
				// the set. It's either 1 or 0. 0 does not mean failure it 
				// could mean the element is already part of the set. There is 
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



