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
class Filial extends TRecord
{
    const TABLENAME = 'sis_filial';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}  

    private $matriz;
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome');
        //parent::addAttribute('cnpj');
        parent::addAttribute('endereco');        
        parent::addAttribute('telefone');        
        parent::addAttribute('id_matriz');   
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
    
     public function get_matriz()
      {
           if (empty($this->matriz))
            $this->matriz = new Matriz($this->id_matriz);
    
           // returns the associated object
           return $this->matriz;
      }
}
