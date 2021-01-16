<?php
class ApiCest 
{
    //private $base = 'http://ftr-tests.fivefilters.org/v1/';

    private function getBaseURL() {
        $config = \Codeception\Configuration::config();
        $apiSettings = \Codeception\Configuration::suiteSettings('api', $config);
        return $apiSettings['modules']['enabled'][0]['REST']['url'];
    }

    private function getTestHTMLURL() {
        return $this->getBaseURL().'/tests/_data';
    }

    private function checkBasicXML(ApiTester $I) {
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsXml();
        $I->seeXmlResponseMatchesXpath('//rss/channel/title');
        $I->seeXmlResponseIncludes("<title>Monsanto condamné à payer 81 millions de dollars à un malade du cancer</title>");
        $I->seeResponseContains('#include &amp;lt;stdio.h&amp;gt;');
        $I->seeResponseContains('C’était le procès à ne pas perdre');
    }

    private function checkBasicJSON(ApiTester $I) {
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesXpath('//rss/channel/title');
        $I->seeResponseContainsJson(["title"=>"Monsanto condamné à payer 81 millions de dollars à un malade du cancer"]);
        $I->seeResponseContains('#include &lt;stdio.h&gt;');
        $I->seeResponseContains('C\u2019\u00e9tait le proc\u00e8s \u00e0 ne pas perdre ; Monsanto l\u2019a perdu.');
    }

    public function checkBasicHTML5XML(ApiTester $I)
    {
        $url = $this->getTestHTMLURL().'/char-entities.html';
        $I->sendGET('/makefulltextfeed.php', ['url'=>$url, 'parser'=>'html5php']);
        $this->checkBasicXML($I);
    }

    public function checkBasicHTML5JSON(ApiTester $I)
    {
        $url = $this->getTestHTMLURL().'/char-entities.html';
        $I->sendGET('/makefulltextfeed.php', ['url'=>$url, 'parser'=>'html5php', 'format'=>'json']);
        $this->checkBasicJSON($I);
    }

    /*
    public function checkBasicGumboXML(ApiTester $I) {
        $url = $this->getTestHTMLURL().'/char-entities.html';
        $I->sendGET('/makefulltextfeed.php', ['url'=>$url, 'parser'=>'gumbo']);
        $this->checkBasicXML($I);
    }

    public function checkBasicGumboJSON(ApiTester $I) {
        $url = $this->getTestHTMLURL().'/char-entities.html';
        $I->sendGET('/makefulltextfeed.php', ['url'=>$url, 'parser'=>'gumbo', 'format'=>'json']);
        $this->checkBasicJSON($I);
    }
    */

    public function inputHTML(ApiTester $I) {
        $I->sendPOST('/extract.php', [
            'inputhtml'=>'<html><head><title>Example</title><body><article itemprop="articleBody"><p>Test</p></article></body></html>',
            'url'=>'chomsky.info/articles/20131105.htm',
            'xss'=>1
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(["title"=>"Example"]);
        $I->seeResponseContainsJson(["content"=>"<p>Test</p>"]);
    }
}