# Beacon Wallet API

## Setup

```
git clone git@github.com:gabac/beacon-wallet.git
cd beacon-wallet/beacon-wallet-api
php composer.phar install
cp config.php.dist config.php
cp private_key.pem.dist private_key.pem
mysql < sql/install.sql
```

## Development

```
php -S localhost:8000 -t web/
```

For development you can use the built-in PHP server by running the previous 
command and accessing the API at [http://localhost:8000/](http://localhost:8000/).

## Tests

To run the test suite use the PHPUnit binary from the Composer vendors.

```
vendor/bin/phpunit
```

## Generating keys

```
openssl genrsa -out private_key.pem 2048
openssl req -new -x509 -key private_key.pem -out cert.pem -days 1095
openssl x509 -in cert.pem -outform der -out cert.der
```
