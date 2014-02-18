<?php 
namespace RedUNIT\Base;
use RedUNIT\Base as Base;
use RedBeanPHP\Facade as R;
use \RedBeanPHP\ModelHelper as ModelHelper;
use \RedBeanPHP\RedException as RedException;
use RedBeanPHP\OODBBean as OODBBean;
use RedBeanPHP\BeanHelper\SimpleFacadeBeanHelper as SimpleFacadeBeanHelper;
/**
 * RedUNIT_Base_Fuse
 *
 * @file    RedUNIT/Base/Fuse.php
 * @desc    Tests Fuse feature; coupling beans to models.
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license New BSD/GPLv2
 *
 * (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the New BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class Fuse extends Base
{
	/**
	 * Tests the SimpleFacadeBeanHelper factory setter.
	 *
	 * @return void
	 */
	public function testFactory()
	{
		SimpleFacadeBeanHelper::setFactoryFunction( function( $name ) {
			$model = new $name();
			$model->setNote( 'injected', 'dependency' );
			return $model;
		} );

		$bean = R::dispense( 'band' )->box();

		asrt( ( $bean instanceof \Model_Band ), TRUE );
		asrt( ( $bean->getNote('injected') ), 'dependency' );

		SimpleFacadeBeanHelper::setFactoryFunction( NULL );
	}
	
	/**
	 * Test FUSE and model formatting.
	 * 
	 * @todo move tagging tests to tag tester.
	 * 
	 * @return void
	 */
	public function testFUSE()
	{
		$toolbox = R::getToolBox();
		$adapter = $toolbox->getDatabaseAdapter();

		$blog = R::dispense( 'blog' );

		$blog->title = 'testing';
		$blog->blog  = 'tesing';

		R::store( $blog );

		$blogpost = R::load( "blog", 1 );

		$post = R::dispense( "post" );

		$post->message = "hello";

		$blog->sharedPost[] = $post;
		R::store($blog);
		
		$a = R::getAll( "select * from blog " );

		R::tag( $post, "lousy,smart" );

		asrt( implode( ',', R::tag( $post ) ), "lousy,smart" );

		R::tag( $post, "clever,smart" );

		$tagz = implode( ',', R::tag( $post ) );

		asrt( ( $tagz == "smart,clever" || $tagz == "clever,smart" ), TRUE );

		R::tag( $blog, array( "smart", "interesting" ) );

		asrt( implode( ',', R::tag( $blog ) ), "smart,interesting" );

		try {
			R::tag( $blog, array( "smart", "interesting", "lousy!" ) );

			pass();
		} catch ( RedException $e ) {
			fail();
		}

		asrt( implode( ',', R::tag( $blog ) ), "smart,interesting,lousy!" );
		asrt( implode( ",", R::tag( $blog ) ), "smart,interesting,lousy!" );

		R::untag( $blog, array( "smart", "interesting" ) );

		asrt( implode( ",", R::tag( $blog ) ), "lousy!" );

		asrt( R::hasTag( $blog, array( "lousy!" ) ), TRUE );
		asrt( R::hasTag( $blog, array( "lousy!", "smart" ) ), TRUE );
		asrt( R::hasTag( $blog, array( "lousy!", "smart" ), TRUE ), FALSE );

		R::tag( $blog, FALSE );

		asrt( count( R::tag( $blog ) ), 0 );

		R::tag( $blog, array( "funny", "comic" ) );

		asrt( count( R::tag( $blog ) ), 2 );

		R::addTags( $blog, array( "halloween" ) );

		asrt( count( R::tag( $blog ) ), 3 );
		asrt( R::hasTag( $blog, array( "funny", "commic", "halloween" ), TRUE ), FALSE );

		R::unTag( $blog, array( "funny" ) );
		R::addTags( $blog, "horror" );

		asrt( count( R::tag( $blog ) ), 3 );
		asrt( R::hasTag( $blog, array( "horror", "commic", "halloween" ), TRUE ), FALSE );

		// No double tags
		R::addTags( $blog, "horror" );

		asrt( R::hasTag( $blog, array( "horror", "commic", "halloween" ), TRUE ), FALSE );
		asrt( count( R::tag( $blog ) ), 3 );
	}
}
