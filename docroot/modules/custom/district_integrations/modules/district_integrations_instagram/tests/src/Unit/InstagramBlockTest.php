<?php

namespace Drupal\Tests\district_integrations_instagram\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\district_integrations_instagram\Plugin\Block\InstagramRemoteContentBlock;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * Tests instagram block.
 *
 * @group doghouse
 */
class InstagramBlockTest extends UnitTestCase {

  /**
   * Test data for rendering.
   *
   * @var array
   */
  protected $testData = [
    // End point.
    'id' => 'test',
    'label' => 'Test',
    'tag' => 'test',
    // Media items.
    'items' => [
      [
        'id' => 'id1',
        'title' => 'alt1',
        'uri' => 'http://example.org/image1',
        'tag' => 'test1',
        'shortcode' => 'test1',
      ],
      [
        'id' => 'id2',
        'title' => 'alt2',
        'uri' => 'http://example.org/image2',
        'tag' => 'test2',
        'shortcode' => 'test2',
      ],
      [
        'id' => 'id3',
        'title' => 'alt3',
        'uri' => 'http://example.org/image3',
        'tag' => 'test3',
        'shortcode' => 'test3',
      ],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // We need a testing container because we have string translations.
    $container = new ContainerBuilder();
    \Drupal::setContainer($container);
    $container->set('string_translation', self::getStringTranslationStub());
  }

  /**
   * Test the block render function.
   */
  public function testBlockBuild() {
    $data = $this->testData;
    // Mock injecting dependencies for the test.
    $config = [];
    $plugin_id = '';
    $plugin_definition = NULL;

    $block = new InstagramRemoteContentBlock($config, $plugin_id, $plugin_definition);

    $output = $block->renderData($data);

    // Validations.
    $this->assertEquals('instagram_post_list', $output['post-list']['#type']);

    $outputList = $output['post-list']['#posts'];
    foreach ($this->testData['items'] as $item) {
      $test = array_shift($outputList);
      $this->assertEquals('instagram_post', $test['#type']);
      $this->assertEquals($item['title'], $test['#title']);
      $this->assertEquals($item['tag'], $test['#tag']);
      $this->assertEquals($item['uri'], $test['#imageuri']);
      $this->assertEquals('http://www.instagram.com/p/' . $item['shortcode'] . '/', $test['#posturi']);
    }
  }

}
