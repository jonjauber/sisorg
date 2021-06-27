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
class LancamentoForm extends TStandardForm
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
        $this->form = new BootstrapFormBuilder('form_Lancamento');
        $this->form->setFormTitle( 'Lançamentos Diários' );
        
        // defines the database
        parent::setDatabase('permission');
        
        // defines the active record
        parent::setActiveRecord('Lancamento');
        
        // create the form fields
        $id            = new TEntry('id');           
        
        $competencia   = new TCombo('competencia');//ano-mês
        $escolha_tipo = array();
        $escolha_tipo['JAN/'.date('Y')] = 'JAN/'.date('Y');  
        $escolha_tipo['FEV/'.date('Y')] = 'FEV/'.date('Y');
        $escolha_tipo['MAR/'.date('Y')] = 'MAR/'.date('Y');
        $escolha_tipo['ABR/'.date('Y')] = 'ABR/'.date('Y');
        $escolha_tipo['MAI/'.date('Y')] = 'MAI/'.date('Y');
        $escolha_tipo['JUN/'.date('Y')] = 'JUN/'.date('Y');
        $escolha_tipo['JUL/'.date('Y')] = 'JUL/'.date('Y');
        $escolha_tipo['AGO/'.date('Y')] = 'AGO/'.date('Y');
        $escolha_tipo['SET/'.date('Y')] = 'SET/'.date('Y');
        $escolha_tipo['OUT/'.date('Y')] = 'OUT/'.date('Y');
        $escolha_tipo['NOV/'.date('Y')] = 'NOV/'.date('Y');
        $escolha_tipo['DEZ/'.date('Y')] = 'DEZ/'.date('Y');
        $competencia->addItems($escolha_tipo);
        
        if (TSession::getValue('login') != 'admin') {            
            //TSession::getValue('chave'))) é capturada em LoginForm.class.php            
               $criteria = new TCriteria;
               $criteria->add(new TFilter('id_matriz', '=', TSession::getValue('id_matriz')));                              
               $conta_debito = new TDBUniqueSearch('conta_debito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao', $criteria);
               $conta_credito = new TDBUniqueSearch('conta_credito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao', $criteria);
               $historico = new TDBUniqueSearch('historico', 'enfermeirovirtual', 'Historico', 'id', 'descricao', 'descricao', $criteria);
                          
        } else{
            $conta_debito = new TDBUniqueSearch('conta_debito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao');
            $conta_credito = new TDBUniqueSearch('conta_credito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao');
            $historico = new TDBUniqueSearch('historico', 'enfermeirovirtual', 'Historico', 'id', 'descricao', 'descricao');
        } 
        $conta_debito->setMinLength(1);//para onde foi o valor
        $conta_credito->setMinLength(1);//de onde veio o valor        
        $historico->setMinLength(1);//histórico padrão para balancetes       
                             
        $numero_documento         = new TEntry('numero_documento');
        $data_documento         = new TDate('data_documento');//inserida pelo usuário
        $data_lancamento         = new TDate('data_lancamento');//inserida pelo sistema 
        $valor         = new TNumeric('valor', '2', ',', '.' );
        $observacao         = new TEntry('observacao');
        //$id_matriz         = new TEntry('id_matriz');//inserida pelo variável de sessão         
        
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink( _t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addActionLink( _t('Back'), new TAction(array('LancamentoList','onReload')), 'far:arrow-alt-circle-left blue');       
        
        $data_documento->setMask('dd/mm/yyyy');        
        $data_lancamento->setValue(date('d/m/Y'));                                
        $data_lancamento->setMask('dd/mm/yyyy');         
        
        // define the sizes
        $id->setSize('100%');        
        $competencia->setSize('100%');                
        $conta_debito->setSize('100%');
        $historico->setSize('100%');
        $conta_credito->setSize('100%');
        $numero_documento->setSize('100%');
        $data_documento->setSize('100%');
        $data_lancamento->setSize('100%');
        $valor->setSize('100%');
        $observacao->setSize('100%');
        
        // outros
        $id->setEditable(false);
        //$competencia->setEditable(false);
        $data_lancamento->setEditable(false);
        
        // validations        
        $competencia->addValidation('Competência', new TRequiredValidator);
        $conta_debito->addValidation('Conta Débito', new TRequiredValidator);        
        $historico->addValidation('Histórico Padrão', new TRequiredValidator);
        $conta_credito->addValidation('Conta Crédito', new TRequiredValidator);
        $numero_documento->addValidation('Número Documento', new TRequiredValidator);
        $data_documento->addValidation('Data Documento', new TRequiredValidator);
        $valor->addValidation('Valor', new TRequiredValidator);
                
        $this->form->addFields( [new TLabel('ID')], [$id], [new TLabel('Competência')], [$competencia]);                        
        $this->form->addFields( [new TLabel('Entrada Deb')], [$conta_debito], [new TLabel('Histórico')], [$historico]);        
        $this->form->addFields( [new TLabel('Saída Cred')], [$conta_credito], [new TLabel('Documento')], [$numero_documento]);        
        $this->form->addFields( [new TLabel('Data Documento')], [$data_documento], [new TLabel('Data Lançamento')], [$data_lancamento]);        
        $this->form->addFields( [new TLabel('Valor')], [$valor], [new TLabel('Observação')], [$observacao]);       
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'LancamentoList'));
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
            
            $object = new Lancamento;
            $object->id = $data->id;            
            $object->competencia = $data->competencia;            
            $object->conta_debito = $data->conta_debito;
            $object->historico = $data->historico;
            $object->conta_credito = $data->conta_credito;
            $object->numero_documento = $data->numero_documento;
            
            $object->data_documento = TDate::date2us($data->data_documento);
            //$object->data_documento = $data->data_documento;
            
            $object->data_lancamento = TDate::date2us($data->data_lancamento);
            //$object->data_lancamento = $data->data_lancamento;
            
            $object->valor = $data->valor;
            $object->observacao = $data->observacao;
            $object->id_filial = TSession::getValue('id_filial');
            $object->id_matriz = TSession::getValue('id_matriz');
            
            //incrementa a conte destino debito
            $newTab1 = new PlanoConta($data->conta_debito);
            $newTab1->valor -= (int) TSession::getValue('valor_insert_old');
            $newTab1->valor += (int) $data->valor;
            $newTab1->store(); 
                    
            //decrementa a conta origem crédito
            $newTab2 = new PlanoConta($data->conta_credito);
            $newTab2->valor += (int) TSession::getValue('valor_insert_old');
            $newTab2->valor -= (int) $data->valor;
            $newTab2->store();
            
            TSession::setValue('valor_insert_old','');
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
                TSession::setValue('valor_insert_old','');
                // open a transaction with database 'permission'
                TTransaction::open('permission');
                
                // instantiates object Pessoa
                $object = new Lancamento($key);  
                TSession::setValue('valor_insert_old',$object->valor);
                $object->data_lancamento = TDate::date2br($object->data_lancamento);
                $object->data_documento = TDate::date2br($object->data_documento);
                
                // fill the form with the active record data
                $this->form->setData($object);
                
                // close the transaction
                TTransaction::close();
            }
            /**else
            {
                $this->form->clear();
            }***/
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
