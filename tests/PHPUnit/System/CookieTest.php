<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Access;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

class CookieTest extends SystemTestCase
{
    public const USERAGENT_CHROME = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/3.0.195.38 Safari/532.0';
    public const USERAGENT_FIREFOX = 'Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20100101 Firefox/6.0';
    public const USERAGENT_SAFARI = 'Mozilla/5.0 (X11; U; Linux x86_64; en-us) AppleWebKit/531.2+ (KHTML, like Gecko) Version/5.0 Safari/531.2+';

    private $testVars;

    private $originalAssumeSecureValue;

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2024-02-02');
        $this->testVars = static::$fixture->getTestEnvironment();
        $this->originalAssumeSecureValue = Config::getInstance()->General['assume_secure_protocol'];
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->testVars->overrideConfig('General', 'assume_secure_protocol', $this->originalAssumeSecureValue);
        $this->testVars->save();
    }

    public function testIgnoreCookieSameSiteChromeSecure()
    {
        $headers = $this->setIgnoreCookie(self::USERAGENT_CHROME, 1);
        $cookie = $this->findIgnoreCookie($headers);
        $this->assertCookieSameSiteMatches('None', $cookie);
    }

    public function testIgnoreCookieSameSiteChromeNotSecure()
    {
        $headers = $this->setIgnoreCookie(self::USERAGENT_CHROME);
        $cookie = $this->findIgnoreCookie($headers);
        $this->assertCookieSameSiteMatches('Lax', $cookie);
    }

    public function testIgnoreCookieSameSiteFirefox()
    {
        $headers = $this->setIgnoreCookie(self::USERAGENT_FIREFOX);
        $cookie = $this->findIgnoreCookie($headers);
        $this->assertCookieSameSiteMatches('Lax', $cookie);
    }

    public function testIgnoreCookieSameSiteSafari()
    {
        $headers = $this->setIgnoreCookie(self::USERAGENT_SAFARI);
        $cookie = $this->findIgnoreCookie($headers);
        self::assertStringNotContainsString($cookie, 'SameSite');
    }

    private function setIgnoreCookie($userAgent, $assumeSecure = 0)
    {
        $matomoUrl = Fixture::getTestRootUrl();
        $cookieFile = tempnam(StaticContainer::get('path.tmp'), 'testSessionCookie');

        $params = array(
            'module' => 'UsersManager',
            'action' => 'userSettings',
            'idSite' => 1,
            'period' => 'day',
            'date' => 'yesterday',
        );

        $url = $matomoUrl . 'index.php?' . http_build_query($params);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);

        $content = curl_exec($ch);
        curl_close($ch);

        preg_match('/set-ignore-cookie-nonce="&quot;([a-z0-9]+)&quot;"/i', $content, $matches);

        $nonce = $matches[1] ?? '';

        $this->testVars->overrideConfig('General', 'assume_secure_protocol', $assumeSecure);
        $this->testVars->save();

        $params = array(
            'module' => 'UsersManager',
            'action' => 'setIgnoreCookie',
            'idSite' => 1,
            'period' => 'day',
            'date' => 'yesterday',
            'nonce' => $nonce,
        );

        $url = $matomoUrl . 'index.php?' . http_build_query($params);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        return curl_exec($ch);
    }

    private function findIgnoreCookie($rawHeaders)
    {
        $ignoreCookieName = Config::getInstance()->Tracker['ignore_visits_cookie_name'];
        preg_match('/^Set-Cookie: ' . $ignoreCookieName . '=.*/m', $rawHeaders, $matches);
        return $matches[0] ?? '';
    }

    private function assertCookieSameSiteMatches($expectedSameSite, $cookieHeader)
    {
        self::assertStringContainsString('SameSite=' . $expectedSameSite, $cookieHeader);
    }

    /**
     * Use this method to return custom container configuration that you want to apply for the tests.
     * This configuration will override Fixture config.
     *
     * @return array
     */
    public static function provideContainerConfigBeforeClass()
    {
        $fakeAccess = new FakeAccess();
        $fakeAccess->setSuperUserAccess(true);
        return [
            Access::class => $fakeAccess
        ];
    }
}
