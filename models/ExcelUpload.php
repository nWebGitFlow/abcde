<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ExcelUpload extends Model {

	public $excel;

	public function rules() 
	{
		return [
			[['excel'], 'required'],
			[['excel'], 'file', 'extensions' => 'xls,xlsx'],
		];
	}

	public function getFolder() 
	{
		return Yii::getAlias('@web').'uploads/' ;
	}

	public function uploadFile(UploadedFile $file)
	{
		$this->excel = $file;

		if($this->validate()) {

	        $fileName = strtolower(md5(uniqid($file->baseName))).'.'. $file->extension;
	        // var_dump($file); die;
			$file->saveAs($this->getFolder().$fileName);
	        return $fileName;
		}
	}
}

