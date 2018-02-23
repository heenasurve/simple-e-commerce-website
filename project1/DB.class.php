<?php
require_once "/home/hfs7520/db_conn.php";

class DB
{

// Store the single instance of DB
    private static $db_instance;

    private $server = ""; //database server
    private $user = ""; //database login name
    private $pass = ""; //database login password
    private $database = ""; //database name
    private $pre = ""; //table prefix
    private $mysqli;        //mysqli object
    private $error;         //error

    private $affected_rows = 0; //number of rows affected by SQL query
    private $insert_id;         //last insert id
    private $results = array(); //results of query
    private $colum_info = array();


    /**
     * desc: constructor
     * usage: $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
     *             or $db = new Database(); and will using info from config file
     */
    private function __construct($host = DB_HOST, $user = DB_USER, $pass = DB_PASS, $database = DB_NAME)
    {
        $this->server = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->database = $database;

        //connect
        $this->mysqli = new mysqli($host, $user, $pass, $database);

        if ($this->mysqli->connect_errno) {
            $this->error = "Connect failed: " . $this->mysqli->connect_error;
            echo $this->error;
            die();
        } else {
            $this->error = "";
        }
    }
    function do_query( $query, $vars=array(), $types=array() ) {

        //determine which type of query: select, insert, update, delete
        $select = false;
        $delete = false;
        $insert = false;
        $update = false;
        $field_cnt = 0;
        $this->results = array();
        $this->error = "";
        $this->colum_info = array();


        //first get the command and convert to lower case
        $command = strtolower( substr( trim( $query ), 0, strpos( $query, " " ) ) );
        switch ( $command ) {
            case "select":
                $select = true;
                break;
            case "insert":
                $insert = true;
                break;
            case "update":
                $update = true;
                break;
            case "delete":
                $delete = true;
                break;
        }

        // data integrity check: ensure that the number of parameters and specified data types matches the query
        if ( substr_count( $query, "?" ) != count( $vars ) || count( $vars ) != count( $types ) ) {
            $this->error = "Wrong number of parameters for query";
            return $this->error;
        }
        else if ( $stmt = @$this->mysqli->prepare( $query ) ) {
            //echo $query;
            if ( $select ) {
                //get the column information
                $meta = $stmt->result_metadata();
                $field_cnt = $meta->field_count;
                $col_names = array();
                while ( $colinfo=$meta->fetch_field() ) {
                    array_push( $col_names, $colinfo->name );
                }
            }

            //call the bind_param function ?
            if ( count( $vars ) > 0 ) {

                //create the datatypes and array of values for query and binding params
                $list = array(); // list of parameters that will be passed to bind_param()
                $bindtypes="";   // string of datatypes that will be first param for bind_param()
                $i=0;
                foreach ( $types as $type ) {
                    $bindtypes .= $type;
                }
                //!! is this loop necessary? or can $vars just be used instead of $list
                foreach ( $vars as $val ) {
                    $bind_name = 'bind' . $i; //give them an arbitrary name
                    $$bind_name = $val;       //add the parameter to the variable variable
                    $list[] =&$$bind_name;
                    $i++;
                }

                // add the string of datatypes as the first index in the list
                array_unshift( $list, $bindtypes );

                //call the function bind_param with dynamic params
                call_user_func_array( array( $stmt, "bind_param" ), $list );
//        print_r($query);
//        print_r( $vars );
//        print_r($list);
            }

            if ( $select ) {
                //declare and bind the results
                $res = array_fill( 0, $field_cnt, '' ); // creates an array with $field_cnt empty indexes
                $bind_res[0] = $stmt; //make the statement the first element
                //add references to columns array to the parameter list
                for ( $i=0; $i<$field_cnt; $i++ ) {
                    //!! is it necessary to pass by reference?
                    $bind_res[]=&$res[$i];
                }

                //pass the array to the bind results function
                //!! can we pass array( $stmt, 'bind_result' ) instead?
                call_user_func_array( "mysqli_stmt_bind_result", $bind_res );
            }

            //execute the statement
            $executed_successfully = @$stmt->execute();

            if ( $select ) {
                //get resultset for metadata
                $metadata = $stmt->result_metadata();

                // retrieve field information from metadata result set
                $field = $metadata->fetch_fields();

                foreach ( $field as $val ) {
                    $this->column_info[$val->name] = array(
                        "length" => $val->length,
                        "type"   => $this->get_type_name( $val->type, $val->length, $val->decimals, $val->charsetnr, $val->flags ),
                        "flags"  => $val-flags
                    );
                }
            }

            @$stmt->store_result();

            //get the affected number of rows
            //!! can't we always use affected_rows ?
            if ( $insert || $update || $delete ) {
                $this->affected_rows = @$stmt->affected_rows;
            }
            else {
                $this->affected_rows = @$stmt->num_rows;
            }

            //get the inserted record's primary key ID
            if ( $insert ) {
                $this->insert_id = $stmt->insert_id;
            }
            else {
                $this->insert_id = null;
            }

            if ( $select ) {
                // fetch values and make associative array
                while ( $stmt->fetch() ) {
                    $row = array();
                    for ( $i=0; $i<$field_cnt; $i++ ) {
                        $row[$col_names[$i]] = $res[$i];
                    }
                    $this->results[] = $row;
                }
            }

            if ( $executed_successfully ) {
                $this->error="";
            }
            else if ( isset( $this->mysqli ) ) {
                $this->error = $this->mysqli->error;
            }
            else {
                $this->error="Error with last statement execution *" . mysql_error() . "*";
            }

            $stmt->close();

        }//prepare query
        else {
            if ( isset( $this->mysqli ) ) {
                $this->error = $this->mysqli->error;
            }
            else {
                $this->error="Error with last statement execution <".mysql_error().">";
            }
        }

        return $this->error;
    }
    function fetch_all_array() {
        /*echo "<pre>";
        echo $this->results['cnt_products'];
        echo "</pre>";*/
        return $this->results;
    }

    private function get_type_name( $code, $size, $decimals, $charset, $flags ) {
        switch ($code) {
            case 1    : return "TINYINT($size)";
            case 2    : return "SMALLINT($size)";
            case 3    : return "INT($size)";
            case 4    : return "FLOAT($size, $decimals)";
            case 5    : return "DOUBLE($size, $decimals)";
            case 6    : return "NULL";
            case 7    : return "TIMESTAMP($size)";
            case 8    : return "BIGINT($size)";
            case 9    : return "MEDIUMINT($size)";
            case 10   : return "DATE";
            case 11   : return "TIME($size)";
            case 12   : return "DATETIME($size)";
            case 13   : return "YEAR($size)";
            case 14   : return "NEWDATE";   // according to http://www.redferni.uklinux.net/mysql/MySQL-Protocol.html
            case 16   : return "BIT($size)";
            case 246  : return "DECIMAL($size, $decimals)";
            case 247  : return "ENUM";      // according to http://www.redferni.uklinux.net/mysql/MySQL-Protocol.html
            case 248  : return "SET";       // according to http://www.redferni.uklinux.net/mysql/MySQL-Protocol.html
            case 252  : if ( $charset==63 ) { // 63 is binary pseudocollation, used for non-string types
                if ( $size==255 )       return 'TINYBLOB / TINYTEXT BINARY';
                if ( $size==65535 )     return 'BLOB / TEXT BINARY';
                if ( $size==16777215 )  return 'MEDIUMBLOB / MEDIUMTEXT BINARY';
                if ( $size==-1 )        return 'LONGBLOB / LONGTEXT BINARY';
            }
            else {
                if ( $size==255 )       return 'TINYTEXT';
                if ( $size==65535 )     return 'TEXT';
                if ( $size==16777215 )  return 'MEDIUMTEXT';
                if ( $size==-1 )        return 'LONGTEXT';
            }
            case 253  : return "VARCHAR($size)";
            case 254  : if ( $flags==4481 ) {
                return "ENUM";          // is this reliable?
            }
            elseif ( $flags==6273 ) {
                return "SET";
            }
            else {
                return "CHAR($size)";
            }
            case 255  : return "GEOMETRY";
            default   : return "?";
        }
    }
    function get_affected_rows() {
        return $this->affected_rows;
    }
    function get_insert_id() {
        return $this->insert_id;
    }
    public static function getInstance() {
        if ( !self::$db_instance ) {
            self::$db_instance = new DB();
        }

        return self::$db_instance;
    }

}

?>