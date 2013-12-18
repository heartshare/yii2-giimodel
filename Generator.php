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
                'tableSchema' => $tableSchema,
                'labels' => $this->generateLabels($tableSchema),
                'rules' => $this->generateRules($tableSchema),
                'relations' => isset($relations[$className]) ? $relations[$className] : [],
            ];
            $files[] = new CodeFile(
                Yii::getAlias('@common/models/' . $className . '.php'),
                $this->render('model.php', $params)
            );

            $cf = new CodeFile(
                Yii::getAlias('@common/models/base/' . $className . '.php'),
                $this->render('basemodel.php', $params)
            );
            $cf->operation !== CodeFile::OP_SKIP && $_POST['answers'][$cf->id] = true;

            $files[] = $cf;
        }

        return $files;
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
