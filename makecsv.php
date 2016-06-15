<?php
/**
 * 性能テスト用のソースファイルの読み込み
 */
require_once './include/Utility.php';
memory_usage_start();
pro_start_time();

/**
 * DB接続を行うソースファイルの読み込み
 */
require_once './include/DBManager.php';
/**
 * CSV出力を行うソースファイルの読み込み
 */
require_once './include/CSVManager.php';
/**
 * 復号化を行うソースファイルの読み込み
 */
require_once './include/Decryption.php';
/**
 * ログ管理を行うソースファイルの読み込み
 */
require_once './include/LogManager.php';

/**
 * TimeZoneを設定
 */
date_default_timezone_set('Asia/Tokyo');
/**
 * スクリプトの実行制限時間を設定
 */
set_time_limit(0);

//
try{
    //CSVManagerを生成
    $csv_manager = new CSVManager();
    //DecryptionManagerを生成
    $decryption_manager = new Decryption();
    //LogManagerを生成
    $log_manager = new LogManager();

    //DBを接続
    $dbh = db_connect();
    //処理対象クエリ文字列を作成
    $sql_target = "SELECT DISTINCT organization_id, paper_id, set_id
                 FROM {$file_parts_table}
                 WHERE status = {$unprocessed}";
    // SQL処理を実行
    $stt_target_list = sql_execute($dbh, $sql_target);
    $query_num = $stt_target_list->rowCount();
    if($query_num != 0) {
        /**
         * 処理対象リストを作成
         */
        $target_list = array();
        while ($row = $stt_target_list->fetch(PDO::FETCH_ASSOC)) {
            $document_id_index = 0;
            $document_id = explode('_', $row['paper_id'])[$document_id_index];
            $unique_id = $document_id . '_' . $row['set_id'];
            if (!in_array($unique_id, $target_list[$row['organization_id']])) {
                $target_list[$row['organization_id']][] = $unique_id;
            }
        }
        //
        $stt_target_list = null;

        /**
         * 処理対象データをDB file_partsからメモリへ読み込み、csvファイルへ出力
         */
        foreach ($target_list as $target_list_key => $tmp_value) {
            foreach ($tmp_value as $tmp_key => $target_list_value) {
                /**
                 * set_idを取り出す
                 */
                $set_id_index = 1;
                $set_id = explode('_', $target_list_value)[$set_id_index];

                /**
                 * group_idからdocument_idを取り出す
                 */
                $document_id_index = 0;
                $document_id = explode('_', $target_list_value)[$document_id_index];

                /**
                 * csvファイルパスを生成
                 */
                $csv_manager->createFilePath($target_list_key, $document_id);

                /**
                 * 処理対象内容を復号化し、csv出力リストへ入れる
                 */
                //処理対象クエリ文字列を作成
                $sql_target = "SELECT entry_id, parts_d_code, item_name, type, created_at, paper_id
                       FROM {$file_parts_table}
                       WHERE organization_id = '{$target_list_key}'
                       AND set_id = '{$set_id}'
                       ORDER BY entry_id";
                //SQL処理を実行
                $stt_target_data_list = sql_execute($dbh, $sql_target);
                //csvデータを生成
                $csv_manager->csv_array = null;
                //commentデータを生成
                $csv_manager->comment_array = null;
                $csv_format_title = array();
                //
                $update_status_sql = '';
                $update_price_sql = '';
                $update_character_aggregation_sql = '';
                $skip_flag = false;
                while ($row_csv_data = $stt_target_data_list->fetch(PDO::FETCH_ASSOC)) {
                    //csvフォーマットを取得
                    $csv_format_title[] = $row_csv_data['item_name'];
                    //復号化を実施
                    $post_data = array(
                        'parts_d_code' => $row_csv_data['parts_d_code']
                    );
                    list($response_flag, $response_value, $err_message) = $decryption_manager->decodeObject($post_data);
                    $decrypted_result = $response_value;

                    //
                    if(!$response_flag) {
                        //作成日付を取得
                        $target_datetime = date("Y-m-d H:i:s", strtotime($row_csv_data['created_at']));
                        //
                        $target_paper_id = $row_csv_data['paper_id'];
                        $target_item_name = $row_csv_data['item_name'];
                        $target_entry_id = $row_csv_data['entry_id'];
                        //
                        /*
                        $csv_manager->comment_array[$target_paper_id][$target_entry_id]['paper_id'] = "[paper_id:$target_paper_id]";
                        $csv_manager->comment_array[$target_paper_id][$target_entry_id]['entry_id'] = "[entry_id:$target_entry_id]";
                        $csv_manager->comment_array[$target_paper_id][$target_entry_id]['item_name'] = "[item_name:$target_item_name]";
                        */
                        //
                        if ($target_datetime <= $csv_manager->before_yesterday_end) {
                            //前々日以前の場合は、値を設定
                            $decrypted_result = $csv_manager::VALUE_NOT_EXIST;
                            //Commentを生成
                            //$csv_manager->comment_array[$target_paper_id][$target_entry_id]['decrypted_result'] = "[set_to:$decrypted_result]";

                            //Comment messageを生成
                            $comment_message = "[set:$decrypted_result]" . "[paper_id:{$target_paper_id}]" .
                                "[entry_id:{$target_entry_id}]" . "[item_name:{$target_item_name}]";
                            $csv_manager->addCommentMessage($comment_message);


                        } elseif ($target_datetime >= $csv_manager->yesterday_start
                                    && $target_datetime <= $csv_manager->yesterday_end) {
                            //前日の場合は、スキップ
                            $skip_flag = true;
                            //Commentを生成
                            //$csv_manager->comment_array[$target_paper_id][$target_entry_id]['decrypted_result'] = "[skip]";

                            //
                            $comment_message = "[skip]" . "[paper_id:{$target_paper_id}]";
                            $csv_manager->addCommentMessage($comment_message);

                        } else {
                            //当日の場合は？
                            $skip_flag = true;
                        }

                        //Comment messageを生成
                        /*
                        $comment_message = "[result:{$decrypted_result}]" . "[paper_id:{$target_paper_id}]" .
                                           "[entry_id:{$target_entry_id}]" . "[item_name:{$target_item_name}]";
                        $csv_manager->addCommentMessage($comment_message);
                        */

                        //log messageを生成
                        $log_message = "[export.php][response:false]" . "[response_value:{$response_value}]" .
                            "[err_msg:$err_message]" . "[set:$decrypted_result]" .
                            "[paper_id:{$target_paper_id}]" . "[entry_id:{$target_entry_id}]" .
                            "[item_name:{$target_item_name}]";
                        $log_manager->addLogMessage('ERR', $log_message);

                        //
                        if ($skip_flag) {
                            break;
                        }
                    }



                    //バリエーション処理を実施
                    /*↓↓↓ [start]バリエーションを実施<未実装> ↓↓↓*/
                    /*↑↑↑ [end]バリエーションを実施<未実装> ↑↑↑*/

                    //resultを設定
                    $csv_manager->csv_array[$target_list_key][$target_list_value][$row_csv_data['entry_id']] =
                        $decrypted_result;

                    //priceを算出し
                    //$target_price = cal_price($decrypted_result, $row_csv_data['type']);
                    $target_price = cal_price_without_type($decrypted_result);
                    //price更新用のSQL文を生成
                    $update_price_sql .=
                        "UPDATE {$file_parts_table}
                         SET price = {$target_price}
                         WHERE parts_d_code = '{$row_csv_data['parts_d_code']}'
                         AND set_id = '{$set_id}';";

                    //status更新用のSQL文を生成
                    $update_status_sql .=
                        "UPDATE {$file_parts_table}
                         SET status = {$processed}
                         WHERE parts_d_code = '{$row_csv_data['parts_d_code']}'
                         AND set_id = '{$set_id}';";

                    //不読と可読の文字数を算出
                    list($read_count, $unread_count) = aggregate_characters($decrypted_result);
                    //不読と可読の文字数の更新用のSQLを生成
                    $update_character_aggregation_sql .=
                        "UPDATE {$file_parts_table}
                         SET read_count = {$read_count}, unread_count = {$unread_count}
                         WHERE parts_d_code = '{$row_csv_data['parts_d_code']}'
                         AND set_id = '{$set_id}';";
                }//while

                //
                if (!$skip_flag) {
                    /**
                     * csvファイルへ出力
                     */
                    $csv_manager->csv_title = $csv_format_title;
                    $csv_manager->outputCsv();

                    /**
                     * csv生成が完了したら、DBを更新
                     */
                    try {
                        //トランザクションを開始
                        $dbh->beginTransaction();
                        //statusを更新
                        //$dbh->exec($update_status_sql);
                        //priceを更新
                        $dbh->exec($update_price_sql);
                        //不読と可読の文字数を更新
                        $dbh->exec($update_character_aggregation_sql);
                        //トランザクションをコミット
                        $dbh->commit();
                    } catch (Exception $e) {
                        //トランザクションをロールバック
                        $dbh->rollBack();
                        $log_manager->addLogMessage('ERR', 'DB update Failed:' . $e->getMessage());
                    }//try & catch
                }//if
                /**
                 * commentファイルへ出力
                 */
                $csv_manager->outputCommentFile();
            }//foreach $tmp_value
        }//foreach $target_list
    }//if
} catch (Exception $e){
    //
    $log_manager->addLogMessage('ERR', $e->getMessage());
} finally {
    /**
     * logファイルへ出力
     */
    $log_manager->outputLog();
}//try & catch & finally

//
$dbh = null;

//性能結果を表示
memory_usage_end();
pro_end_time();