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

//$matrix = load_matrix("sigmageek_stone_input.txt");
$matrix = load_matrix("sigmageek_TESTE_input.txt");

# input -> load the file to feed the matrix
//$myfile = fopen("sigmageek_stone_input.txt", "r");
// $myfile = fopen("sigmageek_TESTE_input.txt", "r");
// try
// {
//     while ( ! feof($myfile))
//     {
//         $line = fgets($myfile);

//         //removing "\n" in the last position
//         $line = str_replace(["\n",chr(13)],['',''],$line);

//         $matrix[] = explode(' ', $line);
//     }
// }
// finally
// {
//     fclose($myfile);
// }
function load_matrix($file_name)
{
    $result = array();
    $myfile = fopen($file_name, "r");
    try
    {
        while ( ! feof($myfile))
        {
            $line = fgets($myfile);
    
            //removing "\n" in the last position
            $line = str_replace(["\n",chr(13)],['',''],$line);
    
            $result[] = explode(' ', $line);
        }
    }
    finally
    {
        fclose($myfile);
    }
    return $result;
}
function save_matrix($matrix,$file_name)
{
    //if( ! file_exists($file_name))
    //{
        $output_file = fopen($file_name, "w");
        try
        {
            foreach($matrix as $y => $row)
            {
                fwrite($output_file, implode(' ',$row)."\n");
            }
        }
        finally
        {
            fclose($output_file);
        }
    //}
}




# first validatiion
$rows = count($matrix);
$cols = count($matrix[0]);

$test1 = "matrix has {$rows} rows(Y) and {$cols} columns(X)";
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
# show the matrix, in a different way
// for($y =0; $y < count($matrix); $y++)
// {
//     for($x=0; $x < count($matrix[$y]); $x++)
//     {
//         $cell = $matrix[$y][$x];
//         echo " {$cell}";
//     }
//     echo "<br>";
// }



# I need to run the matrix, until find the final '4'!
# Each step I'll need to apply 1 propagation
# and then test the adjacent cells on the new matrix.
# I'll have to run each adjacent cell looking for a valid path 
# and be "prepared" to come back to the previous position(s) when stucked (recursive?)
# or run again since the begin, but remembering what paths do not take again... :-O


//teste_2($matrix);
//teste_3();

# test with recusive function...
// $pos_y = 0;
// $pos_x = 0;

// $steps = '';
// $ok = recursive_test($matrix, $pos_y, $pos_x, $steps);
// echo "<h1>STEPS = {$steps}</h1>";



#### RECURSIVE FUNCTION, TEST 2 ####

#output file
$output_file = fopen("output_file.txt", "w");
try
{
    $steps = '';
    $steps_list = array();
    //$recursive_level = 0;

    $pos_y = 0;
    $pos_x = 0;

    recursive_test_2($pos_y, $pos_x, 0);

    fwrite($output_file, $steps."\n---\n");

    echo "<p>STEPS={$steps}</p>";

    foreach($steps_list as $_steps)
    {
        fwrite($output_file, $_steps."\n");
        echo "<p>_STEPS={$_steps}</p>";
    }
}
finally
{
    fclose($output_file);
}


function teste_3()
{
    global $matrix;
    for($i = 0; $i<=100; $i++)
    {
        $new_matrix = apply_propagation($matrix);

        echo "<h4>NEW MATRIX, AFTER PROPAGATION {$i}...</h4>";
        foreach($new_matrix as $row)
        {
            echo implode(' ', $row). '<br>';
        }

        $matrix = $new_matrix;
    }
}






function teste_2($amatrix)
{
    //$count_loop = 0;

    //global $matrix;

    $pos_y = 0;
    $pos_x = 0;

    echo "<h4>X = {$pos_x} | Y = {$pos_y}</h4>";

    $new_matrix = apply_propagation($amatrix);


    echo "<h4>NEW MATRIX, AFTER PROPAGATION...</h4>";
    foreach($new_matrix as $row)
    {
        echo implode(' ', $row). '<br>';
    }

    $adjacents = adjacent_white_cells_to_move($new_matrix, $pos_y, $pos_x);

    echo "<p>count(adjacents) = ".count($adjacents).'</p>';
    //echo "<p>steps = {$steps}</p>";

    foreach($adjacents as $key => $pos)
    {
        $_y = $pos[0];
        $_x = $pos[1];
        echo "<h4>adjacent: {$key} => {$_y},{$_x} </h4>";



        // PROPAGATION 2

        $new_matrix1 = apply_propagation($new_matrix);

        echo "<h4> -- NEW MATRIX, AFTER PROPAGATION...</h4>";
        foreach($new_matrix1 as $row1)
        {
            echo implode(' ', $row1). '<br>';
        }
        
        $adjacents1 = adjacent_white_cells_to_move($new_matrix1, $_y, $_x);
        echo "<p> -- count(adjacents1) = ".count($adjacents1).'</p>';
        //echo "<p>steps = {$steps}</p>";
        
        foreach($adjacents1 as $key1 => $pos1)
        {
            $_y1 = $pos1[0];
            $_x1 = $pos1[1];
            echo " -- adjacent: {$key1} => {$_y1},{$_x1} <br>";
        }
    }
}



# repeat recursively (apply the propagation, test the adjacents after propagation, particle moves, ) until find the cell '4' inside the adjacents
#  I could create a file for each path... :-/ ??

function recursive_test_2($ay, $ax, $level)
{
    global $matrix;
    //global $output_file;
    //global $steps;
    //global $steps_list;
    //global $recursive_level;

    # 
    echo "level = {$level}<br>";
    //save_matrix($matrix,$recursive_level);

    # replacing the matrix for the next move...
    $matrix = apply_propagation($matrix);

    # saving the board after the propagation...
    # because I'll have to reload it after each adjacent
    save_matrix($matrix,"matrix_{$level}.txt");

    $adjacents = adjacent_white_cells_to_move($matrix, $ay, $ax);
    echo "<p>adjacents=".count($adjacents)."</p>";

    foreach($adjacents as $step => $pos)
    {
        # each adjacent is a new path... :-O ? how to "rollback"? 
        # I though recursive would solve that... :-/
        # I need a counter to know the "level"..
        // if($steps != '')
        // {
        //     $steps_list[] = $steps;
        // }

        $_y = $pos[0];
        $_x = $pos[1];

        echo "<H5> -- level: {$level} | adjacent: {$step} => {$_y},{$_x} </H5>";


        # reload here the matrix?? 
        $matrix = load_matrix("matrix_{$level}.txt");


        if($matrix[$_y][$_x] == '0') // white
        {
            // store the step
            //$steps.= ' '.$step;
            //fwrite($output_file, $steps);
            
            //echo "<p>recursive_level={$recursive_level}</p>";
            //$recursive_level++;

            recursive_test_2($_y, $_x, $level+=1);

            //$recursive_level--;
        }
        elseif($matrix[$_y][$_x] == '4') // FINAL
        {
            // do nothing.... ?
            echo "ACHOU O FINALL!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!";
        }
        else
        {
            echo "achou verde...";
        }
    }
    echo "saiu...level = {$level}<br>";


}

function recursive_test($amatrix, $ay, $ax, &$steps)
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

    $adjacents = adjacent_white_cells_to_move($new_matrix, $ay, $ax);
    //echo "<p>count(adjacents) = ".count($adjacents).'</p>';
    //echo "<p>steps = {$steps}</p>";

    $result = FALSE;
    foreach($adjacents as $key => $pos)
    {
        $_y = $pos[0];
        $_x = $pos[1];
        //echo "adjacent: {$x},{$y} <br>";

        if($new_matrix[$_y][$_x] == '0') // white
        {
            //$result.= $key . ' '. recursive_test($new_matrix,$_x,$_y); // it doesn't make sense concat the path as a result...

            $result = recursive_test($new_matrix, $_y, $_x, $steps);
        }
        elseif($new_matrix[$_y][$_x] == '1') // green
        {
            // come back?? Actually the adjacent_white_cells_to_move doen't return green... :-/
            $result = FALSE;
        }
        elseif($new_matrix[$_y][$_x] == '4') // FINISH CELL!!!
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
function apply_propagation($amatrix)
{
    $result = array();
    foreach($amatrix as $y => $row)
    {
        foreach($row as $x => $cell)
        {
            switch($cell)
            {
                case '0': // white
                    $qty = count_green_adjacents($amatrix,$y,$x);

                    // if "number of adjacent green cells greater than 1 and less than 5"
                    if ($qty > 1 and $qty < 5)
                    {
                        $result[$y][$x] = '1'; // turn green
                    }
                    else
                    {
                        $result[$y][$x] = $cell; // remain white
                    }
                break;

                case '1': // green
                    $qty = count_green_adjacents($amatrix,$y,$x);

                    // if "number of green adjacent cells greater than 3 and less than 6"
                    if ($qty > 3 and $qty < 6)
                    {
                        $result[$y][$x] = '1'; // remain green
                    }
                    else
                    {
                        $result[$y][$x] = '0'; // become white
                    }
                break;

                default: $result[$y][$x] = $cell;
            }
        }
    }
    return $result;
}


/**
 * count adjacent green cells around the specified position
 * check the 8 possible possitions (like an 360 degree):
 * left;upper-left;upper;upper-right;right;down-right;down;down-left
 */
function count_green_adjacents($matrix,$y,$x)
{

    $last_pos_y = count($matrix) - 1;
    $last_pos_x = count($matrix[$y]) - 1;
    $result = 0;

    // 1 = left cell
    if($x > 0)
    {
        if($matrix[$y][$x-1] == '1')
        {
            $result++;
        }
    }

    // 2 = upper-left cell
    if(($y > 0) and ($x > 0))
    {
        if($matrix[$y - 1][$x - 1] == '1')
        {
            $result++;
        }
    }

    // 3 = upper cell
    if($y > 0)
    {
        if($matrix[$y - 1][$x] == '1')
        {
            $result++;
        }
    }

    // 4 = upper-right cell
    if(($y > 0) and ($x < $last_pos_x))
    {
        if($matrix[$y - 1][$x + 1] == '1')
        {
            $result++;
        }
    }

    // 5 = right cell
    if($x < $last_pos_x)
    {
        if($matrix[$y][$x + 1] == '1')
        {
            $result++;
        }
    }

    // 6 = down-right cell
    if(($y < $last_pos_y) and ($x < $last_pos_x))
    {
        if($matrix[$y + 1][$x + 1] == '1')
        {
            $result++;
        }
    }

    // 7 = down cell
    if($y < $last_pos_y)
    {
        if($matrix[$y + 1][$x] == '1')
        {
            $result++;
        }
    }

    // 8 = down-left cell
    if(($y < $last_pos_y) and ($x > 0))
    {
        if($matrix[$y + 1][$x - 1] == '1')
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
function adjacent_white_cells_to_move($amatrix, $y, $x)
{
    $result = array();

    $last_pos_y = count($amatrix) - 1;
    $last_pos_x = count($amatrix[$y]) - 1;

    // echo "adjacent_white_cells_to_move(amatrix,{$x},{$y})<br>";
    // echo "last_pos_x={$last_pos_x} | last_pos_y={$last_pos_y}<br>";

    // 1 up
    if($y > 0)
    {
        if($amatrix[$y - 1][$x] == '0')
        {
            $result['U'] = [$y - 1, $x];
        }
    }

    // 2 right
    if($x < $last_pos_x)
    {
        if(($amatrix[$y][$x + 1] == '0') or ($amatrix[$y][$x + 1] == '4'))
        {
            $result['R'] = [$y, $x + 1];
        }
    }

    // 3 down
    if($y < $last_pos_y)
    {
        //echo "DOWN: amatrix[{$x}][{$y} + 1] = ".$amatrix[$x][$y + 1].'<br>';
        if(($amatrix[$y + 1][$x] == '0')  or ($amatrix[$y + 1][$x] == '4'))
        {
            $result['D'] = [$y + 1, $x];
        }
    }

    // 4 left
    if($x > 0)
    {
        if($amatrix[$y][$x - 1] == '0')
        {
            $result['L'] = [$y,$x - 1];
        }
    }

    return $result;
}



?>