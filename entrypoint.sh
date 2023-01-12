#!/bin/sh -l

composer install

php /changelog-generator.php $1 $2