<?php

namespace artkost\rbac\models;

use yii\base\Model;

class RbacDefinition extends Model
{
    public $permissions = [];
    public $assignments = [];
    public $roles = [];
    public $rules = [];

    public $module;
}
