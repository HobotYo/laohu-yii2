<?php

namespace app\modules\manage\controllers;

use app\models\Music;
use app\models\search\MusicSearch;
use app\modules\core\helpers\EasyHelper;
use app\modules\core\helpers\FileHelper;
use app\modules\core\helpers\UserHelper;
use app\modules\manage\controllers\base\ModuleController;
use app\modules\portal\models\MusicForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * MusicController implements the CRUD actions for Music model.
 */
class MusicController extends ModuleController
{
    /**
     * Lists all Music models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MusicSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Updates an existing Music model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $this->layout = 'manage_form';

        $model = $this->findModel($id);

        $form = new MusicForm();
        $form->scenario = 'update';

        if ($form->load(Yii::$app->request->post())) {
            $form->music_file = UploadedFile::getInstance($form, 'music_file');
            if ($form->validate()) {
                $original_file_name = $model->music_file;//记录原文件名
                $savePath = '';
                $flow = true;

                //如果上传了文件，上传新文件
                if ($form->music_file) {
                    $file_name = FileHelper::generateFileName();
                    $savePath = FileHelper::getMusicFullPath($file_name);
                    $model->music_file = $file_name;
                    if (!$form->music_file->saveAs($savePath)) {
                        $form->addError('music_file', '文件上传失败');//上传文件跟这些类没关系，要是失败了就手动给music_file这个属性添加错误
                        $flow = false;
                    }
                }

                if ($flow) {
                    $model->track_title = $form->track_title;
                    $model->visible = $form->visible;
                    if (UserHelper::isAdmin()) {
                        $model->status = $form->status;
                    }

                    if ($model->save()) {

                        //如果上传了文件，删除原文件
                        if ($form->music_file) {
                            unlink(FileHelper::getMusicFullPath($original_file_name));
                        }

                        EasyHelper::setSuccessMsg('修改成功');
                        return $this->redirect(['index']);
                    } else {

                        //如果上传了文件，删除新文件
                        if ($form->music_file) {
                            unlink($savePath);
                        }

                        EasyHelper::setErrorMsg('修改失败');
                        $form->addErrors($model->getErrors());//获取两个类相同属性的错误
                    }
                }
            }
        } else {
            $form->setAttributes($model->getAttributes());
        }

        return $this->render('@app/modules/portal/views/music/update', [
            'model' => $form,
        ]);
    }

    /**
     * Deletes an existing Music model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->delete()) {
            unlink(FileHelper::getMusicFullPath($model->music_file));
            EasyHelper::setSuccessMsg('删除成功');
        } else {
            EasyHelper::setErrorMsg('删除失败');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Music model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Music the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Music::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
