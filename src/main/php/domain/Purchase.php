<?php
declare (strict_types=1);

namespace domain;


/**
 * Responsible for representing purchases.
 */
class Purchase
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $bundle;
    private $date;
    private $price;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a purchase.
     *
     * @param       Bundle $bundle Purchased bundle
     * @param       \DateTime $date Purchase date
     * @param       float $price Purchase value
     */
    public function __construct(Bundle $bundle, \DateTime $date, float $price)
    {
        $this->bundle = $bundle;
        $this->date = $date;
        $this->price = $price;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets purchased bundle.
     *
     * @return      Bundle Purchased bundle
     */
    public function getBundle() : Bundle
    {
        return $this->bundle;
    }
    
    /**
     * Gets purchase date.
     *
     * @return      \DateTime Purchase date
     */
    public function getDate() : \DateTime
    {
        return $this->date;
    }
    
    /**
     * Gets purchase value
     *
     * @return      float Purchase value
     */
    public function getPrice() : float
    {
        return $this->price;
    }
}
