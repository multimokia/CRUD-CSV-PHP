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

    define("RECORD_CSV_HEADER", ["Id","Type","Brand","Model","Size","Price","Sale","Desc"]);
    //id : TVRecord
    //This is essentially a runtime map of TVRecord objects.
    $records = array();

    //Load all records from persistent
    $records = get_all_records();

    //C
    if (isset($_POST["createsubmit"]))
    {
        try
            { create_record(); }

        //General catchall
        catch (Exception $e)
            { generate_error($e -> getMessage()); }
    }

    //R
    if (isset($_POST["searchsubmit"]))
        { $records = [find_single_record($_POST["searchid"])]; }

    elseif (isset($_POST["filtersubmit"]))
        { $records = filter_by_brand($_POST["brandfilter"]); }

    elseif (isset($_POST["uploadsubmit"]))
    {
        debug_log(print_r($_FILES["newrecords"], true));

        if ($_FILES["newrecords"]["error"])
        {
            generate_error("Error uploading file ({$_FILES['newrecords']['error']})");
            return;
        }

        $is_safe = open_file_context_manager(
            $_FILES["newrecords"]["tmp_name"], FILE_MODE_READ,
            function($file)
            {
                $cols = fgetcsv($file);
                return $cols == RECORD_CSV_HEADER;
            }
        );

        if (!$is_safe)
        {
            generate_error("File has invalid headers. It must have the following: Id,Type,Brand,Model,Size,Price,Sale,Desc");
            return;
        }

        //Checks pass, let's accept the file
        move_uploaded_file(
            $_FILES["newrecords"]["tmp_name"],
            "./data/tvs.csv"
        );

        //Reload the page w/ new file
        header("Refresh:0; Location: index.php");
    }

    //U
    if (isset($_POST["updatesubmit"]))
    {
        try
            { update_record($_POST["id"]); }

        //General catchall
        catch (Exception $e)
            { generate_error($e -> getMessage()); }
    }

    //D
    if (isset($_POST["deleterecord"]))
    {
        try
        {
            delete_record($_POST["deleteid"]);
            //Redirect to the index page, clear the requests
            header("Refresh:0; Location: index.php");
        }

        //General catchall
        catch (Exception $e)
            { generate_error($e -> getMessage()); }
    }

    //Confirm deletion function
    //Echos a form to confirm record deletion w/ a confirm onsubmit event.
    //If user confirms, send post info to delete the record
    function add_delete_button($id)
    {
        echo "<form method='POST' onsubmit=\"return confirm('Are you sure you want to delete this record?');\">";
        echo "<input type='hidden' name='deleteid' value='$id'/>";
        echo "<input type='submit' name='deleterecord' value='Delete' style='background: rgba(211, 44, 44, 0.5);'>";
        echo "</form>";
    }

    //Echos a form that allows the user to update the record its attached to
    function add_update_button($id)
    {
        echo "<form method='get' action='update.php'>";
        echo "<input type='hidden' name='id' value='$id'/>";
        echo "<button type='submit' style='background: rgba(234, 159, 53, 0.5);'>Update</button>";
        echo "</form>";
    }

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
        public $id;
        public $type;
        public $brand;
        public $model;
        public $size;
        public $price;
        public $sale_price;
        public $description;

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
            $rv = "<td><a href=info.php?id={$this -> id} style='background: rgba(80, 181, 65, 0.5);'>{$this->id}</a></td>";
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
                debug_log("Generated id: $hex_value");

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

    //Debug log to file
    function debug_log($message)
    {
        open_file_context_manager(
            "debug/log.txt", FILE_MODE_READWRITE_APPEND,
            function($file) use ($message) {
                //Get currdate
                $date = date("Y-m-d H:i:s");
                fwrite($file, "[$date]: $message\n");
            }
        );
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
    function _create($record)
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

    //Finds a record by id. Returns the record if found or null if not.
    function find_single_record($id)
    {
        global $records;
        return $records[$id] ?? null;
    }

    //Helper function to get all the brands in the current records
    function get_all_brands()
    {
        global $records;

        $brands = [];

        foreach ($records as $record)
        {
            if (!in_array($record->brand, $brands))
                { $brands[] = $record->brand; }
        }

        return $brands;
    }

    //Helper function to filter records by brand name
    function filter_by_brand($brand)
    {
        global $records;

        $filtered_records = [];

        foreach ($records as $record)
        {
            if ($record -> brand == $brand)
                { $filtered_records[] = $record; }
        }

        return $filtered_records;
    }

    //CRUD WRAPPER FUNCTIONS (Frontend use only)
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

        $brand = filter_input(INPUT_POST, "brand", FILTER_SANITIZE_STRING);
        $model = filter_input(INPUT_POST, "model", FILTER_SANITIZE_STRING);
        $size = filter_input(INPUT_POST, "size", FILTER_SANITIZE_NUMBER_INT);
        $base_price = $_POST["price"]; //These are validated by the regex pattern
        $sale_price = $_POST["saleprice"]; //Same as above
        $description = filter_input(INPUT_POST, "description", FILTER_SANITIZE_STRING);

        //initialize a TVRecord
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

        //Checks passed, create the record in the runtime map
        _create($record);

        debug_log("Created record: " . print_r($record, true));

        //Redirect to the index page, clearing requests
        header("Refresh:0; Location: index.php");
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

    //UPDATE
    function update_record($id)
    {
        debug_log("Updating record: $id");

        //Delete the record
        delete_record($id);

        //Create a new record with the new data
        create_record($id);
    }

    //DELETE
    function delete_record($id)
    {
        global $records;

        debug_log("Deleting record: $id");

        //Get the record
        $record = find_single_record($id);

        //If the record doesn't exist, throw an error
        if (!$record)
        {
            debug_log("Record not found.");
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
                fputcsv($file, RECORD_CSV_HEADER);

                //Repopulate the file
                foreach ($records as $dataline)
                    { fputcsv($file, $dataline->to_array()); }
            }
        );
    }
?>
