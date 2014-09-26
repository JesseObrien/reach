<?php

namespace spec\Reach\Transformers;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MetaphoneSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Reach\Transformers\Metaphone');
    }

    function it_should_clean_text()
    {
        $this->clean("Python #$ ^^*isn't my [<>]favourite 99 thing_!?           ")->shouldReturn("python isn't my favourite 99 thing");
    }

    function it_should_aggregate_metaphones()
    {
        $this->aggregateMetaphones("Python isn't my favourite thing")
            ->shouldReturn(["P0N", "ISNT", "FFRT", "0NK"]);
    }
}
