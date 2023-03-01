<?php

require_once __DIR__.'/vendor/autoload.php';

use Github\AuthMethod;
use Github\Client;
use Symfony\Component\HttpClient\HttplugClient;

if (count($argv) !== 2) {
    echo 'Usage: '.__FILE__.' {owner}/{repo}';
    exit(1);
}

$token = getenv('GITHUB_TOKEN');
$repository = explode('/', $argv[1]);
$owner = $repository[0];
$repo = $repository[1];

echo "Generating changelog for $owner / $repo".PHP_EOL;

$client = Client::createWithHttpClient(new HttplugClient());

$client->authenticate($token, AuthMethod::ACCESS_TOKEN);

$repoApi = $client->api('repo')->releases();

$repository = $client->api('repo')->show($owner, $repo);

$defaultBranch = $repository['default_branch'];

$paginator = new Github\ResultPager($client);
$releases = $paginator->fetchAll($repoApi, 'all', [$owner, $repo]);

echo sprintf('Found %s releases', count($releases)).PHP_EOL;

$changelog = '# Changelog '.PHP_EOL.PHP_EOL;

if (count($releases) > 0) {
    $lastRelease = $releases[0];

    $lastTag = $lastRelease['tag_name'];

    $unreleasedUrl = "https://github.com/$owner/$repo/compare/$lastTag...$defaultBranch";

    $changelog .= "[Unreleased changes]($unreleasedUrl)" . PHP_EOL;
}

foreach ($releases as $release) {

    $created = explode('T', $release['created_at'])[0];

    $changelog .= sprintf('## [%s](%s) - %s', $release['tag_name'], $release['html_url'], $created).PHP_EOL.PHP_EOL;

    $changelog .= deepenHeadings($release['body']).PHP_EOL.PHP_EOL;
}

file_put_contents('CHANGELOG.md', $changelog);


function deepenHeadings(string $markdown): string
{
    return preg_replace_callback('/^(#+)(.*)$/m', function ($matches): string {
        $level = strlen($matches[1]);
        return str_repeat('#', $level + 1).$matches[2];
    }, $markdown);
}
