<?php

namespace app\models;

use vendor\core\base\Model;

class Main extends Model
{
    public $language = '.ru';
    public $city = '';
    public $error = '';

    public function errorRequiredField()
    {
        $result = '<div class="alert alert-danger">';
        $result .= 'Чтобы начать поиск, введите направление';
        $result .= '</div>';
        return $result;
    }

    public function getResultRequest()
    {
        $link = $this->createLink($_POST['ss']);
        $array = $this->parseSite($link);
        if (!empty($array)) {
            $result = '<div>';
            $result .= '<h3>Найдены лучшие варианты</h3>';
            $result .= '<ul class="list">';
            foreach ($array as $item) {
                $result .= '<li>' . $item . '</li>';
            }
            $result .= '</ul>';
            $result .= '</div>';
        } else {
            $result = '<div class="alert alert-danger">';
            $result .= 'По данному запросу ничего не найдено';
            $result .= '</div>';
        }
        return $result;
    }

    public function parseSite($site)
    {
        $res = array();
        $data = file_get_html($site);
        foreach ($data->find('a.hotel_name_link.url') as $hotels) {
            foreach ($hotels->find('span.sr-hotel__name') as $name) {
                $href = str_replace(' ', '', str_replace(';', '&', str_replace('html;', 'html?', $hotels->href)));
                $res[] = '<a href="https://www.booking.com' . $href . '">' . $name->plaintext . '</a>';
            }
        }
        $data->clear();
        unset($data);
        return $res;
    }

    public function createLink($city)
    {
        if (!empty($city)) $this->city = '&ss=' . urlencode($city);
        $result = 'https://www.booking.com/searchresults' . $this->language . '.html?';
        $result .= $this->city;
        return $result;
    }

    public function checkCommands($bot, $text, $chat_id)
    {
        if (strpos($text, '/start') !== false)  $this->startCommand($bot, $chat_id);
        elseif (strpos($text, '/help') !== false) $this->helpCommand($bot, $chat_id);
        elseif (strpos($text, '/findhotels') !== false) $this->findHotelsCommand($bot, $chat_id, $text);
        elseif (strpos($text, '/stop') !== false) $this->stopCommand($chat_id);
        elseif (strpos($text, '/mysearch') !== false) $this->mySearchCommand($bot, $chat_id);
        elseif (strpos($text, '/clear') !== false) $this->clearCommand($bot, $chat_id);
        else {
            if ($this->checkPrevCommands($chat_id) == TRUE) {
                $this->checkPrevQuestion($bot, $chat_id, $text);
                $this->findHotels($bot, $text, $chat_id);
            } else {
                $this->helpCommand($bot, $chat_id);
            }
        }
    }

    public function clearCommand($bot, $chat_id)
    {
        $find_chat = $this->findBySql("SELECT * FROM `results` WHERE `chat_id` = '$chat_id'");
        if (!empty($find_chat)) {
            $this->query("DELETE FROM `results` WHERE `chat_id` = '$chat_id'");
            $bot->sendMessage(['chat_id' => $chat_id, 'text' => "Данные о Ваших запросах очищены"]);
        } else {
            $bot->sendMessage(['chat_id' => $chat_id, 'text' => "Ваша история поиска и так пуста"]);
        }

    }
    public function helpCommand($bot, $chat_id)
    {
        $answer = "Доступные команды:\n";
        $answer .= "/help - вывод справки\n";
        $answer .= "/findhotels - поиск отелей\n";
        $answer .= "/mysearch - показать историю поиска\n";
        $answer .= "/stop - остановить текущий поиск\n";
        $answer .= "/clear - очистить историю поиска";
        $bot->sendMessage(['chat_id' => $chat_id, 'text' => $answer]);
    }

    public function startCommand($bot, $chat_id)
    {
        $answer = 'Добро пожаловать! Для ознакомления с командами воспользуйтесь справкой /help';
        $bot->sendMessage(['chat_id' => $chat_id, 'text' => $answer]);
        $this->checkChatId($chat_id, 'start');
    }

    public function findHotelsCommand($bot, $chat_id, $text)
    {
        $this->checkChatId($chat_id, 'findhotels');
        $this->query("UPDATE `chats` SET `last_command` = 'findhotels' WHERE `chat_id` = '$chat_id'");
        $this->findHotels($bot, $text, $chat_id);
    }

    public function stopCommand($chat_id)
    {
        $find_chat = $this->findBySql("SELECT * FROM `chats` WHERE `chat_id` = '$chat_id'");
        if (!empty($find_chat)) {
            $this->query("DELETE FROM `chats` WHERE `chat_id` = '$chat_id'");
        }
    }
    
    public function checkChatId($chat_id, $command)
    {
        $exist = $this->findBySql("SELECT `chat_id` FROM `chats` WHERE `chat_id` = '$chat_id'");
        if (!empty($exist)) {
            $this->query("UPDATE `chats` SET `last_command` = '$command' WHERE `chat_id` = '$chat_id'");
        } else {
            $this->query("INSERT INTO `chats`(`chat_id`, `last_command`) VALUES('$chat_id', '$command')");
        }
    }

    public function checkPrevQuestion($bot, $chat_id, $text)
    {
        $last_question = $this->findBySql("SELECT `last_question` FROM `chats` WHERE `chat_id` = '$chat_id'");
        $last_question = $last_question[0]['last_question'];
        if ($this->validText($chat_id, $last_question, $text) == true) {
            $this->query("UPDATE `chats` SET `$last_question` = '$text' WHERE `chat_id` = '$chat_id'");
        }
        else {
            $bot->sendMessage(['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => "<i>$this->error</i>"]);
        }
    }

    public function mySearchCommand($bot, $chat_id)
    {
        $mysearches = $this->findBySql("SELECT * FROM `results` WHERE `chat_id` = '$chat_id'");
        if (!empty($mysearches)) {
            foreach ($mysearches as $key => $value) {
                $city = $value['city'];
                $result = htmlspecialchars_decode($value['result']);
                $bot->sendMessage(['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => "<b>$city</b>\n$result"]);
            }
        } else {
            $bot->sendMessage(['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => "<b>Ваша история запросов пуста</b>"]);
        }
    }
    
    public function validText($chat_id, $last_question, $text)
    {
        if ($last_question == 'city') return $this->validCity($text) == true ? true : false;
    }
    
    public function validCity($text)
    {
        if (!preg_match( "/[a-zA-Zа-яА-ЯёЁ]+/", $text )) {
            $this->error = 'Город может содержать только символы кириллицы, латиницы или пробел';
            return false;
        }
        return true;
    }
    
    public function checkPrevCommands($chat_id)
    {
        $last_command = $this->findBySql("SELECT `last_command` FROM `chats` WHERE `chat_id` = '$chat_id'");
        $last_command = $last_command[0]['last_command'];
        if ($last_command === 'findhotels') return true;
        return false;
    }
    
    public function findHotels($bot, $text, $chat_id) {
        $fields = $this->findBySql("SELECT * FROM `chats` WHERE `chat_id` = '$chat_id'");
        $fields = $fields[0];
        $flag = 0;
        foreach ($fields as $key => $value) {
            if ($key != 'last_question' && $value === "") {
                if ($key === 'city') $answer = 'Введите город для поиска отелей';
                $bot->sendMessage(['chat_id' => $chat_id, 'text' => $answer]);
                $this->findBySql("UPDATE `chats` SET `last_question` = '$key' WHERE `chat_id` = '$chat_id'");
                $flag = 1;
                break;
            }
        }
        if ($flag == 0) $this->resultList($bot, $chat_id);
    }
    
    public function resultList($bot, $chat_id)
    {
        $list = $this->findBySql("SELECT * FROM `chats` WHERE `chat_id` = '$chat_id'");
        $list = $list[0];
        if (!empty($list)) {
            foreach ($list as $key => $value) {
                if ($key == 'city') $city = $value;
            }
            $link = $this->createLink($city);
            $array = $this->parseSite($link);
            $result = '';
            if (!empty($array)) {
                $i = 1;
                foreach ($array as $item) {
                    $result .= "$i. $item\n";
                    $i++;
                }
                $bot->sendMessage(['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'text' => "<b>Запрос сформирован</b>\n$result"]);
                $city = str_replace('&ss=', '', $city);
                $result = htmlspecialchars($result);
                $this->query("DELETE FROM `chats` WHERE `chat_id` = '$chat_id'");
                $this->findBySql("INSERT INTO `results` (`chat_id`, `city`, `result`) VALUES ('$chat_id', '$city', '$result')");
            } else {
                $this->query("DELETE FROM `chats` WHERE `chat_id` = '$chat_id'");
                $bot->sendMessage(['chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => '<b>По данному запросу ничего не найдено</b>']);
            }
        }
    }
}
