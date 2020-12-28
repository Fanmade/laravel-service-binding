<?php

namespace Fanmade\ServiceBinding\Tests;

use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function test_overlap()
    {
        $sut = str_overlap('AaBbCc123BFoo', 'AaBbCc123Foo');
        self::assertEquals('AaBbCc123', $sut);
    }

    public function test_overlap_with_shorter_haystack()
    {
        $sut = str_overlap('AaBbCc123', 'AaBbCc1');
        self::assertEquals('AaBbCc1', $sut);
    }

    public function test_overlap_with_equal_strings()
    {
        $sut = str_overlap('AaBbCc123', 'AaBbCc123');
        self::assertEquals('AaBbCc123', $sut);
    }

    public function test_overlap_with_umlaut()
    {
        $sut = str_overlap('AaBbÜCc1', 'AaBbÜCc123');
        self::assertEquals('AaBbÜCc1', $sut);
    }

    public function test_case_sensitive_overlap()
    {
        $sut = str_overlap('AaBbÜCc1', 'AabbÜCc123', true);
        self::assertEquals('Aa', $sut);
    }

    public function test_str_match_humps()
    {
        self::assertTrue(str_match_humps('mytestString', '\My\Great\TestingStringMethod'));
    }

    public function test_str_match_humps_false()
    {
        self::assertFalse(str_match_humps('notmytestString', '\My\Great\TestingStringMethod'));
    }
}
