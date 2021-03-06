<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @copyright 2010-2013 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Descriptor\Filter;

use \Mockery as m;
use phpDocumentor\Descriptor\Analyzer;

/**
 * Tests the functionality for the StripInternal class.
 */
class StripInternalTest extends \PHPUnit_Framework_TestCase
{
    /** @var Analyzer|m\Mock */
    protected $analyzerMock;

    /** @var StripInternal $fixture */
    protected $fixture;

    /**
     * Creates a new (empty) fixture object.
     */
    protected function setUp()
    {
        $this->analyzerMock = m::mock('phpDocumentor\Descriptor\Analyzer');
        $this->fixture = new StripInternal($this->analyzerMock);
    }

    /**
     * @covers phpDocumentor\Descriptor\Filter\StripInternal::__construct
     */
    public function testAnalyzerIsSetUponConstruction()
    {
        $this->assertAttributeSame($this->analyzerMock, 'analyzer', $this->fixture);
    }

    /**
     * @covers phpDocumentor\Descriptor\Filter\StripInternal::filter
     */
    public function testStripsInternalTagFromDescription()
    {
        $this->analyzerMock->shouldReceive('isVisibilityAllowed')->andReturn(false);
        $descriptor = m::mock('phpDocumentor\Descriptor\DescriptorAbstract');
        $descriptor->shouldReceive('getTags->get')->with('internal')->andReturn(null);

        $descriptor->shouldReceive('getDescription')->andReturn('without {@internal blabla }}internal tag');
        $descriptor->shouldReceive('setDescription')->with('without internal tag');

        $this->assertSame($descriptor, $this->fixture->filter($descriptor));
    }

    /**
     * @covers phpDocumentor\Descriptor\Filter\StripInternal::filter
     */
    public function testStripsInternalTagFromDescriptionIfTagDescriptionContainsBraces()
    {
        $this->analyzerMock->shouldReceive('isVisibilityAllowed')->andReturn(false);
        $descriptor = m::mock('phpDocumentor\Descriptor\DescriptorAbstract');
        $descriptor->shouldReceive('getTags->get')->with('internal')->andReturn(null);

        $descriptor->shouldReceive('getDescription')->andReturn('without {@internal bla{bla} }}internal tag');
        $descriptor->shouldReceive('setDescription')->with('without internal tag');

        $this->assertSame($descriptor, $this->fixture->filter($descriptor));
    }

    /**
     * @covers phpDocumentor\Descriptor\Filter\StripInternal::filter
     */
    public function testResolvesInternalTagFromDescriptionIfParsePrivateIsTrue()
    {
        $this->analyzerMock->shouldReceive('isVisibilityAllowed')->andReturn(true);
        $descriptor = m::mock('phpDocumentor\Descriptor\DescriptorAbstract');

        $descriptor->shouldReceive('getDescription')->andReturn('without {@internal blabla }}internal tag');
        $descriptor->shouldReceive('setDescription')->with('without blabla internal tag');

        $this->assertSame($descriptor, $this->fixture->filter($descriptor));
    }

    /**
     * @covers phpDocumentor\Descriptor\Filter\StripInternal::filter
     */
    public function testRemovesDescriptorIfTaggedAsInternal()
    {
        $this->analyzerMock->shouldReceive('isVisibilityAllowed')->andReturn(false);

        $descriptor = m::mock('phpDocumentor\Descriptor\DescriptorAbstract');
        $descriptor->shouldReceive('getDescription');
        $descriptor->shouldReceive('setDescription');
        $descriptor->shouldReceive('getTags->get')->with('internal')->andReturn(true);

        $this->assertNull($this->fixture->filter($descriptor));
    }

    /**
     * @covers phpDocumentor\Descriptor\Filter\StripInternal::filter
     */
    public function testKeepsDescriptorIfTaggedAsInternalAndParsePrivateIsTrue()
    {
        $this->analyzerMock->shouldReceive('isVisibilityAllowed')->andReturn(true);

        $descriptor = m::mock('phpDocumentor\Descriptor\DescriptorAbstract');
        $descriptor->shouldReceive('getDescription');
        $descriptor->shouldReceive('setDescription');
        $descriptor->shouldReceive('getTags->get')->with('internal')->andReturn(true);

        $this->assertSame($descriptor, $this->fixture->filter($descriptor));
    }

    /**
     * @covers phpDocumentor\Descriptor\Filter\StripInternal::filter
     */
    public function testDescriptorIsUnmodifiedIfThereIsNoInternalTag()
    {
        $this->analyzerMock->shouldReceive('isVisibilityAllowed')->andReturn(true);

        $descriptor = m::mock('phpDocumentor\Descriptor\DescriptorAbstract');
        $descriptor->shouldReceive('getDescription');
        $descriptor->shouldReceive('setDescription');
        $descriptor->shouldReceive('getTags->get')->with('internal')->andReturn(false);

        // we clone the descriptor so its references differ; if something changes in the descriptor then
        // the $descriptor variable and the returned clone will differ
        $this->assertEquals($descriptor, $this->fixture->filter(clone $descriptor));
    }
}
