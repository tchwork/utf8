<?php

namespace Patchwork\Tests\PHP\Override;

use Patchwork\PHP\Override\Iconv as p;

/**
 * @covers Patchwork\PHP\Override\Iconv::<!public>
 */
class IconvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Patchwork\PHP\Override\Iconv::iconv
     * @covers Patchwork\PHP\Override\Iconv::iconv_workaround52211
     */
    function testIconv()
    {
        if (PHP_VERSION_ID >= 50400)
        {
            $this->assertSame( false, @iconv('UTF-8', 'ISO-8859-1', 'nœud') );
        }
        else
        {
            // Expected buggy behavior. See https://bugs.php.net/52211
            $this->assertSame( 'n', @iconv('UTF-8', 'ISO-8859-1', 'nœud') );
        }

        $this->assertSame( false, @p::iconv('UTF-8', 'ISO-8859-1', 'nœud') );
        $this->assertSame( false, @p::iconv_workaround52211('UTF-8', 'ISO-8859-1', 'nœud') );
        $this->assertSame( 'nud', @p::iconv('UTF-8', 'ISO-8859-1//IGNORE', 'nœud') );
        $this->assertSame( 'nud', @p::iconv_workaround52211('UTF-8', 'ISO-8859-1//IGNORE', 'nœud') );
    }
}
