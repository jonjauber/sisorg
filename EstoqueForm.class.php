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
class EstoqueForm extends TStandardForm
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
        $this->form = new BootstrapFormBuilder('form_Estoque');
        $this->form->setFormTitle( 'Controle de Estoque' );
        
        // defines the database
        parent::setDatabase('permission');
        
        // defines the active record
        parent::setActiveRecord('Estoque');
        
        // create the form fields
        $id            = new TEntry('id');           
        
        /**$competencia   = new TCombo('competencia');//ano-mês
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
        $competencia->addItems($escolha_tipo);**/
        $data_insert         = new TDate('data_insert');//inserida pelo sistema
        //$data_edit         = new TDate('data_edit');//pega a última data de insert
        
        if (TSession::getValue('login') != 'admin') {            
            //TSession::getValue('chave'))) é capturada em LoginForm.class.php            
               $criteria = new TCriteria;
               $criteria->add(new TFilter('id_matriz', '=', TSession::getValue('id_matriz')));                              
               $conta_debito = new TDBUniqueSearch('conta_debito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao', $criteria);
               $conta_credito = new TDBUniqueSearch('conta_credito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao', $criteria);
               $historico = new TDBUniqueSearch('historico', 'enfermeirovirtual', 'Historico', 'id', 'descricao', 'descricao', $criteria);
               $produto = new TDBUniqueSearch('produto', 'enfermeirovirtual', 'Produto', 'id', 'descricao', 'descricao', $criteria);
                          
        } else{
            $conta_debito = new TDBUniqueSearch('conta_debito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao');
            $conta_credito = new TDBUniqueSearch('conta_credito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao');
            $historico = new TDBUniqueSearch('historico', 'enfermeirovirtual', 'Historico', 'id', 'descricao', 'descricao');
            $produto = new TDBUniqueSearch('produto', 'enfermeirovirtual', 'Produto', 'id', 'descricao', 'descricao');
        } 
        
        $conta_debito->setMinLength(1);//para onde foi o valor
        $conta_credito->setMinLength(1);//de onde veio o valor        
        $historico->setMinLength(1);//histórico padrão para balancetes       
        $produto->setMinLength(1);                             
        $quantidade_insert         = new TNumeric('quantidade_insert', '0', ',', '.' );       
        $valor_insert         = new TNumeric('valor_insert', '2', ',', '.' );
        
        $quantidade_edit         = new TNumeric('quantidade_edit', '0', ',', '.' );       
        $valor_edit         = new TNumeric('valor_edit', '2', ',', '.' );       
        
        //$id_matriz         = new TEntry('id_matriz');//inserida pelo variável de sessão         
        
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink( _t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addActionLink( _t('Back'), new TAction(array('EstoqueList','onReload')), 'far:arrow-alt-circle-left blue');               
              
        $data_insert->setValue(date('d/m/Y'));//inserida pelo sistema                                
        $data_insert->setMask('dd/mm/yyyy'); 

        $change_action = new TAction(array($this, 'onChangeAction'));
        $produto->setChangeAction($change_action);
        
        // define the sizes
        $id->setSize('100%');        
        $data_insert->setSize('100%');//inserida pelo sistema
        //$data_edit->setSize('100%');
        $conta_debito->setSize('100%');//para onde foi o valor
        $historico->setSize('100%');//histórico padrão do sistema
        $conta_credito->setSize('100%');//de onde veio o valor                
        $produto->setSize('100%');
        $quantidade_insert->setSize('100%');//inserida pelo usuário
        //$quantidade_edit->setSize('100%');//pega a quantidade do banco e soma com a quantidade insert             
        $valor_insert->setSize('100%');//inserido pelo usuário
        //$valor_edit->setSize('100%');//pega o valor do banco e soma com o valor insert       
        
        // outros
        $id->setEditable(false);        
        $data_insert->setEditable(false);
        $quantidade_edit->setEditable(false);
        $valor_edit->setEditable(false);
        
        // validations        
        $conta_debito->addValidation('Entrada Deb', new TRequiredValidator);        
        $historico->addValidation('Histórico Padrão', new TRequiredValidator);
        $conta_credito->addValidation('Saída Cred', new TRequiredValidator);
        $produto->addValidation('Produto', new TRequiredValidator);
        $quantidade_insert->addValidation('Quantidade', new TRequiredValidator);        
        $valor_insert->addValidation('Valor', new TRequiredValidator);
                
        $this->form->addFields( [new TLabel('ID')], [$id], [new TLabel('Data de Inserção')], [$data_insert]);                        
        $this->form->addFields( [new TLabel('Entrada Deb')], [$conta_debito], [new TLabel('Histórico')], [$historico]);        
        $this->form->addFields( [new TLabel('Saída Cred')], [$conta_credito], [new TLabel('Produto')], [$produto]);        
        $this->form->addFields( [new TLabel('Qtd Atual')], [$quantidade_insert], [new TLabel('Vl Atual')], [$valor_insert]);                
        $this->form->addFields( [new TLabel('Qtd Total')], [$quantidade_edit], [new TLabel('Vl Total')], [$valor_edit]);                
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'EstoqueList'));
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
            
            $repos = new TRepository('Estoque');
            //$total = $repos->count();
            $criteria = new TCriteria();
            $criteria->add(new TFilter('produto','=',$data->produto));
            //$produto = $repos->count($criteria);
            $produtos = $repos->load($criteria);
            $count= $repos->count($criteria); 
            $object = new Estoque;
            if($count == 1){
                foreach ($produtos as $produto){                    
                    $newTab = new Estoque($produto->id);                
                    $newTab->data_insert = TDate::date2us($data->data_insert);       
                    $newTab->conta_debito = $data->conta_debito;
                    $newTab->historico = $data->historico;
                    $newTab->conta_credito = $data->conta_credito;            
                    $newTab->produto = $data->produto;            
                    $newTab->quantidade_insert = $data->quantidade_insert;                      
                    $newTab->valor_insert = $data->valor_insert;            
                    $newTab->id_filial = TSession::getValue('id_filial');
                    $newTab->id_matriz = TSession::getValue('id_matriz'); 
                    //Warning:  A non-numeric value encountered in 
                    //C:\xampp\htdocs\sisorg\app\control\sisorg\EstoqueForm.class.php on line 164 e 165
                    $newTab->quantidade_edit = (int) $data->quantidade_edit + $data->quantidade_insert;                 
                    $newTab->valor_edit = (int) $data->valor_edit + $data->valor_insert;
                    
                    //incrementa a conte destino debito
                    $newTab1 = new PlanoConta($data->conta_debito);
                    $newTab1->valor -= (int) TSession::getValue('valor_insert_old');
                    $newTab1->valor += $data->valor_insert;
                    $newTab1->store(); 
                    
                    //decrementa a conta origem crédito
                    $newTab2 = new PlanoConta($data->conta_credito);
                    $newTab2->valor += (int) TSession::getValue('valor_insert_old');
                    $newTab2->valor -= $data->valor_insert;
                    $newTab2->store();
                    
                    TSession::setValue('valor_insert_old','');
                    $this->form->validate();
                    $newTab->store();    
                }   
            }else{
                    $object->id = $data->id;           
                    $object->data_insert = TDate::date2us($data->data_insert);       
                    $object->conta_debito = $data->conta_debito;
                    $object->historico = $data->historico;
                    $object->conta_credito = $data->conta_credito;            
                    $object->produto = $data->produto;            
                    $object->quantidade_insert = $data->quantidade_insert;                      
                    $object->valor_insert = $data->valor_insert;            
                    $object->id_filial = TSession::getValue('id_filial');
                    $object->id_matriz = TSession::getValue('id_matriz');                    
                    $object->quantidade_edit = $data->quantidade_insert;  
                    $object->valor_edit = $data->valor_insert;                   
                    
                    //incrementa a conte destino debito
                    $newTab1 = new PlanoConta($data->conta_debito);
                    $newTab1->valor += (int) $data->valor_insert;
                    $newTab1->store(); 
                    
                    //decrementa a conta origem crédito
                    $newTab2 = new PlanoConta($data->conta_credito);
                    $newTab2->valor -= (int) $data->valor_insert;
                    $newTab2->store();
                    
                    $this->form->validate();
                    $object->store();
                }
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
                //TSession::setValue('quantidade_insert_old','');
                TSession::setValue('valor_insert_old','');
                // open a transaction with database 'permission'
                TTransaction::open('permission');
                
                // instantiates object Estoque
                $object = new Estoque($key);  
                //TSession::setValue('quantidade_insert_old',$object->quantidade_insert);
                TSession::setValue('valor_insert_old',$object->valor_insert);
                $object->data_insert = TDate::date2br($object->data_insert);
                $object->quantidade_edit = $object->quantidade_edit - $object->quantidade_insert;
                $object->valor_edit = $object->valor_edit - $object->valor_insert;                
                
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
    
    public static function onChangeAction($param)
    {    
      TTransaction::open("enfermeirovirtual");
      $edit_quantidade = '';
      $edit_valor = '';      
      //$insert_quantidade = '0';
      //$insert_valor = '0';  
      //TSession::setValue('codigoPai','');
      $estoque = Estoque::orderBy('produto','asc')
              ->where("produto","=",$param['produto'])
              ->where("id_matriz", "=", TSession::getValue('id_matriz'))
              ->load();   
      foreach($estoque as $estoq)
      {
         $edit_quantidade.=$estoq->quantidade_edit;         
         $edit_valor.=$estoq->valor_edit;         
         //$insert_quantidade.=$estoq->quantidade_insert;         
         //$insert_valor.=$estoq->valor_insert;
      }     
      
      TTransaction::close();
      TForm::sendData('form_Estoque',(object)['quantidade_edit'=>$edit_quantidade]);
      TForm::sendData('form_Estoque',(object)['valor_edit'=>$edit_valor]);
      TForm::sendData('form_Estoque',(object)['quantidade_insert'=>'0']);
      TForm::sendData('form_Estoque',(object)['valor_insert'=>'0']);
    }    
}
