<?php

namespace app\controllers;

use app\models\Main;

class MainController extends \vendor\core\base\Controller
{
    public function indexAction()
    {
        $this->view = 'index';
        $title = 'Booking';
        $this->set(compact('title'));
    }

    public function ajaxAction()
    {
        if (!empty($_POST["ss"])) {
            $model = new Main();
            $result = $model->getResultRequest();
            echo json_encode(array('html' => $result));
        } else {
            $model = new Main();
            $result = $model->errorRequiredField();
            echo json_encode(array('html' => $result));
        }
    }

    public function botAction()
    {
        $token = "885909595:AAFIRbwaaXvqea-UETGClI6_is_WkNsDtvA";
        $bot = new \Telegram\Bot\Api($token);
        $result = $bot->getWebhookUpdates();
        $text = $text = $result["message"]["text"];
        $chat_id = $result["message"]["chat"]["id"];
        if (isset($text)) {
            $model = new Main();
            $model->checkCommands($bot, $text, $chat_id);
        }
    }
}