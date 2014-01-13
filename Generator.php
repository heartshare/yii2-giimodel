<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 17.12.13
 */

namespace opus\giimodel;
use Yii;
use yii\gii\CodeFile;
use yii\helpers\ArrayHelper;

/**
 * Class Generator
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package advmodel
 */
class Generator extends \yii\gii\generators\model\Generator
{
    public $tableName = '*';
    public $baseClass = 'common\components\ActiveRecord';
    public $ns = 'common\models';
<<<<<<< HEAD

    /**
     * Maps a table prefix to a namespace (e.g. c_ => \common\models\core)
     * @var array
     */
    public $prefixMap = null;
=======
>>>>>>> b6270430ec969b8cc2d5b6463ca482f2710cd563

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
    public function generate()
    {
        $files = [];
        $relations = $this->generateRelations();
        $db = $this->getDbConnection();
        foreach ($this->getTableNames() as $tableName) {
            $className = $this->generateClassName($tableName);
            $tableSchema = $db->getTableSchema($tableName);
            $params = [
                'tableName' => $tableName,
                'className' => $className,
                'namespace' => $this->ns,
                'tableSchema' => $tableSchema,
                'labels' => $this->generateLabels($tableSchema),
                'rules' => $this->generateRules($tableSchema),
                'relations' => isset($relations[$className]) ? $relations[$className] : [],
            ];

            $baseAlias = '@common/models/';

            if (isset($this->prefixMap) && ($prefix = $this->tablePrefixMatches($tableName)))
            {
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

    protected function tablePrefixMatches($tableName)
    {
        foreach ($this->prefixMap as $prefix => $ns)
        {
            if (preg_match(sprintf('/^%s/', preg_quote($prefix)), $tableName))
            {
                return $prefix;
            }
        }
        return false;
    }

    /**
     * Generates a class name from the specified table name.
     * @param string $tableName the table name (which may contain schema prefix)
     * @return string the generated class name
     */
    protected function generateClassName($tableName)
    {
        if ($prefix = $this->tablePrefixMatches($tableName))
        {
            $tableName = substr($tableName, strlen($prefix));
        }
        return parent::generateClassName($tableName);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'tableName' => 'Table pattern'
        ]);
    }
} 
