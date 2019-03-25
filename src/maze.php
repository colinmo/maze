<?php
/**
 * Maze generator
 *
 * PHP Version 5
 *
 * @category  Code
 * @package   Maze
 * @author    Colin Morris <relapse@gmail.com>
 * @copyright 2018 Colin Morris
 * @license   https://en.wikipedia.org/wiki/FreeBSD_Documentation_License FreeBSD
 * @link      https://vonexplaino.com/code/maze
 */
// Dimensions
$max_x = 30;
$max_y = 30;
if (isset($_GET['x'])) {
    $max_x = (int) $_GET['x'];
}
if (isset($_GET['y'])) {
    $max_y = (int) $_GET['y'];
}
if (isset($_GET['moo']) || isset($_GET['prof'])) {
    $max_x = 61;
    $max_y = 24;
}

$visited = array();
$canpick = array();
$square = array('S' => null, 'W' => null, 'visited' => false);
$maze = makeMap($max_x, $max_y, $square);

$cursquare_x = mt_rand(0, count($maze) - 1);
$cursquare_y = mt_rand(0, count($maze[$cursquare_x]) - 1);
$cursquare = $maze[$cursquare_x][$cursquare_y];

$maze[$cursquare_x][$cursquare_y]['visited'] = true;
$visited[] = array("X" => $cursquare_x, 'Y' => $cursquare_y);
$canpick[] = array("X" => $cursquare_x, 'Y' => $cursquare_y);

$loops = 60;
$total_to_visit = $max_x * $max_y;
while (count($visited) < $total_to_visit) {
    $direction = getDirection($maze, $cursquare_x, $cursquare_y, $max_x, $max_y);
    while ($direction == '-') {
        list($cursquare_x, $cursquare_y) = getLegal($canpick, $maze, $max_x, $max_y);
        $direction = getDirection($maze, $cursquare_x, $cursquare_y, $max_x, $max_y);
    }
    makePath($maze, $cursquare_x, $cursquare_y, $direction, $canpick, $visited);
}

$maze[0][0]['W'] = 1;
drawMaze($maze, $max_x, $max_y);

/**
 * Fill the array with an empty map
 *
 * @param integer $max_x  Width
 * @param integer $max_y  Height
 * @param array   $square no wall array
 *
 * @return array Fully populated two dimensional empty, unvisited map
 */
function makeMap($max_x, $max_y, $square)
{
    return array_fill(0, $max_x, array_fill(0, $max_y, $square));
}

/**
 * Get a legal move from the current position
 *
 * @param array   $canpick Locations we can pick from
 * @param array   $maze    The maze as it stands
 * @param integer $max_x   Maze width
 * @param integer $max_y   Maze height
 *
 * @return array Chosen location
 */
function getLegal(&$canpick, $maze, $max_x, $max_y)
{
    while (1) {
        $me = array_rand($canpick, 1);
        $direction = getDirection(
            $maze,
            $canpick[$me]['X'],
            $canpick[$me]['Y'],
            $max_x,
            $max_y
        );
        if ($direction == '-') {
            unset($canpick[$me]);
        } else {
            break;
        }
    }
    return array($canpick[$me]['X'], $canpick[$me]['Y']);
}

/**
 * Record visited location
 *
 * @param array   $canpick Locations we can pick from
 * @param array   $visited Locations visited
 * @param integer $x       Location x
 * @param integer $y       Location y
 *
 * @return void
 */
function addVisited(&$canpick, &$visited, $x, $y)
{
    if (!in_array(array('X' => $x, 'Y' => $y), $visited)) {
        $canpick[] = array('X' => $x, 'Y' => $y);
        $visited[] = array('X' => $x, 'Y' => $y);
    }
}

/**
 * Pick a random direction
 *
 * @param array   $maze  Full maze
 * @param integer $x     Starting X
 * @param integer $y     Starting Y
 * @param integer $max_x Max width
 * @param integer $max_y Max height
 *
 * @return string Direction (NSWE)
 */
function getDirection($maze, $x, $y, $max_x, $max_y)
{
    $valid_dirs = array('N', 'S', 'E', 'W');
    shuffle($valid_dirs);
    foreach ($valid_dirs as $dir) {
        switch ($dir) {
        case 'S':
            if ($y < $max_y - 1 && empty($maze[$x][$y]['S']) && !$maze[$x][$y + 1]['visited']) {
                return "S";
            }
            break;
        case 'W':
            if ($x > 0 && empty($maze[$x][$y]['W']) && !$maze[$x - 1][$y]['visited']) {
                return "W";
            }
            break;
        case 'N':
            if ($y > 0 && empty($maze[$x][$y - 1]['S']) && !$maze[$x][$y - 1]['visited']) {
                return "N";
            }
            break;
        case 'E':
            if ($x < $max_x - 1 && empty($maze[$x + 1][$y]['W']) && !$maze[$x + 1][$y]['visited']) {
                return "E";
            }
            break;
        }
    }
    return '-';
}

/**
 * Make a path in the selected direction. This moves the x, y in the selected
 * direction, and links the previous location to the new one by a path. It
 * changes the maze points we can pick from, and marks the location as visited.
 *
 * @param array   $maze      The maze array
 * @param integer $x         Current location x
 * @param integer $y         Current location y
 * @param string  $direction Direction to go in
 * @param array   $canpick   Array of pickable locations
 * @param array   $visited   Array of visited locations
 *
 * @return void
 */
function makePath(&$maze, &$x, &$y, $direction, &$canpick, &$visited)
{
    switch ($direction) {
    case 'S':
        $maze[$x][$y]['S'] = array($x, $y + 1);
        $y++;
        break;
    case 'W':
        $maze[$x][$y]['W'] = array($x - 1, $y);
        $x--;
        break;
    case 'N':
        $maze[$x][$y - 1]['S'] = array($x, $y);
        $y--;
        break;
    default:
        $maze[$x + 1][$y]['W'] = array($x, $y);
        $x++;
        break;
    }
    $maze[$x][$y]['visited'] = true;
    addVisited($canpick, $visited, $x, $y);
}

/**
 * Draw the maze by creating a SVG. This is output to STDOUT.
 *
 * @param array   $maze  Maze
 * @param integer $max_x Width
 * @param integer $max_y Height
 *
 * @return void
 */
function drawMaze($maze, $max_x, $max_y)
{
    // header('Content-type: image/svg+xml');
    $buffer = 2;
    // echo '<?xml version="1.0"  standalone="no"';
    echo '<svg xmlns="http://www.w3.org/2000/svg" height="' . ($max_y * 20 + $buffer * 2) . '" width="' . ($max_x * 20 + $buffer * 2) . '">
        <line x1="2" y1="1" x2="' . ($max_x * 20 + $buffer + 1) . '" y2="1" style="stroke:rgb(255,0,0);stroke-width:2" />
        <line x1="' . ($max_x * 20 + $buffer) . '" y1="1" x2="' . ($max_x * 20 + $buffer) . '" y2="' . ($max_y - 1) * 20 . '" style="stroke:rgb(255,0,0);stroke-width:2" />' . chr(13);
    foreach (range(0, $max_y - 1) as $y) {
        foreach (range(0, $max_x - 1) as $x) {
            $done = $maze[$x][$y];
            if (empty($done['W'])) {
                echo '<line x1="' . ($x * 20 + $buffer) . '" y1="' . (($y) * 20) . '" x2="' . ($x * 20 + $buffer) . '" y2="' . (($y + 1) * 20 + 1) . '" style="stroke:rgb(255,0,0);stroke-width:2" />' . chr(13);
            } else {
                echo '<line x1="' . ($x * 20 + $buffer) . '" y1="' . (($y + 1) * 20 - 1) . '" x2="' . ($x * 20 + $buffer) . '" y2="' . (($y + 1) * 20 + 1) . '" style="stroke:rgb(255,0,0);stroke-width:2" />' . chr(13);
            }
            if (empty($done['S'])) {
                echo '<line x1="' . ($x * 20 + $buffer) . '" y1="' . (($y + 1) * 20) . '" x2="' . (($x + 1) * 20 + $buffer) . '" y2="' . (($y + 1) * 20) . '" style="stroke:rgb(255,0,0);stroke-width:2" />' . chr(13);
            }
        }
    }
    echo chr(13) . '</svg>';

}
