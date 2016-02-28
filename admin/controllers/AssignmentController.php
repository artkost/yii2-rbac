<?php

namespace artkost\rbac\admin\controllers;

use app\modules\admin\components\Controller;
use app\modules\rbac\models\RbacAssignment;
use app\modules\rbac\models\RbacAssignmentSearch;
use app\modules\rbac\Module;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Roles controller.
 */
class AssignmentController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['index'],
                'roles' => ['adminAssignmentView']
            ],
            [
                'allow' => true,
                'actions' => ['create'],
                'roles' => ['adminAssignmentCreate']
            ],
            [
                'allow' => true,
                'actions' => ['update'],
                'roles' => ['adminAssignmentUpdate']
            ],
            [
                'allow' => true,
                'actions' => ['delete', 'batch-delete'],
                'roles' => ['adminAssignmentDelete']
            ]
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'create' => ['get', 'post'],
                'update' => ['get', 'put', 'post'],
                'delete' => ['post', 'delete'],
                'batch-delete' => ['post', 'delete']
            ]
        ];

        return $behaviors;
    }

    /**
     * Roles list page.
     */
    public function actionIndex()
    {
        $searchModel = new RbacAssignmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'provider' => $dataProvider,
            'rolesArray' => RbacAssignment::rolesWithPermissionsArray()
        ]);
    }

    /**
     * Create role page.
     */
    public function actionCreate()
    {
        $model = new RbacAssignment(['scenario' => 'admin-create']);
        $roleArray = ArrayHelper::map($model->roles, 'name', 'description');

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->save()) {
                    return $this->redirect(['update', 'name' => $model->item_name]);
                } else {
                    Yii::$app->session->setFlash('danger', Module::t('rbac', 'BACKEND_ROLES_FLASH_FAIL_ADMIN_CREATE'));
                }
            } elseif (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model->getErrors();
            }
        }

        return $this->render('create', [
            'model' => $model,
            'roleArray' => $roleArray,
        ]);
    }

    /**
     * Update role page.
     *
     * @param string $name Role name
     *
     * @return mixed
     */
    public function actionUpdate($name)
    {
        $model = $this->findAssignment($name);
        $model->setScenario('admin-update');
        $roleArray = ArrayHelper::map($model->roles, 'name', 'name');
        $ruleArray = ArrayHelper::map($model->rules, 'name', 'name');
        $permissionArray = ArrayHelper::map($model->permissions, 'name', 'name');

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->update()) {
                    return $this->refresh();
                } else {
                    Yii::$app->session->setFlash('danger', Module::t('rbac', 'BACKEND_ROLES_FLASH_FAIL_ADMIN_UPDATE'));
                }
            } elseif (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model->getErrors();
            }
        }

        return $this->render('update', [
            'model' => $model,
            'roleArray' => $roleArray,
            'ruleArray' => $ruleArray,
            'permissionArray' => $permissionArray
        ]);
    }

    /**
     * Delete role page.
     *
     * @param string $name Role name
     *
     * @return mixed
     */
    public function actionDelete($name)
    {
        $model = $this->findAssignment($name);

        if (!Yii::$app->authManager->remove($model)) {
            Yii::$app->session->setFlash('danger', Module::t('rbac', 'BACKEND_ROLES_FLASH_FAIL_ADMIN_DELETE'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Delete multiple roles page.
     *
     * @return mixed
     * @throws \yii\web\HttpException 400 if request is invalid
     */
    public function actionBatchDelete()
    {
        if (($names = Yii::$app->request->post('names')) !== null) {
            $auth = Yii::$app->authManager;
            foreach ($names as $item) {
                $role = $this->findAssignment($item['name']);
                $auth->remove($role);
            }
            return $this->redirect(['index']);
        } else {
            throw new BadRequestHttpException('BACKEND_ROLES_ONLY_POST_IS_ALLOWED');
        }
    }

    /**
     * Find Assignment by name.
     *
     * @param string $name Assignment name
     *
     * @return RbacAssignment
     *
     * @throws HttpException 404 error if role not found
     */
    protected function findAssignment($name)
    {
        if (($model = RbacAssignment::find()->where(['item_name' => $name])->one() !== null)) {
            return $model;
        } else {
            throw new HttpException(404, Module::t('rbac', 'BACKEND_ROLES_NOT_FOUND'));
        }
    }
}
