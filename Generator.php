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
use yii\helpers\Inflector;

/**
 * Class Generator
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package advmodel
 */
class Generator extends \yii\gii\generators\model\Generator
{
    public $tableName = '*';
    public $relationClassName = 'common\components\db\ActiveQuery';
    public $baseClass = 'common\components\db\ActiveRecord';
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
        if (!$this->generateRelations) {
            return [];
        }

        $db = $this->getDbConnection();

        if (($pos = strpos($this->tableName, '.')) !== false) {
            $schemaName = substr($this->tableName, 0, $pos);
        } else {
            $schemaName = '';
        }

        $relations = [];
        foreach ($db->getSchema()->getTableSchemas($schemaName) as $table) {
            $tableName = $table->name;
            $className = $this->generateClassName($tableName);

            foreach ($table->foreignKeys as $refs) {
                $refTable = $refs[0];
                unset($refs[0]);
                $fks = array_keys($refs);
                $refClassName = $this->generateClassName($refTable);
                $refClassNameShortTag = Inflector::id2camel(substr(trim($refClassName, '\\'), strlen(trim($this->ns, '\\'))), '\\');


                // skip audit fields
                if (isset($refs['created_by']) || isset($refs['deleted_by']) || isset($refs['updated_by'])) {
                    continue;
                }

                // Add relation for this table
                $link = $this->generateRelationLink(array_flip($refs));

                $relationName = $this->generateRelationName($relations, $className, $table, $fks[0], false);

                $relations[$className][$relationName] = [
                    "return \$this->hasOne($refClassName::className(), $link);",
                    $refClassName,
                    false,
                ];

                // Add relation for the referenced table
                $hasMany = false;
                if (count($table->primaryKey) > count($fks)) {
                    $hasMany = true;
                } else {
                    foreach ($fks as $key) {
                        if (!in_array($key, $table->primaryKey, true)) {
                            $hasMany = true;
                            break;
                        }
                    }
                }
                $link = $this->generateRelationLink($refs);
                $relationTag = Inflector::id2camel(substr(trim($className, '\\'), strlen(trim($this->ns, '\\'))), '\\');
                $relationName = $this->generateRelationName($relations, $refClassName, $refTable, $relationTag, $hasMany);

                // rename User->UserProfile relations to User->Profile
                if (strlen($relationName) > strlen(Inflector::pluralize($refClassNameShortTag)) && substr($relationName, 0, strlen($refClassNameShortTag)) === $refClassNameShortTag) {
                    $relationName = substr($relationName, strlen($refClassNameShortTag));
                }

                $relations[$refClassName][$relationName] = [
                    "return \$this->" . ($hasMany ? 'hasMany' : 'hasOne') . "($className::className(), $link);",
                    $className,
                    $hasMany,
                ];
            }

            if (($fks = $this->checkPivotTable($table)) === false) {
                continue;
            }
            $table0 = $fks[$table->primaryKey[0]][0];
            $table1 = $fks[$table->primaryKey[1]][0];
            $className0 = $this->generateClassName($table0);
            $className1 = $this->generateClassName($table1);

            $link = $this->generateRelationLink([$fks[$table->primaryKey[1]][1] => $table->primaryKey[1]]);
            $viaLink = $this->generateRelationLink([$table->primaryKey[0] => $fks[$table->primaryKey[0]][1]]);

            $relationTag = Inflector::id2camel(substr(trim($className0, '\\'), strlen(trim($this->ns, '\\'))), '\\');
            $relationName = $this->generateRelationName($relations, $className0, $db->getTableSchema($table0), $relationTag, true);
            $relations[$className0][$relationName] = [
                "return \$this->hasMany($className1::className(), $link)->viaTable('{$table->name}', $viaLink);",
                $className1,
                true,
            ];

            $link = $this->generateRelationLink([$fks[$table->primaryKey[0]][1] => $table->primaryKey[0]]);
            $viaLink = $this->generateRelationLink([$table->primaryKey[1] => $fks[$table->primaryKey[1]][1]]);
            $relationName = $this->generateRelationName($relations, $className1, $db->getTableSchema($table1), $relationTag, true);

            $relations[$className1][$relationName] = [
                "return \$this->hasMany($className0::className(), $link)->viaTable('{$table->name}', $viaLink);",
                $className0,
                true,
            ];
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
            if (list($prefix, $ns) = $this->tablePrefixMatches($tableName)) {
                $baseAlias = trim($baseAlias . $this->prefixMap[$prefix], '/') . '/';

                $params['namespace'] = trim($this->ns . '\\' . $ns, '\\');
            }

            $files[] = new CodeFile(
                Yii::getAlias($baseAlias . $params['className'] . '.php'),
                $this->render('model.php', $params)
            );

            $cf = new CodeFile(
                Yii::getAlias($baseAlias . 'base/' . $params['className'] . '.php'),
                $this->render('basemodel.php', $params)
            );
            // use this little hack to always preselect all base modules (if they are not explicitly unchecked)
            if (isset($_POST['preview']) && $cf->operation !== CodeFile::OP_SKIP) {
                $_POST['answers'][$cf->id] = true;
            }

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
                return [$prefix, str_replace('/', '\\', $ns)];
            }
        }
        return false;
    }

    /**
     * Generates a class name with namespace prefix from the specified table name.
     *
     * @param string $tableName the table name (which may contain schema prefix)
     * @param bool $short
     * @return string the generated class name
     */
    protected function generateClassName($tableName, $short = false)
    {
        $ns = null;
        if (list($prefix, $ns) = $this->tablePrefixMatches($tableName)) {
            $tableName = substr($tableName, strlen($prefix));
        }
        $className = parent::generateClassName($tableName);

        if (null !== $ns && $short === false) {
            $className = '\\' . trim($this->ns . '\\' . $ns, '\\') . '\\' . $className;
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
