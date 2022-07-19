<?php

declare(strict_types=1);

/*
 * (c) Maciej Borkowski <maciej.borkowski@borksoft.pl>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Bs\Notifier\Bridge\Plus;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

class PlusTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();

        if (!in_array($scheme, $this->getSupportedSchemes())) {
            throw new UnsupportedSchemeException($dsn, $scheme, $this->getSupportedSchemes());
        }

        return (new PlusTransport(
            $this->getUser($dsn),
            $this->getPassword($dsn),
            $dsn->getRequiredOption('service_id'),
            $dsn->getRequiredOption('cert_file'),
            $dsn->getRequiredOption('cert_password'),
            $this->client,
            $this->dispatcher
        ))->setHost($dsn->getHost());
    }

    protected function getSupportedSchemes(): array
    {
        return [PlusTransport::SCHEME];
    }
}