<?php

set_time_limit(30000);

CONST COLOR_WHITE = '0';
CONST COLOR_GREEN = '1';
CONST COLOR_FINAL = '4';

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

$initial_filename = 'sigmageek_TESTE_input.txt';
//$initial_filename = "sigmageek_stone_input.txt";
$output_filename = "output_file.txt";

if(file_exists( $output_filename ))
{
    $matrix = array();
    test_results( $initial_filename, $output_filename );
    die('<H1>the end...</H1>');
}
else
{
    $matrix = load_matrix($initial_filename);


    # first validatiion
    // $rows = count($matrix);
    // $cols = count($matrix[0]);

    // $test1 = "matrix has {$rows} rows(Y) and {$cols} columns(X)";
    // // if($test1 != 'matrix has 65 rows and 85 columns')
    // // {
    // //     die('errorr...');
    // // }
    // echo "<h1>{$test1}</h1>";


    # show the matrix
    // foreach($matrix as $row)
    // {
    //     echo implode(' ', $row). '<br>';
    // }




    #### RECURSIVE FUNCTION, TEST 2 ####

    $path = '';
    $step = 1;
    recursive_test_2(0, 0, $step);

    #output file
    $output_file = fopen($output_filename, "w");
    try
    {
        fwrite($output_file, $path);
        echo "<p>STEPS/PATH={$path}</p>";
    }
    finally
    {
        fclose($output_file);
    }

}




function load_matrix($file_name)
{
    $result = array();
    $matrix_file = fopen($file_name, "r");
    try
    {
        while ( ! feof($matrix_file))
        {
            $line = fgets($matrix_file);
    
            if($line != '')
            {
                //removing "\n" in the last position
                $line = str_replace(["\n",chr(13)],['',''],$line);

                $result[] = explode(' ', $line);
            }
        }
    }
    finally
    {
        fclose($matrix_file);
    }
    return $result;
}
function save_matrix($filename)
{
    if( ! file_exists($filename))
    {
        global $matrix;
        $output_file = fopen($filename, "w");
        try
        {
            $qty = count($matrix);
            for($y = 0; $y < $qty; $y++)
            {
                $row = $matrix[$y];
                $line = ($y > 0 ? "\n" : ''). implode(' ',$row);
                fwrite($output_file, $line);
            }
        }
        finally
        {
            fclose($output_file);
        }
    }
}
function show_matrix()
{
    global $matrix;
    echo "<p>M A T R I X</p>";
    foreach($matrix as $y => $row)
    {
        echo implode(' ', $row)."<br>";
    }
}








/**
 * repeat recursively (apply the propagation, test the adjacents after propagation, particle moves...)
 *  until find the cell '4' inside the adjacents 
 */
function recursive_test_2($ay, $ax, $step)
{

    global $matrix;

    # 
    //echo "<h5>level = {$step}</h5>";

    if(file_exists("matrix_{$step}.txt"))
    {
        $matrix = load_matrix("matrix_{$step}.txt");
    }
    else
    {
        # replacing the matrix for the next move...
        $matrix = apply_propagation($matrix);

        # saving the board after the propagation...
        # because I'll have to reload it after each adjacent
        save_matrix("matrix_{$step}.txt");
    }

    $adjacents = adjacent_white_cells_to_move($matrix, $ay, $ax, $step);
    //echo "<p>level {$step} | adjacents=".count($adjacents)."</p>";

    $i = 0;
    $result = FALSE;
    foreach($adjacents as $move => $pos)
    {
        echo $move.'<br>';
        # each adjacent is a new path... :-O ? how to "rollback"? 
        # I though recursive would solve that... :-/
        # I need a counter to know the "level"...

        if( ! $result)
        {
            $_y = $pos[0];
            $_x = $pos[1];

            //echo "<H5> -- level: {$step} | adjacent: {$move} => {$_y},{$_x} </H5>";

            # reload here the matrix??
            if ($i > 0)
            {
                $matrix = load_matrix("matrix_{$step}.txt");
            }

            //show_matrix();

            if($matrix[$_y][$_x] == COLOR_WHITE)
            {
                $result = recursive_test_2($_y, $_x, $step+1);
            }
            elseif($matrix[$_y][$_x] == COLOR_FINAL)
            {
                $result = TRUE;
            }
            else
            {
                # load_matrix isn't loading the same matrix...?
                die("<h1>green found at level {$step}, it isn't supposed to happen!!</h1>");
            }

            if($result)
            {
                global $path;
                # store the move 
                $path = $move. ' '.$path;
            }
        }
        $i++;
    }
    //echo "got off...level = {$step}<br>";
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
                case COLOR_WHITE: // white
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
                        $result[$y][$x] = $cell; // remain green
                    }
                    else
                    {
                        $result[$y][$x] = COLOR_WHITE; // become white
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
 * return a list with the white (and FINAL) adjacent cells
 * test the 4 adjacent cells to move next.
 * U - movement up; D - movement down; R - movement to the right; L - movement to the left
 */
function adjacent_white_cells_to_move($amatrix, $y, $x, $step)
{
    $result = array();

    $last_pos_y = count($amatrix) - 1;
    $last_pos_x = count($amatrix[$y]) - 1;

    // echo "adjacent_white_cells_to_move(amatrix,{$x},{$y})<br>";
    // echo "last_pos_x={$last_pos_x} | last_pos_y={$last_pos_y}<br>";


    # I changed the order of the adjacents
    # and now I had the ideia of testing the STEP
    # when the STEP is ODD priorize the DOWN path
    # but if the STEP is EVEN priorize the RIGHT path... 
    # hahahah why are you so serious?!?!!?

    if ($step % 2 == 0)
    {
        // 2 right
        if($x < $last_pos_x)
        {
            if(($amatrix[$y][$x + 1] == COLOR_WHITE) or ($amatrix[$y][$x + 1] == COLOR_FINAL))
            {
                $result['R'] = [$y, $x + 1];
            }
        }

        // 3 down
        if($y < $last_pos_y)
        {
            //echo "DOWN: amatrix[{$x}][{$y} + 1] = ".$amatrix[$x][$y + 1].'<br>';
            if(($amatrix[$y + 1][$x] == COLOR_WHITE)  or ($amatrix[$y + 1][$x] == COLOR_FINAL))
            {
                $result['D'] = [$y + 1, $x];
            }
        }
    }
    else
    {
        // 3 down
        if($y < $last_pos_y)
        {
            //echo "DOWN: amatrix[{$x}][{$y} + 1] = ".$amatrix[$x][$y + 1].'<br>';
            if(($amatrix[$y + 1][$x] == COLOR_WHITE)  or ($amatrix[$y + 1][$x] == COLOR_FINAL))
            {
                $result['D'] = [$y + 1, $x];
            }
        }

        // 2 right
        if($x < $last_pos_x)
        {
            if(($amatrix[$y][$x + 1] == COLOR_WHITE) or ($amatrix[$y][$x + 1] == COLOR_FINAL))
            {
                $result['R'] = [$y, $x + 1];
            }
        }
    }


    // 1 up
    if($y > 0)
    {
        if($amatrix[$y - 1][$x] == COLOR_WHITE)
        {
            $result['U'] = [$y - 1, $x];
        }
    }

    // 4 left
    if($x > 0)
    {
        if($amatrix[$y][$x - 1] == COLOR_WHITE)
        {
            $result['L'] = [$y, $x - 1];
        }
    }


    return $result;
}




/**
 * testing the results, based on the files.
 */
function test_results($initial_filename,$output_filename)
{
    global $matrix;

    $moves = array();
    # load the moves
    $moves_file = fopen($output_filename, "r");
    try
    {
        $line = fgets($moves_file);
        $moves = explode(' ', $line);
    }
    finally
    {
        fclose($moves_file);
    }

    if(count($moves) > 0)
    {
        # load the initial matrix (0)
        $matrix = load_matrix($initial_filename);
        show_matrix();

        # show the list of matrix and each move
        $y = 0;
        $x = 0;
        $step = 1;
        $the_end = FALSE;
        while (file_exists("matrix_{$step}.txt") and ! $the_end)
        {
            $move = $moves[$step-1];
            echo "<p>next move: {$move}</p>\n";

            switch($move)
            {
                case 'U':
                    $y--;
                break;
                case 'R':
                    $x++;
                break;
                case 'D':
                    $y++;
                break;
                case 'L':
                    $x--;
                break;
            }

            $matrix = load_matrix("matrix_{$step}.txt");
            //show_matrix();

            foreach($matrix as $_y=>$row)
            {
                foreach($row as $_x=>$cell)
                {
                    if($x == $_x and $y == $_y)
                    {
                        echo '* ';
                    }
                    else
                    {
                        echo $cell.' ';
                    }
                }
                //echo "<br>";
                echo "\n"; // CLI
            }

            if($matrix[$y][$x] == COLOR_FINAL)
            {
                $the_end = TRUE;
            }

            $step++;
        }
    }
}



?>