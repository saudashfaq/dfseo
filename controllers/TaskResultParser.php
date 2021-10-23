<?php
require_once __DIR__ . '/../repositories/KeywordTasks.php';
require_once __DIR__ . '/../repositories/KeywordRankings.php';


class TaskResultParser
{


    public static function parseTaskGetResponse($response, $keyword_task_data_from_db = null, $called_from_file = null)
    {

        if( $response['status_code'] === 20000 && $response['tasks_count'] === 1 ) {

            $current_time = date('Y-m-d H:i:s');
            $get_task_attempt_due_at = date('Y-m-d H:i:s', time() + 5 * 60);

            $task = $response['tasks'][0];
            $task_id = $task['id'];
            $status_message = htmlspecialchars($task['status_message'], ENT_QUOTES);

            if( $response['tasks_error'] !== 0 && !null($called_from_file) ) {

                $keyword_task_data_from_db['data'] = $status_message;
                $keyword_task_data_from_db['updated_at'] = $current_time;
                $keyword_task_data_from_db['status'] = 3;
                $keyword_task_data_from_db['get_task_attempts'] = "get_task_attempts + 1";
                $keyword_task_data_from_db['get_task_attempt_due_at'] = $get_task_attempt_due_at;

                self::updateKeywordTaskTable( $keyword_task_data_from_db );

                return;
            }

            $data = $task['data'];
            //$keyword_task_id = get_tid_from_tag($data['tag']);
            //$keyword_id = get_kid_from_tag($data['tag']);
            $result_array = $task['result'][0];
            $url = $keyword_task_data_from_db['url'];
            $items = $result_array['items'];

            unset($result_array['items']);



            $data = [
                'task_id' => $task_id,
                'check_url' => $url,
                'created_at' => $current_time,
                'updated_at' => $current_time,

            ];


            $item = self::findUrlInItems($url, $items);
            $data['result_stats'] = array_merge($result_array, $item);

            // there is much dependable data that is going into
            // the following function as additional_data
            $inserted_in_kr_table = self::insertIntoKeywordsRankingTable($data, $keyword_task_data_from_db);

            $keyword_task_data_from_db['status'] = 5;
            $keyword_task_data_from_db['updated_at'] = $current_time;
            unset($keyword_task_data_from_db['created_at']);


            //Now update keyword_tasks table only if result has been updated in keywords ranking table
            if( !empty($inserted_in_kr_table) ) {

                self::updateKeywordTaskTable($data, $keyword_task_data_from_db);

            }

        }

    }



    private static function findUrlInItems($check_url, $items) {

        foreach ($items as $item) {

            if( stristr($item['url'], $check_url) !== false && $item['type'] !== 'paid' ) {

                return $item;
            }

        }

        return [];

    }




    private static function updateKeywordTaskTable($data, $additional_data = [])
    {

        if (count($additional_data) > 0) {

            $data = array_merge($additional_data, $data);

        }

        $where = [
            'id' => $data['id'],
        ];

        if(isset($data['created_at'])) {
            unset($data['created_at']);
        }

        //print_r($data);
        //die();

        KeywordTasks::updateData($data, $where);
    }


    private static function insertIntoKeywordsRankingTable($data, $additional_data = []) {

        if (count($additional_data) > 0) {

            $data = array_merge($additional_data, $data);

        }

        return KeywordRankings::receiveDataAndPrepareForInsertOrUpdate($data);


    }


}