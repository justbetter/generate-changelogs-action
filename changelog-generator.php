<?php

require_once __DIR__.'/vendor/autoload.php';

use Github\Api\GitData\Tags;
use Github\Api\Repository\Commits;
use Github\Api\Repository\Releases;
use Github\AuthMethod;
use Github\Client;
use Symfony\Component\HttpClient\HttplugClient;

if (count($argv) < 2) {
    echo 'Usage: '.__FILE__.' {owner}/{repo} {sha?} {output?}';
    exit(1);
}

$token = getenv('GITHUB_TOKEN');
$repository = explode('/', $argv[1]);
$owner = $repository[0];
$repo = $repository[1];
$sha = $argv[2] ?? null;
$outputFile = $argv[3] ?? 'CHANGELOG.md';

echo "Generating changelog for $owner / $repo on branch/commit ".($sha ?? 'default').PHP_EOL;
echo "Output file: $outputFile" . PHP_EOL;

$client = Client::createWithHttpClient(new HttplugClient());

$client->authenticate($token, AuthMethod::ACCESS_TOKEN);

/** @var Releases $repoApi */
$repoApi = $client->api('repo')->releases();

/** @var Commits $commitApi */
$commitApi = $client->api('repo')->commits();

/** @var Tags $tagsApi */
$tagsApi = $client->api('git')->tags();

$repository = $client->api('repo')->show($owner, $repo);

$defaultBranch = $sha ?? $repository['default_branch'];

$paginator = new Github\ResultPager($client);
$releases = $paginator->fetchAll($repoApi, 'all', [$owner, $repo]);

echo sprintf('Found %s releases', count($releases)).PHP_EOL;

if ($sha !== null) {

    $commits = $paginator->fetchAll($commitApi, 'all', [$owner, $repo, ['sha' => urlencode($sha)]]);
    $tags = $paginator->fetchAll($tagsApi, 'all', [$owner, $repo]);

    $hashesToInclude = array_map(fn (array $commit): string => $commit['sha'], $commits);

    $tagsToInclude = array_filter(
        $tags,
        fn (array $tag): bool => in_array($tag['object']['sha'], $hashesToInclude)
    );

    $tagsToInclude = array_map(
        fn (array $tag): string => str_replace('refs/tags/', '', $tag['ref']),
        $tagsToInclude
    );

    $releases = array_filter(
        $releases,
        fn (array $release): bool => in_array($release['tag_name'], $tagsToInclude)
    );

    echo sprintf("%s releases after filtering on $sha", count($releases)).PHP_EOL;
}

$changelog = '# Changelog '.PHP_EOL.PHP_EOL;

if (count($releases) > 0) {
    $lastRelease = $releases[0];

    $lastTag = $lastRelease['tag_name'];

    $unreleasedUrl = "https://github.com/$owner/$repo/compare/$lastTag...$defaultBranch";

    $changelog .= "[Unreleased changes]($unreleasedUrl)".PHP_EOL;
}

foreach ($releases as $release) {
    $created = explode('T', $release['created_at'])[0];

    $changelog .= sprintf('## [%s](%s) - %s', $release['tag_name'], $release['html_url'], $created).PHP_EOL.PHP_EOL;

    $changelog .= deepenHeadings($release['body']).PHP_EOL.PHP_EOL;
}

file_put_contents($outputFile, $changelog);

function deepenHeadings(string $markdown): string
{
    return preg_replace_callback('/^(#+)(.*)$/m', function ($matches): string {
        $level = strlen($matches[1]);

        // Only deepen the heading when the GitHub 'What's Changed' text has been added
        if ($level === 2) {
            $level++;
        }

        return str_repeat('#', $level).$matches[2];
    }, $markdown);
}
