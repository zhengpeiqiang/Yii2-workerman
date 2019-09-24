<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class Content extends Model
{
    public function content(){
        $sql = "SELECT * FROM content ";
        return Yii::$app->db->createCommand($sql)->queryAll();
    }
}
