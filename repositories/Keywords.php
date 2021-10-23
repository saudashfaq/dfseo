<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__.'/../query_helper.php';
require_once __DIR__.'/../db/DB.php';
require_once __DIR__.'/../vars_and_const/Serp.php';

//TODO: take common functions to a class and then extend this class from that one

class Keywords
{

    private static string $table = 'keywords';
    private static array $fillable = ['user_account_id', 'campaign_id', 'keyword', 'latest_rank_checked_on', 'created_at', 'updated_at'];
    private static array $all_table_fields = ['user_account_id', 'campaign_id', 'keyword', 'latest_rank_checked_on', 'created_at', 'updated_at'];



    public static function getEligibleKeywordsForTaskPosting( $limit = 1000 ):array {

        echo $current_time = date('H:i:s');
        echo '<br>';
        echo $current_date = date('Y-m-d');
        echo '<br>';

        $sql =
            "
            SELECT keywords.keyword_id,
            CONCAT ('kid_',keywords.keyword_id) as tag,
            keywords.keyword, 
            campaigns.location_code, campaigns.language_code
            FROM keywords 
            INNER JOIN campaigns on keywords.campaign_id = campaigns.campaign_id 
            INNER JOIN user_accounts on keywords.user_account_id = user_accounts.id 
            
            WHERE user_accounts.status = 1
            AND campaigns.status = 1
            AND campaigns.rank_check_due_time < now()
            AND  keywords.latest_rank_checked_on < current_date 
            GROUP BY keywords.keyword_id limit $limit ";

        $res = DB::run_mysql_query($sql, 'Error in class: '. __CLASS__. ' in file: '. __FILE__ . ' at line: '. __LINE__);

        if( $res->num_rows > 0 ) {

            return mysqli_fetch_all($res, MYSQLI_ASSOC);

        }
        return [];


    }


    public static function addNewKeyWordsInDB($keywords = []):void {

        //todo: before adding keywords sanitize them
        //mb_convert_encoding(str_replace(':', ' ', "albert einstein"), "UTF-8"),

    }




    /**
     * @param $ids array
     */
    public static function updateKeywordsDueDateThatWerePickedUpForTaskPosting(array $ids) {

        if( is_array($ids) && count($ids) > 0 ) {

            $ids_string = implode(',', array_map('intval', $ids));

            $sql = "update keywords set latest_rank_checked_on = current_date where keyword_id IN ( ". $ids_string ." )";

            return DB::run_mysql_query($sql, __FILE__ . ' ' . __CLASS__ . ' at line number '. __LINE__);

        }

    }

    public static function insertData($data) {
        if( false != ($new_data = convertArrayToKeyValuesForInsert($data, self::$fillable)) ) {

            $sql = make_insert_query($new_data, self::$table);
            return DB::run_mysql_query($sql, 'Error at line: '. __FILE__ . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . __LINE__);

        }

        return NULL;
    }

    public static function updateData($data, $where) {
        if( false != ($new_data = convertArrayToKeyValuesForInsert($data, self::$fillable)) ) {

            $sql = make_insert_query($new_data, self::$table);
            return DB::run_mysql_query($sql, 'Error at line: '. __FILE__ . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . __LINE__);

        }

        return NULL;
    }



}

