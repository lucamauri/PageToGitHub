<?php
/**
 * PageToGitHub — MediaWiki extension that uploads page content to a GitHub
 * repository as a file on every page save.
 *
 * @file
 * @license GPL-2.0-or-later
 * @author Luca Mauri
 */

use MediaWiki\Content\TextContent;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Revision\SlotRecord;

class PageToGitHubHooks {

    /** @var ConfigFactory */
    private ConfigFactory $configFactory;

    /**
     * Constructor — receives dependencies via MediaWiki's service container.
     * Wired through the HookHandlers block in extension.json.
     *
     * @param ConfigFactory $configFactory Factory used to retrieve extension config
     */
    public function __construct( ConfigFactory $configFactory ) {
        $this->configFactory = $configFactory;
    }

    /**
     * Triggered after a page has been saved.
     * Checks namespace and optional keyword filter, then uploads the page
     * content to GitHub via writeToGithub().
     *
     * @param WikiPage $wikiPage The page that was saved
     * @param MediaWiki\User\UserIdentity $user The user who saved the page
     * @param string $summary The edit summary
     * @param int $flags Edit flags (e.g. EDIT_MINOR)
     * @param MediaWiki\Revision\RevisionRecord $revisionRecord The new revision
     * @param MediaWiki\Storage\EditResult $editResult Result of the edit
     *
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageSaveComplete
     */
    public function onPageSaveComplete(
        WikiPage $wikiPage,
        MediaWiki\User\UserIdentity $user,
        string $summary,
        int $flags,
        MediaWiki\Revision\RevisionRecord $revisionRecord,
        MediaWiki\Storage\EditResult $editResult
    ): void {
        $config = $this->configFactory->makeConfig( 'PageToGitHub' );

        $P2GNameSpace   = $config->get( 'P2GNameSpace' );
        $P2GKeyword     = $config->get( 'P2GKeyword' );
        $P2GIgnoreMinor = (bool)$config->get( 'P2GIgnoreMinor' );

        if ( $P2GNameSpace === null ) {
            wfDebugLog( 'PageToGitHub', '[PageToGitHub]WARNING: P2GNameSpace is not configured — no pages will be synced' );
            return;
        }

        wfDebugLog( 'PageToGitHub', '[PageToGitHub]||| Entered |||' );

        $pageNameSpace = $wikiPage->getTitle()->getNsText();
        $pageTitle     = $wikiPage->getTitle()->getRootText();

        // Use RevisionRecord instead of deprecated WikiPage::getContent()
        $content = $revisionRecord->getContent( SlotRecord::MAIN );
        if ( !( $content instanceof TextContent ) ) {
            wfDebugLog( 'PageToGitHub', '[PageToGitHub]Skipping: content is not text' );
            return;
        }
        $pageContent = $content->getText();

        // Use $flags instead of deprecated WikiPage::getMinorEdit()
        $isMinor = (bool)( $flags & EDIT_MINOR );

        wfDebugLog( 'PageToGitHub', '[PageToGitHub]Summary: ' . $summary );
        wfDebugLog( 'PageToGitHub', '[PageToGitHub]Keyword: ' . $P2GKeyword );
        wfDebugLog( 'PageToGitHub', '[PageToGitHub]Is minor: ' . $isMinor );

        if ( $P2GIgnoreMinor && $isMinor ) {
            wfDebugLog( 'PageToGitHub', '[PageToGitHub]IGNORING Minor' );
            return;
        }

        wfDebugLog( 'PageToGitHub', '[PageToGitHub]NOT ignoring Minor' );

        if ( $pageNameSpace !== $P2GNameSpace ) {
            wfDebugLog( 'PageToGitHub', '[PageToGitHub]Namespace KO' );
            return;
        }

        wfDebugLog( 'PageToGitHub', '[PageToGitHub]Namespace OK' );
        wfDebugLog( 'PageToGitHub', '[PageToGitHub]Keyword: ' . $P2GKeyword );

        // empty() handles both null and "" safely; !== false is the correct strpos check
        if ( !empty( $P2GKeyword ) && strpos( $pageContent, $P2GKeyword ) === false ) {
            wfDebugLog( 'PageToGitHub', '[PageToGitHub]Keyword KO' );
            return;
        }

        wfDebugLog( 'PageToGitHub', '[PageToGitHub]Keyword OK' );
        $return = $this->writeToGithub( $pageTitle, $pageContent, $summary, $config );
        wfDebugLog( 'PageToGitHub', '[PageToGitHub]Returned: ' . $return );
    }

    /**
     * Uploads or updates a file in the configured GitHub repository.
     * Creates the file if it does not exist, updates it if it does.
     *
     * @param string $pageName The title of the wiki page (without namespace)
     * @param string $pageContent The wikitext content of the page
     * @param string|null $description The edit summary, used as the commit message
     * @param \Config $extConfig The extension's Config object
     *
     * @return bool True on success, false on failure
     */
    private function writeToGithub(
        string $pageName,
        string $pageContent,
        ?string $description,
        \Config $extConfig
    ): bool {
        try {
            wfDebugLog( 'PageToGitHub', '[PageToGitHub]Function writeToGithub' );

            $P2GAuthToken  = $extConfig->get( 'P2GAuthToken' );
            $P2GOwner      = $extConfig->get( 'P2GOwner' );
            $P2GRepo       = $extConfig->get( 'P2GRepo' );
            $P2GNameSpace  = $extConfig->get( 'P2GNameSpace' );
            $P2GKeyword    = $extConfig->get( 'P2GKeyword' );
            $P2GAddKeyword = $extConfig->get( 'P2GAddKeyword' );

            wfDebugLog( 'PageToGitHub', '[PageToGitHub]Token: ' . $P2GAuthToken );

            $client = new \Github\Client();
            wfDebugLog( 'PageToGitHub', '[PageToGitHub]Init done' );

            // https://github.com/KnpLabs/php-github-api/blob/master/doc/security.md
            $client->authenticate( $P2GAuthToken, '', \Github\AuthMethod::ACCESS_TOKEN );

            // Prefix filename with keyword if configured to do so
            if ( !empty( $P2GKeyword ) && $P2GAddKeyword === true ) {
                wfDebugLog( 'PageToGitHub', '[PageToGitHub]Keyword OK and Add OK' );
                $pageName = $P2GKeyword . '-' . $pageName;
            } else {
                wfDebugLog( 'PageToGitHub', '[PageToGitHub]No Keyword' );
            }

            $fileParamArray = [ $P2GOwner, $P2GRepo, $pageName . '.lua' ];

            // Build file header using i18n messages
            $headerUpload = wfMessage( 'auto-upload' )->inContentLanguage()->plain();
            $headerPage   = wfMessage( 'code-from-page' )->inContentLanguage()->plain();
            $fileContent  = '-- ' . $headerUpload . ' ' . date( 'c' ) . PHP_EOL
                          . '-- ' . $headerPage . ' ' . $P2GNameSpace . ':' . $pageName . PHP_EOL
                          . $pageContent;

            // Use edit summary as commit message, fall back to default
            if ( $description === null || $description === '' ) {
                $commitText = wfMessage( 'auto-commit' )->inContentLanguage()->plain() . ' ' . date( 'Y-m-d' );
            } else {
                $commitText = $description;
            }

            // https://github.com/KnpLabs/php-github-api/blob/master/doc/repo/contents.md
            $fileExists = $client->api( 'repo' )->contents()->exists( ...$fileParamArray );

            if ( $fileExists === true ) {
                wfDebugLog( 'PageToGitHub', '[PageToGitHub]Exist' );
                $oldFile = $client->api( 'repo' )->contents()->show( ...$fileParamArray );
                wfDebugLog( 'PageToGitHub', '[PageToGitHub]File retrieved. SHA: ' . $oldFile['sha'] );
                $fileInfo = $client->api( 'repo' )->contents()->update(
                    $P2GOwner, $P2GRepo, $pageName . '.lua', $fileContent, $commitText, $oldFile['sha']
                );
                wfDebugLog( 'PageToGitHub', '[PageToGitHub]File updated: ' . ( $fileInfo['url'] ?? 'unknown' ) );
            } else {
                wfDebugLog( 'PageToGitHub', '[PageToGitHub]Does NOT exist' );
                $client->api( 'repo' )->contents()->create(
                    $P2GOwner, $P2GRepo, $pageName . '.lua', $fileContent, $commitText
                );
                wfDebugLog( 'PageToGitHub', '[PageToGitHub]File created' );
            }
        } catch ( \Throwable $e ) {
            wfDebugLog( 'PageToGitHub', '[PageToGitHub]Error ' . $e->getMessage() );
            return false;
        }

        return true;
    }
}