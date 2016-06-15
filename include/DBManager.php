<?php

//csv処理フラグ
$unprocessed = 1;
$processed = 2;

//対象DBテーブル
$file_parts_table = 'file_parts';

/**
 * connect
 *
 * DBを接続
 * @return PDO $dbh
 */
function db_connect() {
    //DBアクセスのパラメタを設定

    $dsn = 'mysql:host=insideai-nex-db-pro.cmgi4vqkorx6.ap-northeast-1.rds.amazonaws.com;port=3306;dbname=nex';
    $username = 'nex_master_pro';
    $password = 'fjalkjdflajfkaj';

    /*
    $dsn = 'mysql:host=127.0.0.1;dbname=test';
    $username = 'root';
    $password = 'root';
    */
    $options = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    );
    //DBを接続
    $dbh = new PDO($dsn, $username, $password, $options);
    return $dbh;
}

/**
 * sql_execute
 *
 * SQL処理を実施
 *
 * @param PDO $dbh
 * @param string $sql_target
 * @return PDOStatement $stt
 */
function sql_execute($dbh, $sql_target) {
    //プリペアドステートメントを生成
    $stt = $dbh->prepare($sql_target);
    //プリペアドステートメントを実行
    $stt->execute();
    //
    return $stt;
}

/**
 * cal_price
 *
 * priceを算出
 *
 * @param string $value
 * @param string $type
 * @return double $target_price
 */
function cal_price($value, $type) {
    switch ($type) {
        case 'int':
            if (is_numeric($value)) {
                $length = mb_strlen($value,'UTF-8');
                $length > 4 ? $target_price = 2 : $target_price = 1;
            } else {
                # code ...
            }
            break;
        case 'var':
            $length = mb_strlen($value,'UTF-8');
            $length > 4 ? $target_price = 3 : $target_price = 2;
            break;
        case 'txt':
            $value_tmp = str_replace(array("\r\n","\n","\r"), '', $value);
            $length = mb_strlen($value_tmp,'UTF-8');
            $unit_price = 0.5;
            $target_price = $unit_price * $length;
            break;
        case 'chk':
            # code ...
            break;
        case 'img':
            # code ...
            break;
        default:
            $target_price = 0;
            break;
    }
    return $target_price;
}

/**
 * cal_price_without_type
 *
 * priceを算出
 *
 * @param string $value
 * @return double $target_price
 */
function cal_price_without_type($value) {
    if (is_numeric($value)) {
        $length = mb_strlen($value,'UTF-8');
        $length > 4 ? $target_price = 2 : $length > 0 ? $target_price = 1 : $target_price = 0;
    } elseif (strpos($value,"\n")) {
        $value_tmp = str_replace(array("\r\n","\n","\r"), '', $value);
        $length = mb_strlen($value_tmp,'UTF-8');
        $unit_price = 0.5;
        $target_price = $unit_price * $length;
    } else {
        $length = mb_strlen($value,'UTF-8');
        $length > 4 ? $target_price = 3 : $length > 0 ? $target_price = 2 : $target_price = 0;
    }
    return $target_price;
}

/**
 * aggregate_characters
 *
 * priceを算出
 *
 * @param string $value
 * @return array $read_count, $unread_count
 */
function aggregate_characters($value) {
    $total_count = mb_strlen($value,'UTF-8');
    $unread_count = substr_count($value, '●');
    $read_count = $total_count - $unread_count;
    return array($read_count, $unread_count);
}






/*---------- 今後見直す ----------*/
/**
 * DBManager
 *
 * DBアクセスを管理
 *
 * @package none
 * @subpackage none
 */
class DBManager {
    /**
     * __construct
     *
     * コンストラクタ関数
     *
     * @internal param $none
     */
    public function __construct() {
        $this->unprocessed = 1;
        $this->processed = 2;
        $this->file_parts_table = 'file_parts';
    }

    /**
     * __set
     *
     * 属性を設定
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value) {
        $this->$name = $value;
    }

    /**
     * __get
     *
     * 属性を取得
     *
     * @param $name
     * @return $this->$name
     */
    public function __get($name) {
        return $this->$name;
    }

    /**
     * db_connect
     *
     * DBを接続
     * @return PDO $dbh
     */
    public function dbConnect() {
        try {
            //DBを接続
            $options = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            );
            $dbh = new PDO($this::DSN, $this::USER_NAME, $this::PASSWORD, $options);
        } catch (PDOException $e) {
            print('DB_connecting_error:'.$e->getMessage());
            die();
        }
        return $dbh;
    }

    /**
     * csvデータ未更新
     */
    private $unprocessed;
    /**
     * csvデータ更新済
     */
    private $processed;
    /**
     * 対象DBテーブル
     */
    private $file_parts_table;
    /**
     * DB handler
     */
    private $dbh;
    /**
     * DSN
     */
    const DSN = 'mysql:host=127.0.0.1;dbname=test';
    /**
     * ユーザ名
     */
    const USER_NAME = 'root';
    /**
     * パスワード
     */
    const PASSWORD = 'root';
}