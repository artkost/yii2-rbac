# Yii2 RBAC
Yii2 Rbac manager

# Usage

Configure your `authManager`
```php
[
    'class' => 'yii\rbac\DbManager',
    'itemTable' => '{{%rbac_item}}',
    'itemChildTable' => '{{%rbac_item_child}}',
    'assignmentTable' => '{{%rbac_assignment}}',
    'ruleTable' => '{{%rbac_rule}}',
    'defaultRoles' => ['guest'],
    'cache' => 'cache'
]
```
and `user` component

```php
[
  'class' => 'app\modules\rbac\components\User',
]
```
Run Migrate command
`php yii migrate --migrationPath=@app/modules/rbac/migrations`

Then run module command `php yii rbac/manager/refresh'
That command adds rules from `rules.php` to database
