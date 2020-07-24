<?php
namespace models;

use core\Model;


/**
 * Responsible for managing authorization table.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0
 * @since		1.0
 */
class Authorization extends Model
{
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates authorization table manager.
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    
    //-------------------------------------------------------------------------
    //        Methods
    //-------------------------------------------------------------------------
    /**
     * Gets admin authorization.
     * 
     * @param       int $id_admin Admin id
     * 
     * @return      \models\Authorization Admin authorization 
     */
    public function getAuthorization($id_admin)
    {
        $sql = $this->db->prepare("
            SELECT  *
            FROM    authorization
            WHERE   id_authorization = (select  id_authorization
                                        from    admins
                                        where   id_admin = ?)
        ");
        $sql->execute(array($id_admin));
        $sql = $sql->fetch();
        return new Authorization(
            $sql['id_authorization'],
            $sql['name'], 
            $sql['level']
        );
    }
    
}