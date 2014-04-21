<?php
/**
 * This is the template for generating the model class of a specified table.
 *
 * @var yii\web\View $this
 * @var yii\gii\generators\model\Generator $generator
 * @var string $tableName full table name
 * @var string $className class name
 * @var string $namespace namespace
 * @var yii\db\TableSchema $tableSchema
 * @var string[] $labels list of attribute labels (name=>label)
 * @var string[] $rules list of validation rules
 * @var array $relations list of relations (name=>relation declaration)
 */

echo "<?php\n";
?>

namespace <?= $namespace ?>;

/**
 * This is the model class for table "<?= $tableName ?>".
 *
 */
class <?= $className ?> extends <?= 'base\\' . $className . "\n" ?>
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            // add additional translations
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            // add additional rules
        ]);
    }
}
