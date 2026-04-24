[![StyleCI](https://github.styleci.io/repos/238323866/shield?branch=master)](https://github.styleci.io/repos/238323866)
[![Latest Stable Version](https://poser.pugx.org/lucamauri/page-to-github/v/stable)](https://packagist.org/packages/lucamauri/page-to-github)
[![Total Downloads](https://poser.pugx.org/lucamauri/page-to-github/downloads)](https://packagist.org/packages/lucamauri/page-to-github)
[![Latest Unstable Version](https://poser.pugx.org/lucamauri/page-to-github/v/unstable)](https://packagist.org/packages/lucamauri/page-to-github)
[![License](https://poser.pugx.org/lucamauri/page-to-github/license)](https://packagist.org/packages/lucamauri/page-to-github)
[![Monthly Downloads](https://poser.pugx.org/lucamauri/page-to-github/d/monthly)](https://packagist.org/packages/lucamauri/page-to-github)
[![Daily Downloads](https://poser.pugx.org/lucamauri/page-to-github/d/daily)](https://packagist.org/packages/lucamauri/page-to-github)
[![composer.lock](https://poser.pugx.org/lucamauri/page-to-github/composerlock)](https://packagist.org/packages/lucamauri/page-to-github)
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/lucamauri/PageToGitHub.svg)](http://isitmaintained.com/project/lucamauri/PageToGitHub "Average time to resolve an issue")
[![Percentage of issues still open](http://isitmaintained.com/badge/open/lucamauri/PageToGitHub.svg)](http://isitmaintained.com/project/lucamauri/PageToGitHub "Percentage of issues still open")

## Badges

[![GPL-2.0-or-later License](https://img.shields.io/badge/License-GPL--2.0--or--later-008033?logo=gpl)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
[![Built with Visual Studio Code](https://img.shields.io/badge/Built_with-VS_Code-007ACC?logo=visualstudiocode)](https://code.visualstudio.com)
[![Discuss on StackOverflow](https://img.shields.io/badge/Discuss_on-Stack_Overflow-fe7a16?logo=stackoverflow)](https://stackoverflow.com/questions/tagged/pagetogithub?tab=Active)

# PageToGitHub

<img src="https://upload.wikimedia.org/wikipedia/commons/9/9e/PageToGitHub.svg" width="256" align="left" />

PageToGitHub (P2G) is a MediaWiki extension that automatically uploads page content to a GitHub repository on every page save. It listens for the `PageSaveComplete` hook and can be scoped to a specific namespace and an optional keyword that must be present in the page body.

It was originally conceived and written by [Luca Mauri](https://github.com/lucamauri) for use in [WikiTrek](https://github.com/WikiTrek) and is released as open source in case it is useful to others.

## Features

- Automatically uploads wikitext content to a GitHub repository on page save
- Configurable namespace filter: only pages in the specified namespace are synced
- Optional keyword filter: only pages containing a specific string are synced
- Optional filename prefix: the keyword can be prepended to the uploaded filename
- Minor edits can be excluded from syncing
- Upload and commit messages use the wiki's i18n system
- Special page (`Special:PageToGitHub`) shows the current configuration

## Requirements

- PHP 8.1 or later
- MediaWiki 1.42 or later
- A GitHub personal access token with repository write permissions
- [Composer](https://getcomposer.org/) for dependency management

## Install

The easiest way to install the extension is using _Composer_: it will automatically resolve and install all dependencies.

Add the following to `composer.local.json` at the root of your MediaWiki installation (create the file if it does not exist):

```json
{
    "require": {
        "lucamauri/page-to-github": "~2.1"
    },
    "extra": {
        "merge-plugin": {
            "include": []
        }
    },
    "config": {}
}
```

Then run Composer from the root of your MediaWiki installation:

```shell
composer install --no-dev
```

Add the following line near the rest of the extension loading calls in `LocalSettings.php`:

```php
wfLoadExtension( 'PageToGitHub' );
```

Then add the configuration parameters described in the _Configuration_ section below.

## Configuration

Add the following to `LocalSettings.php`:

```php
$wgP2GAuthToken    = 'your-github-personal-access-token';
$wgP2GIgnoreMinor  = true;
$wgP2GNameSpace    = 'Module';
$wgP2GOwner        = 'github-username-or-organisation';
$wgP2GRepo         = 'repository-name';
$wgP2GKeyword      = '';      // optional
$wgP2GAddKeyword   = false;   // optional
```

### `$wgP2GAuthToken`

The GitHub personal access token used to authenticate API calls. Generate one in your GitHub account under _Settings_ > _Developer settings_ > _Personal access tokens_. The token must have repository write permissions.

### `$wgP2GIgnoreMinor`

When set to `true` (the default), page saves flagged as minor edits are not synced to GitHub.

### `$wgP2GNameSpace`

Only pages belonging to this namespace are synced. Set to the namespace label as a string, e.g. `'Module'`.

### `$wgP2GOwner`

The GitHub username or organisation that owns the target repository.

### `$wgP2GRepo`

The name of the GitHub repository where files are uploaded.

### `$wgP2GKeyword`

An optional keyword string. When set, only pages whose content contains this string are synced. Leave empty (the default) to sync all pages in the configured namespace.

### `$wgP2GAddKeyword`

When set to `true` and `$wgP2GKeyword` is non-empty, the keyword is prepended to the uploaded filename, e.g. a page named `Foo` with keyword `bar` is uploaded as `bar-Foo.lua`. Defaults to `false`.

## Troubleshooting

To read detailed log messages, intercept the [log group](https://www.mediawiki.org/wiki/Manual:$wgDebugLogGroups) named `PageToGitHub` by adding the following to `LocalSettings.php`:

```php
$wgShowExceptionDetails = true;
$wgDebugLogGroups['PageToGitHub'] = "/var/log/mediawiki/PageToGitHub-{$wgDBname}.log";
```

## Changelog

See the [GitHub releases page](https://github.com/lucamauri/PageToGitHub/releases) for the full changelog.

## License

This extension is released under the [GNU General Public License 2.0 or later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html).

## Maintainers

[Luca Mauri](https://github.com/lucamauri)

## Contributors

[Luca Mauri](https://github.com/lucamauri)