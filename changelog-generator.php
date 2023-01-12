<?php

$releaseFile = file_get_contents('./releases');

$json = json_decode($releaseFile, true);

$changelog = '# Changelog '.PHP_EOL.PHP_EOL;

foreach ($json as $release) {

    $created = explode('T', $release['created_at'])[0];

    $changelog .= sprintf('## %s - %s', $release['tag_name'], $created).PHP_EOL;

    $changelog .= $release['body'].PHP_EOL.PHP_EOL;

}

echo $changelog;