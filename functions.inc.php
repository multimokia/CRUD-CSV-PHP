<?php
    define("FILE_MODE_READ", "r"); //NOTE: DIES NOT CREATE A FILE IF IT DOESN'T EXIST
    define("FILE_MODE_READWRITE", "r+"); //NOTE: DIES NOT CREATE A FILE IF IT DOESN'T EXIST
    define("FILE_MODE_WRITE", "w");
    define("FILE_MODE_READWRITE_TRUNCATE", "w+");
    define("FILE_MODE_WRITE_APPEND", "a");
    define("FILE_MODE_READWRITE_APPEND", "a+");

    class TVRecord
    {
        public $id;
        public $type;
        public $brand;
        public $model;
        public $price;
        public $sale_price;
        public $description;

        public function __construct(
            $id,
            $type,
            $brand,
            $model,
            $price,
            $sale_price,
            $description
        )
        {
            $this -> id = $id;
            $this -> type = $type;
            $this -> brand = $brand;
            $this -> model = $model;
            $this -> price = $price;
            $this -> sale_price = $sale_price;
            $this -> description = $description;
        }

        public function equals(TVRecord $other): bool
        {
            return $this -> id == $other -> id;
        }

        public static function filter_records(
            //TODO: Maybe this? Get clarification
        )
        {

        }
    }

    //Context manager for files. Pass in a callable to have it executed assuming the given file
    //NOTE: callable MUST accept the file handle as an argument
    function open_file_context_manager($file, string $mode, callable $callable)
    {
        //Open file
        $file = fopen($file, $mode);

        //Execute func
        $result = $callable($file);

        //Dispose file
        fclose($file);

        //If the work function returned anything, we should return it
        return $result;
    }


    //Helper function for filtering
    function filter_records($data, $filters)
    {
        $data = array_filter(
            $data,
            // Build a func that checks if we don't meet a certain criteria. If we don't make it, then we exclude
            function($record) use ($filters) {
                foreach ($filters as $filter_key => $filter_val)
                {
                    if (!in_array($record[$filter_key], $filter_val))
                        { return false; }
                }
                return true;
            }
        );

        // Return the filtered values
        return $data;
    }

    //Dropdown maker
    function select($name, $options, $selected_choice = null)
    {
        $rv = "<select name='$name' id='$name'>";

        foreach ($options as $option_name)
        {
            $rv .= "<option value='$option_name'";

            if ($selected_choice == $option_name)
                { $rv .= " selected"; }

            $rv .= ">$option_name</option>";
        }

        $rv .= "</select>";

        echo $rv;
    }

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
    function get_all_records()
    {
        $rv = array();

        open_file_context_manager(
            "./data/records.csv", "r",
            function($file) {
                global $rv;
                $rv = fgetcsv($file, 1024);
            }
        );

        return $rv;
    }

    //Finds a record by id. Returns the record if found or null if not.
    function find_single_record($id)
    {

    }
?>
