CREATE TABLE IF NOT EXISTS chats (
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
);

CREATE TABLE IF NOT EXISTS results (
  `request_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `chat_id` INT(11) NOT NULL DEFAULT -1,
  `city` VARCHAR(100) NOT NULL DEFAULT '',
  `result` VARCHAR(15000) NOT NULL DEFAULT ''
);