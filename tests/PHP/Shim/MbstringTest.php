<?php

namespace Patchwork\Tests\PHP\Shim;

use Patchwork\PHP\Shim\Mbstring as p;
use Normalizer as n;

/**
 * @covers Patchwork\PHP\Shim\Mbstring::<!public>
 */
class MbstringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_internal_encoding
     * @covers Patchwork\PHP\Shim\Mbstring::mb_list_encodings
     * @covers Patchwork\PHP\Shim\Mbstring::mb_substitute_character
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testmb_stubs()
    {
        $this->assertFalse(p::mb_substitute_character('?'));
        $this->assertSame('none', p::mb_substitute_character());

        $this->assertContains('UTF-8', p::mb_list_encodings());

        $this->assertTrue(p::mb_internal_encoding('utf8'));
        $this->assertFalse(p::mb_internal_encoding('no-no'));
        $this->assertSame('UTF-8', p::mb_internal_encoding());

        p::mb_encode_mimeheader('');
        $this->assertFalse(true, 'mb_encode_mimeheader() is bugged. Please use iconv_mime_encode() instead');
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_convert_encoding
     */
    public function testmb_convert_encoding()
    {
        $this->assertSame(utf8_decode('déjà'), p::mb_convert_encoding('déjà', 'Windows-1252'));
        $this->assertSame(base64_encode('déjà'), p::mb_convert_encoding('déjà', 'Base64'));
        $this->assertSame('&#23455;<&>d&eacute;j&agrave;', p::mb_convert_encoding('実<&>déjà', 'Html-entities'));
        $this->assertSame('déjà', p::mb_convert_encoding(base64_encode('déjà'), 'Utf-8', 'Base64'));
        $this->assertSame('déjà', p::mb_convert_encoding('d&eacute;j&#224;', 'Utf-8', 'Html-entities'));
        $this->assertSame('déjà', p::mb_convert_encoding(utf8_decode('déjà'), 'Utf-8', 'ASCII,ISO-2022-JP,UTF-8,ISO-8859-1'));
        $this->assertSame('déjà', p::mb_convert_encoding(utf8_decode('déjà'), 'Utf-8', array('ASCII', 'ISO-2022-JP', 'UTF-8', 'ISO-8859-1')));
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strtolower
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strtoupper
     * @covers Patchwork\PHP\Shim\Mbstring::mb_convert_case
     */
    public function testStrCase()
    {
        $this->assertSame('déjà σσς iiıi', p::mb_strtolower('DÉJÀ Σσς İIıi'));
        $this->assertSame('DÉJÀ ΣΣΣ İIII', p::mb_strtoupper('Déjà Σσς İIıi'));
        $this->assertSame('Déjà Σσσ Iı Ii İi', p::mb_convert_case('DÉJÀ ΣΣΣ ıı iI İİ', MB_CASE_TITLE));
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strlen
     */
    public function testmb_strlen()
    {
        $this->assertSame(3, mb_strlen('한국어'));
        $this->assertSame(8, mb_strlen(n::normalize('한국어', n::NFD)));

        $this->assertSame(3, p::mb_strlen('한국어'));
        $this->assertSame(8, p::mb_strlen(n::normalize('한국어', n::NFD)));
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_substr
     */
    public function testmb_substr()
    {
        $c = 'déjà';

        if (PHP_VERSION_ID >= 50408) {
            $this->assertSame('jà', mb_substr($c, 2, null));
        }

        $this->assertSame('jà', mb_substr($c,  2));
        $this->assertSame('jà', mb_substr($c, -2));
        $this->assertSame('jà', mb_substr($c, -2,  3));
        $this->assertSame('',   mb_substr($c, -1,  0));
        $this->assertSame('',   mb_substr($c,  1, -4));
        $this->assertSame('j',  mb_substr($c, -2, -1));
        $this->assertSame('',   mb_substr($c, -2, -2));
        $this->assertSame('',   mb_substr($c,  5,  0));
        $this->assertSame('',   mb_substr($c, -5,  0));

        $this->assertSame('jà', p::mb_substr($c,  2, null));
        $this->assertSame('jà', p::mb_substr($c,  2));
        $this->assertSame('jà', p::mb_substr($c, -2));
        $this->assertSame('jà', p::mb_substr($c, -2, 3));
        $this->assertSame('',   p::mb_substr($c, -1,  0));
        $this->assertSame('',   p::mb_substr($c,  1, -4));
        $this->assertSame('j',  p::mb_substr($c, -2, -1));
        $this->assertSame('',   p::mb_substr($c, -2, -2));
        $this->assertSame('',   p::mb_substr($c,  5,  0));
        $this->assertSame('',   p::mb_substr($c, -5,  0));
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strpos
     * @covers Patchwork\PHP\Shim\Mbstring::mb_stripos
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strrpos
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strripos
     */
    public function testmb_strpos()
    {
        $this->assertSame(false, @mb_strpos('abc', ''));
        $this->assertSame(false, @mb_strpos('abc', 'a', -1));
        $this->assertSame(false, mb_strpos('abc', 'd'));
        $this->assertSame(false, mb_strpos('abc', 'a', 3));
        $this->assertSame(1, mb_strpos('한국어', '국'));
        $this->assertSame(3, mb_stripos('DÉJÀ', 'à'));
        $this->assertSame(false, mb_strrpos('한국어', ''));
        $this->assertSame(1, mb_strrpos('한국어', '국'));
        $this->assertSame(3, mb_strripos('DÉJÀ', 'à'));
        $this->assertSame(1, mb_stripos('aςσb', 'ΣΣ'));
        $this->assertSame(1, mb_strripos('aςσb', 'ΣΣ'));
        $this->assertSame(3, mb_strrpos('ababab', 'b', -2));

        $this->assertSame(false, @p::mb_strpos('abc', ''));
        $this->assertSame(false, @p::mb_strpos('abc', 'a', -1));
        $this->assertSame(false, p::mb_strpos('abc', 'd'));
        $this->assertSame(false, p::mb_strpos('abc', 'a', 3));
        $this->assertSame(1, p::mb_strpos('한국어', '국'));
        $this->assertSame(3, p::mb_stripos('DÉJÀ', 'à'));
        $this->assertSame(false, p::mb_strrpos('한국어', ''));
        $this->assertSame(1, p::mb_strrpos('한국어', '국'));
        $this->assertSame(3, p::mb_strripos('DÉJÀ', 'à'));
        $this->assertSame(1, p::mb_stripos('aςσb', 'ΣΣ'));
        $this->assertSame(1, p::mb_strripos('aςσb', 'ΣΣ'));
        $this->assertSame(3, p::mb_strrpos('ababab', 'b', -2));
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strpos
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testmb_strpos_empty_delimiter()
    {
        try {
            mb_strpos('abc', '');
            $this->assertFalse(true, 'The previous line should trigger a warning (Empty delimiter)');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            p::mb_strpos('abc', '');
            $this->assertFalse(true, 'The previous line should trigger a warning (Empty delimiter)');
        }
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strpos
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testmb_strpos_negative_offset()
    {
        try {
            mb_strpos('abc', 'a', -1);
            $this->assertFalse(true, 'The previous line should trigger a warning (Offset not contained in string)');
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            p::mb_strpos('abc', 'a', -1);
            $this->assertFalse(true, 'The previous line should trigger a warning (Offset not contained in string)');
        }
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strstr
     * @covers Patchwork\PHP\Shim\Mbstring::mb_stristr
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strrchr
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strrichr
     */
    public function testmb_strstr()
    {
        $this->assertSame('국어', mb_strstr('한국어', '국'));
        $this->assertSame('ÉJÀ', mb_stristr('DÉJÀ', 'é'));

        $this->assertSame('국어', p::mb_strstr('한국어', '국'));
        $this->assertSame('ÉJÀ', p::mb_stristr('DÉJÀ', 'é'));

        $this->assertSame('éjàdéjà', p::mb_strstr('déjàdéjà', 'é'));
        $this->assertSame('ÉJÀDÉJÀ', p::mb_stristr('DÉJÀDÉJÀ', 'é'));
        $this->assertSame('ςσb', p::mb_stristr('aςσb', 'ΣΣ'));
        $this->assertSame('éjà', p::mb_strrchr('déjàdéjà', 'é'));
        $this->assertSame('ÉJÀ', p::mb_strrichr('DÉJÀDÉJÀ', 'é'));

        $this->assertSame('d', p::mb_strstr('déjàdéjà', 'é', true));
        $this->assertSame('D', p::mb_stristr('DÉJÀDÉJÀ', 'é', true));
        $this->assertSame('a', p::mb_stristr('aςσb', 'ΣΣ', true));
        $this->assertSame('déjàd', p::mb_strrchr('déjàdéjà', 'é', true));
        $this->assertSame('DÉJÀD', p::mb_strrichr('DÉJÀDÉJÀ', 'é', true));
        $this->assertSame('Paris', p::mb_stristr('der Straße nach Paris', 'Paris'));
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_check_encoding
     */
    public function testmb_check_encoding()
    {
        $this->assertFalse(p::mb_check_encoding());
        $this->assertTrue(p::mb_check_encoding('aςσb', 'UTF8'));
        $this->assertTrue(p::mb_check_encoding('abc', 'ASCII'));
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_detect_encoding
     */
    public function testmb_detect_encoding()
    {
        $this->assertSame('ASCII', p::mb_detect_encoding('abc'));
        $this->assertSame('UTF-8', p::mb_detect_encoding('abc', 'UTF8, ASCII'));
        $this->assertSame('ISO-8859-1', p::mb_detect_encoding("\x9D", array('UTF-8', 'ASCII', 'ISO-8859-1')));
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_detect_order
     */
    public function testmb_detect_order()
    {
        $this->assertSame(array('ASCII', 'UTF-8'), p::mb_detect_order());
        $this->assertTrue(p::mb_detect_order('UTF-8, ASCII'));
        $this->assertSame(array('UTF-8', 'ASCII'), p::mb_detect_order());
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_language
     */
    public function testmb_language()
    {
        $this->assertSame('neutral', p::mb_language());
        $this->assertTrue(p::mb_language('UNI'));
        $this->assertFalse(p::mb_language('ABC'));
        $this->assertSame('uni', p::mb_language());
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_encoding_aliases
     */
    public function testmb_encoding_aliases()
    {
        $this->assertSame(array('utf8'), p::mb_encoding_aliases('UTF-8'));
        $this->assertFalse(p::mb_encoding_aliases('ASCII'));
    }

    /**
     * @covers Patchwork\PHP\Shim\Mbstring::mb_strwidth
     */
    public function testmb_strwidth()
    {
        $this->assertSame(3, p::mb_strwidth("\000実", 'UTF-8'));
        $this->assertSame(4, p::mb_strwidth('déjà'));
        $this->assertSame(4, p::mb_strwidth(utf8_decode('déjà'), 'CP1252'));
    }
}
