<?php
    define("FILE_MODE_READ", "r"); //NOTE: DIES NOT CREATE A FILE IF IT DOESN'T EXIST
    define("FILE_MODE_READWRITE", "r+"); //NOTE: DIES NOT CREATE A FILE IF IT DOESN'T EXIST
    define("FILE_MODE_WRITE", "w");
    define("FILE_MODE_READWRITE_TRUNCATE", "w+");
    define("FILE_MODE_WRITE_APPEND", "a");
    define("FILE_MODE_READWRITE_APPEND", "a+");

    //Context manager for files. Pass in a callable to have it executed assuming the given file
    //NOTE: callable MUST accept the file handle as an argument
    function open_file_context_manager($file, $mode, $callable)
    {
        //Open file
        $file = fopen($file, $mode);

        //Execute func
        $callable($file);

        //Dispose file
        fclose($file);
    }

    //Helper function for
    function create(
        $type,
        $brand,
        $model,
        $size,
        $base_price,
        $sale_price,
        $description
    )
    {
        open_file_context_manager(
            "./data/records.csv", "a+",
            function($file) {
                echo $file;
            }
        );
    }

    //READ
    //Finds a record by id. Returns the record if found or null if not.
    function find_single_record($id)
    {

    }
?>
