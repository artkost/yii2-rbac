<?php

namespace artkost\rbac;

use artkost\rbac\models\RbacDefinition;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;

/**
 * This class finds rules definitions in other modules
 * and instantiates it
 * @package app\modules\rbac
 */
class DefinitionManager extends Component
{
    const DEFINITION_FILE = 'rules.php';

    const CACHE_KEY = 'rbacModuleDefinition';
    const CACHE_TAG = 'rbacModuleDefinition';

    public $cacheDuration = 604800;

    /**
     * @var RbacDefinition[]
     */
    protected $definitions = [];

    public function init()
    {
        parent::init();

        $this->createDefinitions();
    }

    protected function createDefinitions()
    {
        foreach (Yii::$app->getModules() as $id => $config) {
            $this->createDefinition($id, $this->getDefinitionConfig($id, $config));
        }
    }

    /**
     * @return models\RbacDefinition[]
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    public function getDefinitionConfig($id, $config = [])
    {
        $module = Yii::$app->getModule($id);

        return $module->getBasePath() . DIRECTORY_SEPARATOR . self::DEFINITION_FILE;
    }

    /**
     * @param $id
     * @param $path
     * @return RbacDefinition|bool
     */
    protected function createDefinition($id, $path)
    {
        if (!isset($this->definitions[$id])) {
            $rules = [];

            $cached = $this->getCache()->get(self::CACHE_KEY . ':' . $path);
            $dependency = Yii::createObject(TagDependency::className(), ['tags' => [self::CACHE_TAG]]);

            if ($cached) {
                $rules = $cached;
            } else {
                if (is_array($path)) {
                    $rules = $path;
                } elseif (file_exists($path)) {
                    $rules = include $path;
                }
            }

            if (is_array($rules) && !empty($rules)) {
                $rules['module'] = $id;
                $this->getCache()->set(self::CACHE_KEY . ':' . $path, $rules, $this->cacheDuration, $dependency);
                $this->definitions[$id] =  new RbacDefinition($rules);
            }
        }

        return isset($this->definitions[$id]) ? $this->definitions[$id] : false;
    }

    public function refreshDefinitions()
    {
        TagDependency::invalidate($this->getCache(), [self::CACHE_TAG]);
    }

    /**
     * @return \yii\caching\Cache
     */
    protected function getCache()
    {
        return Yii::$app->cache;
    }
}
