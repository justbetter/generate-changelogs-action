<?php


require_once __DIR__ . '/vendor/autoload.php';

use Github\AuthMethod;
use Github\Client;
use Symfony\Component\HttpClient\HttplugClient;

if (count($argv) !== 3) {
    echo 'Usage: ' . __FILE__ . ' {user} {repo}';
    exit(1);
}

$token = getenv('GITHUB_TOKEN');

$client = Client::createWithHttpClient(new HttplugClient());

$client->authenticate($token,  AuthMethod::ACCESS_TOKEN);

$releases = $client->api('repo')->releases()->all($argv[1], $argv[2]);

$changelog = '# Changelog '.PHP_EOL.PHP_EOL;

foreach ($releases as $release) {

    $created = explode('T', $release['created_at'])[0];

    $changelog .= sprintf('## %s - %s', $release['tag_name'], $created).PHP_EOL.PHP_EOL;

    $changelog .= $release['body'].PHP_EOL.PHP_EOL;
}

echo $changelog;

file_put_contents('CHANGELOG.md', $changelog);