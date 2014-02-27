<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 17.12.13
 */

namespace opus\giimodel;

use yii\db\ActiveQuery;
use yii\gii\CodeFile;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Class Generator
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package advmodel
 */
class Generator extends \yii\gii\generators\model\Generator
{
    public $tableName = '*';
    public $relationClassName = 'common\components\ActiveQuery';
    public $baseClass = 'common\components\ActiveRecord';
    public $ns = 'common\models';

    /**
     * Maps a table prefix to a namespace (e.g. c_ => \common\models\core)
     *
     * @var array
     */
    public $prefixMap = [];

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Advanced Model Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'Advanced model generator with base classes';
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['model.php', 'basemodel.php'];
    }

    /**
     * @inheritdoc
     */
    public function validateNamespace()
    {
        if (empty($this->prefixMap)) {
            parent::validateNamespace();
        }
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['relationClassName']);
    }

    /**
     * @inheritdoc
     */
    protected function generateRelations()
    {
        $relations = parent::generateRelations();

        foreach ($relations as $className => $classRelations) {
            foreach ($classRelations as $relationName => $relation) {
                $nameParts = explode('\\', $relationName);
                $relations[$className][$relationName][3] = $nameParts[count($nameParts) - 1];
            }
        }
        return $relations;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        $relations = $this->generateRelations();

        $db = $this->getDbConnection();
        foreach ($this->getTableNames() as $tableName) {
            $fullClassName = $this->generateClassName($tableName);
            $parts = explode('\\', $fullClassName);
            $className = $parts[count($parts) - 1];
            $tableSchema = $db->getTableSchema($tableName);
            $params = [
                'tableName' => $tableName,
                'className' => $className,
                'fullClassName' => $fullClassName,
                'relationClassName' => $this->relationClassName,
                'namespace' => $this->ns,
                'tableSchema' => $tableSchema,
                'labels' => $this->generateLabels($tableSchema),
                'rules' => $this->generateRules($tableSchema),
                'relations' => isset($relations[$fullClassName]) ? $relations[$fullClassName] : [],
            ];

            $baseAlias = sprintf('@%s/', str_replace('\\', '/', $this->ns));

            if ($prefix = $this->tablePrefixMatches($tableName)) {
                $ns = trim($this->prefixMap[$prefix], '\\');
                $baseAlias = sprintf('@%s/', str_replace('\\', '/', $ns));
                $params['namespace'] = $ns;
            }

            $files[] = new CodeFile(
                Yii::getAlias($baseAlias . $params['className'] . '.php'),
                $this->render('model.php', $params)
            );

            $cf = new CodeFile(
                Yii::getAlias($baseAlias . 'base/' . $params['className'] . '.php'),
                $this->render('basemodel.php', $params)
            );
            $cf->operation !== CodeFile::OP_SKIP && $_POST['answers'][$cf->id] = true;

            $files[] = $cf;
        }

        return $files;
    }

    /**
     * @param $tableName
     * @return bool|string
     */
    protected function tablePrefixMatches($tableName)
    {
        foreach ($this->prefixMap as $prefix => $ns) {
            if (preg_match(sprintf('/^%s/', preg_quote($prefix)), $tableName)) {
                return $prefix;
            }
        }
        return false;
    }

    /**
     * Generates a class name with namespace prefix from the specified table name.
     *
     * @param string $tableName the table name (which may contain schema prefix)
     * @return string the generated class name
     */
    protected function generateClassName($tableName)
    {
        $className = parent::generateClassName($tableName);
        if ($prefix = $this->tablePrefixMatches($tableName)) {
            $ns = trim($this->prefixMap[$prefix], '\\');
            $className = '\\' . $ns . '\\' . substr($className, strlen($prefix) - 1);
        }
        return $className;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(),
            [
                'tableName' => 'Table pattern',
                'relationClassName' => 'Relation class',
            ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(),
            [
                [['relationClassName'], 'validateClass', 'params' => ['extends' => ActiveQuery::className()]],
            ]);
    }
} 
