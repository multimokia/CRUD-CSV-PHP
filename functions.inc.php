<?php
    //Log errors to file
    ini_set('log_errors', 1);
    ini_set('error_log', "./error.log");
?>
<?php
    define("FILE_MODE_READ", "r"); //NOTE: DOES NOT CREATE A FILE IF IT DOESN'T EXIST
    define("FILE_MODE_READWRITE", "r+"); //NOTE: DOES NOT CREATE A FILE IF IT DOESN'T EXIST
    define("FILE_MODE_WRITE", "w");
    define("FILE_MODE_READWRITE_TRUNCATE", "w+");
    define("FILE_MODE_WRITE_APPEND", "a");
    define("FILE_MODE_READWRITE_APPEND", "a+");

    //id : TVRecord
    //This is essentially a runtime map of TVRecord objects.
    $records = array();

    //Load all records from persistent
    $records = get_all_records();

    //Create a session
    session_start();

    //Function that generates a warning we can display to the user
    function generate_warning($message) {
        echo "<div class='warning'>$message</div>";
    }

    //Function that generates an error we can display to the user
    function generate_error($message) {
        echo "<div class='error'>$message</div>";
    }

    //Function that generates a success message we can display to the user
    function generate_success($message) {
        echo "<div class='success'>$message</div>";
    }

    //Internal function to convert a string to an ascii numeric value
    //(sum of all of the ascii values of each character)
    function ascii_add($str)
    {
        //Trim excess whitespace
        $str = trim($str);

        //Calculate the sum of all the ascii values of each character
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

            //If we have a record of this already, we should raise an error
            if (in_array($id, array_keys($records)))
                { throw new ErrorException("A TV with that specification already exists in the records."); }

            $this -> id = $id; //TVRecord::serialize($type, $brand, $model, $size);
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
            $rv = "<td><a href=info.php?id={$this -> id}>{$this->id}</a></td>";
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

            $rv .= "<td>{$this -> description}</td>";

            return $rv;
        }

        public function to_array()
        {
            return array(
                $this -> id,
                $this -> type,
                $this -> brand,
                $this -> model,
                $this -> size,
                $this -> price,
                $this -> sale_price,
                $this -> description
            );
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
        $file = fopen($file, $mode) or die(error_get_last()["message"]);

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

    //Internal function to add a record
    //NOTE: DOES NO VALIDATION TO CHECK IF RECORD ALREADY EXISTS
    function _create(TVRecord $record)
    {
        //First check if the record already exists
        open_file_context_manager(
            "data/tvs.csv", FILE_MODE_WRITE_APPEND,
            function($file) use ($record) {
                fputcsv(
                    $file,
                    [
                        $record->id,
                        $record->type,
                        $record->brand,
                        $record->model,
                        $record->size,
                        $record->price,
                        $record->sale_price,
                        $record->description
                    ]
                );
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
                        $data[$entries[0]] = new TVRecord(...$entries);
                    }
                }
                return $data;
            }
        );
    }

    //Finds a record by id. Returns the record if found or null if not.
    function find_single_record($id)
    {
        global $records;
        return $records[$id] ?? null;
    }

    //CRUD WRAPPER FUNCTIONS

    //CREATE
    function create_record()
    {
        //Get the form data and validate

        $type = $_POST["tv_type"]; //From dropdown, no need to validate the string

        //However we should make sure the default hasn't been submitted
        if ($type === "--")
        {
            generate_error("Please select a TV type.");
            return;
        }

        $brand = $_POST["brand"]; //From radio buttons, no need to validate
        $model = filter_input(INPUT_POST, "model", FILTER_SANITIZE_STRING);
        $size = filter_input(INPUT_POST, "size", FILTER_SANITIZE_NUMBER_INT);
        $base_price = $_POST["price"]; //These are validated by the regex pattern
        $sale_price = $_POST["saleprice"]; //Same as above
        $description = filter_input(INPUT_POST, "description", FILTER_SANITIZE_STRING);

        //Try to initialize a TVRecord. If we fail then we catch the error and report it
        try
        {
            $record = new TVRecord(
                TVRecord::serialize($type, $brand, $model, $size),
                $type,
                $brand,
                $model,
                $size,
                $base_price,
                $sale_price,
                $description
            );
        }

        catch (ErrorException $e)
        {
            generate_error($e -> getMessage());
            return;
        }

        //Checks passed, create the record in the runtime map
        _create($record);

        $_SESSION["success_message"] = ["success", "Record created successfully."];

        //Redirect to the index page
        header("Location: index.php");
    }

    if (isset($_POST["createsubmit"]))
    {
        try
            { create_record(); }

        //General catchall
        catch (Exception $e)
            { generate_error($e -> getMessage()); }
    }

    //DELETE
    function delete_record($id)
    {
        global $records;

        open_file_context_manager("debug/ihatelife.txt", FILE_MODE_WRITE_APPEND,
            function($file) use ($id) {
                fwrite($file, "Deleting record with id: $id\n");
            }
        );

        //Get the record
        $record = find_single_record($id);

        //If the record doesn't exist, throw an error
        if (!$record)
        {
            generate_error("Record not found.");
            return;
        }

        //Pop from the runtime map
        unset($records[$record->id]);

        //Delete the record
        open_file_context_manager(
            "data/tvs.csv", FILE_MODE_WRITE,
            function($file) use ($records) {
                //Add columns
                fputcsv($file, ["Id","Type","Brand","Model","Size","Price","Sale","Desc"]);

                //Repopulate the file
                foreach ($records as $dataline)
                    { fputcsv($file, $dataline->to_array()); }
            }
        );

        //Delete the record from the runtime map
        unset($records[$id]);

        $_SESSION["success_message"] = ["success", "Record deleted successfully."];

        //Redirect to the index page
        header("Location: index.php");
    }

    //Confirm deletion function
    //Echos javascript to confirm record deletion. If user confirms, send post info to delete the record
    function add_delete_button($id)
    {
        echo "<form method='POST' onsubmit=\"return confirm('Are you sure you want to delete this record?');\">";
        echo "<input type='hidden' name='deleteid' value='$id'/>";
        echo "<input type='submit' name='deleterecord' value='Delete'>";
        echo "</form>";
    }

    if (isset($_POST["deleterecord"]))
    {
        try
            { delete_record($_POST["deleteid"]); }

        //General catchall
        catch (Exception $e)
            { generate_error($e -> getMessage()); }
    }
?>
