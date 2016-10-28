<?php

namespace spec\Tgallice\FBMessenger\Model\Button;

use PhpSpec\ObjectBehavior;
use Tgallice\FBMessenger\Model\Button;

class PostbackSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('title', 'payload');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Tgallice\FBMessenger\Model\Button\Postback');
    }

    function it_is_a_button()
    {
        $this->shouldImplement(Button::class);
    }

    function it_has_a_type()
    {
        $this->getType()->shouldReturn(Button::TYPE_POSTBACK);
    }

    function it_has_a_title()
    {
        $this->getTitle()->shouldReturn('title');
    }

    function it_has_a_payload()
    {
        $this->getPayload()->shouldReturn('payload');
    }

    function it_should_be_serializable()
    {
        $this->shouldImplement(\JsonSerializable::class);
        $expected = [
            'type' => 'postback',
            'title' => 'title',
            'payload' => 'payload',
        ];

        $this->jsonSerialize()->shouldBeLike($expected);
    }

    function it_throws_exception_when_the_title_exceed_20_characters()
    {
        $this->beConstructedWith(str_repeat('title', 5), 'payload');

        $this
            ->shouldThrow(new \InvalidArgumentException(
                'The button title field should not exceed 20 characters.'
            ))
            ->duringInstantiation();
    }

    function it_throws_exception_when_the_payload_exceed_1000_characters()
    {
        $this->beConstructedWith('title', str_repeat('a', 1001));

        $this
            ->shouldThrow(new \InvalidArgumentException(
                'Payload should not exceed 1000 characters.'
            ))
            ->duringInstantiation();
    }
}
