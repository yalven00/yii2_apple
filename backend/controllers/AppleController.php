<?php

namespace backend\controllers;

use Yii;
use common\models\Apple;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * AppleController implements the CRUD actions for Apple model.
 */
class AppleController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?'], // Только авторизованные пользователи
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'generate' => ['POST'],
                    'fall' => ['POST'],
                    'eat' => ['POST'],
                    'delete-all' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Apple models.
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Apple::find()->where(['is_deleted' => false]),
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 12,
            ],
        ]);

        // Регистрируем JS для Bootstrap модальных окон
        $this->view->registerJs('
            $(document).ready(function() {
                $(\'[data-toggle="modal"]\').click(function() {
                    var target = $(this).data(\'target\');
                    $(target).modal(\'show\');
                });
            });
        ');

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Генерация случайных яблок
     */
    public function actionGenerate()
    {
        $count = rand(1, 10); // Генерируем от 1 до 10 яблок
        
        $created = 0;
        for ($i = 0; $i < $count; $i++) {
            $apple = Apple::createRandom();
            if ($apple->save()) {
                $created++;
            }
        }
        
        Yii::$app->session->setFlash('success', "Сгенерировано $created яблок(а) из $count");
        return $this->redirect(['index']);
    }

    /**
     * Уронить яблоко
     */
    public function actionFall($id)
    {
        $apple = $this->findModel($id);
        
        try {
            $apple->fallToGround();
            Yii::$app->session->setFlash('success', 'Яблоко упало на землю');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Съесть часть яблока
     */
    public function actionEat($id)
    {
        $apple = $this->findModel($id);
        $percent = Yii::$app->request->post('percent');
        
        if (!$percent || !is_numeric($percent)) {
            Yii::$app->session->setFlash('error', 'Укажите корректный процент');
            return $this->redirect(['index']);
        }
        
        try {
            $apple->eat($percent);
            
            if ($apple->is_deleted) {
                $message = 'Яблоко полностью съедено и удалено';
            } else {
                $message = "Съедено $percent%. Осталось: " . $apple->getRemainingPercent() . "%";
            }
            
            Yii::$app->session->setFlash('success', $message);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        
        return $this->redirect(['index']);
    }


public function actionEatPercent($id, $percent)
{
    $apple = Apple::findOne($id);
    
    if (!$apple) {
        Yii::$app->session->setFlash('error', 'Яблоко не найдено');
        return $this->redirect(['index']);
    }
    
    try {
        $apple->eat($percent);
        Yii::$app->session->setFlash('success', "Успешно съедено {$percent}%");
    } catch (Exception $e) {
        Yii::$app->session->setFlash('error', 'Ошибка: ' . $e->getMessage());
    }
    
    return $this->redirect(['index']);
}

public function actionDelete($id)
{
    try {
        $apple = $this->findModel($id);
        
        // Устанавливаем флаг удаления
        $apple->is_deleted = true;
        
        // Сохраняем без валидации
        if ($apple->save(false)) {
            Yii::$app->session->setFlash('success', 'Яблоко успешно удалено');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при удалении яблока');
        }
        
    } catch (\yii\web\NotFoundHttpException $e) {
        Yii::$app->session->setFlash('error', 'Яблоко не найдено');
    } catch (\Exception $e) {
        Yii::$app->session->setFlash('error', 'Ошибка: ' . $e->getMessage());
    }
    
    return $this->redirect(['index']);
}


public function actionDeleteAll()
{
    try {
        // Используем 1 вместо true для MySQL
        $count = Apple::updateAll(['is_deleted' => 1]);
        Yii::$app->session->setFlash('success', "Удалено $count яблок(а)");
    } catch (\Exception $e) {
        Yii::$app->session->setFlash('error', 'Ошибка при удалении яблок: ' . $e->getMessage());
    }
    
    return $this->redirect(['index']);
}

/**
 * Finds the Apple model based on its primary key value.
 * If the model is not found, a 404 HTTP exception will be thrown.
 */
protected function findModel($id)
{
    // Используем 0 вместо false для MySQL
    if (($model = Apple::findOne(['id' => $id, 'is_deleted' => 0])) !== null) {
        return $model;
    }

    throw new \yii\web\NotFoundHttpException('Яблоко не найдено или уже удалено');
}
   
}