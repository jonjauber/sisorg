<?php
/**
 * arquivo derivado dos arquivos do Adianti Framework
 * @version    1.0
 * @package    sisorg
 * @subpackage model
 * @author     Jacques Geraldo Rocha
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */

//http://www.portaldecontabilidade.com.br/guia/planodecontas.htm
class Produto extends TRecord
{
    const TABLENAME = 'sis_produto';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}  

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('descricao');
        parent::addAttribute('id_matriz');
        parent::addAttribute('id_filial');        
    }
    
    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related System_userSystem_user_group objects
        $id = isset($id) ? $id : $this->id; 
        // delete the object itself
        parent::delete($id);
    }    
}
