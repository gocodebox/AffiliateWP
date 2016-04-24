<?php
/**
 * Tests for Affiliate_WP_DB_Affiliates class
 *
 * @covers Affiliate_WP_DB_Affiliates
 * @group database
 * @group affiliates
 */
class Affiliate_DB_Tests extends WP_UnitTestCase {

	/**
	 * Test users.
	 *
	 * @access public
	 * @var array
	 */
	public $users = array();

	/**
	 * Test affiliates.
	 *
	 * @access public
	 * @var array
	 */
	public $affiliates = array();

	/**
	 * Tear down.
	 */
	public function tearDown() {
		// Reset fixtures.
		$this->users = array();
		$this->affiliates = array();

		parent::tearDown();
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_method_should_allow_searching_by_display_name() {
		$display_name = 'Foo';

		$user = $this->factory->user->create_and_get( array(
			'display_name' => $display_name
		) );

		// Add the affiliate.
		affiliate_wp()->affiliates->add( array(
			'user_id' => $user->ID
		) );

		// Get affiliates based on the search term(s).
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'search' => 'foo'
		) );

		// Assert that results were found.
		$this->assertNotEmpty( $results );

		// Assert that the result we're looking for was found.
		$this->assertNotEmpty( wp_list_filter( $results, array( 'user_id' => $user->ID ) ) );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_method_should_allow_searching_by_user_login() {
		$user_login = 'foo_bar';

		$user = $this->factory->user->create_and_get( array(
			'user_login' => $user_login
		) );

		// Add the affiliate.
		affiliate_wp()->affiliates->add( array(
			'user_id' => $user->ID
		) );

		// Get affiliates based on the search term(s).
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'search' => 'foo'
		) );

		// Assert that results were found.
		$this->assertNotEmpty( $results );

		// Assert that the result we're looking for was found.
		$this->assertNotEmpty( wp_list_filter( $results, array( 'user_id' => $user->ID ) ) );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_method_should_allow_searching_either_display_name_or_user_login() {
		$display_name = 'Bar Baz';
		$user_login   = 'foo_bar';

		$user = $this->factory->user->create( array(
			'display_name' => $display_name,
			'user_login'   => 'Scooby Doo'
		) );

		$user2 = $this->factory->user->create( array(
			'display_name' => 'Garfield',
			'user_login'   => $user_login
		) );

		$user3 = $this->factory->user->create( array(
			'display_name' => 'Nemo',
			'user_login'   => 'Dory'
		) );

		// Add affiliates.
		foreach ( array( $user, $user2, $user3 ) as $id ) {
			affiliate_wp()->affiliates->add( array(
				'user_id' => $id
			) );
		}

		// Get affiliates based on the search term(s).
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'search' => 'bar'
		) );

		// Assert two results were found.
		$this->assertCount( 2, $results );

		// Assert that the first user was found.
		$this->assertNotEmpty( wp_list_filter( $results, array( 'user_id' => $user ) ) );

		// Assert that the second user was found.
		$this->assertNotEmpty( wp_list_filter( $results, array( 'user_id' => $user2 ) ) );

		// Assert that the third user wasn't found.
		$this->assertEmpty( wp_list_filter( $results, array( 'user_id' => $user3 ) ) );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_with_integer_user_id_should_return_affiliate_for_that_user() {
		$user = $this->factory->user->create_and_get();

		// Add the affiliate.
		$affilite_id = affiliate_wp()->affiliates->add( array(
			'user_id' => $user->ID
		) );

		// Query affiliates.
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'user_id' => $user->ID
		) );

		$this->assertEqualSets( array( $affilite_id ), wp_list_pluck( $results, 'affiliate_id' ) );
		$this->assertEqualSets( array( $user->ID ), wp_list_pluck( $results, 'user_id' ) );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_with_array_of_user_ids_should_return_matching_affiliates() {
		$users = $this->factory->user->create_many( 3 );

		// Add affiliates.
		foreach ( $users as $user_id ) {
			affiliate_wp()->affiliates->add( array(
				'user_id' => $user_id
			) );
		}

		// Query affiliates.
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'user_id' => $users
		) );

		$found_users = wp_list_pluck( $results, 'user_id' );
		// Users.
		$this->assertTrue( in_array( $users[0], $found_users ) );
		$this->assertTrue( in_array( $users[1], $found_users ) );
		$this->assertTrue( in_array( $users[2], $found_users ) );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_with_integer_affiliate_id_should_return_that_affiliate() {
		$user = $this->factory->user->create_and_get();

		// Add the affiliate.
		$affiliate_id = affiliate_wp()->affiliates->add( array(
			'user_id' => $user->ID
		) );

		// Query affiliates.
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'affiliate_id' => $affiliate_id
		) );

		$this->assertEqualSets( array( $affiliate_id ), wp_list_pluck( $results, 'affiliate_id' ) );
		$this->assertEqualSets( array( $user->ID ), wp_list_pluck( $results, 'user_id' ) );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_with_array_of_affiliate_ids_should_return_matching_affiliates() {
		$users = $this->factory->user->create_many( 3 );
		$affiliates = array();

		// Add affiliates.
		foreach ( $users as $user_id ) {
			$affiliate_id = affiliate_wp()->affiliates->add( array(
				'user_id' => $user_id
			) );

			// Grab affiliates.
			$affiliates[] = $affiliate_id;
		}

		// Query affiliates.
		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'affiliate_id' => $affiliates
		) );

		$found_affiliates = wp_list_pluck( $results, 'affiliate_id' );

		$this->assertTrue( in_array( $affiliates[0], $found_affiliates ) );
		$this->assertTrue( in_array( $affiliates[1], $found_affiliates ) );
		$this->assertTrue( in_array( $affiliates[2], $found_affiliates ) );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_default_orderby_should_order_by_affiliate_id() {
		$this->_set_up_affiliates( 3 );

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'order' => 'ASC'
		) );

		// Order should be as created, 0, 1, 2.
		$this->assertEquals( $this->affiliates[0], $results[0]->affiliate_id );
		$this->assertEquals( $this->affiliates[1], $results[1]->affiliate_id );
		$this->assertEquals( $this->affiliates[2], $results[2]->affiliate_id );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_status_should_order_by_status() {
		$this->_set_up_affiliates( 1, array( 'status' => 'active' ) );
		$this->_set_up_affiliates( 1, array( 'status' => 'rejected' ) );
		$this->_set_up_affiliates( 1, array( 'status' => 'inactive' ) );

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => 'status',
			'order'   => 'ASC', // A-Z
		) );

		// Order should be alphabetical: 0 (active), 2 (inactive), 1 (rejected).
		$this->assertEquals( $this->affiliates[0], $results[0]->affiliate_id );
		$this->assertEquals( $this->affiliates[2], $results[1]->affiliate_id );
		$this->assertEquals( $this->affiliates[1], $results[2]->affiliate_id );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_date_should_order_by_registered_date() {
		$this->_set_up_affiliates( 1, array( 'date_registered' => ( time() - WEEK_IN_SECONDS ) ) );
		$this->_set_up_affiliates( 1, array( 'date_registered' => ( time() + WEEK_IN_SECONDS ) ) );
		$this->_set_up_affiliates( 1 );

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => 'date', // Default 'order' is DESC
		) );

		// Order should be newest to oldest: 2, 0, 1.
		$this->assertEquals( $this->affiliates[2], $results[0]->affiliate_id );
		$this->assertEquals( $this->affiliates[0], $results[1]->affiliate_id );
		$this->assertEquals( $this->affiliates[1], $results[2]->affiliate_id );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_name_should_order_by_user_display_name() {
		$this->users[] = $this->factory->user->create( array(
			'display_name' => 'Bravo'
		) );

		$this->users[] = $this->factory->user->create( array(
			'display_name' => 'Alpha'
		) );

		$this->users[] = $this->factory->user->create( array(
			'display_name' => 'Charlie'
		) );

		foreach ( $this->users as $user_id ) {
			$this->affiliates[] = affiliate_wp()->affiliates->add( array(
				'user_id' => $user_id
			) );
		}

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => 'name', // Default 'order' is 'DESC'
		) );

		// Order should be reverse alphabetical: 2 (Charlie), 0 (Beta), 1 (Alpha).
		$this->assertEquals( $this->affiliates[2], $results[0]->affiliate_id );
		$this->assertEquals( $this->affiliates[0], $results[1]->affiliate_id );
		$this->assertEquals( $this->affiliates[1], $results[2]->affiliate_id );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_valid_referral_status_should_order_by_that_referral_status_count() {
		$this->_set_up_affiliates( 3 );
		$referrals = array();

		// Add 1 'unpaid' referral for affiliate 1.
		affiliate_wp()->referrals->add( array(
			'affiliate_id' => $this->affiliates[0],
			'status'       => 'unpaid'
		) );

		// Add 3 'unpaid' referrals for affiliate 2.
		for ( $i = 0; $i <= 3; $i++ ) {
			affiliate_wp()->referrals->add( array(
				'affiliate_id' => $this->affiliates[1],
				'status'       => 'unpaid'
			) );
		}

		// Add 2 'paid' referrals for affiliate 3.
		for ( $i = 0; $i <= 2; $i++ ) {
			affiliate_wp()->referrals->add( array(
				'affiliate_id' => $this->affiliates[2],
				'status'       => 'paid'
			) );
		}

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => 'unpaid',
			'order'   => 'ASC', // Small to large.
		) );

		// Order should be 2 (zero unpaid), 0 (1 unpaid), 1 (3 unpaid).
		$this->assertEquals( $this->affiliates[2], $results[0]->affiliate_id );
		$this->assertEquals( $this->affiliates[0], $results[1]->affiliate_id );
		$this->assertEquals( $this->affiliates[1], $results[2]->affiliate_id );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_invalid_referral_status_should_default_to_order_by_primary_key() {
		$this->_set_up_affiliates( 3 );

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => rand_str( 15 )
		) );

		// With invalid orderby, should return ordered by affiliate_id, descending.
		$this->assertEquals( $this->affiliates[2], $results[0]->affiliate_id );
		$this->assertEquals( $this->affiliates[1], $results[1]->affiliate_id );
		$this->assertEquals( $this->affiliates[0], $results[2]->affiliate_id );
	}

	/**
	 * @covers Affiliate_WP_DB_Affiliates::get_affiliates()
	 */
	public function test_get_affiliates_orderby_earnings_should_order_by_earnings() {
		$this->_set_up_affiliates( 3 );

		affiliate_wp()->affiliates->update( $this->affiliates[0], array( 'earnings' => '20' ) );
		affiliate_wp()->affiliates->update( $this->affiliates[1], array( 'earnings' => '10' ) );
		affiliate_wp()->affiliates->update( $this->affiliates[2], array( 'earnings' => '30' ) );

		$results = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby' => 'earnings',
			'order'   => 'ASC',
		) );

		// Order should least to greatest: 1 (10), 0 (20), 2 (30).
		$this->assertEquals( $this->affiliates[1], $results[0]->affiliate_id );
		$this->assertEquals( $this->affiliates[0], $results[1]->affiliate_id );
		$this->assertEquals( $this->affiliates[2], $results[2]->affiliate_id );
	}

	/**
	 * Helper to set up user accounts and affiliates.
	 *
	 * @since 1.8
	 * @access public
	 *
	 * @param int   $count          Optional. Number of affiliates to create. Default 3.
	 * @param array $affiliate_args Optional. Arguments for adding affiliates. Default empty array.
	 */
	public function _set_up_affiliates( $count = 3, $affiliate_args = array() ) {
		$this->users = $this->factory->user->create_many( $count );

		foreach ( $this->users as $user_id ) {
			$args = array_merge( array( 'user_id' => $user_id ), $affiliate_args );

			$this->affiliates[] = affiliate_wp()->affiliates->add( $args );
		}
	}
}
