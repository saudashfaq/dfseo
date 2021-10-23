<?php
function make_insert_query($new_data_prepared, $table_name): string
{

    return 'INSERT INTO ' . $table_name . ' ' . $new_data_prepared;

}

function make_update_query($new_data_prepared, $table_name, $where_clause_prepared = null): string
{

    return 'UPDATE ' . $table_name . ' SET ' . $new_data_prepared . ' where ' . $where_clause_prepared;

}

function convertArrayToKeyValuesForInsert($data, $fillables = [])
{
    $keys = '';
    $vals = '';

    if (!empty($fillables)) {

        $fillables_present = true;

    }

    foreach ($data as $key => $val) {

        //if the key provided in $data is not a table column name
        if (!empty($fillables) && in_array($key, $fillables) === false) {
            continue;
        }

        $keys .= $key . ', ';
        $vals .= '"' . $val . '", ';

    }

    if ($keys !== '') {

        $keys = substr(trim($keys), 0, -1);
        $vals = substr(trim($vals), 0, -1);


        return '(' . $keys . ')' . ' values ' . '(' . $vals . ')';

    }

    return false;

}


function convertArrayToKeyValuesForUpdate($data, $fillables = [])
{
    $key_values = '';
    $int_keys = ['post_task_attempts', 'get_task_attempts'];

    if (!empty($fillables)) {
        $fillables_present = true;
    }


    foreach ($data as $key => $val) {

        //if the key provided in $data is not a table column name
        if ($fillables_present && in_array($key, $fillables) === false) {
            continue;
        }

        if (in_array($key, $int_keys)) {

            $key_values .= ' ' . $key . ' = ' . $val . ', ';

        } else {

            $key_values .= ' ' . $key . ' = "' . $val . '", ';
        }


    }


    if ($key_values !== '') {

        return substr(trim($key_values), 0, -1);

    }

    return false;

}


function convertArrayToWhereClause($data, $all_table_fields = [])
{

    $where = '';

    if (!empty($all_table_fields)) {
        $all_table_fields_present = true;
    }

    foreach ($data as $key => $val) {

        //if the key provided in $data is not a table column name
        if (!empty($all_table_fields_present) && in_array($key, $all_table_fields) === false) {
            continue;
        }

        $where .= ' ' . $key . ' = ' . '"' . $val . '" and';

    }


    if ($where !== '') {

        return substr($where, 0, -3);

    }

    return false;

}

