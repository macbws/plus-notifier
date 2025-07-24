<?php

declare(strict_types=1);

/*
 * (c) Maciej Borkowski <maciej.borkowski@borksoft.pl>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Bs\Notifier\Bridge\Plus\Tests;

use Bs\Notifier\Bridge\Plus\PlusTransportFactory;
use Symfony\Component\Notifier\Test\TransportFactoryTestCase;

class PlusTransportFactoryTest extends TransportFactoryTestCase
{
    public function createFactory(): PlusTransportFactory
    {
        return new PlusTransportFactory('project_dir');
    }

    public static function supportsProvider(): iterable
    {
        yield [true, 'plus://api_key@default'];
        yield [false, 'somethingElse://api_key@default'];
    }

    public static function createProvider(): iterable
    {
        yield [
            'plus://testlogin:testpwd@host.test?service_id=00000&cert_file=cert.pem&cert_password=testcertpwd',
            'plus://testlogin:testpwd@host.test?service_id=00000&cert_file=cert.pem&cert_password=testcertpwd',
        ];
        yield [
                'plus://testlogin:testpwd@host.test?service_id=00000&cert_file=project_dir/cert.pem&cert_password=testcertpwd',
                'plus://testlogin:testpwd@host.test?service_id=00000&cert_file=%kernel.project_dir%/cert.pem&cert_password=testcertpwd',
        ];
    }

    public static function unsupportedSchemeProvider(): iterable
    {
        yield ['somethingElse://api_key@default'];
    }

    public static function incompleteDsnProvider(): iterable
    {
        yield [
            'plus://host.test?service_id=00000&cert_file=cert.pem&cert_password=testcertpwd',
            'Invalid "plus://host.test" notifier DSN: User is not set.',
        ];
        yield [
            'plus://testlogin@host.test?service_id=00000&cert_file=cert.pem&cert_password=testcertpwd',
            'Invalid "plus://testlogin@host.test?service_id=00000&cert_file=cert.pem&cert_password=testcertpwd" notifier DSN: Password is not set.',
        ];
    }

    public static function missingRequiredOptionProvider(): iterable
    {
        yield [
            'plus://testlogin:testpwd@host.test?service_id=00000&cert_file=cert.pem',
            'The option "cert_password" is required but missing.',
        ];
        yield [
            'plus://testlogin:testpwd@host.test?service_id=00000&cert_password=testcertpwd',
            'The option "cert_file" is required but missing.',
        ];
        yield [
            'plus://testlogin:testpwd@host.test?cert_file=cert.pem&cert_password=testcertpwd',
            'The option "service_id" is required but missing.',
        ];
    }
}