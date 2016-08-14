<?php

namespace spec\Tgallice\FBMessenger;

use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\RequestOptions;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Tgallice\FBMessenger\Client;
use Tgallice\FBMessenger\Exception\ApiException;
use Tgallice\FBMessenger\Model\Attachment;
use Tgallice\FBMessenger\Model\Attachment\Template;
use Tgallice\FBMessenger\Model\Message;
use Tgallice\FBMessenger\Model\MessageResponse;
use Tgallice\FBMessenger\Model\ThreadSetting;
use Tgallice\FBMessenger\Model\ThreadSetting\MenuItem;
use Tgallice\FBMessenger\Model\ThreadSetting\Postback;
use Tgallice\FBMessenger\Model\ThreadSetting\WebUrl;
use Tgallice\FBMessenger\Model\UserProfile;
use Tgallice\FBMessenger\NotificationType;

class MessengerSpec extends ObjectBehavior
{
    function let(Client $client)
    {
        $this->beConstructedWith($client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Tgallice\FBMessenger\Messenger');
    }

    function it_get_user_profile($client, ResponseInterface $response)
    {
        $response->getBody()->willReturn('
            {
                "first_name": "Peter",
                "last_name": "Chang",
                "profile_pic": "https://fbcdn-profile-a.akamaihd.net/hprofile70ec9c19b18"
            }
        ');

        $query = [
            'fields' => 'first_name,last_name,profile_pic',
        ];

        $client->get('/user_id', $query)->willReturn($response);

        $userProfile = new UserProfile([
            'first_name' => 'Peter',
            'last_name' => 'Chang',
            'profile_pic' => 'https://fbcdn-profile-a.akamaihd.net/hprofile70ec9c19b18'
        ]);

        $this->getUserProfile('user_id', ['first_name', 'last_name', 'profile_pic'])
            ->shouldBeLike($userProfile);
    }

    function it_send_message_to_user($client, Message $message, ResponseInterface $response)
    {
        $message->hasFileToUpload()->willReturn(false);

        $client->send('POST', '/me/messages', null, [], [], [
            RequestOptions::JSON => [
                'recipient' => ['id' =>'1008372609250235'],
                'message' => $message,
                'notification_type' => NotificationType::REGULAR,
            ]
        ])->willReturn($response);

        $response->getBody()->willReturn('
            {
                "recipient_id": "1008372609250235",
                "message_id": "mid.1456970487936:c34767dfe57ee6e339"
            }
        ');

        $this->sendMessage('1008372609250235', $message)->shouldBeLike(
            new MessageResponse('1008372609250235', 'mid.1456970487936:c34767dfe57ee6e339')
        );
    }

    function it_send_text_message_to_user($client, ResponseInterface $response)
    {
        $client->send('POST', '/me/messages', null, [], [], Argument::that(function ($value) {
            $message = $value['json']['message'];

            if (!$message instanceof Message) {
                return false;
            }

            return $message->getType() === 'text' && $message->getData() === 'message';
        }))->willReturn($response);

        $response->getBody()->willReturn('
            {
                "recipient_id": "1008372609250235",
                "message_id": "mid.1456970487936:c34767dfe57ee6e339"
            }
        ');

        $this->sendMessage('1008372609250235', 'message')->shouldBeLike(
            new MessageResponse('1008372609250235', 'mid.1456970487936:c34767dfe57ee6e339')
        );
    }

    function it_send_template_message_to_user($client, Template $template, ResponseInterface $response)
    {
        $client->send('POST', '/me/messages', null, [], [], Argument::that(function ($value) use ($template) {
            $message = $value['json']['message'];

            if (!$message instanceof Message) {
                return false;
            }

            $data = $message->getData();

            if (!$data instanceof Attachment) {
                return false;
            }

            return $data->getType() === 'template' && $data->getPayload() === $template->getWrappedObject();
        }))->willReturn($response);

        $response->getBody()->willReturn('
            {
                "recipient_id": "1008372609250235",
                "message_id": "mid.1456970487936:c34767dfe57ee6e339"
            }
        ');

        $this->sendMessage('1008372609250235', $template)->shouldBeLike(
            new MessageResponse('1008372609250235', 'mid.1456970487936:c34767dfe57ee6e339')
        );
    }

    function it_send_attachment_message_to_user($client, Attachment $attachment, ResponseInterface $response)
    {
        $client->send('POST', '/me/messages', null, [], [], Argument::that(function ($value) use ($attachment) {
            $message = $value['json']['message'];

            if (!$message instanceof Message) {
                return false;
            }

            return $message->getData() === $attachment->getWrappedObject();

        }))->willReturn($response);

        $response->getBody()->willReturn('
            {
                "recipient_id": "1008372609250235",
                "message_id": "mid.1456970487936:c34767dfe57ee6e339"
            }
        ');

        $this->sendMessage('1008372609250235', $attachment)->shouldBeLike(
            new MessageResponse('1008372609250235', 'mid.1456970487936:c34767dfe57ee6e339')
        );
    }

    function it_throw_an_exception_if_try_to_send_bad_message_type()
    {
        $exception = new \InvalidArgumentException('$message should be a string, Message, Attachment or Template');
        $this->shouldThrow($exception)->duringSendMessage('1008372609250235', 1);
    }

    function it_subscribe_the_app($client, ResponseInterface $response)
    {
        $response->getBody()->willReturn('
            {
              "success": true
            }
        ');

        $client->post('/me/subscribed_apps')
            ->willReturn($response);

        $this->subscribe()->shouldReturn(true);
    }

    // Thread settings
    function it_should_define_greeting_text($client)
    {
        $body = [
            'setting_type' => 'greeting',
            'greeting' => [
                'text' => 'my text',
            ],
        ];

        $client->post('/me/thread_settings', json_encode($body))->shouldBeCalled();

        $this->setGreetingText('my text');
    }

    function it_should_define_get_started_button($client)
    {
        $body = [
            'setting_type' => 'call_to_actions',
            'thread_state' => 'new_thread',
            'call_to_actions' => [
                ['payload' => 'my_payload']
            ],
        ];

        $client->post('/me/thread_settings', json_encode($body))->shouldBeCalled();

        $this->setStartedButton('my_payload');
    }

    function it_should_delete_started_button($client)
    {
        $body = [
            'setting_type' => 'call_to_actions',
            'thread_state' => 'new_thread',
        ];

        $client->send('DELETE', '/me/thread_settings', json_encode($body))->shouldBeCalled();

        $this->deleteStartedButton();
    }

    function it_should_define_persistent_menu($client)
    {
        $body = [
            'setting_type' => 'call_to_actions',
            'thread_state' => 'existing_thread',
            'call_to_actions' => [
                [
                    'type' => 'postback',
                    'title' => 'Help',
                    'payload' => 'DEVELOPER_DEFINED_PAYLOAD_FOR_HELP',
                ],
                [
                    'type' => 'postback',
                    'title' => 'Start a New Order',
                    'payload' => 'DEVELOPER_DEFINED_PAYLOAD_FOR_START_ORDER',
                ],
                [
                    'type' => 'web_url',
                    'title' => 'View Website',
                    'url' => 'http://petersapparel.parseapp.com/',
                ],
            ]
        ];

        $client->post('/me/thread_settings', json_encode($body))->shouldBeCalled();

        $this->setPersistentMenu([
            new Postback('Help', 'DEVELOPER_DEFINED_PAYLOAD_FOR_HELP'),
            new Postback('Start a New Order', 'DEVELOPER_DEFINED_PAYLOAD_FOR_START_ORDER'),
            new WebUrl('View Website', 'http://petersapparel.parseapp.com/'),
        ]);
    }

    function it_should_not_add_more_than_5_menu_item(MenuItem $menuItem)
    {
        $exception = new \InvalidArgumentException('You should not set more than 5 menu items.');
        $this->shouldThrow($exception)->duringSetPersistentMenu([
            $menuItem, $menuItem, $menuItem, $menuItem, $menuItem, $menuItem
        ]);
    }

    function it_should_delete_persistent_menu($client)
    {
        $body = [
            'setting_type' => 'call_to_actions',
            'thread_state' => 'existing_thread',
        ];

        $client->send('DELETE', '/me/thread_settings', json_encode($body))->shouldBeCalled();

        $this->deletePersistentMenu();
    }
}
