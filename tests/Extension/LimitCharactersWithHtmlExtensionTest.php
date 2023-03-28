<?php

namespace Csoellinger\SilverStripe\LimitCharactersWithHtml\Tests;

use Csoellinger\SilverStripe\LimitCharactersWithHtml\LimitCharactersWithHtmlExtension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBHTMLVarchar;
use SilverStripe\Versioned\Versioned;

class LimitCharactersWithHtmlExtensionTest extends SapphireTest
{
    protected $usesDatabase = false;

    protected $usesTransactions = false;

    protected static $fixture_file = null;

    protected static $required_extensions = [
        DBHTMLText::class => [
            LimitCharactersWithHtmlExtension::class,
        ],
        DBHTMLVarchar::class => [
            LimitCharactersWithHtmlExtension::class,
        ],
    ];

    public static function tearDownAfterClass(): void
    {
        // Override sapphire test tear down
    }

    /**
     * @dataProvider htmlTextProvider
     */
    public function testLimitCharactersWithHtml(string $htmlText, int $truncateLength, mixed $add, string $expectedExact)
    {
        $dbField = DBHTMLText::create('TestField')->setValue($htmlText);

        $this->assertEquals($expectedExact, $dbField->LimitCharactersWithHtml($truncateLength, $add));
    }

    /**
     * @dataProvider htmlTextProvider
     */
    public function testLimitCharactersWithHtmlToClosestWord(
        string $htmlText,
        int $truncateLength,
        mixed $add,
        string $expectedExact,
        string $expectedWordExact
    ) {
        $dbField = DBHTMLText::create('TestField')->setValue($htmlText);

        $this->assertEquals($expectedWordExact, $dbField->LimitCharactersWithHtmlToClosestWord($truncateLength, $add));
    }

    /**
     * @dataProvider htmlTextProvider
     */
    public function testLongerThan(
        string $htmlText,
        int $truncateLength,
        mixed $add,
        string $expectedExact,
        string $expectedWordExact,
        bool $longerThan
    ) {
        $dbField = DBHTMLText::create('TestField')->setValue($htmlText);

        $this->assertEquals($longerThan, $dbField->LongerThan($truncateLength));
        $this->assertEquals($longerThan, $dbField->LongerThan($truncateLength, false));
    }

    public function htmlTextProvider(): array
    {
        return [
            [
                trim(file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'data', '01.html']))),
                150,
                false,
                '<div><h2>What is Lorem Ipsum?</h2><p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy tex…</p></div>', //phpcs:ignore
                '<div><h2>What is Lorem Ipsum?</h2><p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy…</p></div>', //phpcs:ignore
                true,
            ],
            [
                trim(file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'data', '01.html']))),
                150,
                '---',
                '<div><h2>What is Lorem Ipsum?</h2><p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy t---</p></div>', //phpcs:ignore
                '<div><h2>What is Lorem Ipsum?</h2><p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy---</p></div>', //phpcs:ignore
                true,
            ],
            [
                trim(file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'data', '02.html']))),
                150,
                false,
                '<div><h2>Why do we use &amp; like it?</h2><p> It is a long established &quot;fact&quot; that a reader will be distracted by the readable content of a page when looking at its layou…</p></div>', //phpcs:ignore
                '<div><h2>Why do we use &amp; like it?</h2><p> It is a long established &quot;fact&quot; that a reader will be distracted by the readable content of a page when looking at its…</p></div>', //phpcs:ignore
                true,
            ],
            [
                '<b>Hello World</b>',
                150,
                false,
                '<b>Hello World</b>',
                '<b>Hello World</b>',
                false,
            ],
            [
                '<b>Hello World</b>',
                9,
                false,
                '<b>Hello Wo…</b>',
                '<b>Hello…</b>',
                true,
            ],
        ];
    }
}
