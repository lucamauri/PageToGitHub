# PageToGitHub
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

PageToGitHub, P2G in short, is a MediaWiki extension to automatically transfer code from a MediaWiki wiki to GitHub.
It was originally conceived and written by [Luca Mauri](https://github.com/lucamauri) for use in [Wikitrek](https://github.com/WikiTrek): it is released as open source here in case it can be useful to anybody else.

## Features

## Requirements

## Install
First of all, add the composer configuration to the `composer.local.json` at the root of your mediawiki installation, or create the file if it does not exist yet:
```
{
  "extra": {
    "merge-plugin": {
      "include": [
        "extensions/PageToGitHub/composer.json"
      ]
    }
  }
}
```

and run Composer in a console from the root of your mediawiki installation: using just one of these methods:
```
composer install --no-dev
```


## Configuration
In the `LocalSettigs.php` file add:

```
$wgP2GNameSpace = 'Module';
$wgP2GRepo = 'Repository';
$wgP2GAuthToken = 'GitHubToken';
$wgP2GOwner = 'ProjectOrPerson';
$wgP2GKeyword = 'Keyword';
```
### $wgP2GNameSpace
P2G will upload pages only belonging to the namespace spedified in this variable
### $wgP2GRepo
The name of the repository where the codes must be uploaded
### $wgP2GAuthToken
The GitHub token needed to authenticate and made modification the the repository. You can generate one in your GitHub account in *Settings* > *Developer settings* > *Personal access tokens*
### $wgP2GOwner
The Person or Organization owner of the repository
### $wgP2GKeyword
An optional keyword to check into the page. When present, P2G will *not* upload pages if the keyword is not written in the page. If the parameter is omitted, P2G will upload all pages in the Namespace specified above.
 
## Troubleshoot
To read detailed logging messages, you can intercept the [log group](https://www.mediawiki.org/wiki/Manual:$wgDebugLogGroups) named `PageToGitHub`: for instace with the following configuration into `LocalSetting.php`:

```
$wgShowExceptionDetails = true;
$wgDebugLogGroups['PageToGitHub'] = "/var/log/mediawiki/PageToGitHub-{$wgDBname}.log";
```

## Documentation
## License
[GNU General Public License, version 2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)

## Maintainers
[Luca Mauri](https://github.com/lucamauri)

## Contributors
[Luca Mauri](https://github.com/lucamauri)
