<?php

namespace spec\Tgallice\FBMessenger\Model\Attachment\Template\Generic;

use PhpSpec\ObjectBehavior;
use Tgallice\FBMessenger\Model\Button;

class ElementSpec extends ObjectBehavior
{
    function let(Button $button)
    {
        $this->beConstructedWith('title', 'subtitle', 'image_url', 'item_url', [$button]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Tgallice\FBMessenger\Model\Attachment\Template\Generic\Element');
    }

    function it_has_a_title()
    {
        $this->getTitle()->shouldReturn('title');
    }

    function it_should_return_the_image_url()
    {
        $this->getImageUrl()->shouldReturn('image_url');
    }

    function it_should_return_the_item_url()
    {
        $this->getItemUrl()->shouldReturn('item_url');
    }

    function it_should_return_the_buttons($button)
    {
        $this->getButtons()->shouldReturn([$button]);
    }

    function it_throws_exception_when_the_subtitle_exceed_80_characters()
    {
        $this->beConstructedWith('title', str_repeat('subtitle', 11));
        $this
            ->shouldThrow(new \InvalidArgumentException('In a element, the "subtitle" field should not exceed 80 characters.'))
            ->duringInstantiation();
    }

    function it_throws_exception_when_more_than_3_buttons_is_provided(Button $button)
    {
        $this->beConstructedWith('title', 'subtitle',  null, null, [
            $button,
            $button,
            $button,
            $button,
        ]);

        $this
            ->shouldThrow(new \InvalidArgumentException('A generic element can not have more than 3 buttons.'))
            ->duringInstantiation();
    }

    function it_throws_exception_when_the_title_exceed_80_characters()
    {
        $this->beConstructedWith(str_repeat('title', 20));

        $this
            ->shouldThrow(new \InvalidArgumentException('In a element, the "title" field should not exceed 80 characters.'))
            ->duringInstantiation();
    }

    function it_should_be_serializable(Button $button)
    {
        $this->shouldImplement(\JsonSerializable::class);

        $expected = [
            'title' => 'title',
            'item_url' => 'item_url',
            'image_url' => 'image_url',
            'subtitle' => 'subtitle',
            'buttons' => [$button],
        ];

        $this->jsonSerialize()->shouldBeLike($expected);
    }
}
