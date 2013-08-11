<?php
/**
 * RedUNIT_Base_Finding
 *
 * @file    RedUNIT/Base/Finding.php
 * @desc    Tests finding beans.
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license New BSD/GPLv2
 *
 * (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the New BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */

class RedUNIT_Base_Finding extends RedUNIT_Base
{
	public function unnamed0()
	{
		$toolbox = R::$toolbox;
		$adapter = $toolbox->getDatabaseAdapter();
		$writer  = $toolbox->getWriter();
		$redbean = $toolbox->getRedBean();
		$pdo     = $adapter->getDatabase();

		$a = new RedBean_AssociationManager( $toolbox );

		$page = $redbean->dispense( "page" );

		$page->name = "John's page";

		$idpage = $redbean->store( $page );

		$page2 = $redbean->dispense( "page" );

		$page2->name = "John's second page";

		$idpage2 = $redbean->store( $page2 );

		$a->associate( $page, $page2 );

		$pageOne = $redbean->dispense( "page" );

		$pageOne->name = "one";

		$pageMore = $redbean->dispense( "page" );

		$pageMore->name = "more";

		$pageEvenMore = $redbean->dispense( "page" );

		$pageEvenMore->name = "evenmore";

		$pageOther = $redbean->dispense( "page" );

		$pageOther->name = "othermore";

		set1toNAssoc( $a, $pageOther, $pageMore );
		set1toNAssoc( $a, $pageOne, $pageMore );
		set1toNAssoc( $a, $pageOne, $pageEvenMore );

		asrt( count( $redbean->find( "page", array(), " name LIKE '%more%' ", array() ) ), 3 );
		asrt( count( $redbean->find( "page", array(), " name LIKE :str ", array( ":str" => '%more%' ) ) ), 3 );
		asrt( count( $redbean->find( "page", array(), array( " name LIKE :str ", array( ":str" => '%more%' ) ) ) ), 3 );
		asrt( count( $redbean->find( "page", array(), " name LIKE :str ", array( ":str" => '%mxore%' ) ) ), 0 );
		asrt( count( $redbean->find( "page", array( "id" => array( 2, 3 ) ) ) ), 2 );

		$bean = $redbean->dispense( "wine" );

		$bean->name = "bla";

		for ( $i = 0; $i < 10; $i++ ) {
			$redbean->store( $bean );
		}

		$redbean->find( "wine", array( "id" => 5 ) ); //  Finder:where call RedBean_OODB::convertToBeans

		$bean2 = $redbean->load( "anotherbean", 5 );

		asrt( $bean2->id, 0 );

		$keys = $adapter->getCol( "SELECT id FROM page WHERE " . $writer->esc( 'name' ) . " LIKE '%John%'" );

		asrt( count( $keys ), 2 );

		$pages = $redbean->batch( "page", $keys );

		asrt( count( $pages ), 2 );

		$p = R::findLast( 'page' );

		pass();

		$row = R::getRow( 'select * from page ' );

		asrt( is_array( $row ), true );

		asrt( isset( $row['name'] ), true );

		// Test findAll -- should not throw an exception
		asrt( count( R::findAll( 'page' ) ) > 0, true );
		asrt( count( R::findAll( 'page', ' ORDER BY id ' ) ) > 0, true );

		$beans = R::findOrDispense( "page" );

		asrt( count( $beans ), 6 );

		asrt( is_null( R::findLast( 'nothing' ) ), true );

		try {
			R::find( 'bean', ' id > 0 ', 'invalid bindings argument' );
			fail();
		} catch ( RedBean_Exception_Security $exception ) {
			pass();
		}
	}
}
