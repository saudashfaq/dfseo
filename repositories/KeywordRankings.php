<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../query_helper.php';
require_once __DIR__ . '/../db/DB.php';
require_once __DIR__ . '/../vars_and_const/Serp.php';

//TODO: take common functions to a class and then extend this class from that one

class KeywordRankings
{

    private static string $table = 'keyword_rankings';
    private static array $fillable = ['user_account_id', 'campaign_id', 'keyword_id', 'rank_for_date', 'google_organic', 'google_organic_stats', 'google_organic_change', 'google_local', 'google_local_stats', 'google_local_change', 'google_mobile', 'google_mobile_stats', 'google_mobile_change', 'bing_organic', 'bing_organic_stats', 'bing_organic_chnage', 'created_at', 'updated_at'];
    private static array $all_table_fields = ['id', 'user_account_id', 'campaign_id', 'keyword_id', 'rank_for_date', 'google_organic', 'google_organic_stats', 'google_organic_change', 'google_local', 'google_local_stats', 'google_local_change', 'google_mobile', 'google_mobile_stats', 'google_mobile_change', 'bing_organic', 'bing_organic_stats', 'bing_organic_chnage', 'created_at', 'updated_at'];


    public static function receiveDataAndPrepareForInsertOrUpdate($data)
    {

        $rank = !empty($data['result_stats']['rank_group']) ? $data['result_stats']['rank_group'] : 0;

        $result_stats = $data['result_stats'];

        if (!empty($result_stats)) {

            array_walk_recursive($result_stats, 'filter');

        }

        $result_stats = str_replace('"', "'", json_encode($result_stats));

        // 1=google-organic, 2=google-local, 3=google-mobile, 4=bing-organic
        switch ($data['serp_type']) {
            case 1:
                $data['google_organic'] = $rank;
                $data['google_organic_stats'] = $result_stats;
                break;
            case 2:
                $data['google_local'] = $rank;
                $data['google_local_stats'] = $result_stats;
                break;
            case 3:
                $data['google_mobile'] = $rank;
                $data['google_mobile_stats'] = $result_stats;
                break;
            case 4:
                $data['bing_organic'] = $rank;
                $data['bing_organic_stats'] = $result_stats;
                break;
        }

        $where = [ 'keyword_id' => $data['keyword_id'],
            'user_account_id' => $data['user_account_id'],
            'campaign_id' => $data['campaign_id'],
            'rank_for_date' => $data['rank_for_date']
        ];

        $id = self::getRecordID($where);

        if ($id === false) {

            //insert new record
            $res = self::insertData($data);

        } else {

            //update existing record
            if( !empty( $data['created_at'] ) ){ unset($data['created_at']); };
            $res = self::updateData($data, ['id' => $id]);

        }

        return $res;
    }


    public static function insertData($data) {


        if( false != ($new_data = convertArrayToKeyValuesForInsert($data, self::$fillable)) ) {

            $sql = make_insert_query($new_data, self::$table);

            return DB::run_mysql_query($sql, 'Error at line: '. __FILE__ . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . __LINE__);

        }

        return NULL;

    }


    public static function updateData($data, $where)
    {
        if (false != ($new_data = convertArrayToKeyValuesForUpdate($data, self::$fillable))) {

            $where = convertArrayToWhereClause($where, self::$all_table_fields);

            $sql = make_update_query($new_data, self::$table, $where);

            return DB::run_mysql_query($sql, 'Error at line: ' . __FILE__ . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . __LINE__ . '<br>');

        }

        return NULL;
    }


    public static function getRecordID($where)
    {

        $where = convertArrayToWhereClause($where, self::$all_table_fields);

        $table = self::$table;

        $sql = "SELECT id FROM $table WHERE $where LIMIT 1";

        $res = DB::run_mysql_query($sql, 'Error at line: ' . __FILE__ . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . __LINE__);

        if ( $res->num_rows > 0 ) {

            return mysqli_fetch_assoc($res)['id'];

        }

        return false;

    }


}


//$res = KeywordRankings::getRecordID(['user_account_id' => 1, 'campaign_id' => 1, 'keyword_id' => 1, 'ranking_for_date' => '2021-09-14']);
//print_r($res);