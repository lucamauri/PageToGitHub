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

class PageToGitHubSpecial extends SpecialPage {
	function __construct() {
		parent::__construct('PageToGitHub');
	}

	function execute( $par ) {
	       $variablesNames = ["P2GNameSpace", "P2GKeyword", "P2GAddKeyword", "P2GIgnoreMinor","P2GAuthToken", "P2GOwner", "P2GRepo", "P2GFileExtension", "P2GBranch"];
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		# Get request data from, e.g.
		$param = $request->getText( 'param' );

		# Do stuff
        $config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig('PageToGitHub');
        
        // https://doc.wikimedia.org/mediawiki-core/master/php/classOutputPage.html
        $output->addWikiMsg('p2g-specialpage-text');
        $output->addWikiMsg('p2g-specialpage-config-title');
        $output->addWikiMsg('p2g-specialpage-variables-text');

        $output->addHTML('<table class="wikitable plainlinks" id="p2g-variables"><tbody>
            <tr>
            <th>Variabile</th>
            <th>Valore</th>
            </tr>');

        foreach ($variablesNames as $variableName) {
            ${$variableName} = $config->get($variableName);
            $output->addHTML('<tr>');
            $output->addHTML('<th>' . $variableName . '</th>');
            if ($variableName == 'P2GAuthToken') {
                $variableContent = "''hidden''";
            } else {
                $variableContent = "<code>" . ${$variableName} . "</code>";
            }
            $output->addHTML('<td>');
            $output->addWikiTextAsContent($variableContent);
            $output->addHTML('</td>');
            $output->addHTML('</tr>');
        }
        
        $output->addHTML('</tbody></table>');

        // Configuration validation
        $service = new P2GGitHubService();
        $configStatus = $service->getConfigStatus();

        if (!$configStatus['isConfigured']) {
            $output->addWikiMsg('p2g-specialpage-config-validation-title');
            $missing = [];
            if (!$configStatus['hasToken']) $missing[] = wfMessage('p2g-config-token')->parse();
            if (!$configStatus['hasOwner']) $missing[] = wfMessage('p2g-config-owner')->parse();
            if (!$configStatus['hasRepo']) $missing[] = wfMessage('p2g-config-repo')->parse();
            $output->addWikiMsg('p2g-specialpage-config-missing', implode(', ', $missing));
        } else {
            $output->addWikiMsg('p2g-specialpage-config-valid');
        }

        // Manual sync form
        $output->addWikiMsg('p2g-specialpage-manual-sync-title');
        $output->addHTML('<form method="post" action="' . htmlspecialchars($this->getPageTitle()->getLocalURL()) . '">');
        $output->addHTML('<fieldset>');
        $output->addHTML('<legend>' . wfMessage('p2g-specialpage-manual-sync-legend')->parse() . '</legend>');
        $output->addHTML('<p>' . wfMessage('p2g-specialpage-manual-sync-text')->parse() . '</p>');
        $output->addHTML('<label for="page_title">' . wfMessage('p2g-specialpage-page-title')->parse() . '</label>');
        $output->addHTML('<input type="text" id="page_title" name="page_title" required>');
        $output->addHTML('<input type="submit" value="' . wfMessage('p2g-specialpage-sync-button')->parse() . '">');
        $output->addHTML('</fieldset>');
        $output->addHTML('</form>');

        // Handle form submission
        if ($request->wasPosted() && $request->getText('page_title')) {
            $pageTitle = $request->getText('page_title');
            $this->handleManualSync($pageTitle, $output);
        }
    }

    private function handleManualSync($pageTitle, $output) {
        $title = \Title::newFromText($pageTitle);
        if (!$title || !$title->exists()) {
            $output->addWikiMsg('p2g-specialpage-page-not-found', $pageTitle);
            return;
        }

        $wikiPage = \WikiPage::factory($title);
        $content = $wikiPage->getContent()->getNativeData();

        $service = new P2GGitHubService();
        $result = $service->uploadToGitHub($title->getRootText(), $content, 'Manual sync from Special:PageToGitHub');

        if ($result) {
            $output->addWikiMsg('p2g-specialpage-sync-success', $pageTitle);
        } else {
            $output->addWikiMsg('p2g-specialpage-sync-failed', $pageTitle);
        }
    }
}