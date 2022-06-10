<?php
declare (strict_types=1);

namespace panel\domain;


/**
 * Responsible for representing a support topic category.
 */
class SupportTopicCategory implements \JsonSerializable
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $idCategory;
    private $name;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a support topic category.
     *
     * @param       int $id_category Category id
     * @param       string $name Category name
     */
    public function __construct(int $id_category, string $name)
    {
        $this->idCategory = $id_category;
        $this->name = $name;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets category id.
     * 
     * @return      int Category id
     */
    public function getId() : int
    {
        return $this->idCategory;
    }
    
    /**
     * Gets category name.
     * 
     * @return      string Category name
     */
    public function getName() : string
    { 
        return $this->name;
    }
    
    
    //-------------------------------------------------------------------------
    //        Serialization
    //-------------------------------------------------------------------------
    /**
     * {@inheritDoc}
     *  @see \JsonSerializable::jsonSerialize()
     *
     *  @Override
     */
    public function jsonSerialize(): array
    {
        return array(
            'id' => $this->idCategory,
            'name' => $this->name
        );
    }
}