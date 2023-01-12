#!/bin/sh -l

cd /
composer install
php /changelog-generator.php $1 $2

mv CHANGELOG.md /github/workspace/CHANGELOG.md