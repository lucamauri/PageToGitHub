<a name="2.1"></a>
# 2.1 (2025-10-09)
## New Features
- **Manual Sync Feature**: Added ability to manually upload pages to GitHub via Special:PageToGitHub
- **Branch Support**: Added configurable target branch for commits (default: 'main')
- **File Extension Config**: Made file extension configurable (default: 'lua')

## Improvements
- **Modular Architecture**: Refactored GitHub API interactions into a dedicated service class for better maintainability
- **Reliability Enhancements**: Implemented retry logic with exponential backoff for GitHub API rate limits
- **Configuration Validation**: Special page now shows validation status and missing settings
- **Code Quality**: Improved error handling, logging, and removed unused code
- **Testing**: Added unit test framework and basic tests

## Bug Fixes
- Fixed P2GKeyword array access bug that could cause fatal errors
- Removed unused P2GCommon.php file
- Added validation for required configuration parameters
- Improved token masking in debug logs

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