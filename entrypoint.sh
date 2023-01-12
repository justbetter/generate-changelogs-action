#!/bin/sh -l

pwd
cd /
composer install

php /changelog-generator.php $1 $2