<?php
    //Log errors to file
    ini_set('log_errors', 1);
    ini_set('error_log', "./error.log");
?>
<?php
    define("FILE_MODE_READ", "r"); //NOTE: DIES NOT CREATE A FILE IF IT DOESN'T EXIST
    define("FILE_MODE_READWRITE", "r+"); //NOTE: DIES NOT CREATE A FILE IF IT DOESN'T EXIST
    define("FILE_MODE_WRITE", "w");
    define("FILE_MODE_READWRITE_TRUNCATE", "w+");
    define("FILE_MODE_WRITE_APPEND", "a");
    define("FILE_MODE_READWRITE_APPEND", "a+");

    // id : TVRecord
    $records = array();

    //Load all records from persistent
    $records = get_all_records();

    function ascii_add($str)
    {
        $rv = 0;
        foreach (str_split($str) as $chr)
            { $rv += ord($chr); }

        return $rv;
    }

    class TVRecord
    {
        public string $id;
        public string $type;
        public string $brand;
        public string $model;
        public int $size;
        public float $price;
        public ?float $sale_price;
        public ?string $description;

        public function __construct(
            $id,
            $type,
            $brand,
            $model,
            $size,
            $price,
            $sale_price = null,
            $description = null
        )
        {
            global $records;

            //Reject if in array
            if (in_array($id, array_keys($records)))
                { throw new ErrorException("Device with that specification already exists in records."); }

            $this -> id = TVRecord::serialize($type, $brand, $model, $size);
            $this -> type = $type;
            $this -> brand = $brand;
            $this -> model = $model;
            $this -> size = intval($size);
            $this -> price = floatval($price);
            $this -> sale_price = $sale_price ? floatval($sale_price) : null;
            $this -> description = $description;
        }

        public function equals(TVRecord $other): bool
        {
            return $this -> id == $other -> id;
        }

        public function __toString()
        {
            $rv = "<tr><td>{$this -> id}</td>";
            $rv .= "<td>{$this -> type}</td>";
            $rv .= "<td>{$this -> brand}</td>";
            $rv .= "<td>{$this -> model}</td>";
            $rv .= "<td>{$this -> size}\"</td>";

            if ($this -> sale_price)
            {
                $pricestr = number_format($this -> sale_price, 2);
                $rv .= "<td class='red'>\$$pricestr</td>";
            }
            else
            {
                $pricestr = number_format($this -> price, 2);
                $rv .= "<td>\$$pricestr</td>";
            }

            $rv .= "<td>{$this -> description}</td></tr>";

            return $rv;
        }

        public static function serialize(string $type, string $brand, string $model, int $size): string
        {
            //Serialization approach:
            //1. ascii total of type, brand, and model
            $ascii_value = ascii_add($type) + ascii_add($brand) + ascii_add($model);
            //2. multiply by size
            $size_multiple = $ascii_value * $size;
            //3. convert to hex
            $hex_value = "0x" . dechex($size_multiple);

            //4. get last 5 chars
            return substr($hex_value, -5);
        }
    }

    //Context manager for files. Pass in a callable to have it executed assuming the given file
    //NOTE: callable MUST accept the file handle as an argument
    function open_file_context_manager($file, string $mode, callable $callable)
    {
        //Open file
        $file = fopen($file, $mode) or die("Cannae read file!");

        //Execute func
        $result = $callable($file);

        //Dispose file
        fclose($file);

        //If the work function returned anything, we should return it
        return $result;
    }

    //Dropdown maker
    function select($name, $options, $selected_choice=null, $required=false)
    {
        $rv = "<select name='$name' id='$name'";

        if ($required)
            { $rv .= " required"; }

        $rv .= ">";

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
        $id,
        $type,
        $brand,
        $model,
        $size,
        $base_price,
        $sale_price,
        $description
    )
    {
        //First check if the record already exists
        if (find_single_record($id))
            { throw new ErrorException("Device with that specification already exists in records."); }

        open_file_context_manager(
            "data/tvs.csv", FILE_MODE_READWRITE_APPEND,
            function($file) use ($id, $type, $brand, $model, $size, $base_price, $sale_price, $description) {
                fputcsv($file, [$id, $type, $brand, $model, $size, $base_price, $sale_price, "\"$description\""]);
            }
        );
    }

    //READ
    function get_all_records()
    {
        return open_file_context_manager(
            "data/tvs.csv", FILE_MODE_READ,
            function($file) {
                $data = array();

                //Dispose the first line
                fgetcsv($file);

                //Iter over the file and populate the records array
                if ($file)
                {
                    while($entries = fgetcsv($file, 1024))
                    {
                        array_push(
                            $data,
                            new TVRecord(...$entries)
                        );
                    }
                }
                return $data;
            }
        );
    }

    //Finds a record by id. Returns the record if found or null if not.
    function find_single_record($id)
    {
        $records = get_all_records();

        foreach ($records as $record)
        {
            if ($record -> id == $id)
                { return $record; }
        }

        return null;
    }
?>
