<?php

namespace Patchwork\Tests\Utf8;

use Patchwork\Utf8\BestFit as u;

/**
 * @covers Patchwork\Utf8\BestFit::<!public>
 */
class BestFitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Patchwork\Utf8\BestFit::fit
     */
    public function testBestFit()
    {
        $this->assertSame('', u::fit(-1, ''));
        $this->assertSame(iconv('UTF-8', 'CP1252', 'déjà vu'), u::fit(1252, 'déjà vu'));
        $this->assertSame(iconv('UTF-8',  'CP936', 'déjà vu'), u::fit(936, 'déjà vu'));
    }
}
