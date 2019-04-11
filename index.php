<?php
if (!isset($_REQUEST)) { return; }
 
$confirmation_token = '2b61ebd1';
$token = '8fb306e98593182d6cb104a959e8fd3fded7172c4b582014be416e87acf2db980559545fe383710ee0a47';
 
$data = json_decode(file_get_contents('php://input')); // vkbot | B3a9B8v7 - Данные от MySQL (Чтобы не забыть)
switch ($data->type) {
    case 'confirmation': echo $confirmation_token; break;
    case 'message_new':
        $user_id    = $data->object->user_id;
        $user_info  = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&access_token={$token}&v=5.0"));
        $user_name  = $user_info->response[0]->first_name;
        $body       = $data->object->body;
        $user_stat  = GetUserStats($user_id);
 
        if(preg_match("/^\/help$/", $body)) sendMessage($user_id, "{$user_name}, Список комманд:&#128540;
        																		 1. /profile - что-бы посмотреть профиль
        																		 2. /coin <ставка>");
        elseif(preg_match("/^\/balance$/", $body)) sendMessage($user_id, "{$user_name}, ваш баланс: {$user_stat['money']} руб. &#127974;");
        elseif(preg_match("/^Бонус/", $body)) bonus($user_id, $money, $status);
        elseif(preg_match("/^\/profile/", $body)) sendMessage($user_id, "{$user_name}, вот ваш профиль: &#128084;<br>ID: {$user_stat['id']} &#128187; <br>UID: {$user_stat['uid']} &#128203;<br>Баланс: {$user_stat['money']} &#128181;");
        elseif(preg_match("/^\/coin (?<sum>\d{1,7})$/", $body, $out)) {
             $a = GetUserStats($user_id);
            if($a['money'] < $out['sum']) { $vk->sendMessage($user_id, "{$user_name}, у тебя нету денег!"); return; }
            $rand = rand(0,100);
            if($rand >= 50) {
                addMoney($user_id, $out['sum'], 1);
                $vk->sendMessage($user_id, "{$user_name}, поздравляю! Ты выйграл!!!");
            } else {
                addMoney($user_id, $out['sum'], 2);
                $vk->sendMessage($user_id, "{$user_name}, прости, но ты проиграл!");
            }
            
            
        }
}

function GetUserStats($user_id) { // Функция с помощью которой мы будем получать данные нашего пользователя, а так же его регистрировать в базе!
    $link = new mysqli("localhost", "alibabashka_game", "Popopo123", "alibabashka_game"); // Подключение к базе данных
    $result = mysqli_query($link, "SELECT * FROM `accounts` WHERE `uid` = '".$user_id."'"); // Делаем запрос с выводом данных нашего пользователя
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC); // Берем данные с запроса в переменную как массив
    if($row['uid'] == $user_id) return $row; // Сверяем, ли наш юзер есть в базе, то есть, что строка не пустая
    else { // если..
        mysqli_query($link, "INSERT INTO `accounts`(`uid`, `money`, `firstMessage`, `property`) VALUES ('".$user_id."', '500', '".time()."', '0&0&0')"); // Создаем аккаунт пользователя в базе
        $result = mysqli_query($link, "SELECT * FROM `accounts` WHERE `uid` = '".$user_id."'"); // Опять получаем информацию о нем
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC); // берем в массив
        return $row; // Возвращаем его..
    }
}
 
function sendMessage($user_id, $message) { // Создаем функцию, которая будет принимать 2е переменные, это UID самого пользователя и само сообщение.
    global $token; // Даем доступ функции к переменной $token
    $request_params = array(
        'message' => $message, // Подстраиваем переменные функции под параметры запроса.
        'user_id' => $user_id,
        'access_token' => $token,
        'v' => '5.0'
    );
    file_get_contents('https://api.vk.com/method/messages.send?'. http_build_query($request_params)); // Чисто мои удобства :)
} 
 
function addMoney($user_id, $money, $status) {
    $link = new mysqli("localhost", "alibabashka_game", "Popopo123", "alibabashka_game"); // Подключение к базе данных
    $result = mysqli_query($link, "SELECT * FROM `accounts` WHERE `uid` = '".$user_id."'"); // Делаем запрос с выводом данных нашего пользователя
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC); // Берем данные с запроса в переменную как массив
    if($status == 1) $row['money'] += $money;
    elseif($status == 2) $row['money'] -= $money;
    mysqli_query($link, "UPDATE `accounts` SET `money`='".$row['money']."' WHERE `uid` = '".$user_id."'");
}
function bonus($user_id) {
	$link = new mysqli("localhost", "alibabashka_game", "Popopo123", "alibabashka_game"); // Подключение к базе данных
    $result = mysqli_query($link, "SELECT * FROM `accounts` WHERE `uid` = '".$user_id."'"); // Делаем запрос с выводом данных нашего пользователя
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC); // Берем данные с запроса в переменную как массив
    if($status == 1) $row['money'] += $money;
    elseif($status == 2) $row['money'] -= $money;
    mysqli_query($link, "UPDATE `accounts` SET `money`='".$row['money']."' WHERE `uid` = '".$user_id."'");
}
 
?>


