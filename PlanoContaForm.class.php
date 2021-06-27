<?php
/**
 * arquivo derivado dos arquivos do Adianti Framework
 * @version    1.0
 * @package    sisorg
 * @subpackage control
 * @author     Jacques Geraldo Rocha
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */

//http://www.portaldecontabilidade.com.br/guia/planodecontas.htm
class PlanoContaForm extends TStandardForm
{
    protected $form; // form    
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_PlanoConta');
        $this->form->setFormTitle( 'Plano de Contas' );
        
        // defines the database
        parent::setDatabase('permission');
        
        // defines the active record
        parent::setActiveRecord('PlanoConta');
        
        // create the form fields
        $id            = new TEntry('id');
        //$id_conta_superior          = new TUniqueSearch('id_conta_superior');
        
        if (TSession::getValue('login') != 'admin') { 
            $criteria2 = new TCriteria;                    
            $criteria2->add(new TFilter('id_matriz', '=', TSession::getValue('id_matriz')));      
            $id_conta_superior = new TDBUniqueSearch('id_conta_superior', 'enfermeirovirtual', 'PlanoConta', 'codigo_conta', 'descricao', 'descricao', $criteria2);
        }else{
            $id_conta_superior = new TDBUniqueSearch('id_conta_superior', 'enfermeirovirtual', 'PlanoConta', 'codigo_conta', 'descricao', 'descricao');
        }    
        
        $codigo_conta          = new TEntry('codigo_conta');
        $descricao          = new TEntry('descricao');        
        $valor          = new TNumeric('valor', '2', ',', '.' );       
        
        $id_conta_superior->setMask('{codigo_conta} - {descricao}');
        $id_conta_superior->setMinLength(1);
        //$idMatriz         = new TText('idMatriz');               
        //$idFilial         = new TText('idFilial');               
        //$periodo   = new TCombo('periodo');
        //$escolha_periodo = array();
        //$escolha_periodo['M'] = 'Manhã';
        //$escolha_periodo['T'] = 'Tarde';
        //$escolha_periodo['N'] = 'Noite';
        //O Atributo periodo, M - Manhã, T - Tarde e N - Noite é irrelevante para o cliente. Não é necessário esse controle.
        //$periodo->addItems($escolha_periodo);
        
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink( _t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addActionLink( _t('Back'), new TAction(array('PlanoContaList','onReload')), 'far:arrow-alt-circle-left blue');
        
        
        $change_action = new TAction(array($this, 'onChangeAction'));
        $id_conta_superior->setChangeAction($change_action);
        //$id_conta_superior->setChangeAction(new TAction(array($this, 'onChangeAction')));
        
        // define the sizes
        $id->setSize('50%');
        $id_conta_superior->setSize('100%');
        $codigo_conta->setSize('100%');
        $descricao->setSize('100%');        
        $valor->setSize('100%');        
        
        // outros
        $id->setEditable(false);       
        $codigo_conta->setEditable(false);             
        
        
        // validations
        $id_conta_superior->addValidation('Conta Superior', new TRequiredValidator);
        $codigo_conta->addValidation('Codigo da Conta', new TRequiredValidator);
        $descricao->addValidation('Descrição', new TRequiredValidator);        
        $valor->addValidation('Valor', new TRequiredValidator);                 
        
        $this->form->addFields( [new TLabel('ID')], [$id]);
        $this->form->addFields( [new TLabel('Conta Superior:')], [$id_conta_superior], [new TLabel('Código da Conta')], [$codigo_conta] );        
        //$this->form->addFields( [new TLabel('Código da Conta')], [$codigo_conta]);
        $this->form->addFields( [new TLabel('Descrição')], [$descricao], [new TLabel('Valor')], [$valor]);                
        //$this->form->addFields( [new TLabel('Valor')], [$valor]);
        
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'PlanoContaList'));
        $container->add($this->form);

        // add the container to the page
        parent::add($container);
    }       
     

    public function onSave()
    {
        try
        {
            TTransaction::open($this->database);
            
            $data = $this->form->getData();
            
            $object = new PlanoConta;
            $object->id = $data->id;
            $object->id_conta_superior = $data->id_conta_superior;
            $object->codigo_conta = $data->codigo_conta;
            $object->descricao = $data->descricao;
            $object->id_filial = TSession::getValue('id_filial');
            $object->id_matriz = TSession::getValue('id_matriz');
            
            if($data->id == NULL){
                $newTab = new PlanoConta(TSession::getValue('codigoPai'));
                $newTab->contador++;
                $newTab->store();   
            } 
            
            $this->form->validate();
            $object->store();
            $data->id = $object->id;
            $this->form->setData($data);             
                    
            TTransaction::close();
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
            
            return $object;
        }
        catch (Exception $e) // in case of exception
        {
            // get the form data
            $object = $this->form->getData($this->activeRecord);
            $this->form->setData($object);
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key=$param['key'];
                
                // open a transaction with database 'permission'
                TTransaction::open('permission');
                
                // instantiates object System_user
                $object = new PlanoConta($key);                          
                
                // fill the form with the active record data
                $this->form->setData($object);
                
                // close the transaction
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public static function onChangeAction($param)
    {    
      TTransaction::open("enfermeirovirtual");
      $data = '';
      $codigoPai = '';
      TSession::setValue('codigoPai','');
      $conta = PlanoConta::orderBy('descricao','asc')
              ->where("codigo_conta","=",$param['id_conta_superior'])
              ->where("id_matriz", "=", TSession::getValue('id_matriz'))
              ->load();   
      foreach($conta as $cont)
      {
         $data.=$cont->contador;         
         $codigoPai.=$cont->id;         
      }     
      TSession::setValue('codigoPai',$codigoPai);
      TTransaction::close();
      TForm::sendData('form_PlanoConta',(object)['codigo_conta'=>$param['id_conta_superior'].$data+1]);
    }    
}
