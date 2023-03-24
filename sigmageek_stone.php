<?php

/**
 * RICARDO VOIGT (https://www.linkedin.com/in/ricardo-voigt-software)
 * 2023-03-24
 * My solution for the "Stone Automata Maze Challenge"
 * by https://sigmageek.com/
 * 
 * List of files:
 *  - sigmageek_TESTE_input.txt (my tests)
 *  - sigmageek_stone_input.txt (input from sigmageek, matrix 0)
 *  - output_file.txt           (text file containing all the movements, created on the FIRST RUN)
 * List of functions:
 *  - load_matrix               (load a matrix from a txt file)
 *  - save_matrix               (save a matrix into a txt file)
 *  - recursive_move            (main function of the solution) 
 *  - apply_propagation         (calculate and apply the propagation rule)
 *  - count_green_adjacents     (quantity of green adjacent cells around the position)
 *  - adjacent_white_cells_to_move (list of possible cells to move, with the direction and coordinates)
 *  - test_results              (evaluate the results creating txt files with each step, it's needed to RUN AGAIN)
 *  - show_results_html         (return a very simple HTML page with a table and javascript to show the steps)
 * 
 * OUTPUT:  RUN once, the program will create the "output_file.txt", RUN again and the result will be shown as HTML.
 * 
 */

CONST COLOR_WHITE = '0';
CONST COLOR_GREEN = '1';
CONST COLOR_FINAL = '4';

CONST MOVE_UP = 'U';
CONST MOVE_DOWN = 'D';
CONST MOVE_RIGHT = 'R';
CONST MOVE_LEFT = 'L';


//$initial_filename = 'sigmageek_TESTE_input.txt';
$initial_filename = "sigmageek_stone_input.txt";
$output_filename = "output_file.txt";

// if(file_exists( $output_filename ))
// {
//     $matrix = array();
//     show_results_html( $initial_filename, $output_filename );
//     //test_results( $initial_filename, $output_filename );
//     //die('<H1>the end...</H1>');
// }
// else
// {
    $matrix = load_matrix($initial_filename);

    $path = '';

    $step = 1;
    recursive_move(0, 0, $step);

    #output file
    // $output_file = fopen($output_filename, "w");
    // try
    // {
    //     fwrite($output_file, $path);
    //     echo "RESULTING STEPS/PATH={$path}\n";
    // }
    // finally
    // {
    //     fclose($output_file);
    // }

    show_results_html($initial_filename);

// }




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
        // echo $move.'<br>';
        # each adjacent is a new path... :-O ? how to "rollback"? 
        # I though recursive would solve that... :-/
        # I need a counter to know the "level"...

        if( ! $result)
        {
            $_y = $pos[0];
            $_x = $pos[1];

            # reload here the matrix??
            if ($i > 0)
            {
                $matrix = load_matrix("matrix_{$step}.txt");
            }

            if($matrix[ $_y ][ $_x ] == COLOR_WHITE)
            {
                $result = recursive_move($_y, $_x, $step+1);
            }
            elseif($matrix[ $_y ][ $_x ] == COLOR_FINAL)
            {
                $result = TRUE;
            }
            else
            {
                die("<h1>green found at level {$step}, it isn't supposed to happen!!</h1>");
            }

            if($result)
            {
                global $path;
                # store the move 
                if($path == '')
                {
                    $path = $move;
                }
                else
                {
                    $path = $move . ' '. $path;
                }
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

    $last_pos_y = count($matrix) - 1;
    $last_pos_x = count($matrix[ $y ]) - 1;
    $result = 0;

    # 1 = Left
    if($x > 0)
    {
        if($matrix[ $y ][ $x - 1 ] == '1')
        {
            $result++;
        }
    }

    # 2 = Upper-Left
    if(($y > 0) and ($x > 0))
    {
        if($matrix[ $y - 1 ][ $x - 1 ] == '1')
        {
            $result++;
        }
    }

    # 3 = Upper
    if($y > 0)
    {
        if($matrix[ $y - 1 ][ $x ] == '1')
        {
            $result++;
        }
    }

    # 4 = Upper-Right
    if(($y > 0) and ($x < $last_pos_x))
    {
        if($matrix[ $y - 1 ][ $x + 1 ] == '1')
        {
            $result++;
        }
    }

    # 5 = Right
    if($x < $last_pos_x)
    {
        if($matrix[ $y ][ $x + 1 ] == '1')
        {
            $result++;
        }
    }

    # 6 = Down-Right
    if(($y < $last_pos_y) and ($x < $last_pos_x))
    {
        if($matrix[ $y + 1 ][ $x + 1 ] == '1')
        {
            $result++;
        }
    }

    # 7 = Down
    if($y < $last_pos_y)
    {
        if($matrix[ $y + 1 ][ $x ] == '1')
        {
            $result++;
        }
    }

    # 8 = Down-Left
    if(($y < $last_pos_y) and ($x > 0))
    {
        if($matrix[ $y + 1 ][ $x - 1 ] == '1')
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

    # 2023-03-24 - this night, I realized that the 
    # shortest path in this case is ...RDRDRD....
    # So I changed the order of the adjacents returned here
    # and now I had the ideia of testing the STEP,
    # when the STEP is ODD priorize the DOWN adjacent
    # but if the STEP is EVEN priorize the RIGHT adjacent...
    # Well done! It works now... :-)

    if ($step % 2 == 0)
    {
        # EVEN -> Right first in the result

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
        # ODD -> Down first in the result

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
 * Testing and showing the results, based on the resulting files.
 * Run the script again to create 'step_step_after_move.txt' files...
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

        # save resulting matrix after each move
        $y = 0;
        $x = 0;
        $step = 1;
        $the_end = FALSE;
        while (file_exists("matrix_{$step}.txt") and ! $the_end)
        {
            $move = $moves[$step-1];

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

            if($matrix[ $y ][ $x ] == COLOR_FINAL)
            {
                $the_end = TRUE;
            }
            else
            {
                $matrix[ $y ][ $x ] = 'X';
            }
            save_matrix("step_{$step}_after_{$move}.txt");

            $step++;
        }
    }
}

/**
 * ICING ON THE CAKE
 * function to show the movimentos in HTML format
 */
function show_results_html($initial_filename)
{
    global $matrix;
    global $path;

    $moves = explode(' ', $path);

    if(count($moves) > 0)
    {
        # load the initial matrix (0)
        $matrix = load_matrix($initial_filename);

        # table structure based on the matrix (0)
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
    
        $output_moves = "<H2>" . implode(' ', $moves) . "</H2>";


        # JS to make the magic...
        $output_javascript = "<SCRIPT>";

        # create a list with all of the steps/propagations... :-O
        $y = 0;
        $x = 0;
        $output_javascript.= " const list_matrix = [\n";
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

        # make the magic and show the moviments...
        $output_javascript.= "var i = 0;
            var elem = undefined;
            setInterval(
                function()
                {
                    i++;
                    const matrix = list_matrix[ i ];

                    for (var y = 0; y < matrix.length; y++)
                    {
                        var row = matrix[ y ];
                        for (var x = 0; x < row.length; x++)
                        {
                            var cell = row[ x ];
                            elem = document.getElementById( \"cell_\" + y.toString() + \"_\" + x.toString() );
                            elem.className = \"class_\" + cell;
                            // elem.innerHTML = cell;
                        }
                    }
                }, 200);";
        $output_javascript.= "</SCRIPT>";


        # output the final HTML
        $output_html = "<HTML>
            <HEAD>
            <STYLE>
                table {width: 100%; height: 50%; border-collapse: collapse; }
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
            {$output_table}
            {$output_moves}
            </BODY>
            </HTML>";
        echo $output_html;
    }
}

?>