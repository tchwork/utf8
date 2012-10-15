<?php

namespace Patchwork\Tests\PHP\Shim;

use Patchwork\PHP\Shim\Iconv as p;

/**
 * @covers Patchwork\PHP\Shim\Iconv::<!public>
 */
class IconvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Patchwork\PHP\Shim\Iconv::iconv
     * @covers Patchwork\PHP\Shim\Iconv::iconv_workaround52211
     */
    function testIconv()
    {
        if (PHP_VERSION_ID >= 50400)
        {
            $this->assertSame( false, @iconv('UTF-8', 'ISO-8859-1', 'nœud') );
            $this->assertSame( false, @iconv('UTF-8', 'ISO-8859-1//IGNORE', 'nœud') );
        }
        else
        {
            // Expected buggy behavior. See https://bugs.php.net/52211
            $this->assertSame( 'n',   @iconv('UTF-8', 'ISO-8859-1', 'nœud') );
            $this->assertSame( 'nud', @iconv('UTF-8', 'ISO-8859-1//IGNORE', 'nœud') );
        }

        $this->assertSame( false, @p::iconv_workaround52211('UTF-8', 'ISO-8859-1', 'nœud') );
        $this->assertSame( false, @p::iconv_workaround52211('UTF-8', 'ISO-8859-1//IGNORE', 'nœud') );

        $this->assertSame( false, @p::iconv('UTF-8', 'ISO-8859-1', 'nœud') );
        $this->assertSame( false, @p::iconv('UTF-8', 'ISO-8859-1//IGNORE', 'nœud') );
    }

    /**
     * @covers Patchwork\PHP\Shim\Iconv::iconv_strpos
     */
    function testIconvStrPos()
    {
        $this->assertSame( 1, p::iconv_strpos('11--', '1-', 0, 'UTF-8') );
        $this->assertSame( 2, p::iconv_strpos('-11--', '1-', 0, 'UTF-8') );
    }

    /**
     * @covers Patchwork\PHP\Shim\Iconv::iconv_substr
     */
    function testIconvSubstr()
    {
        $this->assertSame( 'x', p::iconv_substr('x', 0, 1, 'UTF-8') );
    }

    /**
     * @covers Patchwork\PHP\Shim\Iconv::iconv_mime_encode
     */
    function testIconvMimeEncode()
    {
        $text = "\xE3\x83\x86\xE3\x82\xB9\xE3\x83\x88\xE3\x83\x86\xE3\x82\xB9\xE3\x83\x88";
        $options = array(
            'scheme' => 'Q',
            'input-charset' => 'UTF-8',
            'output-charset' => 'UTF-8',
            'line-length' => 30,
        );

        $this->assertSame(
            "Subject: =?UTF-8?Q?=E3=83=86?=\r\n =?UTF-8?Q?=E3=82=B9?=\r\n =?UTF-8?Q?=E3=83=88?=\r\n =?UTF-8?Q?=E3=83=86?=\r\n =?UTF-8?Q?=E3=82=B9?=\r\n =?UTF-8?Q?=E3=83=88?=",
            p::iconv_mime_encode('Subject', $text, $options)
        );
    }

    /**
     * @covers Patchwork\PHP\Shim\Iconv::iconv_mime_decode
     */
    function testIconvMimeDecode()
    {
        $this->assertSame( 'Legal encoded-word: * .', p::iconv_mime_decode("Legal encoded-word: =?utf-8?B?Kg==?= ."));
        $this->assertSame( 'Legal encoded-word: * .', p::iconv_mime_decode("Legal encoded-word: =?utf-8?Q?*?= ."));
        $this->assertSame( 'Illegal encoded-word:  .', p::iconv_mime_decode("Illegal encoded-word: =?utf-8?B?".chr(0xA1)."?= ."));
        $this->assertSame( 'Illegal encoded-word:  .', p::iconv_mime_decode("Illegal encoded-word: =?utf-8?Q?".chr(0xA1)."?= .", ICONV_MIME_DECODE_CONTINUE_ON_ERROR));
        $this->assertSame( 'Illegal encoded-word:  .', @p::iconv_mime_decode("Illegal encoded-word: =?utf-8?Q?".chr(0xA1)."?= ."));

        try
        {
            p::iconv_mime_decode("Illegal encoded-word: =?utf-8?Q?".chr(0xA1)."?= .");
            $this->assertFalse( true );
        }
        catch (\PHPUnit_Framework_Error_Notice $e)
        {
        }
    }

    /**
     * @covers Patchwork\PHP\Shim\Iconv::iconv_mime_decode_headers
     */
    function testIconvMimeDecodeHeaders()
    {
        $headers = <<<HEADERS
From: =?UTF-8?B?PGZvb0BleGFtcGxlLmNvbT4=?=
Subject: =?ks_c_5601-1987?B?UkU6odk=?=
X-Foo: =?ks_c_5601-1987?B?UkU6odk=?= Foo
X-Bar: =?ks_c_5601-1987?B?UkU6odk=?= =?UTF-8?Q?Foo?=
To: <test@example.com>
HEADERS;

        $result = array(
            'From' => '<foo@example.com>',
            'Subject' => '=?ks_c_5601-1987?B?UkU6odk=?=',
            'X-Foo' => '=?ks_c_5601-1987?B?UkU6odk=?= Foo',
            'X-Bar' => '=?ks_c_5601-1987?B?UkU6odk=?=Foo',
            'To' => '<test@example.com>',
        );

        $this->assertSame( $result, iconv_mime_decode_headers($headers, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8') );
    }
}
