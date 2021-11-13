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

    //Function that generates a warning we can display to the user
    function generate_warning($message) {
        echo "<div class='warning'>$message</div>";
    }

    //Function that generates an error we can display to the user
    function generate_error($message) {
        echo "<div class='error'>$message</div>";
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
            $this -> id = $id;
            $this -> type = $type;
            $this -> brand = $brand;
            $this -> model = $model;
            $this -> size = intval($size);
            $this -> price = floatval($price);
            $this -> sale_price = $sale_price ? floatval($sale_price) : null;
            $this -> description = $description;
        }

        public function dump_update_form()
        {
            echo "<legend>Editing TV: {$this->id}</legend>";
            echo "<input type='hidden' name='id' value='{$this->id}'/>";
            echo "<label for='type'>TV Type:</label>";
            select("tv_type", ["LCD", "LED", "OLED", "QLED"], $this->type, true);
            echo "<br><br>";
            echo "<h3>Brand:</h3>";

            $brands = ["LG", "Samsung", "Sony", "Toshiba"];

            //Loop over the brands and create a radiobutton for each, selecting the current one
            foreach ($brands as $brand)
            {
                $selected = $brand == $this->brand ? "checked" : "";
                echo "<input type='radio' name='brand' id='brand$brand' value='$brand' required $selected/><label for='brand$brand'>$brand</label>";
            }

            echo "<br><br>";
            echo "<label for='model'>Model:</label>";
            echo "<input type='text' name='model' id='model' value='{$this->model}' required/>";
            echo "<br><br>";
            echo "<label for='size'>Size:</label>";
            echo "<input type='text' name='size' id='size' pattern='^\d+$' value='{$this->size}' required/>";
            echo "<br><br>";
            echo "<label for='price'>Price:</label>";
            echo "<input type='text' name='price' id='price' value='{$this->price}' pattern='^\d+(\.\d+)?$' required/>";
            echo "<br><br>";
            echo "<label for='saleprice'>Sale price:</label>";
            echo "<input type='text' name='saleprice' id='saleprice' value='{$this->sale_price}' pattern='^\d+(\.\d+)?$'/>";
            echo "<br><br>";
            echo "<label for='description'>Description:</label>";
            echo "<textarea name='description' id='description' placeholder='Enter a description about this product'>{$this->description}</textarea>";
            echo "<br><br><input type='submit' name='updatesubmit' value='Submit'/>";
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

        public static function generate_id(): string
        {
            global $records;

            $hex_value = null;
            do
            {
                //Generate a unique id for this record
                $hex_value = substr("0x" . dechex(rand(10000, 99999)), -5);
            } while (in_array($hex_value, array_keys($records)));

            //Return the last 5 chars
            return $hex_value;
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
            $rv .= "<option ";

            //Special case for a `--` option
            if ($selected_choice == $option_name && $option_name == "--")
                { $rv .= "value='' selected"; }
            else
                { $rv .= "value='$option_name'"; }

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
                        { $data[$entries[0]] = new TVRecord(...$entries); }
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
    function create_record($id=null)
    {
        //Get the form data and validate

        //We reuse create to regen a record if we're updating, in which case we use the same id
        if ($id == null)
            { $id = TVRecord::generate_id(); }

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
                $id,
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

        //Redirect to the index page, clearing requests
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

        //Get the record
        $record = find_single_record($id);

        //If the record doesn't exist, throw an error
        if (!$record)
        {
            generate_error("Record not found ({$record->id})");
            return;
        }

        //Pop from the runtime map
        unset($records[$id]);

        //Delete the record by rewriting the file
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
        {
            delete_record($_POST["deleteid"]);
            //Redirect to the index page, clear the requests
            header("Location: index.php");
        }

        //General catchall
        catch (Exception $e)
            { generate_error($e -> getMessage()); }
    }

    //UPDATE
    //Button to open the update form
    function add_update_button($id)
    {
        echo "<form method='get' action='update.php'>";
        echo "<input type='hidden' name='id' value='$id'/>";
        echo "<button type='submit'>Update</button>";
        echo "</form>";
    }

    function update_record($id)
    {
        //Delete the record
        delete_record($id);

        //Create a new record with the new data
        create_record($id);
    }


    if (isset($_POST["updatesubmit"]))
    {
        try
            { update_record($_POST["id"]); }

        //General catchall
        catch (Exception $e)
            { generate_error($e -> getMessage()); }
    }
?>
