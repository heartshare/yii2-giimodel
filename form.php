<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var \opus\giimodel\Generator $generator
 */

echo $form->field($generator, 'tableName');

$nsField = $form->field($generator, 'ns');
if (!empty($generator->prefixMap))
{
    foreach ($generator->prefixMap as $prefix => $ns)
    {
        $items[] = sprintf('<tt>%s</tt> » <tt>%s</tt>', $prefix, $ns);
    }
    $html = \yii\helpers\Html::ul($items, ['encode' => false]);
    $nsField->template = "{label}\n<br />Read from configuration{$html}\n{hint}";
}
echo $nsField;

echo $form->field($generator, 'baseClass');
echo $form->field($generator, 'db');
echo $form->field($generator, 'generateRelations')->checkbox();
echo $form->field($generator, 'generateLabelsFromComments')->checkbox();
