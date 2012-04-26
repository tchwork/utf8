<?php

namespace Patchwork\Tests\Utf8;

use Patchwork\Utf8\Convert as u;

/**
 * @covers Patchwork\Utf8\Convert::<!public>
 */
class ConvertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Patchwork\Utf8\Convert::bestFit
     */
    function testBestFit()
    {
        $this->assertSame( '', u::bestFit(-1, '') );
        $this->assertSame( iconv('UTF-8', 'CP1252', 'déjà vu'), u::bestFit(1252, 'déjà vu') );
        $this->assertSame( iconv('UTF-8', 'CP936', 'déjà vu'), u::bestFit(936, 'déjà vu') );
    }
}
