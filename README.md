Advanced Gii model generator for Yii2
=============

Generates 2 models classes instead of just one.
This is useful if you are frequently re-generating your models from a schema and want to separate model definitions from custom functionality.

For example: table `post`
- `\app\models\base\Post` - contains table meta (relations, property definitions, `tableName`, `rules`, `attributeLabels`)
- `\app\models\Post` - is left almost empty for custom functionality

Installation
------------
The esiest way to obtain the code is using Composer: just modify your `composer.json` to add a custom repository (linking to this project) and require the libary.

```json
{
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/opus-online/yii2-giimodel"
		}
	],
	"require": {
		"opus-online/yii2-giimodel": "*"
	}
}
```

Configuring
-----------
To add the Gii generator to your project, just add the class `\opus\giimodel\Generator` as a new generator to your gii module configuration:
```php
'modules' => [
    'gii' => [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['10.0.2.2'],
        'generators' => [
            'giimodel' => [
                'class' => '\opus\giimodel\Generator'
            ]
        ]
    ]
]
```
