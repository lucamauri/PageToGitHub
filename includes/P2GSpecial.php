<?php
/**
 * PageToGitHub — Special page showing the current extension configuration.
 *
 * @file
 * @license GPL-2.0-or-later
 * @author Luca Mauri
 */

use MediaWiki\MediaWikiServices;

/**
 * Displays a table of all PageToGitHub configuration variables and their
 * current values. The authentication token is never read or displayed.
 */
class PageToGitHubSpecial extends SpecialPage {

    /**
     * @inheritDoc
     */
    public function __construct() {
        parent::__construct( 'PageToGitHub' );
    }

    /**
     * Renders the special page output: an introductory message followed by
     * a wikitable listing every configuration variable and its current value.
     * P2GAuthToken is never read from config — its row always shows "hidden".
     *
     * @param string|null $par Subpage parameter (unused)
     */
    public function execute( $par ): void {
        $variableNames = [
            'P2GNameSpace',
            'P2GKeyword',
            'P2GAddKeyword',
            'P2GIgnoreMinor',
            'P2GAuthToken',
            'P2GOwner',
            'P2GRepo'
        ];

        $output = $this->getOutput();
        $this->setHeaders();

        $config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'PageToGitHub' );

        // https://doc.wikimedia.org/mediawiki-core/master/php/classOutputPage.html
        $output->addWikiMsg( 'p2g-specialpage-text' );
        $output->addWikiMsg( 'p2g-specialpage-config-title' );
        $output->addWikiMsg( 'p2g-specialpage-variables-text' );

        $output->addHTML(
            '<table class="wikitable plainlinks" id="p2g-variables"><tbody>' .
            '<tr>' .
            '<th>' . wfMessage( 'p2g-config-variable-header' )->escaped() . '</th>' .
            '<th>' . wfMessage( 'p2g-config-value-header' )->escaped() . '</th>' .
            '</tr>'
        );

        foreach ( $variableNames as $variableName ) {
            $output->addHTML( '<tr>' );
            $output->addHTML( '<th>' . htmlspecialchars( $variableName ) . '</th>' );

            if ( $variableName === 'P2GAuthToken' ) {
                // Never read the token from config — output the masked value directly
                $variableContent = "''hidden''";
            } else {
                $value = $config->get( $variableName );
                $variableContent = '<code>' . htmlspecialchars( (string)$value ) . '</code>';
            }

            $output->addHTML( '<td>' );
            $output->addWikiTextAsContent( $variableContent );
            $output->addHTML( '</td>' );
            $output->addHTML( '</tr>' );
        }

        $output->addHTML( '</tbody></table>' );
    }
}