<?php

namespace Bundle\LichessBundle\Chess;

class Clock
{
    /**
     * Maximum time of the clock per player
     *
     * @var float
     */
    private $limit = null;

    /**
     * Current player color
     *
     * @var string
     */
    private $color = null;

    /**
     * Times for white and black players
     *
     * @var array of float
     */
    private $times = null;

    /**
     * Internal timer
     *
     * @var float
     */
    private $timer = null;

    public function __construct($limit)
    {
        $this->limit = (int) $limit;
        if($this->limit < 60) {
            throw new \InvalidArgumentException(sprintf('Invalid time limit "%s"', $limit));
        }

        $this->reset();
    }

    /**
     * initializes the clock
     *
     * @return null
     **/
    public function reset()
    {
        $this->color = 'white';
        $this->times = array('white' => 0, 'black' => 0);
        $this->timer = null;
    }

    /**
     * Switch to next player
     *
     * @return null
     **/
    public function step()
    {
        $this->times[$this->color] += microtime(true) - $this->timer;
        $this->color = 'white' === $this->color ? 'black' : 'white';
        $this->timer = microtime(true);
    }

    /**
     * Start the clock now
     *
     * @return null
     **/
    public function start()
    {
        $this->timer = microtime(true);
    }

    /**
     * Stop the clock now
     *
     * @return null
     **/
    public function stop()
    {
        $this->times[$this->color] += microtime(true) - $this->timer;
        $this->timer = null;
    }

    /**
     * Tell if the player with this color is out of tim
     *
     * @return boolean
     **/
    public function isOutOfTime($color)
    {
        return 0 === $this->getRemainingTime($color);
    }

    /**
     * Tell the time a player has to finish the game
     *
     * @return float
     **/
    public function getRemainingTime($color)
    {
        $time = $this->limit - $this->getElapsedTime($color);

        return max(0, round($time, 3));
    }

    /**
     * Tell the time a player has used
     *
     * @return float
     **/
    public function getElapsedTime($color)
    {
        $time = $this->times[$color];
        if($this->isRunning() && $color === $this->color) {
            $time += microtime(true) - $this->timer;
        }

        return round($time, 3);
    }

    public function getRemainingTimes()
    {
        return array(
            'white' => $this->getRemainingTime('white'),
            'black' => $this->getRemainingTime('black')
        );
    }

    /**
     * Get color
     * @return string
     */
    public function getColor()
    {
      return $this->color;
    }

    /**
     * Set color
     * @param  string
     * @return null
     */
    public function setColor($color)
    {
      $this->color = $color;
    }

    /**
     * Tell if the clock is enabled
     *
     * @return boolean
     **/
    public function isEnabled()
    {
        return $this->limit > 0;
    }

    /**
     * Tell if the clock is running
     *
     * @return boolean
     **/
    public function isRunning()
    {
        return (boolean) $this->timer;
    }

    /**
     * Get limit
     * @return int
     */
    public function getLimit()
    {
      return $this->limit;
    }

    /**
     * Get times
     * @return array of float
     */
    public function getTimes()
    {
      return $this->times;
    }

    public function __clone()
    {
        $this->reset();
    }
}