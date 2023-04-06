<?php

/**
 * RICARDO VOIGT (https://www.linkedin.com/in/ricardo-voigt-software)
 * 
 * My solution for the "Stone Automata Maze Challenge"
 * by https://sigmageek.com/
 * 
 * LEVEL 2 - CHALLENGE 3 (2000 rows x 2000 columns)
 * 2023-04-03 - 2023-04-07
 * 
 * 
 *  This time our particle has a special power, its individuality.
 *  It can change the value of any cell on the board.
 *  It can use this trick up to 30 times.
 * 
 * 
 * List of files:
 *  - input3.txt                (input from sigmageek, matrix 0)
 *  - output_file.txt           (resulting text file containing all the movements)
 * List of functions:
 *  - load_matrix               (load a matrix from a txt file)
 *  - save_matrix               (save a matrix into a txt file)
 *  - recursive_move            (main function of the solution) 
 *  - apply_propagation         (calculate and apply the propagation rule)
 *  - count_green_adjacents     (quantity of green adjacent cells around the position)
 *  - adjacent_white_cells_to_move (list of possible cells to move, with the direction and coordinates)
 * 
 */

date_default_timezone_set('America/Montevideo');
const ONE_HOUR_SECONDS = 3600;
set_time_limit( ONE_HOUR_SECONDS * 12 );

/**
 * Execution test 1 between "2023-04-04 xx:xx" to "2023-04-04 yy:yy" 
 * ()
 * 
 */

CONST COLOR_WHITE = 0;
CONST COLOR_GREEN = 1;
CONST COLOR_FINAL = 4;

CONST MOVE_UP = 'U';
CONST MOVE_DOWN = 'D';
CONST MOVE_RIGHT = 'R';
CONST MOVE_LEFT = 'L';

echo "START: " . date("Y-m-d H:i");

# input
$input_filename = "input3.txt";
$output_filename = "output_file_3.txt";

$matrix = load_matrix($input_filename);

# processing
$path = '';

/**
 * Individuality
 * Throughout the challenge, before moving, the particle can activate its individuality 
 * by changing the value of any cell on the board, 
 * with the exception of the starting point and the starting point.
 * The particle can change the value of a cell from 0 to 1 or from 1 to 0.
 * You are allowed to use the individuality as many times as you want per turn,
 * as long as you always do so before moving 
 * and do not exceed the limit of 30 times during the entire challenge.
 */
$adjusted_cells = array(); # store each adjusted cell and step
$power_use_limit = 30;
$power_factor = 300;
$power_enabled = FALSE;

$step = 1;
recursive_move(0, 0, $step);


# save the result
file_put_contents($output_filename,$path);


# output (I think it wont be possible show the result,
#    generate the final JS script to show the HTML table...)
#show_results_html($input_filename);

# running on CLI (command line interface)
echo "\nthe end: " . date("Y-m-d H:i");




/**
 * load a matrix from txt file
 */
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
                # removing "\n" in the last position
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

/**
 * save a matrix to a txt file
 */
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
                $row = $matrix[ $y ];

                # insert "\n" before new lines, not at the end of line.
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



/**
 * repeat recursively (apply the propagation, test the adjacents after propagation, particle moves to an adjacent)
 *  until find the cell '4' inside the adjacents.
 */
function recursive_move($ay, $ax, $step)
{
    global $matrix;

    /**
     * Since this step is "multiple of the factor", let's activate the power
     */
    $adjusted_cell = '';
    global $power_use_limit;
    global $power_enabled;
    global $power_factor;
    if( (! $power_enabled) and ($power_use_limit > 0) and ($step % $power_factor == 0))
    {
        # I've got the power... :-D
        $power_enabled = TRUE;
    }


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

    # my ideia is change only from 1 to 0, to move to that position, 
    # thinking it will probably be better to move R or D.
    # Wont I save the adjusts on the txt ??
    // if(isset($adjusted_cells[$step]))
    // {
    //     $matrix[ $_y ][ $_x ] = COLOR_WHITE;
    // }


    $adjacents = adjacent_white_cells_to_move($matrix, $ay, $ax, $step);

    $i = 0;
    $result = FALSE;
    foreach($adjacents as $move => $pos)
    {

        if( ! $result)
        {
            $_y = $pos[0];
            $_x = $pos[1];

            # reload the matrix, when reading more than 1 adjacent.
            if ($i > 0)
            {
                $matrix = load_matrix("matrix_{$step}.txt");
            }

            if($matrix[ $_y ][ $_x ] == COLOR_WHITE)
            {
                # move to this adjacent
                $result = recursive_move($_y, $_x, $step + 1);
            }
            elseif($matrix[ $_y ][ $_x ] == COLOR_FINAL)
            {
                $result = TRUE;
            }
            else
            {
                // die("green found at level {$step}, it isn't supposed to happen!!");
                # I decided to change an adjacent cell before moving to that position
                if ($power_enabled and ($power_use_limit > 0))
                {
                    //global $path;

                    /**
                     * To signal the use of your individuality the participant must use 
                     * the letter A followed by the row number and the column number 
                     * of the cell that will be modified.
                     */
                    # I had a problem here, because the "A X Y" was inserted at the end of output txt :-(
                    # I think I have to save this Adjusts into a variable and insert into path below...
                    # How will I change a cell if I still don't know
                    #   the return of 'recursive_move' neither if I'm on the right path ???
                    # The risk will remain, of using the power, but taking a wrong way... ?
                    # BUT I cannot save the matrix !!!!!!!!!!!!!!
                    //$path = $path . " A {$_y} {$_x}";
                    $adjusted_cell = "A {$_y} {$_x}";

                    //$adjusted_cells[$step] = [ $_y, $_x ];


                    $power_use_limit--;
                    $power_enabled = FALSE;

                    # this is the "change" in the cell...
                    $matrix[ $_y ][ $_x ] = COLOR_WHITE;

                    # ... but I'll need to save the matrix again... :-( really ? BUT if it's not the right way... ?
                    # should I save different versions of a matrix ? dejavu effect... ?
                    save_matrix("matrix_{$step}.txt");

                    # move to this adjacent
                    $result = recursive_move($_y, $_x, $step + 1);

                    if($result)
                    {
                        // save the matrix here?? It 
                    }
                }
            }

            if($result)
            {
                global $path;

                if($adjusted_cell != '')
                {
                    $path = $adjusted_cell . ($path =! '' ? ' '.$path : '');
                }

                # Store the move in the begining of path 
                # because the program is "leaving" the recursive function.
                $path = $move . ($path =! '' ? ' '.$path : '');
            }
        }
        $i++;
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
                case COLOR_WHITE:
                    $qty = count_green_adjacents($amatrix, $y, $x);

                    // if "number of adjacent green cells greater than 1 and less than 5"
                    if ($qty > 1 and $qty < 5)
                    {
                        $result[ $y ][ $x ] = COLOR_GREEN; // turn green
                    }
                    else
                    {
                        $result[ $y ][ $x ] = $cell; // remain white
                    }
                break;

                case COLOR_GREEN:
                    $qty = count_green_adjacents($amatrix, $y, $x);

                    // if "number of green adjacent cells greater than 3 and less than 6"
                    if ($qty > 3 and $qty < 6)
                    {
                        $result[ $y ][ $x ] = $cell; // remain green
                    }
                    else
                    {
                        $result[ $y ][ $x ] = COLOR_WHITE; // become white
                    }
                break;

                default: $result[ $y ][ $x ] = $cell;
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
    $result = 0;

    $last_pos_y = count($matrix) - 1;
    $last_pos_x = count($matrix[ $y ]) - 1;

    # 1 = Left
    if($x > 0)
    {
        if($matrix[ $y ][ $x - 1 ] == COLOR_GREEN)
        {
            $result++;
        }
    }

    # 2 = Upper-Left
    if(($y > 0) and ($x > 0))
    {
        if($matrix[ $y - 1 ][ $x - 1 ] == COLOR_GREEN)
        {
            $result++;
        }
    }

    # 3 = Upper
    if($y > 0)
    {
        if($matrix[ $y - 1 ][ $x ] == COLOR_GREEN)
        {
            $result++;
        }
    }

    # 4 = Upper-Right
    if(($y > 0) and ($x < $last_pos_x))
    {
        if($matrix[ $y - 1 ][ $x + 1 ] == COLOR_GREEN)
        {
            $result++;
        }
    }

    # 5 = Right
    if($x < $last_pos_x)
    {
        if($matrix[ $y ][ $x + 1 ] == COLOR_GREEN)
        {
            $result++;
        }
    }

    # 6 = Down-Right
    if(($y < $last_pos_y) and ($x < $last_pos_x))
    {
        if($matrix[ $y + 1 ][ $x + 1 ] == COLOR_GREEN)
        {
            $result++;
        }
    }

    # 7 = Down
    if($y < $last_pos_y)
    {
        if($matrix[ $y + 1 ][ $x ] == COLOR_GREEN)
        {
            $result++;
        }
    }

    # 8 = Down-Left
    if(($y < $last_pos_y) and ($x > 0))
    {
        if($matrix[ $y + 1 ][ $x - 1 ] == COLOR_GREEN)
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

    global $power_enabled;

    $last_pos_y = count($amatrix) - 1;
    $last_pos_x = count($amatrix[ $y ]) - 1;

    # The shortest path in this case is RDRDRD.... or DRDRDRDR...
    # So I defined the order of the adjacents
    # returned here testing the STEP,
    # when the STEP is ODD priorize the RIGHT adjacent
    # else if the STEP is EVEN priorize the DOWN adjacent...
    # Doing the opposite, I achived the result with more moves

    if ($step % 2 != 0) 
    {
        # ODD -> Right is the first in result

        # Right
        if($x < $last_pos_x)
        {
            if($power_enabled or ($amatrix[ $y ][ $x + 1 ] == COLOR_WHITE) or ($amatrix[ $y ][ $x + 1 ] == COLOR_FINAL))
            {
                $result[ MOVE_RIGHT ] = [$y, $x + 1];
            }
        }

        # Down
        if($y < $last_pos_y)
        {
            if($power_enabled or ($amatrix[ $y + 1 ][ $x ] == COLOR_WHITE)  or ($amatrix[ $y + 1 ][ $x ] == COLOR_FINAL))
            {
                $result[ MOVE_DOWN ] = [$y + 1, $x];
            }
        }
    }
    else
    {
        # EVEN -> Down is the first in result

        # Down
        if($y < $last_pos_y)
        {
            if($power_enabled or ($amatrix[ $y + 1 ][ $x ] == COLOR_WHITE)  or ($amatrix[ $y + 1 ][ $x ] == COLOR_FINAL))
            {
                $result[ MOVE_DOWN ] = [$y + 1, $x];
            }
        }

        # Right
        if($x < $last_pos_x)
        {
            if($power_enabled or ($amatrix[ $y ][ $x + 1 ] == COLOR_WHITE) or ($amatrix[ $y ][ $x + 1 ] == COLOR_FINAL))
            {
                $result[ MOVE_RIGHT ] = [$y, $x + 1];
            }
        }
    }

    # UP
    if($y > 0)
    {
        if ($amatrix[ $y - 1 ][ $x ] == COLOR_WHITE)
        {
            $result[ MOVE_UP ] = [$y - 1, $x];
        }
    }

    # Left
    if($x > 0)
    {
        if ($amatrix[ $y ][ $x - 1 ] == COLOR_WHITE)
        {
            $result[ MOVE_LEFT ] = [$y, $x - 1];
        }
    }

    return $result;
}


# function  show_results_html removed...

?>