<?php

namespace app\controllers;

use app\models\Main;
use vendor\core\base\Pagination;

class MainController extends \vendor\core\base\Controller
{
    public $commands = ['start', 'help', 'findhotels'];
    public $content = '';
    public $text;

    public function indexAction()
    {
        $model = new Main();
        $model->query("CREATE TABLE IF NOT EXISTS chats (
          `chat_id` INT(11) NOT NULL PRIMARY KEY DEFAULT -1,
          `last_command` VARCHAR(100) NOT NULL DEFAULT '',
          `last_question` VARCHAR(100) NOT NULL DEFAULT '',
          `city` VARCHAR(100) NOT NULL DEFAULT '',
          `group_adults` INT(2) NOT NULL DEFAULT -1,
          `group_children` INT(2) NOT NULL DEFAULT -1,
          `no_rooms` INT(2) NOT NULL DEFAULT -1,
          `checkin_year` INT(4) NOT NULL DEFAULT -1,
          `checkin_month` INT(2) NOT NULL DEFAULT -1,
          `checkin_monthday` INT(2) NOT NULL DEFAULT -1,
          `checkout_year` INT(4) NOT NULL DEFAULT -1,
          `checkout_month` INT(2) NOT NULL DEFAULT -1,
          `checkout_monthday` INT(2) NOT NULL DEFAULT -1,
          `sb_travel_purpose` VARCHAR(10) NOT NULL DEFAULT ''
        )");
        $model->query("CREATE TABLE IF NOT EXISTS results (
          `chat_id` INT(11) NOT NULL PRIMARY KEY DEFAULT -1,
          `city` VARCHAR(100) NOT NULL DEFAULT '',
          `result` VARCHAR(10000) NOT NULL DEFAULT ''
        )");

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
        $this->view = 'index';
    }

    public function botAction()
    {
        $token = "825990148:AAH-sTSEO4-5F3Npq9J9ga3dd2sGih78Mmk";
        $bot = new \Telegram\Bot\Api($token);
        $result = $bot->getWebhookUpdates();
        $text = $result["message"]["text"];
        $chat_id = $result["message"]["chat"]["id"];
        if (isset($text)) {
            $model = new Main();
            $model->checkCommands($bot, $text, $chat_id);
        }
        $this->view = 'index';
    }
}
