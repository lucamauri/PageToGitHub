# PageToGitHub
[![StyleCI](https://github.styleci.io/repos/238323866/shield?branch=master)](https://github.styleci.io/repos/238323866)
[![Latest Stable Version](https://poser.pugx.org/lucamauri/page-to-github/v/stable)](https://packagist.org/packages/lucamauri/page-to-github)
[![Total Downloads](https://poser.pugx.org/lucamauri/page-to-github/downloads)](https://packagist.org/packages/lucamauri/page-to-github)
[![Latest Unstable Version](https://poser.pugx.org/lucamauri/page-to-github/v/unstable)](https://packagist.org/packages/lucamauri/page-to-github)
[![License](https://poser.pugx.org/lucamauri/page-to-github/license)](https://packagist.org/packages/lucamauri/page-to-github)
[![Monthly Downloads](https://poser.pugx.org/lucamauri/page-to-github/d/monthly)](https://packagist.org/packages/lucamauri/page-to-github)
[![Daily Downloads](https://poser.pugx.org/lucamauri/page-to-github/d/daily)](https://packagist.org/packages/lucamauri/page-to-github)
[![composer.lock](https://poser.pugx.org/lucamauri/page-to-github/composerlock)](https://packagist.org/packages/lucamauri/page-to-github)

MediaWiki extension to automatically transfer code from a MediaWiki wiki to GitHub

## Features

## Requirements

## Install

## Configuration
In the `LocalSettigs.php` file add:

```
$wgP2GNameSpace = 'Module';
$wgP2GAuthToken = 'GitHubToken';
$wgP2GOwner = 'ProjectOrPerson';
$wgP2GRepo = 'Repository';
```
## Troubleshoot
To read detailed logging messages, intercept the [https://www.mediawiki.org/wiki/Manual:$wgDebugLogGroups](log group) named `PageToGitHub`, for instace with the following configuration:

```
$wgShowExceptionDetails = true;
$wgDebugLogGroups['PageToGitHub'] = "/var/log/mediawiki/PageToGitHub-{$wgDBname}.log";
```

## Documentation
## License
[https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html](GNU General Public License, version 2)

## Maintainers
## Contributors
