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
namespace colinmo;

class Maze
{
    private $max_x = 30;
    private $max_y = 30;
    private $visited = array();
    private $canpick = array();
    private $maze = array();

    public function __construct($options = [])
    {
        $options = (object) $options;
        if (isset($options->x)) {
            $this->max_x = $options->x;
        }
        if (isset($options->y)) {
            $this->max_y = $options->y;
        }
        if (isset($options->preset) && in_array($options->preset, ['moo','prof'])) {
            $this->max_x = 61;
            $this->max_y = 24;
        }
    }

    /**
     * Create the maze array
     *
     * @return Maze
     */
    public function createMaze()
    {
        $this->maze = $this->makeMap();
        $cursquare_x = mt_rand(0, count($this->maze) - 1);
        $cursquare_y = mt_rand(0, count($this->maze[$cursquare_x]) - 1);

        $this->maze[$cursquare_x][$cursquare_y]['visited'] = true;
        $this->visited[] = array("X" => $cursquare_x, 'Y' => $cursquare_y);
        $this->canpick[] = array("X" => $cursquare_x, 'Y' => $cursquare_y);

        $total_to_visit = $this->max_x * $this->max_y;
        while (count($this->visited) < $total_to_visit) {
            $direction = $this->getDirection($cursquare_x, $cursquare_y);
            while ($direction == '-') {
                list($cursquare_x, $cursquare_y) = $this->getLegal();
                $direction = $this->getDirection($cursquare_x, $cursquare_y);
            }
            $this->makePath($cursquare_x, $cursquare_y, $direction);
        }

        $this->maze[0][0]['W'] = 1;

        return $this;
    }

    /**
     * Max X
     *
     * @return integer
     */
    public function getMaxX()
    {
        return $this->max_x;
    }

    /**
     * Fill the array with an empty map
     *
     * @return array Fully populated two dimensional empty, unvisited map
     */
    private function makeMap()
    {
        return array_fill(
            0,
            $this->max_x,
            array_fill(
                0,
                $this->max_y,
                array('S' => null, 'W' => null, 'visited' => false)
            )
        );
    }

    /**
     * Get a legal move from the current position
     *
     * @return array Chosen location
     */
    private function getLegal()
    {
        while (1) {
            $me = array_rand($this->canpick, 1);
            $direction = $this->getDirection(
                $this->canpick[$me]['X'],
                $this->canpick[$me]['Y']
            );
            if ($direction == '-') {
                unset($this->canpick[$me]);
            } else {
                break;
            }
        }
        return array($this->canpick[$me]['X'], $this->canpick[$me]['Y']);
    }
    /**
     * Record visited location
     *
     * @param integer $x       Location x
     * @param integer $y       Location y
     *
     * @return void
     */
    private function addVisited($x, $y)
    {
        if (!in_array(array('X' => $x, 'Y' => $y), $this->visited)) {
            $this->canpick[] = array('X' => $x, 'Y' => $y);
            $this->visited[] = array('X' => $x, 'Y' => $y);
        }
    }
    /**
     * Ensure direction chosen is valid
     *
     * @param integer $x
     * @param integer $y
     * @param string  $direction
     * @return string Direction (NSEW)
     */
    private function validDirection($x, $y, $direction)
    {
        $return = '-';
        switch ($direction) {
            case 'S':
                if ($y < $this->max_y - 1 && empty($this->maze[$x][$y]['S']) && !$this->maze[$x][$y + 1]['visited']) {
                    $return = "S";
                }
                break;
            case 'W':
                if ($x > 0 && empty($this->maze[$x][$y]['W']) && !$this->maze[$x - 1][$y]['visited']) {
                    $return = "W";
                }
                break;
            case 'N':
                if ($y > 0 && empty($this->maze[$x][$y - 1]['S']) && !$this->maze[$x][$y - 1]['visited']) {
                    $return = "N";
                }
                break;
            case 'E':
                if ($x < $this->max_x - 1 && empty($this->maze[$x + 1][$y]['W']) && !$this->maze[$x + 1][$y]['visited']) {
                    $return = "E";
                }
                break;
            default:
                $return = '-';
        }
        return $return;
    }
    /**
     * Pick a random direction
     *
     * @param integer $x     Starting X
     * @param integer $y     Starting Y
     *
     * @return string Direction (NSWE)
     */
    private function getDirection($x, $y)
    {
        $valid_dirs = array('N', 'S', 'E', 'W');
        shuffle($valid_dirs);
        foreach ($valid_dirs as $dir) {
            $direction = $this->validDirection($x, $y, $dir);
            if ($direction != '-') {
                return $direction;
            }
        }
        return '-';
    }

    /**
     * Make a path in the selected direction. This moves the x, y in the selected
     * direction, and links the previous location to the new one by a path. It
     * changes the maze points we can pick from, and marks the location as visited.
     *
     * @param integer $x         Current location x
     * @param integer $y         Current location y
     * @param string  $direction Direction to go in
     *
     * @return void
     */
    private function makePath(&$x, &$y, $direction)
    {
        switch ($direction) {
            case 'S':
                $this->maze[$x][$y]['S'] = array($x, $y + 1);
                $y++;
                break;
            case 'W':
                $this->maze[$x][$y]['W'] = array($x - 1, $y);
                $x--;
                break;
            case 'N':
                $this->maze[$x][$y - 1]['S'] = array($x, $y);
                $y--;
                break;
            default:
                $this->maze[$x + 1][$y]['W'] = array($x, $y);
                $x++;
                break;
        }
        $this->maze[$x][$y]['visited'] = true;
        $this->addVisited($x, $y);
    }

    /**
     * Draw the maze by creating a SVG. This is output to STDOUT.
     *
     * @return string
     */
    public function drawMazeSVG()
    {
        $output = "";
        $line_template = '<line x1="%1$d" y1="%2$d" x2="%3$d" y2="%4$d" />' . "\n";
        $buffer = 2;
        $output .= '<?xml version="1.0" standalone="no">' . "\n";
        $output .= '<svg xmlns="http://www.w3.org/2000/svg" height="' . ($this->max_y * 20 + $buffer * 2) . '" width="' . ($this->max_x * 20 + $buffer * 2) . '">' . "\n"
        . "<style>line { stroke: #532109; stroke-width: 3; }</style>\n"
        . sprintf($line_template, 2, 1, $this->max_x * 20 + $buffer + 1, 1) . "\n"
        . sprintf($line_template, $this->max_x * 20 + $buffer, 1, $this->max_x * 20 + $buffer, ($this->max_y - 1) * 20) . "\n";
        foreach (range(0, $this->max_y - 1) as $y) {
            foreach (range(0, $this->max_x - 1) as $x) {
                $done = $this->maze[$x][$y];
                if (empty($done['W'])) {
                    $output .= sprintf($line_template, $x * 20 + $buffer, ($y) * 20, $x * 20 + $buffer, ($y + 1) * 20 + 1);
                } else {
                    $output .= sprintf($line_template, $x * 20 + $buffer, ($y + 1) * 20 - 1, $x * 20 + $buffer, ($y + 1) * 20 + 1);
                }
                if (empty($done['S'])) {
                    $output .= sprintf($line_template, $x * 20 + $buffer, ($y + 1) * 20, ($x + 1) * 20 + $buffer, ($y + 1) * 20);
                }
            }
        }
        return $output . chr(13) . '</svg>';
    }
}
