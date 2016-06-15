<?php
/**
 * CSVManager
 *
 * csvファイル出力を管理
 *
 * @package none
 * @subpackage none
 */
class CSVManager
{
    /**
     * __construct
     *
     * コンストラクタ関数
     *
     */
    public function __construct() {
        date_default_timezone_set('Asia/Tokyo');

        //
        $this->csv_file_name = date("Y_m_d_His") . '.csv';
        $this->csv_file_name_yesterday = date("Y-m-d",strtotime("-1 day")) . '_delay.csv';

        //前日対象データの時間帯
        $this->yesterday_start = date("Y-m-d 17:00:01",strtotime("-2 day"));
        $this->yesterday_end = date("Y-m-d 17:00:00",strtotime("-1 day"));
        //前々日対象データの時間帯
        //$before_yesterday_start = date("Y-m-d 17:00:01",strtotime("-3 day"));
        $this->before_yesterday_end = date("Y-m-d 17:00:00",strtotime("-2 day"));

        //Commentファイル名を生成
        $this->comment_file_name = date("Y_m_d_His") . '.txt';

        //
        $this->csv_title_output_flag = true;
        $this->csv_array = array();
        $this->csv_array_yesterday = array();
        $this->comment_array = array();
    }

    /**
     * getCSVRootPath
     *
     * csvファイルルートパスを取得
     *
     * @param none
     * @return CSV_ROOT_PATH
     */
    public function getCSVRootPath() {
        return $this::CSV_ROOT_PATH;
    }

    /**
     * getCSVRelativePath
     *
     * csvファイル相対パスを取得
     *
     * @param none
     * @return RELATIVE_PATH
     */
    public function getCSVRelativePath() {
        return $this::RELATIVE_PATH;
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
    public function getCSVFileFullPath($fileName) {
        return $this->csv_file_path . $fileName;
    }

    /**
     * getCommentFileFullPath
     *
     * Commentフルパスを取得
     *
     * @param none
     * @return $this->csv_file_path . '/' . $this->comment_file_name
     */
    public function getCommentFileFullPath() {
        return $this->csv_file_path . $this->comment_file_name;
    }

    /**
     * create_file_path
     *
     * ファイルパスを生成
     *
     * @param $organization_id
     * @param $document_id
     */
    public function createFilePath($organization_id, $document_id) {
        //
        $this->csv_file_path = $this->getCSVRootPath() . $organization_id .
            $this->getCSVRelativePath() . $document_id . '/';
        //
        if (!file_exists ($this->csv_file_path)){
            mkdir ($this->csv_file_path, 0777, true);
        }
    }

    /**
     * output_csv
     *
     * csvファイルを出力
     *
     */
    public function outputCsv() {
        //エンコード変換を実施
        mb_convert_variables('sjis-win','UTF-8', $this->csv_title);

        /*
         * 当日ファイルへ出力
         * */
        if ($this->csv_array) {
            if (!file_exists ($this->getCSVFileFullPath($this->csv_file_name))) {
                $fp = fopen($this->getCSVFileFullPath($this->csv_file_name),'w');
                if ($this->csv_title && $this->csv_title_output_flag) {
                    //csvファイルへ出力
                    fputcsv($fp, $this->csv_title);
                }
            } else {
                $fp = fopen($this->getCSVFileFullPath($this->csv_file_name),'a');
            }

            //csvファイルへ出力
            mb_convert_variables('sjis-win','UTF-8', $this->csv_array);
            foreach ($this->csv_array as $csv_key => $csv_value){
                foreach ($csv_value as $key => $value) {
                    //
                    fputcsv($fp, $this->csv_array[$csv_key][$key]);
                }
            }
            //csvファイルをクローズ
            fclose($fp);
        }

        /*
         * 前日ファイルへ出力
         * */
        if ($this->csv_array_yesterday) {
            //タイトルを出力
            if (!file_exists ($this->getCSVFileFullPath($this->csv_file_name_yesterday))) {
                $fp = fopen($this->getCSVFileFullPath($this->csv_file_name_yesterday),'w');
                if ($this->csv_title && $this->csv_title_output_flag) {
                    //csvファイルへ出力
                    fputcsv($fp, $this->csv_title);
                }
            } else {
                $fp = fopen($this->getCSVFileFullPath($this->csv_file_name_yesterday),'a');
            }
            //csvファイルへ出力
            mb_convert_variables('sjis-win','UTF-8', $this->csv_array_yesterday);
            foreach ($this->csv_array_yesterday as $csv_key => $csv_value){
                foreach ($csv_value as $key => $value) {
                    //
                    fputcsv($fp, $this->csv_array_yesterday[$csv_key][$key]);
                }
            }
            //csvファイルをクローズ
            fclose($fp);
        }
    }

    /**
     * addCommentMessage
     *
     * ファイルパスを生成
     *
     */
    public function addCommentMessage($log_message) {
        //log messageを生成
        $this->comment_array[] = array(
            //'date_time' => date("Y_m_d_H:i:s"),
            'message' => $log_message
        );
    }
    /**
     * outputCommentFile
     *
     * Commentファイルを出力
     *
     */
    public function outputCommentFile() {
        if ($this->comment_array) {
            //
            if (!file_exists ($this->getCommentFileFullPath())) {
                $fp = fopen($this->getCommentFileFullPath(),'w');
            } else {
                $fp = fopen($this->getCommentFileFullPath(),'a');
            }

            //エンコード変換を実施
            mb_convert_variables('sjis-win','UTF-8', $this->comment_array);
            //Commentファイルへ出力
            foreach ($this->comment_array as $comment_key => $comment_value){
                fputcsv($fp, $this->comment_array[$comment_key]);
            }

            //csvファイルをクローズ
            fclose($fp);
        }
    }

    /**
     * csv出力rootパス
     */
    const CSV_ROOT_PATH = '/usr/local/lib/data_manager/csv/';
    /**
     * csv出力相対パス
     */
    const RELATIVE_PATH = '/';
    /**
     * 復号対象データが存在しない
     */
    const VALUE_NOT_EXIST = '【REF!】';
    /**
     * csv表題出力フラグ
     */
    private $csv_title_output_flag;
    /**
     * csvファイルフルパス
     */
    private $csv_file_path;
    /**
    * csvファイル名
    */
    private $csv_file_name;
    /**
     * csvファイル名(前日分)
     */
    private $csv_file_name_yesterday;
    /**
     * commentファイル名
     */
    private $comment_file_name;
    /**
     * csvファイル表題
     */
    private $csv_title;
    /**
     * csv出力データ配列
     */
    public $csv_array;
    /**
     * csv出力データ配列
     */
    public $csv_array_yesterday;
    /**
     * Comment出力データ配列
     */
    public $comment_array;
    /**
     * 前日開始日時
     */
    private $yesterday_start;
    /**
     * 前日終了日時
     */
    private $yesterday_end;
    /**
     * 前々日終了日時
     */
    private $before_yesterday_end;
}