<?php

//ini_set("memory_limit","1000M");

/**
 * RICARDO VOIGT (https://www.linkedin.com/in/ricardo-voigt-software)
 * 
 * You will have to walk through an automaton whose matrix has 65 rows and 85 columns, as shown in the picture below.
 * 
 * For this phase, the propagation model is as follows:
 * White cells turn green if they have a number of adjacent green cells greater than 1 and less than 5. Otherwise, they remain white.
 * Green cells remain green if they have a number of green adjacent cells greater than 3 and less than 6. Otherwise they become white.
 * Two cells are considered adjacent if they have a border, either on the side, above, below or diagonally. 
 * In the example below, the white cell in the center therefore has 8 adjacent white cells.
 * 
 * The text file contains 65 lines. Each line contains 85 integer values separated by a blank space followed by a newline character "\n". 
 * These values represent the states of the cells in a matrix with 65 rows and 85 columns. 
 * The value "3" represents the starting point, the value "4" represents the destination point. The value "0" represents a white cell and the value "1" a green cell.
 *  Each row in the file represents a row of the matrix and each row value the value of a cell in that row. The first value in the file represents the upper left corner of the matrix.
 * SOLUTION SUBMISSION
 * You must submit a single line text file containing all the movements of the particle from the starting point to the destination point. 
 * Each movement must be separated by a blank space. Each letter represents a move of the particle, and consequently an update of the board.
 * U - movement up
 * D - movement down
 * R - movement to the right
 * L - movement to the left
 * The particle begins its move in the current state and ends its move after the board update. The particle must never finish its move in a green cell.
 * Example of a hypothetical answer with 10 moves: R R D R D L U D R R
 * 
 */

$matrix = array();

# input -> load the file to feed the matrix
//$myfile = fopen("sigmageek_stone_input.txt", "r");
$myfile = fopen("sigmageek_TESTE_input.txt", "r");
try
{
    while ( ! feof($myfile))
    {
        $line = fgets($myfile);

        //removing "\n" in the last position
        $line = str_replace(["\n",chr(13)],['',''],$line);

        $matrix[] = explode(' ', $line);
    }
}
finally
{
    fclose($myfile);
}

# first validatiion
$rows = count($matrix);
$cols = count($matrix[0]);

$test1 = "matrix has {$rows} rows and {$cols} columns";
// if($test1 != 'matrix has 65 rows and 85 columns')
// {
//     die('errorr...');
// }
echo "<h1>{$test1}</h1>";



# show the matrix
foreach($matrix as $row)
{
    echo implode(' ', $row). '<br>';
}



# I need to run the matrix, until find the final '4'!
# Each step I'll need to apply 1 propagation
# and then test the adjacent cells on the new matrix.
# I'll have to run each adjacent cell looking for a valid path 
# and be "prepared" to come back to the previous position(s) when stucked (recursive?)
# or run again since the begin, but remembering what paths do not take again... :-O



$pos_x = 0;
$pos_y = 0;

//$count_loop = 0;

echo "<h4>X = {$pos_x} | Y = {$pos_y}</h4>";

$new_matrix = apply_propagation($matrix);


echo "<h4>NEW MATRIX, AFTER PROPAGATION...</h4>";
foreach($new_matrix as $row)
{
    echo implode(' ', $row). '<br>';
}


# in this test, I realized that's not necessary to replicate the matrix inside the recursive function...
# There is only one matrix, and the temporary matrix with the propagation, before the move, then I can replace it!

$adjacents = adjacent_white_cells_to_move($new_matrix, $pos_x, $pos_y);

echo "<p>count(adjacents) = ".count($adjacents).'</p>';
//echo "<p>steps = {$steps}</p>";

$result = FALSE;
foreach($adjacents as $key => $pos)
{
    $_x = $pos[0];
    $_y = $pos[1];
    echo "<h4>adjacent: {$key} => {$_x},{$_y} </h4>";


    // PROPAGATION 2
    $new_matrix1 = apply_propagation($new_matrix);

    echo "<h4> - NEW MATRIX, AFTER PROPAGATION...</h4>";
    foreach($new_matrix1 as $row1)
    {
        echo implode(' ', $row1). '<br>';
    }
    
    $adjacents1 = adjacent_white_cells_to_move($new_matrix1, $_x, $_y);
    echo "<p> - count(adjacents) = ".count($adjacents1).'</p>';
    //echo "<p>steps = {$steps}</p>";
    
    $result = FALSE;
    foreach($adjacents1 as $key1 => $pos1)
    {
        $_x1 = $pos1[0];
        $_y1 = $pos1[1];
        echo " - adjacent: {$key1} => {$_x1},{$_y1} <br>";
    }
    


}




# repeat recursively (apply the propagation, test the adjacents after propagation, particle moves, ) until find the cell '4' inside the adjacents

//$steps = '';
//$ok = recursive_test($matrix,$pos_x,$pos_y, $steps);
//echo "<h1>STEPS = {$steps}</h1>";


function recursive_test($amatrix, $ax, $ay, &$steps)
{

    // echo "<h4>MATRIX, BEFORE PROPAGATION...</h4>";
    // foreach($amatrix as $x => $row)
    // {
    //     //echo implode(' ', $row). '<br>';
    //     foreach($row as $y => $cell)
    //     {
    //         if($x == $ax and $y == $ay)
    //         {
    //             echo " <b>{$cell}</b>";
    //         }
    //         else
    //         {
    //             echo " {$cell}";
    //         }
    //     }
    //     echo '<br>';
    // }


    $new_matrix = apply_propagation($amatrix);

    # after apllied the propagation, 
    # test the 4 adjacents (up,right,down and left) 
    # looking for an white cell to move next.

    $adjacents = adjacent_white_cells_to_move($new_matrix, $ax, $ay);
    echo "<p>count(adjacents) = ".count($adjacents).'</p>';
    //echo "<p>steps = {$steps}</p>";

    $result = FALSE;
    foreach($adjacents as $key => $pos)
    {
        $_x = $pos[0];
        $_y = $pos[1];
        //echo "adjacent: {$x},{$y} <br>";

        if($new_matrix[$_x][$_y] == '0') // white
        {
            //$result.= $key . ' '. recursive_test($new_matrix,$_x,$_y); // it doesn't make sense concat the path as a result...

            $result = recursive_test($new_matrix, $_x, $_y, $steps);
        }
        elseif($new_matrix[$_x][$_y] == '1') // green
        {
            // come back??
            $result = FALSE;
        }
        elseif($new_matrix[$_x][$_y] == '4') // FINISH CELL!!!
        {
            // even returning true, I need to know the last step $key
            $result = TRUE;
        }

        if($result)
        {
            $steps.= ' '.$key;
        }
    }
    return $result;

}




/**
 * Apply 1 propagation... 
 * White cells turn green if they have a number of adjacent GREEN cells greater than 1 and less than 5. Otherwise, they remain white.
 * Green cells remain green if they have a number of GREEN adjacent cells greater than 3 and less than 6. Otherwise they become white.
 * Two cells are considered adjacent if they have a border, either on the side, above, below or diagonally.
 * In the example below, the white cell in the center therefore has 8 adjacent white cells.
 */
function apply_propagation($matrix)
{
    $newmatrix = array();
    foreach($matrix as $x => $row)
    {
        foreach($row as $y => $cell)
        {
            switch($cell)
            {
                case '0': // white
                    $qty = count_green_adjacents($matrix,$x,$y);

                    // if "number of adjacent green cells greater than 1 and less than 5"
                    if ($qty > 1 and $qty < 5)
                    {
                        $newmatrix[$x][$y] = '1'; // turn green
                    }
                    else
                    {
                        $newmatrix[$x][$y] = $cell; // remain white
                    }
                break;

                case '1': // green
                    $qty = count_green_adjacents($matrix,$x,$y);

                    // if "number of green adjacent cells greater than 3 and less than 6"
                    if ($qty > 3 and $qty < 6)
                    {
                        $newmatrix[$x][$y] = '1'; // remain green
                    }
                    else
                    {
                        $newmatrix[$x][$y] = '0'; // become white
                    }
                break;

                default: $newmatrix[$x][$y] = $cell;
            }
        }
    }
    return $newmatrix;
}


/**
 * count adjacent green cells around the specified position
 * check the 8 possible possitions (like an 360 degree):
 * left;upper-left;upper;upper-right;right;down-right;down;down-left
 */
function count_green_adjacents($matrix,$x,$y)
{

    $last_pos_x = count($matrix) - 1;
    $last_pos_y = count($matrix[$x]) - 1;
    $result = 0;

    // 1 = left cell
    if($x > 0)
    {
        if($matrix[$x-1][$y] == '1')
        {
            $result++;
        }
    }

    // 2 = upper-left cell
    if(($x > 0) and ($y > 0))
    {
        if($matrix[$x-1][$y-1] == '1')
        {
            $result++;
        }
    }

    // 3 = upper cell
    if($y > 0)
    {
        if($matrix[$x][$y-1] == '1')
        {
            $result++;
        }
    }

    // 4 = upper-right cell
    if(($y > 0) and ($x < $last_pos_x))
    {
        if($matrix[$x+1][$y-1] == '1')
        {
            $result++;
        }
    }

    // 5 = right cell
    if($x < $last_pos_x)
    {
        if($matrix[$x + 1][$y] == '1')
        {
            $result++;
        }
    }

    // 6 = down-right cell
    if(($x < $last_pos_x) and ($y < $last_pos_y))
    {
        if($matrix[$x + 1][$y + 1] == '1')
        {
            $result++;
        }
    }

    // 7 = down cell
    if($y < $last_pos_y)
    {
        if($matrix[$x][$y + 1] == '1')
        {
            $result++;
        }
    }

    // 8 = down-left cell
    if(($x > 0) and ($y < $last_pos_y))
    {
        if($matrix[$x - 1][$y + 1] == '1')
        {
            $result++;
        }
    }

    return $result;
}



/**
 * return a list with the white adjacent cells
 * test the 4 adjacent cells to move next.
 * U - movement up; D - movement down; R - movement to the right; L - movement to the left
 */
function adjacent_white_cells_to_move($matrix,$x,$y)
{
    $result = array();

    // 1 up
    if($y > 0)
    {
        if($matrix[$x][$y - 1] == '0')
        {
            $result['U'] = [$x, $y - 1];
        }
    }

    // 2 right
    if($x < count($matrix) - 1  or $matrix[$x][$y + 1] == '4')
    {
        if($matrix[$x + 1][$y] == '0')
        {
            $result['R'] = [$x + 1, $y];
        }
    }

    // 3 down
    if($y < count($matrix[$x]) - 1)
    {
        if($matrix[$x][$y + 1] == '0'  or $matrix[$x][$y + 1] == '4')
        {
            $result['D'] = [$x, $y + 1];
        }
    }

    // 4 left
    if($x > 0)
    {
        if($matrix[$x - 1][$y] == '0')
        {
            $result['L'] = [$x - 1, $y];
        }
    }

    return $result;
}



?>