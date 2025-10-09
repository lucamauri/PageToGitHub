<?php

use PHPUnit\Framework\TestCase;
use MediaWiki\Config\HashConfig;

class PageToGitHubTest extends TestCase {

    public function testWriteToGithubWithMissingConfig() {
        $config = new HashConfig([
            'P2GAuthToken' => '',
            'P2GOwner' => '',
            'P2GRepo' => '',
        ]);

        $result = PageToGitHubHooks::WriteToGithub('TestPage', 'Test content', 'Test commit', $config);

        $this->assertFalse($result);
    }

    public function testWriteToGithubWithValidConfig() {
        // This would require mocking the GitHub API, which is complex
        // For now, just test the validation part
        $config = new HashConfig([
            'P2GAuthToken' => 'fake-token',
            'P2GOwner' => 'fake-owner',
            'P2GRepo' => 'fake-repo',
            'P2GNameSpace' => 'Module',
            'P2GKeyword' => '',
            'P2GAddKeyword' => false,
            'P2GFileExtension' => 'lua',
            'P2GBranch' => 'main',
        ]);

        // Since we can't mock the API easily, this will fail with exception
        // But at least test that it doesn't fail on validation
        $this->expectException(\Throwable::class);
        PageToGitHubHooks::WriteToGithub('TestPage', 'Test content', 'Test commit', $config);
    }

    public function testGitHubServiceConfigStatus() {
        // Test config status checking
        $service = new P2GGitHubService();
        $status = $service->getConfigStatus();

        // Since we're using real config, this will depend on actual settings
        $this->assertIsArray($status);
        $this->assertArrayHasKey('isConfigured', $status);
        $this->assertArrayHasKey('hasToken', $status);
        $this->assertArrayHasKey('hasOwner', $status);
        $this->assertArrayHasKey('hasRepo', $status);
    }
}