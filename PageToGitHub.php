<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

#namespace MediaWiki\Extension\PageToGitHub;

use MediaWiki\MediaWikiServices;

class PageToGitHubHooks {
    /**
    * Occurs after the save page request has been processed.
    *
    * @param WikiPage $wikiPage
    * @param User $user
    * @param Content $content
    * @param string $summary
    * @param bool $isMinor
    * @param bool $isWatch
    * @param null $section Deprecated
    * @param int $flags
    * @param Revision|null $revision
    * @param Status $status
    * @param int|bool $originalRevId
    * @param int $undidRevId
    *
    * @return boolean
    * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageContentSaveComplete
	 */
    public static function onPageContentSaveComplete($wikiPage, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $originalRevId, $undidRevId) {
        $config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig('PageToGitHub');

        $P2GNameSpace = $config->get('P2GNameSpace');
        
        #wfDebugLog( 'PageToGitHub', "[PageToGitHub]Entered");
        $pageNameSpace = $wikiPage->getTitle()->getNsText();
        $pageTitle = $wikiPage->getTitle()->getRootText();
        $pageContent = $wikiPage->getContent()->getNativeData();

        wfDebugLog('PageToGitHub', "[PageToGitHub]Summary: " . $summary);
        
        if ($pageNameSpace == $P2GNameSpace) {
            wfDebugLog('PageToGitHub', "[PageToGitHub]True");
            #PageToGitHubHooks::Test();     
            #wfDebugLog('PageToGitHub', "[PageToGitHub]" . PageToGitHubHooks::Test());
            $return = PageToGitHubHooks::WriteToGithub($pageTitle, $pageContent, $summary, $config);
            wfDebugLog('PageToGitHub', "[PageToGitHub]Ha restituito: " . $return);
        }
        return true;
    }    

    public static function WriteToGithub($pageName, $pageContent, $description, $extConfig){
        try {
            wfDebugLog('PageToGitHub', "[PageToGitHub]Function WriteToGithub");

            $P2GPathAPI = $extConfig->get('P2GPathAPI');
            $P2GAuthToken = $extConfig->get('P2GAuthToken');
            $P2GOwner = $extConfig->get('P2GOwner');
            $P2GRepo = $extConfig->get('P2GRepo');
            $P2GNameSpace = $extConfig->get('P2GNameSpace');

            wfDebugLog('PageToGitHub', "[PageToGitHub]Token: " . $P2GAuthToken);
            #require_once '/usr/local/bin/php-github-api/vendor/autoload.php';
            require_once $P2GPathAPI;

            $client = new \Github\Client();            
            wfDebugLog('PageToGitHub', "[PageToGitHub]Init done");
            #wfDebugLog('PageToGitHub', "[PageToGitHub]Builder1: " . $client->getBuilderStatus());
            
            
            // https://github.com/KnpLabs/php-github-api/blob/master/doc/security.md
            $client->authenticate($P2GAuthToken, '' , \Github\Client::AUTH_HTTP_TOKEN);
            #wfDebugLog('PageToGitHub', "[PageToGitHub]Builder2: " . $client->getBuilderStatus());

            #echo 'Authenticated' , PHP_EOL;
            #return "Returned";

            /*
            if ($client == null) {
                wfDebugLog('PageToGitHub', "[PageToGitHub]NULL");
            } else {                
                wfDebugLog('PageToGitHub', "[PageToGitHub]Class: ". get_class($client));                
            }
            */

            #$repoContent = $client->api('repo')->contents();
            #wfDebugLog('PageToGitHub', "[PageToGitHub]Contents set");
            #wfDebugLog('PageToGitHub', "[PageToGitHub]Misc: " . $repoContent->show('WikiTrek', 'CodiceLUA'));
            #return $client->getApiVersion();

            $fileParamArray = [$P2GOwner, $P2GRepo, $pageName . '.lua'];
            $fileContent = "-- Auto upload by PageToGitHub on " . date("c") . PHP_EOL . "-- This code from page " . $P2GNameSpace . ":" . $pageName . PHP_EOL . $pageContent;
            if ($description == null) {
                $commitText = "Auto commit by PageToGitHub" . date('Y-m-d');
            } else {
                $commitText = $description;
            }            

            # https://github.com/KnpLabs/php-github-api/blob/master/doc/repo/cohttps://www.php.net/manual/en/function.get-class.phpntents.md
            #$fileExists = $client->api('repo')->contents()->exists($P2GOwner, $P2GRepo, $pageName . '.lua');
            $fileExists = $client->api('repo')->contents()->exists(...$fileParamArray);
            

            wfDebugLog('PageToGitHub', "[PageToGitHub]Message -auto-upload-: " . wfMessage("auto-upload")->parse());
            if ($fileExists == TRUE) {                
                wfDebugLog('PageToGitHub', "[PageToGitHub]Esiste");
                #return "Esiste";
                    
                #$oldFile = $client->api('repo')->contents()->show($P2GOwner, $P2GRepo, $pageName . '.lua');
                $oldFile = $client->api('repo')->contents()->show(...$fileParamArray);
                wfDebugLog('PageToGitHub', "[PageToGitHub]File retrieved. SHA: " . $oldFile['sha']);
                $fileInfo = $client->api('repo')->contents()->update($P2GOwner, $P2GRepo, $pageName . '.lua', $fileContent, $commitText, $oldFile['sha']);
                #wfDebugLog('PageToGitHub', "[PageToGitHub]File updated: " . $fileInfo['url'] . array_values($fileInfo));
                wfDebugLog('PageToGitHub', "[PageToGitHub]File updated: " . $fileInfo['url']);
            } else {
                wfDebugLog('PageToGitHub', "[PageToGitHub]NON Esiste");
                #return "Non esiste";
                $fileInfo = $client->api('repo')->contents()->create($P2GOwner, $P2GRepo, $pageName . '.lua', $fileContent, $commitText);
                wfDebugLog('PageToGitHub', "[PageToGitHub]File created");
            }        
        } catch (\Throwable $e) {
            #wfDebugLog('PageToGitHub', "[PageToGitHub]Error");
            wfDebugLog('PageToGitHub', "[PageToGitHub]Error " . $e->getMessage());
            #return $e->getMessage();
        } finally {
            #return ("Returned from FINALLY");
        }
    } 
}