<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="description" content="Assignment 3">
        <meta name="author" content="Michael D'mello">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Assignment 3</title>
        <link rel="stylesheet" href="css/style.css">
        <?php include "./functions.inc.php"; ?>
    </head>
    <body>
        <?php
            //Hadle all messages here
            if (isset($_SESSION["message"]))
            {
                [$type, $message] = $_SESSION["message"];

                switch ($type)
                {
                    case "warning":
                        generate_warning($message);
                        break;
                    case "error":
                        generate_error($message);
                        break;
                }

                echo $_SESSION["message"];
                unset($_SESSION["message"]);
            }
        ?>
        <hr>
        <form method="POST">
            <fieldset>
                <legend>Create a record:</legend>
                <label for="tv_type">TV Type:</label>
                <?php select("tv_type", ["--", "LCD", "LED", "OLED", "QLED"], "--", true); ?>
                <br><br>
                <h3>Brand:</h3>
                <input type="radio" name="brand" id="brandLG" value="LG" required><label for="brandLG">LG</label>
                <input type="radio" name="brand" id="brandSamsung" value="Samsung"><label for="brandSamsung">Samsung</label>
                <input type="radio" name="brand" id="brandSony" value="Sony"><label for="brandSony">Sony</label>
                <input type="radio" name="brand" id="brandToshiba" value="Toshiba"><label for="brandToshiba">Toshiba</label>
                <br><br>
                <label for="model">Model:</label>
                <input type="text" name="model" id="model" required/>
                <br><br>
                <label for="size">Size:</label>
                <input type="text" name="size" id="size" pattern="^\d+$" required/>
                <br><br>
                <label for="price">Price:</label>
                <input type="text" name="price" id="price" pattern="^\d+(\.\d+)?$" required/>
                <br><br>
                <label for="saleprice">Sale price:</label>
                <input type="text" name="saleprice" id="saleprice" pattern="^\d+(\.\d+)?$"/>
                <br><br>
                <label for="description">Description:</label>
                <textarea name="description" id="description" placeholder="Enter a description about this product"></textarea>

                <input type="submit" name="createsubmit" value="Submit"/>
            </fieldset>
        </form>
        <hr>
        <!-- Form to search for a record by id -->
        <form method="POST">
            <fieldset>
                <legend>Search for a record:</legend>
                <label for="searchid">ID:</label>
                <input type="text" name="searchid" id="searchid" required/>
                <input type="submit" name="searchsubmit" value="Search"/>
            </fieldset>
        </form>
        <hr>
        <!-- Form to filter records by brand -->
        <form method="POST">
            <fieldset>
                <legend>Filter records by brand:</legend>
                <label for="brand">Brand:</label>
                <?php select("brand", ["--", "LG", "Samsung", "Sony", "Toshiba"], "--", true); ?>
                <input type="submit" name="filtersubmit" value="Filter"/>
            </fieldset>
        <hr>
        <!-- Add a way to download the tvs.csv file -->
        <a href="data/tvs.csv" download="tvs.csv"><button type="button">Download</button></a>
        <hr>
        <table>
            <tr>
                <th>#</th>
                <th>Id</th>
                <th>Type</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Size</th>
                <th>Price</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
            <?php
                //Loop over the values via for loop
                for ($i = 0; $i < count($records); $i++) {
                    echo "<tr>";
                    echo "<td>".($i+1)."</td>";
                    echo array_values($records)[$i] -> __toString();
                    echo "<td>";
                    add_delete_button(array_values($records)[$i] -> id);
                    add_update_button(array_values($records)[$i] -> id);
                    echo "</td>";
                    echo "</tr>";
                }
            ?>
        </table>
        <hr>
        <script src=https://my.gblearn.com/js/loadscript.js></script>
        <hr>
        <?php show_source(__file__)?>
    </body>
</html>
