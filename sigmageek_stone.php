<?php

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
$myfile = fopen("sigmageek_stone_input.txt", "r");
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

$rows = count($matrix);
$cols = count($matrix[0]);

$test1 = "matrix has {$rows} rows and {$cols} columns";
if($test1 != 'matrix has 65 rows and 85 columns')
{
    die('errorr...');
}
echo "<h1>{$test1}</h1>";



# show the matrix
foreach($matrix as $row)
{
    echo implode(' ', $row). '<br>';
}




$xmatrix = apply_propagation($matrix);




echo "AGAIN...<br>";
# show the matrix again...
foreach($xmatrix as $row)
{
    echo implode(' ', $row). '<br>';
}


/**
 * Apply 1 propagation... 
 * White cells turn green if they have a number of adjacent green cells greater than 1 and less than 5. Otherwise, they remain white.
 * Green cells remain green if they have a number of green adjacent cells greater than 3 and less than 6. Otherwise they become white.
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
                        $newmatrix[$x][$y] = '1';
                    }
                    else
                    {
                        $newmatrix[$x][$y] = $cell;
                    }
                break;
                case '1': // green
                    $qty = count_green_adjacents($matrix,$x,$y);

                    // if "number of green adjacent cells greater than 3 and less than 6"
                    if ($qty > 3 and $qty < 6)
                    {
                        $newmatrix[$x][$y] = '0';
                    }
                    else
                    {
                        $newmatrix[$x][$y] = $cell;
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








?>