<?php
/* HELLO WORLD */

// test drgdrg dfgadd co

/* User-defined error handler function */
function errorHandler($errno, $errstr, $errfile, $errline)
{
    echo 'ОШИБКА! ' . $errno . ' - ' . $errstr . ' - ' . $errfile . ' - ' . $errline . '<br/>';
}

set_error_handler('errorHandler');


function exceptionHandler($exception)
{
    echo "Неперехватываемое исключение: ", $exception->getMessage(), "\n";
}

set_exception_handler('exceptionHandler');

echo 'Версия PHP - ' . phpversion() . '<hr/>';
define('THIS_DIR', dirname(__FILE__));

class AdminerInstall
{

    private $root = '';
    private $rootphp = '';

    // TODO добавить проверку на версию PHP для скачивания нужной версии админера
    private $options = array(
        'login' => 'skobeeff',
        // логин пользователя
        'rootFolder' => '/adminer/',
        // создаваемая дирректория
        'fileName' => 'index.php',
        // создаваемый файл
        'version' => 'latest',
        // тип версии adminer.php
        'password_size' => 10,
        // длина пароля
        'password_chars' => '1234567890qazxswedcvfrtgbnhyujmkiolpQAZXSWEDCVFRTGBNHYUJMKIOLP'
        // Допустимые символы в пароле
    );


    private function changeOptions($name, $get)
    {
        if (isset($_GET[$get]) && $_GET[$get] != '') {
            switch ($name) {
                case 'rootFolder':
                    break;
                default:
                    $this->options[$name] = $_GET[$get];
            }
        }
    }

    public function __construct()
    {

        if (version_compare(phpversion(), '5.2.17', '<=')) {

            echo "Устаревшая версия PHP<br/>Скачайте <a href='http://tools.orson.pro/pass/adminer.zip'>этот</a> архив и распакуйте его на сервере ручками<hr/>";
            $password = $this->generatePassword();
            $message = $this->getMessage($this->options['login'], $password);
            $message = 'h3. Adminer<br/><br/>';
            $message .= "|link|http://{$_SERVER['HTTP_HOST']}{$this->root}{$this->options['rootFolder']}|<br/>";
            $message .= "|login|skobeeff|<br/>";
            $message .= "|password|lpoVPraaR9|<br/>";
            echo $message;

        } else {


            // проверка переменных
            if (isset($_GET['key']) && $_GET['key'] == 'whats_up_world') {
                $this->changeOptions('login', 'login');
                $this->changeOptions('version', 'version');
                $this->changeOptions('name', 'fileName');
                // $this->changeOptions('root', 'rootFolder');
                $this->changeOptions('psize', 'password_size');
            }


            // корневая папка (где находится этот скрипт)
            $this->root = '/' . trim(str_replace(trim($_SERVER['DOCUMENT_ROOT'], '/'), '', trim(THIS_DIR, '/')), '/');
            if ($this->root == '/') {
                $this->root = '';
            }
            $this->rootphp = '/' . trim($_SERVER['DOCUMENT_ROOT'], '/') . $this->root;


            // создание папки adminer
            if (!$this->checkElement($this->options['rootFolder'])) {
                $path_to_adminer = $this->createFolder($this->options['rootFolder']);
            } else {
                $path_to_adminer = $this->options['rootFolder'];
            }


            // создание файла adminer.php
            if (!$this->checkElement($path_to_adminer . $this->options['fileName'])) {
                $this->createFile($path_to_adminer . $this->options['fileName'], $this->getAdminer());
            }

            // создание файла .htaccess
            if (!$this->checkElement($path_to_adminer . '.htaccess')) {
                $this->createFile($path_to_adminer . '.htaccess', $this->getHtaccess());
            }

            // создание файла .htpasswd
            if (!$this->checkElement($path_to_adminer . '.htpasswd')) {
                // Генерация пароля
                $password = $this->generatePassword();
                $this->createFile($path_to_adminer . '.htpasswd', $this->getHtpasswd($password));
                $message = $this->getMessage($this->options['login'], $password);

                echo '<h4>Добавьте следующую информацию в WIKI проекта</h4>' . $message;

                // создание файла wiki.php
                $wiki = "<?php die('Доступ запрещен');" . PHP_EOL . PHP_EOL . str_replace('<br/>', PHP_EOL, $message);
                $this->createFile($path_to_adminer . 'wiki.php', $wiki);

            } else {
                echo "Все файлы уже существуют!";
            }


            // Уничтожение скрипта
            unlink(__FILE__);
        }
    }


    /**
     * <p>Проверка существования элемента (папки или файла)</p>
     * @param $name - имя файла или папки
     * @return bool
     */
    private function checkElement($name)
    {
        if (file_exists("{$this->rootphp}$name")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * <p>Создание файла</p>
     * @param $element - имя создаваемого файла
     * @param $subject
     */
    private function createFile($element, $subject)
    {

        file_put_contents($this->rootphp . $element, $subject);
    }

    /**
     * <p>Создание папки</p>
     * @param $name - имя создаваемой папки
     * @return mixed
     */
    private function createFolder($name)
    {
        mkdir("{$this->rootphp}$name", 0755, true);

        if (is_dir("{$this->rootphp}$name")) {
            return $name;
        } else {
            throw new Exception("Не удалось создать папку - {$this->rootphp}$name");
        }
    }

    /**
     * <p>Получение контента файла adminer.php</p>
     * @return string
     */
    private function getAdminer()
    {
        // TODO выбор версии
        switch ($this->options['version']) {
            case 'latest':
                $link = 'http://www.adminer.org/latest-mysql.php';
                break;
            default:
                $link = 'http://www.adminer.org/latest.php';
        }

        // получаем файл админера
        $adminer = file_get_contents($link);
        if (empty($adminer)) {
            trigger_error('Возникла проблема с загрузкой файла adminer.php');
            die;
        }
        return $adminer;
    }

    /**
     * <p>Получение контента файла .htaccess</p>
     * @return string
     */
    private function getHtaccess()
    {
        $htaccess = 'AuthName "My Protected Area"' . PHP_EOL;
        $htaccess .= 'AuthType Basic' . PHP_EOL;
        $htaccess .= "AuthUserFile {$this->rootphp}{$this->options['rootFolder']}/.htpasswd" . PHP_EOL;
        $htaccess .= 'require valid-user';

        return $htaccess;
    }

    /**
     * <p>Получение контента файла .htpasswd</p>
     * @param $password - сгенерированный методом generatePassword() пароль
     * @return string
     */
    private function getHtpasswd($password)
    {
        $hash = $this->getHash($password);
        $htpasswd = $this->options['login'] . ':{SHA}' . $hash;

        return $htpasswd;
    }

    /**
     * <p>Получение Hash</p>
     * @param $password - сгенерированный методом generatePassword() пароль
     * @return string
     */
    private function getHash($password)
    {
        return base64_encode(sha1($password, true));
    }

    /**
     * <p>Вывод сообщения для wiki</p>
     * @param $login - логин пользователя
     * @param $password - сгенерированный методом generatePassword() пароль
     * @return string
     */
    private function getMessage($login, $password)
    {
        $message = 'h3. Adminer<br/><br/>';
        $message .= "|link|http://{$_SERVER['HTTP_HOST']}{$this->root}{$this->options['rootFolder']}index.php|<br/>";
        $message .= "|login|$login|<br/>";
        $message .= "|password|$password|<br/>";

        return $message;
    }

    /**
     * <p>Генерация пароля</p>
     * @return null|string
     */
    private function generatePassword()
    {
        $size = StrLen($this->options['password_chars']) - 1;
        $password = null;

        while ($this->options['password_size']--) {
            $password .= $this->options['password_chars'][rand(0, $size)];
        }
        return $password;
    }
}


new AdminerInstall();