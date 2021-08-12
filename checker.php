<?php
require_once 'AccountFinder.php';

$exist = new AccountFinder;

echo $exist->setErrors()->yandex('tuhin@yandex.com')->check();
// echo $exist->setErrors()->apple('tuhin@yandex.ru')->check();