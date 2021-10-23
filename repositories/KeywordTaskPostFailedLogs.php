<?php
require_once __DIR__.'/../functions.php';

//TODO: take common functions to a class and then extend this class from that one

class KeywordTaskPostFailedLogs
{

    private static string $table = 'keyword_task_post_failed_logs';
    private static array $fillable = ['keyword_task_id', 'data', 'created_at']; //only columns that can be inserted the data into
    private static array $all_table_fields = ['keyword_task_id', 'data', 'created_at'];


    public static function insertData($data) {

        if( false != ($new_data = convertArrayToKeyValuesForInsert($data, self::$fillable)) ) {

            $sql = make_insert_query($new_data, self::$table);
            return DB::run_mysql_query($sql, 'Error at line: '. __FILE__ . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . __LINE__);

        }

        return NULL;

    }


    public static function updateData($data, $where) {

        if( false != ($new_data = convertArrayToKeyValuesForInsert($data, self::$fillable)) ) {

            $where = convertArrayToWhereClause($where, self::$all_table_fields);

            $sql = make_update_query($new_data, self::$table, $where);

            return DB::run_mysql_query($sql, 'Error at line: '. __FILE__ . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . __LINE__);

        }

        return NULL;

    }

}