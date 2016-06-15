<?php
/**
 * LogManager
 *
 * csvファイル出力を管理
 *
 * @package none
 * @subpackage none
 */
class LogManager
{
    /**
     * __construct
     *
     * コンストラクタ関数
     *
     */
    public function __construct() {
        //
        date_default_timezone_set('Asia/Tokyo');
        //
        $this->log_file_name = date("Y_m_d") . '.log';
        $this->log_array = array();
        //
        $this->log_title_array = array(
            'date_time',
            'log_type',
            //'log_id',
            'message'
        );
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
     * getCSVFileFullPath
     *
     * csvフルパスを取得
     *
     * @param none
     * @return $this->csv_file_path . '/' . $this->csv_file_name
     */
    public function getLogFilePath() {
        return $this::LOG_PATH . $this->log_file_name;
    }
    /**
     * create_file_path
     *
     * ファイルパスを生成
     *
     */
    public function createFilePath() {
        //
        if (!file_exists ($this::LOG_PATH)){
            mkdir ($this::LOG_PATH, 0777, true);
        }
    }
    /**
     * addLogMessage
     *
     * ファイルパスを生成
     *
     */
    public function addLogMessage($log_type, $log_message) {
        //log messageを生成
        $this->log_array[] = array(
            'date_time' => date("Y_m_d_H:i:s"),
            'log_type' => $log_type,
            'message' => $log_message
        );
    }
    /**
     * outputLog
     *
     * logファイルを出力
     *
     */
    public function outputLog() {
        //
        $this->createFilePath();
        //
        $log_file = $this->getLogFilePath();
        if (!file_exists ($log_file)) {
            //
            $fp = fopen($log_file,'w');
            //エンコード変換を実施
            mb_convert_variables('sjis-win','UTF-8', $this->log_title_array);
            //logファイルへ出力
            fputcsv($fp, $this->log_title_array);

        } else {
            $fp = fopen($log_file,'a');
        }

        //エンコード変換を実施
        mb_convert_variables('sjis-win','UTF-8', $this->log_array);
        //logファイルへ出力
        foreach ($this->log_array as $log_key => $log_value){
            fputcsv($fp, $this->log_array[$log_key]);
        }
        //logファイルをクローズ
        fclose($fp);
    }

    /**
     * log出力パス
     */
    const LOG_PATH = './log/';
    /**
     * logファイル名
     */
    private $log_file_name;
    /**
     * logァイル表題
     */
    private $log_title_array;
    /**
     * log出力データ配列
     */
    public $log_array;
}