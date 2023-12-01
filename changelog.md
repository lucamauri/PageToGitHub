<a name="2.0"></a>
# 2.0 (2023-12-01)
## Fixes
This version fixes two breaking issues:
* [Hook `PageContentSaveComplete`](https://www.mediawiki.org/wiki/Manual:Hooks/PageContentSaveComplete) is deprecated, so the extension now uses the [`PageSaveComplete`](https://www.mediawiki.org/wiki/Manual:Hooks/PageSaveComplete) hook
* Change of the [Atuhentication methods](https://github.com/KnpLabs/php-github-api/blob/master/doc/security.md) of the [php-github-api](https://github.com/KnpLabs/php-github-api) library

## Improvements
One small improvement is the addition of this `changelog.md` file.

## Code
Explicit cast to `(bool)` for boolean variables.
<!-- CHANGELOG SPLIT MARKER -->