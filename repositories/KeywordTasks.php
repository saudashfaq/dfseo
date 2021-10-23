<?php
require_once __DIR__.'/../functions.php';
require_once __DIR__.'/../query_helper.php';
require_once __DIR__.'/../db/DB.php';

//TODO: take common functions to a class and then extend this class from that one

class KeywordTasks
{

    private static string $table = 'keyword_tasks';
    private static int $max_attempts = 3;
    private static array $fillable = ['keyword_id', 'task_id', 'serp_type', 'data', 'status', 'post_task_attempt_due_at', 'post_task_attempts', 'get_task_attempt_due_at', 'get_task_attempts', 'se', 'se_type', 'device', 'created_at', 'updated_at'];
    private static array $all_table_fields = ['id', 'keyword_id', 'task_id', 'serp_type', 'data', 'status', 'post_task_attempt_due_at', 'post_task_attempts', 'get_task_attempt_due_at', 'get_task_attempts', 'se', 'se_type', 'device', 'created_at', 'updated_at'];


    public static function getAllDueTasksAwaitingData($limit = 1000) {

        $current_date_time = getCurrentDateTime();

        $sql =
            "SELECT
            keyword_tasks.id,
            keyword_tasks.serp_type, keyword_tasks.task_id, DATE_FORMAT(keyword_tasks.created_at, '%Y-%m-%d') as rank_for_date,
            keyword_tasks.keyword_id,
            campaigns.campaign_id,
            campaigns.url,
            user_accounts.id as user_account_id
            FROM ". self::$table ."
            INNER JOIN keywords ON keyword_tasks.keyword_id = keywords.keyword_id
            INNER JOIN campaigns ON keywords.campaign_id = campaigns.campaign_id
            INNER JOIN user_accounts ON keywords.user_account_id = user_accounts.id
            WHERE keyword_tasks.status = 2 
            AND keyword_tasks.get_task_attempt_due_at < '$current_date_time' 
            AND (keyword_tasks.get_task_attempts < ". self::$max_attempts . " OR keyword_tasks.get_task_attempts IS NULL) 
            ORDER BY keyword_tasks.created_at ASC 
            LIMIT $limit";

        $res = DB::run_mysql_query($sql, 'Error at line: '. __FILE__ . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . __LINE__);

        if( $res->num_rows > 0 ) {

            return mysqli_fetch_all($res, MYSQLI_ASSOC);

        }

        return NULL;

    }


    public static function getEligibleTasksForReattemptTaskPosting( $limit = 1000 ) {

        $sql = " 
        select 
        kt.serp_type, 
        CONCAT ('kid_',kt.keyword_id,'_tid_',kt.id) as tag,
        c.location_code, c.language_code, 
        keywords.keyword
        
        from ". self::$table ." as kt
        
        LEFT JOIN keywords ON kt.keyword_id = keywords.keyword_id
        LEFT JOIN campaigns c ON keywords.campaign_id = c.campaign_id
        LEFT JOIN user_accounts ua ON keywords.user_account_id = ua.id 
        where 
        
        kt.status = 1 and
        c.status = 1 and
        ua.status = 1 and
         
        (kt.post_task_attempt_due_at < current_timestamp OR kt.post_task_attempt_due_at IS NULL) and 
        (kt.post_task_attempts < ". self::$max_attempts . " or kt.post_task_attempts is null) 
        order by kt.created_at asc limit $limit ";
        $res = DB::run_mysql_query($sql, 'Error at line: '. __FILE__ . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . __LINE__);
        echo '<pre>';
        if( $res->num_rows > 0 ) {
            return mysqli_fetch_all($res, MYSQLI_ASSOC);
        }
        return NULL;

    }


    public static function getTaskDataByDFSTaskId($task_id) {
        $result = false;
        if( !empty($task_id) ) {
            $task_id = stripslashes(strip_tags(htmlspecialchars($task_id, ENT_QUOTES)));
            //$sql = "SELECT * FROM ". self::$table . " WHERE task_id = '".$task_id."' LIMIT 1";


            $sql = "SELECT
            keyword_tasks.id,
            keyword_tasks.serp_type, keyword_tasks.task_id, DATE_FORMAT(keyword_tasks.created_at, '%Y-%m-%d') as rank_for_date,
            keyword_tasks.keyword_id,
            campaigns.campaign_id,
            campaigns.url,
            user_accounts.id as user_account_id
            FROM ". self::$table ."
            INNER JOIN keywords ON keyword_tasks.keyword_id = keywords.keyword_id
            INNER JOIN campaigns ON keywords.campaign_id = campaigns.campaign_id
            INNER JOIN user_accounts ON keywords.user_account_id = user_accounts.id".
                " WHERE task_id = '".$task_id."' LIMIT 1";


            $res = DB::run_mysql_query($sql, 'Error at line: '. __FILE__ . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . __LINE__);
            if( $res->num_rows > 0 ) {
                $result = mysqli_fetch_assoc($res);
            }
        }
        return $result;
    }


    public static function insertData($data) {


        if( false != ($new_data = convertArrayToKeyValuesForInsert($data, self::$fillable)) ) {

            $sql = make_insert_query($new_data, self::$table);

            return DB::run_mysql_query($sql, 'Error at line: '. __FILE__ . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . __LINE__);

        }

        return NULL;

    }

    public static function updateData($data, $where) {

        if( false != ($new_data = convertArrayToKeyValuesForUpdate($data, self::$fillable)) ) {

            $where = convertArrayToWhereClause($where, self::$all_table_fields);

            $sql = make_update_query($new_data, self::$table, $where);

            return DB::run_mysql_query($sql, 'Error at line: '. __FILE__ . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . __LINE__ .'<br>');

        }

        return NULL;

    }


}