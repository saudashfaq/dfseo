<?php
require_once __DIR__ . '/../repositories/KeywordTasks.php';
require_once __DIR__ . '/../repositories/KeywordTaskPostFailedLogs.php';


class TaskPostDataReceiverAndParser
{


    public static function parseKeywordsTaskPostResponse($response, $called_from_file)
    {

        /**$response like the following
         * $response['google_organic' => 'data', 'google_organic_maps' => 'data' , ....];
         */


        foreach ($response as $serp_type => $resp) {

            $current_time = date('Y-m-d H:i:s');
            $get_task_attempt_due_at = date('Y-m-d H:i:s', time() + 45 * 60);
            $post_task_attempt_due_at = date('Y-m-d H:i:s', time() + 5 * 60);

            if (isset($total_tasks)) {
                unset($total_tasks);
            }
            if (isset($total_errors)) {
                unset($total_errors);
            }
            if (isset($tasks)) {
                unset($tasks);
            }

            $total_tasks = $resp['tasks_count'];
            $total_errors = $resp['tasks_error'];
            $tasks = $resp['tasks'];

            if (count($tasks) > 0) {

                foreach ($tasks as $task) {

                    //sanitize the data with htmlspecialchars
                    array_walk_recursive($task, 'filter');


                    if (isset($status_code)) unset($status_code);
                    $status_code = $task['status_code'];

                    if (!empty($data_for_db)) {
                        unset($data_for_db);
                    }
                    //IMPORTANT: don't change keys.
                    //Keys are table column names in several tables
                    $data_for_db = [
                        'task_id' => $task['id'],
                        'status_code' => $status_code,
                        'status_message' => $task['status_message'],
                        'keyword_id' => get_kid_from_tag($task['data']['tag']),
                        'keyword_task_id' => get_tid_from_tag($task['data']['tag']),
                        'keyword' => $task['data']['keyword'],
                        'serp_type' => Serp::ALL_SERP_TYPES[$serp_type],
                        'se' => $task['data']['se'],
                        'se_type' => $task['data']['se_type'],
                        'device' => isset($task['data']['device']) ? $task['data']['device'] : 'desktop',
                        'data' => str_replace('"', "'",json_encode( $task)),
                    ];


                    if ($called_from_file == 'task_posting') {
                        //function was called from task_posting.php file

                        if ($status_code == 20100) {

                            if (!empty($additional_data)) {
                                unset($additional_data);
                            }

                            $additional_data = [
                                'status' => 2,
                                'get_task_attempt_due_at' => $get_task_attempt_due_at,
                                'created_at' => $current_time,
                            ];

                        } else {

                            //function was called from task_posting.php
                            if (!empty($additional_data)) {
                                unset($additional_data);
                            }

                            $additional_data = [
                                'status' => 1,
                                'post_task_attempt_due_at' => $post_task_attempt_due_at,
                                'created_at' => $current_time,
                            ];

                        }
                        //insert the record
                        $new_tid = self::insertIntoKeywordTaskTable($data_for_db, $additional_data);




                    } elseif ($called_from_file == 'reattempt_task_posting_for_failed_keywords') {

                        //function was called from reattempt_task_posting file

                        if ($status_code == 20100) {
                            // success posting again
                            if (!empty($additional_data)) {
                                unset($additional_data);
                            }

                            $additional_data = [
                                'status' => 2,
                                'get_task_attempt_due_at' => $get_task_attempt_due_at,
                                'updated_at' => "$current_time",
                            ];


                        } else {
                            // failed posting again
                            if (!empty($additional_data)) {
                                unset($additional_data);
                            }

                            $additional_data = [
                                'post_task_attempt_due_at' => $post_task_attempt_due_at,
                                'post_task_attempts' => "post_task_attempts + 1",
                                'updated_at' => "$current_time",
                            ];

                        }
                        //TODO: if the following table doesn't get updated while the task
                        // posting was successful then its a big problem
                        self::updateKeywordTaskTable($data_for_db, $additional_data);
                    }


                    if ($status_code != 20100) {

                        if (empty($data_for_db['keyword_task_id'])) {
                            isset($new_tid) ? $data_for_db['keyword_task_id'] = $new_tid : 0;
                        }
                        $data_for_db['created_at'] = $current_time;
                        self::InsertIntoKeywordTaskPostFailedLogsTable($data_for_db);

                    }


                }

            }

        }

    }


    private static function insertIntoKeywordTaskTable($data, $additional_data = [])
    {

        //I can check status_code from $data and decide what additional_data needs to add
        if (count($additional_data) > 0) {

            $data = array_merge($additional_data, $data);

        }

        return KeywordTasks::insertData($data);
    }


    private static function updateKeywordTaskTable($data, $additional_data = [])
    {

        if (count($additional_data) > 0) {

            $data = array_merge($additional_data, $data);

        }

        $where = [
            'id' => $data['keyword_task_id'],
        ];


        KeywordTasks::updateData($data, $where);
    }


    private static function InsertIntoKeywordTaskPostFailedLogsTable($data, $additional_data = [])
    {

        if (count($additional_data) > 0) {

            $data = array_merge($additional_data, $data);

        }

        KeywordTaskPostFailedLogs::insertData($data);
    }


}