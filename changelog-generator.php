<?php

require_once __DIR__.'/vendor/autoload.php';

use Github\AuthMethod;
use Github\Client;
use Symfony\Component\HttpClient\HttplugClient;

if (count($argv) !== 3) {
    echo 'Usage: '.__FILE__.' {owner} {repo}';
    exit(1);
}

$token = getenv('GITHUB_TOKEN');
$owner = $argv[1];
$repo = $argv[2];

$client = Client::createWithHttpClient(new HttplugClient());

$client->authenticate($token, AuthMethod::ACCESS_TOKEN);

$organizationApi = $client->api('repo')->releases();

$paginator = new Github\ResultPager($client);
$releases = $paginator->fetchAll($organizationApi, 'all', [$owner, $repo]);

$changelog = '# Changelog '.PHP_EOL.PHP_EOL;

foreach ($releases as $release) {

    $created = explode('T', $release['created_at'])[0];

    $changelog .= sprintf('## %s - %s', $release['tag_name'], $created).PHP_EOL.PHP_EOL;

    $changelog .= $release['body'].PHP_EOL.PHP_EOL;
}

file_put_contents('CHANGELOG.md', $changelog);