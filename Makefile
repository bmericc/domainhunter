.PHONY: phar install

install:
	composer install

phar:
	php -d phar.readonly=0 bin/build.php
