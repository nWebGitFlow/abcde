<?php

namespace app\controllers;

use Yii;
use app\models\Payment;
use app\models\PaymentSearch;
use app\models\ExcelUpload;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
// use phpexcel\Classes\PHPExcel;
// use app\models\PHPExcel;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Settings;


/**
 * PaymentController implements the CRUD actions for Payment model.
 */
class PaymentController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Payment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PaymentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Payment model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Payment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Payment();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Payment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Payment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Payment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Payment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Payment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    // /**
    //  * Get Payments from excel-file.
    //  * @return mixed
    //  */
    // public function actionLoad()
    // {
    //     $searchModel = new PaymentSearch();
    //     $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


    /**
     * Read Payments from excel-file only with e-mail.
     */
    public function actionImportExcel($inputFile) {
        try {
            // $inputFileType = \PHPExcel_IOFactory::indentify($inputFile);
            // Создаем объект класса PHPExcel
            $inputFileType = PHPExcel_IOFactory::load($inputFile);
            // var_dump($inputFileType);
            // die('Ok – '.$inputFile);
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objReader ->setReadDataOnly(true);
            // var_dump($objReader);
            // die('Ok – '.$inputFile);

            $objPHPExcel = $objReader->load($inputFile); 

            // var_dump($objPHPExcel);
            // die('Ok – '.$inputFile);

        }catch(Exception $e){
            die('Error – '.$inputFile);
        }

        // результирующий массив с почтовыми адресами
        $res = array();

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        // die('Ok! Row:'.$highestRow.'; Column:'.$highestColumn);
        for ($row = 1; $row <= $highestRow ; $row++) 
        {
            // $res[] = array();
            $r_ind = count($res);
            $rowData = $sheet->rangeToArray('A'.$row.':'.$highestColumn.$row, NULL, TRUE, FALSE);
            // var_dump($res);
            // die('Ok! ');
            // проверка на входение признака почтового адреса
            if (strpos($rowData[0][0], '@')>0){
                // var_dump($rowData[0][0]);
                // сохранение строки
                $res[] = $rowData[0]; 
            }

        }

        try {
            // здесь надо отсоединиться от excel-файла, иначе он не удалится
        }catch(Exception $e){
            die('Error – '.$inputFile);
        }

        // var_dump($res);
        // die('Ok! ');
        return $res;
    }

    //     return $this->render('index', [
    //         'searchModel' => $searchModel,
    //         'dataProvider' => $dataProvider,
    //     ]);
    // }

    /**
     * Upload Payments from excel-file.
     * @return mixed
     */
    public function actionGetExcel(){
        $model = new ExcelUpload;
        $fileName = "";
        if (Yii::$app->request->isPost) 
        {
            // die('Кликнули загрузить файл');
            $file = UploadedFile::getInstance($model, 'excel');
            if (!is_null($file)) {
                $fileName = $model->uploadFile($file);
            }

            // var_dump($fileName); die;
            // return $this->render('excel', ['model'=>$model]);
            if (file_exists($model->getFolder().$fileName)){
                // считать данные из excel-файла
                $res = $this->actionImportExcel($model->getFolder().$fileName);

                // загрузить данные в базу данных

                foreach ($res as $rec){
                    // var_dump($rec);
                    // создаем экземпляр класса
                    $modelPay = new Payment();

                    // добавляем email
                    $modelPay->email = $rec[0];

                        /**
                         * Здесь должна быть отправка почты по адресу $rec[0] 
                         * (см. реализацию функции изменения пароля у пользователя)
                         */
                    // добавляем сумму
                    $modelPay->sum = $rec[1];
                    // добавяем идентификатор валюты
                    $modelPay->currency = $rec[2];
                    // добавяем идентификатор источника записи
                    $modelPay->source = 1;
                    // добавяем текущее время (время назначить по умолчанию в БД)
                    $modelPay->created_at = time();

                    // сохраняем запись
                    $modelPay->save();  
                }
            // foreach ($ss as $val2) { //date
            //     if (($date_1 < ($val2['Date'])) && ($out == $val2['From'])) {
            //         $inputs1[$i][] = $val2;
            //         // $j++;
            //         $last_puthes = count($puthes) - 1;
            //         $puthes = recurs($puthes, $bak_puth, $val2, $ss);
            //     }
            // };

                // удалить файл
                unlink($model->getFolder().$fileName);
                // var_dump($res);
                // die('Ok! ');

                // перейти на общую страницу
                return $this->redirect('index');
            }
        }
        return $this->render('excel', ['model'=>$model]);

    }
}
