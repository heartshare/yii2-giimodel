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

namespace <?= $namespace ?>\base;

/**
 * This is the base model class for table "<?= $tableName ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation):
        $classParts = explode('\\', $name); ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($relation[3]) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
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
		return [<?= "\n\t\t\t" . implode(",\n\t\t\t", $rules) . "\n\t\t" ?>];
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
	 * @return \yii\db\ActiveRelation
	 */
	public function get<?= $relation[3] ?>()
	{
		<?= $relation[0] . "\n" ?>
	}
<?php endforeach; ?>
}