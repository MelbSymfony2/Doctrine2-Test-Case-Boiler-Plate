<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dustin
 * Date: 04/09/13
 * Time: 9:00 PM
 * To change this template use File | Settings | File Templates.
 */

namespace DoctrineTest\Test\Assets\Model\Simple;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 */
class Car {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    public $id;

    /**
     * @ORM\Column(type="string")
     */
    public $make;

    /**
     * @ORM\Column(type="string")
     */
    public $model;

    /**
     * @ORM\Column(type="integer")
     */
    public $year;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $make
     */
    public function setMake($make)
    {
        $this->make = $make;
    }

    /**
     * @return mixed
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }


}