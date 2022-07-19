<?php

declare(strict_types=1);

/*
 * (c) Maciej Borkowski <maciej.borkowski@borksoft.pl>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Bs\Notifier\Bridge\Plus\Tests;

use Bs\Notifier\Bridge\Plus\PlusTransport;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Test\TransportTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class PlusTransportTest extends TransportTestCase
{
    public function createTransport(HttpClientInterface $client = null): PlusTransport
    {
        return (new PlusTransport(
            'testlogin',
            'testpwd',
            '00000',
            'cert.pem',
            'testcertpwd',
            $client ?? $this->createMock(HttpClientInterface::class)))->setHost('host.test'
        );
    }

    public function toStringProvider(): iterable
    {
        yield ['plus://testlogin:testpwd@host.test?service_id=00000&cert_file=cert.pem&cert_password=testcertpwd', $this->createTransport()];
    }

    public function supportedMessagesProvider(): iterable
    {
        yield [new SmsMessage('48601357368', 'Hello!')];
    }

    public function unsupportedMessagesProvider(): iterable
    {
        yield [new ChatMessage('Hello!')];
        yield [$this->createMock(MessageInterface::class)];
    }

    public function testSendSuccessfully(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getContent')->willReturn(file_get_contents(__DIR__.'/Fixtures/success-response.txt'));
        $client = new MockHttpClient($response);
        $transport = $this->createTransport($client);
        $sentMessage = $transport->send(new SmsMessage('48601357368', 'Hello!'));

        $this->assertInstanceOf(SentMessage::class, $sentMessage);
        $this->assertSame('123456', $sentMessage->getMessageId());
    }

    /**
     * @dataProvider errorProvider
     */
    public function testExceptionIsThrownWhenSendFailed(int $statusCode, string $content, string $expectedExceptionMessage): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getContent')->willReturn($content);
        $client = new MockHttpClient($response);
        $transport = $this->createTransport($client);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $transport->send(new SmsMessage('48601357368', 'Hello!'));
    }

    public function errorProvider(): iterable
    {
        yield [
            404,
            'Lorem ipsum',
            'Unable to send the SMS: Response status code 404.',
        ];
        yield [
            200,
            file_get_contents(__DIR__.'/Fixtures/failed-response.txt'),
            'Unable to send the SMS: (-21) zbyt długa wiadomość SMS (przekroczono dopuszczalny limit)',
        ];
    }
}