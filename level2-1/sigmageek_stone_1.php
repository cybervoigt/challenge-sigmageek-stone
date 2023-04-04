<?php

/**
 * RICARDO VOIGT (https://www.linkedin.com/in/ricardo-voigt-software)
 * 
 * My solution for the "Stone Automata Maze Challenge"
 * by https://sigmageek.com/
 * 
 * LEVEL 2 - CHALLENGE 1 (2000 rows x 2000 columns)
 * 2023-04-03 - 2023-04-07
 * 
 * List of files:
 *  - input1.txt                (input from sigmageek, matrix 0)
 *  - output_file.txt           (resulting text file containing all the movements)
 * List of functions:
 *  - load_matrix               (load a matrix from a txt file)
 *  - save_matrix               (save a matrix into a txt file)
 *  - recursive_move            (main function of the solution) 
 *  - apply_propagation         (calculate and apply the propagation rule)
 *  - count_green_adjacents     (quantity of green adjacent cells around the position)
 *  - adjacent_white_cells_to_move (list of possible cells to move, with the direction and coordinates)
 *  - show_results_html         (return a very simple HTML page with a table and javascript to show the steps and moves)
 * 
 */

const TIME_ONE_HOUR = 3600;
set_time_limit( TIME_ONE_HOUR * 12 );

/**
 * Execution test 1 between "2023-04-03 15:26" to "2023-04-04 00:56" 
 * (about 8 hours and 30 minutes, 12388 steps and almost 100GB of txt files !!!)
 * 
 */

CONST COLOR_WHITE = 0;
CONST COLOR_GREEN = 1;
CONST COLOR_FINAL = 4;

CONST MOVE_UP = 'U';
CONST MOVE_DOWN = 'D';
CONST MOVE_RIGHT = 'R';
CONST MOVE_LEFT = 'L';

# input
$initial_filename = "input1.txt";

$matrix = load_matrix($initial_filename);

# processing
$path = '';

$step = 1;
recursive_move(0, 0, $step);


# save the result
file_put_contents("output_file_1.txt",$path);


# output (I think it isn't possible this way...)
#show_results_html($initial_filename);
echo "the end...";




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
                die("green found at level {$step}, it isn't supposed to happen!!");
            }

            if($result)
            {
                global $path;

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
            if(($amatrix[ $y ][ $x + 1 ] == COLOR_WHITE) or ($amatrix[ $y ][ $x + 1 ] == COLOR_FINAL))
            {
                $result[ MOVE_RIGHT ] = [$y, $x + 1];
            }
        }

        # Down
        if($y < $last_pos_y)
        {
            if(($amatrix[ $y + 1 ][ $x ] == COLOR_WHITE)  or ($amatrix[ $y + 1 ][ $x ] == COLOR_FINAL))
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
            if(($amatrix[ $y + 1 ][ $x ] == COLOR_WHITE)  or ($amatrix[ $y + 1 ][ $x ] == COLOR_FINAL))
            {
                $result[ MOVE_DOWN ] = [$y + 1, $x];
            }
        }

        # Right
        if($x < $last_pos_x)
        {
            if(($amatrix[ $y ][ $x + 1 ] == COLOR_WHITE) or ($amatrix[ $y ][ $x + 1 ] == COLOR_FINAL))
            {
                $result[ MOVE_RIGHT ] = [$y, $x + 1];
            }
        }
    }

    # UP
    if($y > 0)
    {
        if($amatrix[ $y - 1 ][ $x ] == COLOR_WHITE)
        {
            $result[ MOVE_UP ] = [$y - 1, $x];
        }
    }

    # Left
    if($x > 0)
    {
        if($amatrix[ $y ][ $x - 1 ] == COLOR_WHITE)
        {
            $result[ MOVE_LEFT ] = [$y, $x - 1];
        }
    }

    return $result;
}



/**
 * ICING ON THE CAKE
 * function to show the moviments in HTML format
 */
function show_results_html($initial_filename)
{
    global $matrix;
    global $path;

    # without 'trim', explode returns 1 more empty item :-(
    $moves = explode(' ', trim($path));

    $qty_moves = count($moves);

    if($qty_moves > 0)
    {
        $output_quantity = "Quantity of moves: {$qty_moves}";

        # load the initial matrix (0)
        $matrix = load_matrix($initial_filename);

        # table structure based on the initial matrix (0)
        $output_table = "<TABLE>";
        foreach($matrix as $_y => $row)
        {
            $output_table.= "<TR>";
            foreach($row as $_x => $cell)
            {
                $output_table.= "<TD id='cell_{$_y}_{$_x}' class='class_{$cell}'>&nbsp;</TD>";
            }
            $output_table.= "</TR>";
        }
        $output_table.= "</TABLE>";
    

        # JS to make the magic...
        $output_javascript = "<SCRIPT>";

        # create a list with all of the steps/propagations... :-O
        $y = 0;
        $x = 0;
        $output_javascript.= " const matrix_list = [\n";
        foreach($moves as $i => $move)
        {
            $step = $i+1;
            if (file_exists("matrix_{$step}.txt"))
            {
                # next move
                switch($move)
                {
                    case MOVE_UP:    $y--;
                    break;
                    case MOVE_RIGHT: $x++;
                    break;
                    case MOVE_DOWN:  $y++;
                    break;
                    case MOVE_LEFT:  $x--;
                    break;
                }

                $matrix = load_matrix("matrix_{$step}.txt");

                $output_javascript.= ($i > 0 ? ',' : '') .  "[\n";
                foreach($matrix as $_y => $row)
                {
                    $output_javascript.= ($_y > 0 ? ',' : ''). "[";
                    foreach($row as $_x => $cell)
                    {
                        $output_javascript.= ($_x > 0 ? ',' : '');
                        # insert the particle 'X' on each position
                        if($y == $_y and $x == $_x)
                        {
                            $output_javascript.= "'X'";
                        }
                        else
                        {
                            $output_javascript.= $cell;
                        }
                    }
                    $output_javascript.= "]".  "\n";
                }
                $output_javascript.= "]\n";
            }
        }
        $output_javascript.= "];\n";

        # make the magic JS to show the moviments...
        $miliseconds = 1000;
        $output_javascript.= "
            var i = 0;
            var elem = undefined;
            setInterval(
                function()
                {
                    //console.log(matrix_list.length);
                    if(i < matrix_list.length)
                    {
                        const matrix = matrix_list[ i ];

                        for (var y = 0; y < matrix.length; y++)
                        {
                            var row = matrix[ y ];
                            for (var x = 0; x < row.length; x++)
                            {
                                const cell = row[ x ];
                                elem = document.getElementById( \"cell_\" + y.toString() + \"_\" + x.toString() );
                                elem.className = \"class_\" + cell;
                            }
                        }
                    }
                    i++;
                }, {$miliseconds});";
        $output_javascript.= "</SCRIPT>";


        # output the final HTML
        $output_html = "<HTML>
            <HEAD>
            <STYLE>
                table {width: 100%; border-collapse: collapse; }
                table, tr, td {border: 1px solid black; }
                .class_0 { background-color: white; }
                .class_1 { background-color: green; }
                .class_3 { background-color: blue; }
                .class_4 { background-color: blue; }
                .class_X { background-color: black; }
            </STYLE>
            {$output_javascript}
            </HEAD>
            <BODY>
            <H1>Ricardo Voigt - Stone Automata Maze Challenge - 2023-03-31</H1>
            <H2>cybervoigt@gmail.com</H2>
            <H3>{$path}</H3>
            <H4>{$output_quantity}</H4>
            {$output_table}
            </BODY>
            </HTML>";
        echo $output_html;
    }
}

?>