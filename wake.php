//<?php
//header('Content-Type: text/plain; charset=utf-8');
//
//$mac = $_GET['mac'] ?? '';
//if (empty($mac)) die("MAC-адрес не указан.");
//
//// Убираем все разделители
//$mac = str_replace([':', '-', ' '], '', $mac);
//
//// Формируем магический пакет
//$packet = str_repeat(chr(0xFF), 6);
//for ($i = 0; $i < 16; $i++) {
//    $packet .= hex2bin($mac);
//}
//
//// Отправляем на широковещательный адрес (UDP 9)
//$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
//socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
//socket_sendto($sock, $packet, strlen($packet), 0, '255.255.255.255', 9);
//socket_close($sock);
//
//echo "WoL-пакет отправлен на MAC: $mac";
//?>
<?php
header('Content-Type: text/plain; charset=utf-8');

$mac = $_GET['mac'] ?? '';
if (empty($mac)) die("MAC-адрес не указан.");

// Проверка формата MAC
if (!preg_match('/^([0-9A-Fa-f]{2}[:-]?){5}([0-9A-Fa-f]{2})$/', $mac)) {
    die("Неверный формат MAC-адреса.");
}

// Форматируем MAC (убираем разделители)
$mac = str_replace([':', '-'], '', $mac);

// Создаём магический пакет
$packet = str_repeat(chr(0xFF), 6);
for ($i = 0; $i < 16; $i++) {
    $packet .= hex2bin($mac);
}

// Отправляем UDP-пакет
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if ($socket === false) {
    die("Ошибка создания сокета: " . socket_strerror(socket_last_error()));
}

// Разрешаем широковещательную рассылку
if (!socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1)) {
    die("Ошибка настройки сокета: " . socket_strerror(socket_last_error()));
}

// Отправляем на широковещательный адрес (UDP-порт 9)
$result = socket_sendto(
    $socket, 
    $packet, 
    strlen($packet), 
    0, 
    '255.255.255.255', 
    9
);

if ($result === false) {
    die("Ошибка отправки: " . socket_strerror(socket_last_error()));
}

socket_close($socket);
echo "WoL-пакет отправлен на MAC: $mac";
?>