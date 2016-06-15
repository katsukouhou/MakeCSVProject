<?php

/**
 * Created by PhpStorm.
 * User: gehongpeng
 * Date: 2016/06/13
 * Time: 16:39
 */
class Decryption
{
    /**
     * __construct
     *
     * コンストラクタ関数
     *
     */
    public function __construct() {
    }

    /**
     * POST方式で復号値を取得する
     * @param array $data post用データ
     * @return string 復号されたデータ
     */
    public function decodeObject($data) {
        $post_data = http_build_query($data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $post_data,
                'timeout' => 5 * 60 // タイムアウト（単位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this::DECODE_URL, false, $context);

        //リトライ処理を実施
        $retry_time = 0;
        while(!$result && $retry_time <= $this::RETRY_TIMES){
            //
            $result = file_get_contents($this::DECODE_URL, false, $context);
            $retry_time++;
        }

        //戻り値を生成
        if(!$result) {
            //no response
            $response_flag = false;
            $decrypted_result = 'None';
            $retry_time = $retry_time - 1;
            $err_message = "No_response[Retry:{$retry_time}]";
        } else {
            $response_flag = json_decode($result,true)[$this::RESULT_INDEX];
            $decrypted_result = json_decode($result,true)[$this::DATA_INDEX][$this::TEXT_INDEX];
            $err_message = 'None';
        }

        return array($response_flag, $decrypted_result, $err_message);
    }

    /**
     * 復号URLを定義
     */
    const DECODE_URL = "http://nxbridge-api.inside.ai/export.php";
    /**
     * dataインデックス
     */
    const DATA_INDEX = 'data';
    /**
     * textインデックス
     */
    const TEXT_INDEX = 'text';
    /**
     * resultンデックス
     */
    const RESULT_INDEX = 'result';
    /**
     * リトライ処理回数
     */
    const RETRY_TIMES = 5;
}