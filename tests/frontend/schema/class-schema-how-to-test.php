<?php

namespace Yoast\WP\Free\Tests\Frontend\Schema;

use Yoast\WP\Free\Tests\TestCase;
use \WPSEO_Schema_HowTo_Double;
use \WPSEO_Schema_Context;
use \Mockery;

/**
 * Class WPSEO_Schema_HowTo_Test.
 *
 * @group schema
 *
 * @package Yoast\Tests\Frontend\Schema
 */
class WPSEO_Schema_HowTo_Test extends TestCase {
	/**
	 * @var \WPSEO_Schema_HowTo_Double
	 */
	private $instance;

	/**
	 * Test setup.
	 */
	public function setUp() {
		parent::setUp();

		$context = Mockery::mock( WPSEO_Schema_Context::class )->makePartial();

		$context->title     = 'title';
		$context->canonical = 'example.com';

		$this->instance = $this->getMockBuilder( WPSEO_Schema_HowTo_Double::class )
			->setMethods( [ 'get_main_schema_id', 'get_image_schema' ] )
			->setConstructorArgs( [ $context ] )
			->getMock();

		$this->instance->method( 'get_main_schema_id' )->willReturn( 'https://example.com/#article' );
		$this->instance->method( 'get_image_schema' )->willReturn( 'https://example.com/image.png' );
	}

	/**
	 * Tests the HowTo schema output without any steps.
	 *
	 * @covers \WPSEO_Schema_HowTo::render
	 */
	public function test_schema_output_no_steps() {
		$actual = $this->instance->render(
			[
				[ '@id' => 'OtherGraphPiece' ],
			],
			[
				'attrs' => [
					'jsonDescription' => 'description',
					'name' => 'title',
					'steps' => [],
				],
			]
		);

		$expected = [
			[
				'@id' => 'OtherGraphPiece'
			],
			[
				'@type'            => 'HowTo',
				'@id'              => 'example.com#howto-1',
				'name'             => 'title',
				'mainEntityOfPage' => [ '@id' => 'https://example.com/#article' ],
				'description'      => 'description',
			]
		];

		$this->assertEquals( $actual, $expected );
	}

	/**
	 * Tests the HowTo schema output with steps.
	 *
	 * @covers \WPSEO_Schema_HowTo::render
	 * @covers \WPSEO_Schema_HowTo::add_steps
	 * @covers \WPSEO_Schema_HowTo::add_step_description
	 */
	public function test_schema_output_with_steps() {
		$actual = $this->instance->render(
			[
				[ '@id' => 'OtherGraphPiece' ],
			],
			[
				'attrs' => [
					'jsonDescription' => 'description',
					'name'            => 'title',
					'steps'           => [
						[
							'id'       => 'step-id-1',
							'jsonName' => 'How to step 1',
							'jsonText' => 'How to step 1 description',
							'text'     => [ 'How to step 1 text line' ],
						],
					],
				],
			]
		);

		$expected = [
			[
				'@id' => 'OtherGraphPiece'
			],
			[
				'@type'            => 'HowTo',
				'@id'              => 'example.com#howto-1',
				'name'             => 'title',
				'mainEntityOfPage' => [ '@id' => 'https://example.com/#article' ],
				'description'      => 'description',
				'step'             => [
					[
						'@type' => 'HowToStep',
						'url'   => 'example.com#step-id-1',
						'name'  => 'How to step 1',
						'itemListElement' => [
							[
								'@type' => 'HowToDirection',
								'text'  => 'How to step 1 description',
							]
						],
					],
				],
			]
		];

		$this->assertEquals( $actual, $expected );
	}

	/**
	 * Tests the HowTo schema output with steps and images.
	 *
	 * @covers \WPSEO_Schema_HowTo::render
	 * @covers \WPSEO_Schema_HowTo::add_steps
	 * @covers \WPSEO_Schema_HowTo::add_step_description
	 * @covers \WPSEO_Schema_HowTo::add_step_image
	 */
	public function test_schema_output_with_steps_and_image() {
		$actual = $this->instance->render(
			[
				[ '@id' => 'OtherGraphPiece' ],
			],
			[
				'attrs' => [
					'jsonDescription' => 'description',
					'name'            => 'title',
					'steps'           => [
						[
							'id'       => 'step-id-1',
							'jsonName' => 'How to step 1',
							'jsonText' => 'How to step 1 description',
							'text'     => [
								'How to step 1 text line',
								[
									'type'   => 'img',
									'key'    => 1,
									'ref'    => null,
									'_owner' => null,
									'props'  => [
										'alt' => 'alt text',
										'src' => 'https://example.com/image.png',
									],
								],
							],
						],
					],
				],
			]
		);

		$expected = [
			[
				'@id' => 'OtherGraphPiece'
			],
			[
				'@type'            => 'HowTo',
				'@id'              => 'example.com#howto-1',
				'name'             => 'title',
				'mainEntityOfPage' => [ '@id' => 'https://example.com/#article' ],
				'description'      => 'description',
				'step'             => [
					[
						'@type' => 'HowToStep',
						'url'   => 'example.com#step-id-1',
						'name'  => 'How to step 1',
						'image' => 'https://example.com/image.png',
						'itemListElement' => [
							[
								'@type' => 'HowToDirection',
								'text'  => 'How to step 1 description',
							]
						],
					],
				],
			]
		];

		$this->assertEquals( $actual, $expected );
	}

	/**
	 * Tests the HowTo schema output when no jsonText (description) is provided in the step data.
	 *
	 * In case no description is provided, the HowToStep schema output should have a text attribute containing the description text,
	 * instead of a name and itemListElement attribute.
	 *
	 * @covers \WPSEO_Schema_HowTo::render
	 * @covers \WPSEO_Schema_HowTo::add_steps
	 */
	public function test_schema_output_step_with_no_description() {
		$actual = $this->instance->render(
			[
				[ '@id' => 'OtherGraphPiece' ],
			],
			[
				'attrs' => [
					'jsonDescription' => 'description',
					'name'            => 'title',
					'steps'           => [
						[
							'id'       => 'step-id-1',
							'jsonName' => 'How to step 1',
						],
					],
				],
			]
		);

		$expected = [
			[
				'@id' => 'OtherGraphPiece'
			],
			[
				'@type'            => 'HowTo',
				'@id'              => 'example.com#howto-1',
				'name'             => 'title',
				'mainEntityOfPage' => [ '@id' => 'https://example.com/#article' ],
				'description'      => 'description',
				'step'             => [
					[
						'@type' => 'HowToStep',
						'url'   => 'example.com#step-id-1',
						'text'  => 'How to step 1',
					],
				],
			]
		];

		$this->assertEquals( $actual, $expected );
	}

	/**
	 * Tests the HowTo schema step output when no jsonName (title) is provided in the step data.
	 *
	 * In case no description is provided, the HowToStep schema output should have a text attribute containing the title
	 * text, instead of a name and itemListElement attribute.
	 *
	 * @covers \WPSEO_Schema_HowTo::render
	 * @covers \WPSEO_Schema_HowTo::add_steps
	 * @covers \WPSEO_Schema_HowTo::add_step_description
	 */
	public function test_schema_output_step_with_no_title() {
		$actual = $this->instance->render(
			[
				[ '@id' => 'OtherGraphPiece' ],
			],
			[
				'attrs' => [
					'jsonDescription' => 'description',
					'name'            => 'title',
					'steps'           => [
						[
							'id'       => 'step-id-1',
							'jsonText' => 'How to step 1 description.',
							'text' => [
								'How to step 1 description.',
							],
						],
					],
				],
			]
		);

		$expected = [
			[
				'@id' => 'OtherGraphPiece'
			],
			[
				'@type'            => 'HowTo',
				'@id'              => 'example.com#howto-1',
				'name'             => 'title',
				'mainEntityOfPage' => [ '@id' => 'https://example.com/#article' ],
				'description'      => 'description',
				'step'             => [
					[
						'@type' => 'HowToStep',
						'url'   => 'example.com#step-id-1',
						'text'  => 'How to step 1 description.',
					],
				],
			]
		];

		$this->assertEquals( $actual, $expected );
	}

	/**
	 * Tests the HowTo schema step output when no jsonName (title) is provided in the step data and an image is added
	 * in the description.
	 *
	 * @covers \WPSEO_Schema_HowTo::render
	 * @covers \WPSEO_Schema_HowTo::add_steps
	 * @covers \WPSEO_Schema_HowTo::add_step_image
	 */
	public function test_schema_output_step_with_no_title_and_with_an_image() {
		$actual = $this->instance->render(
			[
				[ '@id' => 'OtherGraphPiece' ],
			],
			[
				'attrs' => [
					'jsonDescription' => 'description',
					'name'            => 'title',
					'steps'           => [
						[
							'id'       => 'step-id-1',
							'jsonText' => '',
							'text' => [
								[
									'type'   => 'img',
									'key'    => 1,
									'ref'    => null,
									'_owner' => null,
									'props'  => [
										'alt' => 'alt text',
										'src' => 'https://example.com/image.png',
									],
								],
							],
						],
					],
				],
			]
		);

		$expected = [
			[
				'@id' => 'OtherGraphPiece'
			],
			[
				'@type'            => 'HowTo',
				'@id'              => 'example.com#howto-1',
				'name'             => 'title',
				'mainEntityOfPage' => [ '@id' => 'https://example.com/#article' ],
				'description'      => 'description',
				'step'             => [
					[
						'@type' => 'HowToStep',
						'url'   => 'example.com#step-id-1',
						'image' => 'https://example.com/image.png',
						'text'  => '',
					],
				],
			]
		];

		$this->assertEquals( $actual, $expected );
	}

	/**
	 * Tests the HowTo schema step output when no jsonName (title), jsonText (description) and image are provided.
	 *
	 * @covers \WPSEO_Schema_HowTo::render
	 * @covers \WPSEO_Schema_HowTo::add_steps
	 * @covers \WPSEO_Schema_HowTo::add_step_image
	 */
	public function test_schema_output_step_with_no_content() {
		$actual = $this->instance->render(
			[
				[ '@id' => 'OtherGraphPiece' ],
			],
			[
				'attrs' => [
					'jsonDescription' => 'description',
					'name'            => 'title',
					'steps'           => [
						[
							'id' => 'step-id-1',
						],
					],
				],
			]
		);

		$expected = [
			[
				'@id' => 'OtherGraphPiece'
			],
			[
				'@type'            => 'HowTo',
				'@id'              => 'example.com#howto-1',
				'name'             => 'title',
				'mainEntityOfPage' => [ '@id' => 'https://example.com/#article' ],
				'description'      => 'description',
			]
		];

		$this->assertEquals( $actual, $expected );
	}

	/**
	 * Tests the HowTo schema step output when no jsonName (title), jsonText (description) and image are provided.
	 *
	 * @covers \WPSEO_Schema_HowTo::render
	 * @covers \WPSEO_Schema_HowTo::add_steps
	 * @covers \WPSEO_Schema_HowTo::add_step_description
	 * @covers \WPSEO_Schema_HowTo::add_duration
	 */
	public function test_schema_output_step_with_duration() {
		$actual = $this->instance->render(
			[
				[ '@id' => 'OtherGraphPiece' ],
			],
			[
				'attrs' => [
					'jsonDescription' => 'description',
					'name'            => 'title',
					'hasDuration'     => true,
					'days'            => 1,
					'hours'           => 12,
					'minutes'         => 30,
					'steps'           => [
						[
							'id'       => 'step-id-1',
							'jsonName' => 'How to step 1',
							'jsonText' => 'How to step 1 description',
							'text'     => [
								'How to step 1 description',
							],
						],
					],
				],
			]
		);

		$expected = [
			[
				'@id' => 'OtherGraphPiece'
			],
			[
				'@type'            => 'HowTo',
				'@id'              => 'example.com#howto-1',
				'name'             => 'title',
				'mainEntityOfPage' => [ '@id' => 'https://example.com/#article' ],
				'description'      => 'description',
				'totalTime'        => 'P1DT12H30M',
				'step'             => [
					[
						'@type' => 'HowToStep',
						'url'   => 'example.com#step-id-1',
						'name'  => 'How to step 1',
						'itemListElement' => [
							[
								'@type' => 'HowToDirection',
								'text'  => 'How to step 1 description',
							]
						],
					],
				],
			]
		];

		$this->assertEquals( $actual, $expected );
	}

	/**
	 * Tests the is_needed function.
	 *
	 * @covers \WPSEO_Schema_HowTo::is_needed
	 */
	public function test_is_needed() {
		$this->assertFalse( $this->instance->is_needed() );
	}

	/**
	 * Tests the generate function.
	 *
	 * @covers \WPSEO_Schema_HowTo::generate
	 */
	public function test_generate() {
		$this->assertEquals( $this->instance->generate(), [] );
	}
}