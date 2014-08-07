Advanced Gii model generator for Yii2
=============

Generates 2 models classes instead of just one.
This is useful if you are frequently re-generating your models from a schema and want to separate model definitions from custom functionality.

For example: table `post`
- `\common\models\base\Post` - contains table meta (relations, property definitions, `tableName`, `rules`, `attributeLabels`)
- `\common\models\Post` - is left almost empty for custom functionality

Installation
------------
The esiest way to obtain the code is using Composer: just modify your `composer.json` to add a custom repository (linking to this project) and require the libary.

```json
{
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
        'allowedIPs' => ['*'],
        'generators' => [
            'giimodel' => [
                'class' => '\opus\giimodel\Generator'
            ]
        ]
    ]
]
```

Todos
-----
- Support for Search models
- Remembering relation/base class names
- Document namespace override parameters


Changelog
---------
1.0.0 
- Added support for overriding relation class name
- Fixed issue with preceding slash of relation class namespace
- Fixed issue where unselected base models still got generated
- Added generating attribute PHPDoc from SQL field comments
- Added support for yii2 1.0-beta
