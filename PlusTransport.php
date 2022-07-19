<?php

declare(strict_types=1);

/*
 * (c) Maciej Borkowski <maciej.borkowski@borksoft.pl>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Bs\Notifier\Bridge\Plus;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PlusTransport extends AbstractTransport
{
    public const SCHEME = 'plus';

    public function __construct(
        private readonly string $login,
        private readonly string $password,
        private readonly string $serviceId,
        private readonly string $certFile,
        private readonly string $certPassword,
        HttpClientInterface $client = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        parent::__construct($client, $dispatcher);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s://%s:%s@%s?service_id=%s&cert_file=%s&cert_password=%s',
            static::SCHEME,
            $this->login,
            $this->password,
            $this->getEndpoint(),
            $this->serviceId,
            $this->certFile,
            $this->certPassword
        );
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof SmsMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, SmsMessage::class, $message);
        }

        $endpoint = sprintf('https://%s/sendsms.aspx', $this->getEndpoint());

        $response = $this->client->request(Request::METHOD_POST, $endpoint, [
            'query' => [
                'login' => $this->login,
                'password' => $this->password,
                'serviceId' => $this->serviceId,
                'delivNotifRequest' => 'true',
                'dest' => $message->getPhone(),
                'text' => $message->getSubject(),
            ],
            'local_cert' => $this->certFile,
            'passphrase' => $this->certPassword,
        ]);

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Could not reach the remote Plus MultiInfo server.', $response, 0, $e);
        }

        $responseContent = preg_split('/\r\n|\r|\n/', $response->getContent(false));

        if (Response::HTTP_OK !== $statusCode) {
            $errorMessage = sprintf('Unable to send the SMS: Response status code %s.', $statusCode);

            throw new TransportException($errorMessage, $response);
        }

        if ('0' !== $responseContent[0]) {
            $errorMessage = sprintf('Unable to send the SMS: (%s) %s', $responseContent[0], $responseContent[1]);

            throw new TransportException($errorMessage, $response);
        }

        $sentMessage = new SentMessage($message, (string) $this);
        $sentMessage->setMessageId($responseContent[1]);

        return $sentMessage;
    }

    protected function getEndpoint(): string
    {
        return $this->host.($this->port ? ':'.$this->port : '');
    }
}