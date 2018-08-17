<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\cache\tests\unit;

use yii\cache\Cache;
use yii\cache\FileCache;
use yii\cache\dependencies\TagDependency;
use yii\tests\TestCase;

/**
 * @group caching
 */
class TagDependencyTest extends TestCase
{
    public function testInvalidate()
    {
        $cache = new Cache(new FileCache('@yii/tests/runtime/cache'));

        // single tag test
        $cache->set('a1', 11, 0, new TagDependency(['tags' => 't1']));
        $cache->set('a2', 12, 0, new TagDependency(['tags' => 't1']));
        $cache->set('b1', 21, 0, new TagDependency(['tags' => 't2']));
        $cache->set('b2', 22, 0, new TagDependency(['tags' => 't2']));

        $this->assertEquals(11, $cache->get('a1'));
        $this->assertEquals(12, $cache->get('a2'));
        $this->assertEquals(21, $cache->get('b1'));
        $this->assertEquals(22, $cache->get('b2'));

        TagDependency::invalidate($cache, 't1');
        $this->assertNull($cache->get('a1'));
        $this->assertNull($cache->get('a2'));
        $this->assertEquals(21, $cache->get('b1'));
        $this->assertEquals(22, $cache->get('b2'));

        TagDependency::invalidate($cache, 't2');
        $this->assertNull($cache->get('a1'));
        $this->assertNull($cache->get('a2'));
        $this->assertNull($cache->get('b1'));
        $this->assertNull($cache->get('b2'));

        // multiple tag test
        $cache->set('a1', 11, 0, new TagDependency(['tags' => ['t1', 't2']]));
        $cache->set('a2', 12, 0, new TagDependency(['tags' => 't1']));
        $cache->set('b1', 21, 0, new TagDependency(['tags' => ['t1', 't2']]));
        $cache->set('b2', 22, 0, new TagDependency(['tags' => 't2']));

        $this->assertEquals(11, $cache->get('a1'));
        $this->assertEquals(12, $cache->get('a2'));
        $this->assertEquals(21, $cache->get('b1'));
        $this->assertEquals(22, $cache->get('b2'));

        TagDependency::invalidate($cache, 't1');
        $this->assertNull($cache->get('a1'));
        $this->assertNull($cache->get('a2'));
        $this->assertNull($cache->get('b1'));
        $this->assertEquals(22, $cache->get('b2'));

        TagDependency::invalidate($cache, 't2');
        $this->assertNull($cache->get('a1'));
        $this->assertNull($cache->get('a2'));
        $this->assertNull($cache->get('b1'));
        $this->assertNull($cache->get('b2'));

        $cache->set('a1', 11, 0, new TagDependency(['tags' => ['t1', 't2']]));
        $cache->set('a2', 12, 0, new TagDependency(['tags' => 't1']));
        $cache->set('b1', 21, 0, new TagDependency(['tags' => ['t1', 't2']]));
        $cache->set('b2', 22, 0, new TagDependency(['tags' => 't2']));

        $this->assertEquals(11, $cache->get('a1'));
        $this->assertEquals(12, $cache->get('a2'));
        $this->assertEquals(21, $cache->get('b1'));
        $this->assertEquals(22, $cache->get('b2'));

        TagDependency::invalidate($cache, ['t1', 't2']);
        $this->assertNull($cache->get('a1'));
        $this->assertNull($cache->get('a2'));
        $this->assertNull($cache->get('b1'));
        $this->assertNull($cache->get('b2'));
    }
}
