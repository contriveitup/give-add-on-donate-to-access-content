<?php
use PHPUnit\Framework\TestCase;
use DTAC\Frontend\Functions;

class FunctionsTest extends TestCase {


	public function testCheckAccessIsNotEmpty() {
		$functions = new Functions();

		$normal_content = 'Normal Content ';
		$this->assertEquals( 'Normal Content', $functions->dtac_give_check_access( $normal_content ) );
	}
}