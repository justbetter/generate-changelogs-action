# GitHub release changelogs

This action will generate a full changelog based on a repositories releases.

## Filtering on branch

It is possible to filter on a branch / commit.
The query parameter `sha` is used for this, see: https://docs.github.com/en/rest/commits/commits

To filter on a branch, add the `sha` parameter:

```yaml
jobs:
  update:
      - name: Generate changelog
        uses: justbetter/generate-changelogs-action@main
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        with:
          repository: ${{ github.repository }}
          sha: ${{ github.head_ref || github.ref_name }}
```

## Example workflow

```yaml
name: "Update Changelog"

on:
  release:
    types: [ published, edited, deleted ]

jobs:
  update:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v3
        with:
          ref: ${{ github.event.release.target_commitish }}

      - name: Generate changelog
        uses: justbetter/generate-changelogs-action@main
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        with:
          repository: ${{ github.repository }}

      - name: Commit CHANGELOG
        uses: stefanzweifel/git-auto-commit-action@v4
        with:
          branch: ${{ github.event.release.target_commitish }}
          commit_message: Update CHANGELOG
          file_pattern: CHANGELOG.md
```