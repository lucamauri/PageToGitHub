<?php
/**
 * PageToGitHub — Special page showing the current extension configuration.
 *
 * @file
 * @license GPL-2.0-or-later
 * @author Luca Mauri
 */

use MediaWiki\Config\ConfigFactory;

/**
 * Displays a table of all PageToGitHub configuration variables and their
 * current values. The authentication token is never read or displayed.
 */
class PageToGitHubSpecial extends SpecialPage {

    /** @var ConfigFactory */
    private ConfigFactory $configFactory;

    /**
     * Constructor — receives ConfigFactory via MediaWiki's service container.
     * Wired through the SpecialPages ObjectFactory spec in extension.json.
     *
     * @param ConfigFactory $configFactory Factory used to retrieve extension config
     */
    public function __construct( ConfigFactory $configFactory ) {
        parent::__construct( 'PageToGitHub' );
        $this->configFactory = $configFactory;
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

        $config = $this->configFactory->makeConfig( 'PageToGitHub' );

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
            $output->addHTML( '<tr><th>' . htmlspecialchars( $variableName ) . '</th><td>' );

            if ( $variableName === 'P2GAuthToken' ) {
                // Never read the token from config — output the masked value directly
                // Use addWikiTextAsContent for wikitext (''hidden'' renders as <em>hidden</em>)
                $output->addWikiTextAsContent( "''hidden''" );
            } else {
                $value = $config->get( $variableName );
                // Use addHTML directly to avoid addWikiTextAsContent wrapping in <p> tags
                $output->addHTML( '<code>' . htmlspecialchars( (string)$value ) . '</code>' );
            }

            $output->addHTML( '</td></tr>' );
        }

        $output->addHTML( '</tbody></table>' );
    }
}