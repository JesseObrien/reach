<?php

namespace spec\Reach;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Predis\Client;
use Reach\Transformer;

class ReachSpec extends ObjectBehavior
{
    function let(Client $client, Transformer $transformer)
    {
        $this->beConstructedWith($client, $transformer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Reach\Reach');
    }

    function it_should_have_a_predis_client_instance()
    {
        $this->getclient()->shouldBeAnInstanceOf('Predis\Client');
    }

    function it_should_validate_namespace()
    {
        $this->validateNamespace('name:space')->shouldReturn(true);
    }

    function it_should_not_allow_dot_namespace()
    {
        $this->shouldThrow('Reach\InvalidNamespaceException')->duringValidateNamespace('hello.namespace');
    }

    function it_should_not_allow_garbage_namespace()
    {
        $this->shouldThrow('Reach\InvalidNamespaceException')->duringValidateNamespace('s3$@#__$@#$%./as/df');
    }

}
