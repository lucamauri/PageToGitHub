<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

use MediaWiki\MediaWikiServices;

class PageToGitHubHooks
{
    /**
     * Occurs after an article has been updated.
     *
     * @param WikiPage $wikiPage
	 * @param MediaWiki\User\UserIdentity $user
	 * @param string $summary
	 * @param int $flags
	 * @param MediaWiki\Revision\RevisionRecord $revisionRecord
	 * @param MediaWiki\Storage\EditResult $editResult
     * 
	 * @return bool|void
     *
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageSaveComplete
     */
    public static function onPageSaveComplete( WikiPage $wikiPage, MediaWiki\User\UserIdentity $user, string $summary, int $flags, MediaWiki\Revision\RevisionRecord $revisionRecord, MediaWiki\Storage\EditResult $editResult )
    {
        $config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig('PageToGitHub');

        $P2GNameSpace = $config->get('P2GNameSpace');
        $P2GKeyword = $config->get('P2GKeyword');
        $P2GIgnoreMinor = (bool) $config->get('P2GIgnoreMinor');

        $pageNameSpace = $wikiPage->getTitle()->getNsText();
        $pageTitle = $wikiPage->getTitle()->getRootText();
        $pageContent = $wikiPage->getContent()->getNativeData();
        $isMinor = (bool) $wikiPage->getMinorEdit();

        if ($P2GIgnoreMinor and $isMinor) {
            wfDebugLog('PageToGitHub', '[PageToGitHub]IGNORING Minor');
        } else {
            wfDebugLog('PageToGitHub', '[PageToGitHub]NOT ignoring Minor');
            if ($pageNameSpace == $P2GNameSpace) {
                wfDebugLog('PageToGitHub', '[PageToGitHub]Namespace OK');
                wfDebugLog('PageToGitHub', '[PageToGitHub]Keyword: '.$P2GKeyword);
                if ($P2GKeyword == null or $P2GKeyword == '' or (strpos($pageContent, $P2GKeyword) > -1)) {
                    wfDebugLog('PageToGitHub', '[PageToGitHub]Keyword OK');
                    $return = self::WriteToGithub($pageTitle, $pageContent, $summary, $config);
                    wfDebugLog('PageToGitHub', '[PageToGitHub]Returned: ' . $return);
                } else {
                    wfDebugLog('PageToGitHub', '[PageToGitHub]Keyword KO');
                }
            } else {
                wfDebugLog('PageToGitHub', '[PageToGitHub]Namespace KO');
            }
        }

        return true;
    }

    public static function WriteToGithub($pageName, $pageContent, $description, $extConfig)
    {
        $service = new P2GGitHubService();
        return $service->uploadToGitHub($pageName, $pageContent, $description);
    }
}
