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
use SoapClient;
use DOMDocument;


/**
 * PaymentController implements the CRUD actions for Payment model.
 */
class PaymentController extends Controller
{
    /**
     * Список данных с телефонными номерами для СМС-рассылки
     */
    private $tel_list = array();

    /**
     * websms SOAP-client
     */
    private $client;

    /**
     * websms registration data
     */
    private $username;
    private $password;

    /**
     * websms sender phone number 
     */
    private $fromNumber;

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


    /**
     * Constract simplexml-object from xml-string.
     */
    public function getXML($inputStr)
    {
        $dom = new DOMDocument;
        $dom->loadXML($inputStr);
        if (!$dom) {
            die('Ошибка при попытке разбора строки: '.PHP_EOL.$inputStr);
        }
        
        // return $dom->saveHTML(); // выводим (echo) XML как есть
        // return htmlspecialchars($dom->saveHTML()); // выводим (echo) XML как текст
        return simplexml_import_dom($dom);  // выводим (var_dump) XML как объект
    }

    /**
     * Initialize SOAP-client to websms-service.
     */
    private function initSoap()
    {
        $this->client = new SoapClient(
            "http://smpp3.websms.ru:8181/soap?WSDL" 
        );
        $regs = require '../config/input_websms.php';
        $this->username = $regs['username'];
        $this->password = $regs['password'];
        // телефонный номер отправителя должен быть извлечён из базы данных 
        $this->fromNumber = '79001234567'; 
    }


    /**
     * Read Payments from excel-file only with e-mail.
     */
    public function importExcel($inputFile) {
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
            // проверка на входение признака почтового адреса и составление выходного массива с e-mail-адресами
            if (strpos($rowData[0][0], '@')>0){
                // var_dump($rowData[0][0]);
                // сохранение строки
                $res[] = $rowData[0]; 
            } else {
                // проверка на признак телефонного номера и составление публичного массива с телефонными номерами
                $number = $rowData[0][0];

                $to_number = preg_replace("/[^0-9]/", '', $number); 
                if (strlen($to_number)==11) {
                    $this->tel_list[] = $rowData[0]; 
                }

                
            }

        }
        // var_dump($this->tel_list);
        // die('Ok! ');

        try {
            // здесь надо отсоединиться от excel-файла, иначе он не удалится
            $objPHPExcel->disconnectWorksheets();
            unset($objPHPExcel);
        }catch(Exception $e){
            die('Error – '.$inputFile);
        }

        // var_dump($res);
        // die('Ok! ');
        return $res;
    }


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
                $res = $this->importExcel($model->getFolder().$fileName);

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



                /**
                 * Инициализация доступа к web-сервису СМС–рассылок
                 */
                $this->initSoap();

                /**
                 * Разбор списка телефонных адресов для СМС-рассылки
                 * (список не должен быть большой, иначе он будет долго обрабатываться
                 * web-сервисом СМС-рассылок)
                 * 
                 * Здесь обнуляется массив $tel_list
                 */

                $saldo = 0;
                while (count($this->tel_list)>0) {
                    $recieve_tel_data = array_pop($this->tel_list);
                    $number = $recieve_tel_data[0]; // номер телефона
                    $saldo = $this->client->getBalance($this->username, $this->password); 
                        // var_dump($saldo);
                    $xSaldo = $this->getXML($saldo);
                    $balance = (float)$xSaldo->balance;
                    // var_dump($balance);

                    // контролируем сумму на нашем балансе 
                    // здесь может быть интегрирована бизнес-логика обработки ситуации с недостатком средств на счёте оператора web-сервиса
                    if ($balance<1.0) {
                        // die('<br />Cancel ');
                        $this->tel_list = array();
                        break;
                    }
                    // die('<br />Ok! ');
                    // строим сообщение для его отправки на телефон клиента
                    $message = "На ваш счёт поступило ".$recieve_tel_data[1].$recieve_tel_data[2];
                    // непосредственная отсылка СМС-сообщения с получением статуса исполнения команды
                    $sendStatus = $this->client->sendSMS($this->username, $this->password, $this->fromNumber, $message, $number);

                    // трансформируем ответ от web-сервиса websms в объект
                    $xSendStatus = $this->getXML($sendStatus);
                    // здесь должна быть реализована какая-либо обработка статуса отправки СМС-сообщения
                    // var_dump($xSendStatus);
                    // die('Ok! ');
                } 

                // удалить excel-файл 
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
