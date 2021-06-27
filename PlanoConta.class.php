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
class PlanoConta extends TRecord
{
    const TABLENAME = 'sis_planoconta';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}  

    /**
     * Constructor method
     */
    private $matriz;
    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('id_conta_superior');
        parent::addAttribute('codigo_conta');
        parent::addAttribute('descricao');
        parent::addAttribute('contador');
        parent::addAttribute('valor');
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
    
    public function get_matriz()
      {
           if (empty($this->matriz))
            $this->matriz = new Matriz($this->id_matriz);
    
           // returns the associated object
           return $this->matriz;
      }
}
