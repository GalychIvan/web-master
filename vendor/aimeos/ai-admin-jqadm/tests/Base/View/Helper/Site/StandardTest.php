<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2022
 */


namespace Aimeos\Base\View\Helper\Site;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;


	protected function setUp() : void
	{
		$view = new \Aimeos\Base\View\Standard();
		$view->pageSiteItem = new TestSite( '1.', 'label1' );

		$this->object = new \Aimeos\Base\View\Helper\Site\Standard( $view );
	}


	protected function tearDown() : void
	{
		$this->object = null;
	}


	public function testTransform()
	{
		$this->assertInstanceOf( '\\Aimeos\\Base\\View\\Helper\\Site\\Iface', $this->object->transform() );
	}


	public function testCan()
	{
		$this->assertEquals( true, $this->object->transform()->can( '1.' ) );
		$this->assertEquals( true, $this->object->transform()->can( '1.2.' ) );
		$this->assertEquals( false, $this->object->transform()->can( '3.' ) );
	}


	public function testLabel()
	{
		$this->assertEquals( 'label1', $this->object->transform()->label() );
	}


	public function testMatch()
	{
		$this->assertEquals( 'label1', $this->object->transform()->match( '1.' ) );
	}


	public function testReadonly()
	{
		$this->assertEquals( 'readonly', $this->object->transform()->readonly( '1.2.' ) );
	}


	public function testSiteid()
	{
		$this->assertEquals( '1.', $this->object->transform()->siteid() );
	}
}


class TestSite
{
	private $id;
	private $label;

	public function __construct( $id, $label )
	{
		$this->id = $id;
		$this->label = $label;
	}

	public function getSiteId()
	{
		return $this->id;
	}

	public function getLabel()
	{
		return $this->label;
	}
}
