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
class Lancamento extends TRecord
{
    const TABLENAME = 'sis_lancamento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}  

    private $debito;
    private $credito;
    private $historico_padrao;
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('competencia');//ano-mês
        parent::addAttribute('conta_debito');//para onde foi o valor
        parent::addAttribute('historico');//histórico padrão para balancete        
        parent::addAttribute('conta_credito');//de onde veio o valor        
        parent::addAttribute('numero_documento');        
        parent::addAttribute('data_documento');//inserida pelo usuário        
        parent::addAttribute('data_lancamento');//inserida pelo sistema        
        parent::addAttribute('valor');        
        parent::addAttribute('observacao');        
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
    
    public function get_debito()
      {
           if (empty($this->debito)){
            $this->debito = new PlanoConta($this->conta_debito);
           }
    
           // returns the associated object
           return $this->debito;
      }
      public function get_credito()
      {
           if (empty($this->credito)){
            $this->credito = new PlanoConta($this->conta_credito);
           }
    
           // returns the associated object
           return $this->credito;
      }
      public function get_historico_padrao()
      {
           if (empty($this->historico_padrao)){
            $this->historico_padrao = new Historico($this->historico);
           }
    
           // returns the associated object
           return $this->historico_padrao;
      }
}
