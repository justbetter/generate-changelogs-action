#!/bin/sh

echo "GENERATING CHANGELOG"

echo $1
echo $2

gh api /repos/$1/$2/releases > releases

php /changelog-generator.php