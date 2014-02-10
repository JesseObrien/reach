reach
=====
[![Build Status](https://api.travis-ci.org/JesseObrien/reach.png)](https://travis-ci.org/JesseObrien/reach)


Redis search layer in PHP.

### Install

Add `"jesseobrien/reach": "dev-master"` to the require section of your composer.json.

### Usage

#### Insert A Book

    # Ensure your object has the searchable variables set.
    class Book {
      protected $searchableAttributes = ['name', 'author'];
      protected $searchableNamespace = 'books';
    }
  
    // Create a new book and save it to the database.
    $b = new Book();
    $b->name = 'The Adventures of Sherlock Holmes';
    $b->author = 'Arthur Conan Doyle';
    $b->save();
  
    // Now that our object has an id from save(), insert it into Reach
    $searchIndex = new Reach();
    $searchIndex->add($b);


#### Find Books

    // Search in books for 'sherlock holmes'
    // Reach will return the ids of books it finds matching 'sherlock' and 'holmes'
    $ids = $searchIndex->find('books', 'sherlock holmes');
  
    $results = [];
    foreach ($ids as $id)
    {
      $result[] = Book::find($id);
    }
  
  
  
