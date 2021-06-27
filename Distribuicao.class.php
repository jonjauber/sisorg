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
class Distribuicao extends TRecord
{
    const TABLENAME = 'sis_distribuicao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}  

    private $debito;
    private $credito;
    private $historico_id;
    private $produto_id;
    private $pessoa_id;
    /**
     * Constructor method
     */
    //antes do registro verificar se ja existe o produto
    //caso tenha atualiza a tabela
    //caso nao tenha, cria-se um novo registro
    public function __construct($id = NULL)
    {
        parent::__construct($id);             
        parent::addAttribute('produto');//relacionamento com Produto
        parent::addAttribute('quantidade');//inserida pelo usuário
        parent::addAttribute('valor');//calculado pela média do valor estoque sobre a quantidade
        parent::addAttribute('pessoa');//pessoa (Física ou Jurídica) do sistema que recebeu a saída
        parent::addAttribute('observacao');//informações relevantes        
        parent::addAttribute('data_insert');//inserida pelo sistema                                            
        parent::addAttribute('conta_debito');//para onde foi o valor
        parent::addAttribute('historico');//histórico padrão para balancete        
        parent::addAttribute('conta_credito');//de onde veio o valor                           
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
      public function get_historico_id()
      {
           if (empty($this->historico_id)){
            $this->historico_id = new Historico($this->historico);
           }
    
           // returns the associated object
           return $this->historico_id;
      }
      
      public function get_produto_id()
      {
           if (empty($this->produto_id)){
            $this->produto_id = new Produto($this->produto);
           }
    
           // returns the associated object
           return $this->produto_id;
      }
      
      public function get_pessoa_id()
      {
           if (empty($this->pessoa_id)){
            $this->pessoa_id = new Pessoa($this->pessoa);
           }
    
           // returns the associated object
           return $this->pessoa_id;
      }
}
