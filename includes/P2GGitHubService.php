<?php
/**
 * GitHub API service for PageToGitHub extension
 *
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

class P2GGitHubService {

    private $client;
    private $config;

    public function __construct() {
        $this->config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig('PageToGitHub');
        $this->client = new \Github\Client();
    }

    /**
     * Upload content to GitHub
     *
     * @param string $pageName
     * @param string $pageContent
     * @param string $description
     * @return bool
     */
    public function uploadToGitHub($pageName, $pageContent, $description) {
        $maxRetries = 3;
        $retryDelay = 1; // seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                return $this->doUpload($pageName, $pageContent, $description);
            } catch (\Github\Exception\RuntimeException $e) {
                // Check if it's a rate limit error
                if ($e->getCode() === 403 && strpos($e->getMessage(), 'API rate limit exceeded') !== false) {
                    if ($attempt < $maxRetries) {
                        wfDebugLog('PageToGitHub', "[PageToGitHub]Rate limit hit, retrying in {$retryDelay}s (attempt {$attempt}/{$maxRetries})");
                        sleep($retryDelay);
                        $retryDelay *= 2; // Exponential backoff
                        continue;
                    }
                }
                wfDebugLog('PageToGitHub', '[PageToGitHub]Error after retries: '.$e->getMessage());
                return false;
            } catch (\Throwable $e) {
                wfDebugLog('PageToGitHub', '[PageToGitHub]Unexpected error: '.$e->getMessage());
                return false;
            }
        }

        return false;
    }

    /**
     * Perform the actual upload
     */
    private function doUpload($pageName, $pageContent, $description) {
        $P2GAuthToken = $this->config->get('P2GAuthToken');
        $P2GOwner = $this->config->get('P2GOwner');
        $P2GRepo = $this->config->get('P2GRepo');
        $P2GNameSpace = $this->config->get('P2GNameSpace');
        $P2GKeyword = $this->config->get('P2GKeyword');
        $P2GAddKeyword = $this->config->get('P2GAddKeyword');
        $P2GFileExtension = $this->config->get('P2GFileExtension') ?: 'lua';
        $P2GBranch = $this->config->get('P2GBranch') ?: 'main';

        // Validate required configuration
        if (empty($P2GAuthToken) || empty($P2GOwner) || empty($P2GRepo)) {
            wfDebugLog('PageToGitHub', '[PageToGitHub]Error: Missing required configuration (AuthToken, Owner, or Repo)');
            return false;
        }

        $this->client->authenticate($P2GAuthToken, '', \Github\AuthMethod::ACCESS_TOKEN);

        if ($P2GKeyword != null && $P2GKeyword != '' && $P2GAddKeyword == true) {
            $pageName = $P2GKeyword . "-" . $pageName;
        }

        $fileParamArray = [$P2GOwner, $P2GRepo, $pageName.'.'.$P2GFileExtension];
        $fileContent = '-- [P2G] Auto upload by PageToGitHub on '.date('c').PHP_EOL.'-- [P2G] This code from page '.$P2GNameSpace.':'.$pageName.PHP_EOL.$pageContent;
        if ($description == null) {
            $commitText = 'Auto commit by PageToGitHub'.date('Y-m-d');
        } else {
            $commitText = $description;
        }

        $fileExists = $this->client->api('repo')->contents()->exists($P2GOwner, $P2GRepo, $pageName.'.'.$P2GFileExtension, $P2GBranch);

        if ($fileExists == true) {
            $oldFile = $this->client->api('repo')->contents()->show($P2GOwner, $P2GRepo, $pageName.'.'.$P2GFileExtension, $P2GBranch);
            $fileInfo = $this->client->api('repo')->contents()->update($P2GOwner, $P2GRepo, $pageName.'.'.$P2GFileExtension, $fileContent, $commitText, $oldFile['sha'], $P2GBranch);
            wfDebugLog('PageToGitHub', '[PageToGitHub]File updated: '.$fileInfo['url']);
        } else {
            $fileInfo = $this->client->api('repo')->contents()->create($P2GOwner, $P2GRepo, $pageName.'.'.$P2GFileExtension, $fileContent, $commitText, $P2GBranch);
            wfDebugLog('PageToGitHub', '[PageToGitHub]File created');
        }

        return true;
    }

    /**
     * Get configuration status
     *
     * @return array
     */
    public function getConfigStatus() {
        $P2GAuthToken = $this->config->get('P2GAuthToken');
        $P2GOwner = $this->config->get('P2GOwner');
        $P2GRepo = $this->config->get('P2GRepo');

        return [
            'hasToken' => !empty($P2GAuthToken),
            'hasOwner' => !empty($P2GOwner),
            'hasRepo' => !empty($P2GRepo),
            'isConfigured' => !empty($P2GAuthToken) && !empty($P2GOwner) && !empty($P2GRepo)
        ];
    }
}