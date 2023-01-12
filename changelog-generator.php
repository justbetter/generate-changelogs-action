<?php

require_once __DIR__.'/vendor/autoload.php';

use Github\AuthMethod;
use Github\Client;
use Symfony\Component\HttpClient\HttplugClient;

if (count($argv) !== 2) {
    echo 'Usage: '.__FILE__.' {owner} {repo}';
    exit(1);
}

$token = getenv('GITHUB_TOKEN');
$repository = explode('/', $argv[1]);
$owner = $repository[0];
$repo = $repository[1];

echo "Generating changelog for $owner / $repo" . PHP_EOL;

$client = Client::createWithHttpClient(new HttplugClient());

$client->authenticate($token, AuthMethod::ACCESS_TOKEN);

$repoApi = $client->api('repo')->releases();

$paginator = new Github\ResultPager($client);
$releases = $paginator->fetchAll($repoApi, 'all', [$owner, $repo]);

echo sprintf('Found %s releases', count($releases)) . PHP_EOL;

$changelog = '# Changelog '.PHP_EOL.PHP_EOL;

foreach ($releases as $release) {

    $created = explode('T', $release['created_at'])[0];

    $changelog .= sprintf('## %s - %s', $release['tag_name'], $created).PHP_EOL.PHP_EOL;

    $changelog .= $release['body'].PHP_EOL.PHP_EOL;
}

file_put_contents('CHANGELOG.md', $changelog);