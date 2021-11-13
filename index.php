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
        <form method="POST">
            <fieldset>
                <legend>What do?</legend>
                <label for="tv_type">TV Type:</label>
                <?php select("tv_type", ["--", "LCD", "LED", "OLED", "QLED"], null, true); ?>
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
                <input type="number" name="size" id="size" required/>
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
                    echo "</td>";
                    echo "</tr>";
                }
            ?>
        </table>
        <hr>
        <?php
            //Hadle all messages here
            if (isset($_SESSION["message"]))
            {
                [$type, $message] = $_SESSION["message"];

                switch ($type)
                {
                    case "success":
                        generate_success($message);
                        break;
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
        <script src=https://my.gblearn.com/js/loadscript.js></script>
        <hr>
        <?php show_source(__file__)?>
    </body>
</html>
