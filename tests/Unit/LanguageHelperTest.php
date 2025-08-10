<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\LanguageHelper;

class LanguageHelperTest extends TestCase
{
    /**
     * Test normalizeLanguageName function
     */
    public function test_normalize_language_name()
    {
        // Test Arabic to English conversion
        $this->assertEquals('Arabic', LanguageHelper::normalizeLanguageName('العربية'));
        $this->assertEquals('English', LanguageHelper::normalizeLanguageName('الإنجليزية'));
        $this->assertEquals('French', LanguageHelper::normalizeLanguageName('الفرنسية'));
        $this->assertEquals('Spanish', LanguageHelper::normalizeLanguageName('الإسبانية'));
        
        // Test that English names remain unchanged
        $this->assertEquals('Arabic', LanguageHelper::normalizeLanguageName('Arabic'));
        $this->assertEquals('English', LanguageHelper::normalizeLanguageName('English'));
        
        // Test edge cases
        $this->assertEquals('Unknown', LanguageHelper::normalizeLanguageName('Unknown'));
        $this->assertEquals('English', LanguageHelper::normalizeLanguageName('')); // Returns default language for empty string
    }

    /**
     * Test arabizeLanguageName function
     */
    public function test_arabize_language_name()
    {
        // Test English to Arabic conversion
        $this->assertEquals('العربية', LanguageHelper::arabizeLanguageName('Arabic'));
        $this->assertEquals('الإنجليزية', LanguageHelper::arabizeLanguageName('English'));
        
        // Test that Arabic names remain unchanged
        $this->assertEquals('العربية', LanguageHelper::arabizeLanguageName('العربية'));
        
        // Test edge cases
        $this->assertEquals('Unknown', LanguageHelper::arabizeLanguageName('Unknown'));
    }

    /**
     * Test isValidLanguage function
     */
    public function test_is_valid_language()
    {
        $this->assertTrue(LanguageHelper::isValidLanguage('Arabic'));
        $this->assertTrue(LanguageHelper::isValidLanguage('English'));
        $this->assertTrue(LanguageHelper::isValidLanguage('العربية'));
        $this->assertTrue(LanguageHelper::isValidLanguage('الإنجليزية'));
        
        $this->assertFalse(LanguageHelper::isValidLanguage('InvalidLanguage'));
        $this->assertFalse(LanguageHelper::isValidLanguage(''));
    }
}
