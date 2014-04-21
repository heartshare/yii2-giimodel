<?php
/**
 * This is the template for generating the model class of a specified table.
 *
 * @var yii\web\View $this
 * @var yii\gii\generators\model\Generator $generator
 * @var string $tableName full table name
 * @var string $className class name
 * @var string $relationClassName relation class name
 * @var string $namespace namespace
 * @var string $fullClassName full class name
 * @var yii\db\TableSchema $tableSchema
 * @var string[] $labels list of attribute labels (name=>label)
 * @var string[] $rules list of validation rules
 * @var array $relations list of relations (name=>relation declaration)
 */

echo "<?php\n";
?>

namespace <?= $namespace ?>\base;

/**
 * This is the base model class for table "<?= $tableName ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}" . ($column->comment ? " {$column->comment}" : '') . "\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation):
        $classParts = explode('\\', $name); ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($relation[3]) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 * @method static <?='\\' . ltrim($relationClassName)?> find()
 * @method static <?=$fullClassName?>|null find(mixed $condition)
 * @method static <?=$fullClassName?>[] findAll(mixed $condition)
 */
abstract class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '<?= $tableName ?>';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [<?= "\n" . str_repeat('    ', 3) . implode(",\n" . str_repeat('    ', 3), $rules) . "\n" . str_repeat('    ', 2) ?>];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => '" . addslashes($label) . "',\n" ?>
<?php endforeach; ?>
        ];
    }
<?php foreach ($relations as $relation): ?>

    /**
     * @return <?= '\\' . ltrim($relationClassName) . "\n"?>
     */
    public function get<?= $relation[3] ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
}
