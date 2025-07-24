# Plus MultiInfo Notifier

Provides [Plus MultiInfo](https://www.multiinfo.plus.pl) integration for Symfony Notifier.

## DSN example

```yaml
PLUS_DSN=plus://LOGIN:PASSWORD@HOST?service_id=SERVICE_ID&cert_file=CERT_FILE&cert_password=CERT_PASSWORD
```

where:

- `LOGIN` is user login with access to API HTTPS channel
- `PASSWORD` is user password
- `HOST` is API host (`api1.multiinfo.plus.pl` or `api2.multiinfo.plus.pl`)
- `SERVICE_ID` is service identity for sending message
- `CERT_FILE` is file name with path to certificate file (PEM format)
- `CERT_PASSWORD` is certificate password

All special chars must be URL encoding prefixed by `%` (e.g. `#` is `%%23`)

example:

```yaml
PLUS_DSN=plus://apiuser:PaSwOrD%%231@api1.multiinfo.plus.pl?service_id=00000&cert_file=%kernel.project_dir%/cert.pem&cert_password=PaSwOrD%%232
```

## Configuring transport

To make the transport visible in symfony/notifier, you need to add the following configuration to your Symfony application:

```yaml
# config/services.yaml

Bs\Notifier\Bridge\Plus\PlusTransportFactory:
    tags: ['notifier.transport_factory']
    arguments:
        $projectDir: '%kernel.project_dir%'

Symfony\Component\Notifier\Transport:
    arguments:
        $factories: !tagged_iterator notifier.transport_factory
```