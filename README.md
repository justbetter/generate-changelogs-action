# GitHub release changelogs

This action will generate a full changelog based on a repositories releases.

## Example workflow

```yaml
name: "Update Changelog"

on:
  release:
      types: [ published ]

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
          owner: ${{ github.event.repository.owner.name }}
          repo: ${{ github.event.repository.name }}

      - name: Commit CHANGELOG
        uses: stefanzweifel/git-auto-commit-action@v4
        with:
          branch: ${{ github.event.release.target_commitish }}
          commit_message: Update CHANGELOG
          file_pattern: CHANGELOG.md
```