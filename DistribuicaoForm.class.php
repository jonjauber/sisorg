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
class DistribuicaoForm extends TStandardForm
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
        $this->form = new BootstrapFormBuilder('form_Distribuicao');
        $this->form->setFormTitle( 'Distribuição e Retirada de Estoque' );
        
        // defines the database
        parent::setDatabase('permission');
        
        // defines the active record
        parent::setActiveRecord('Distribuicao');
        
        // create the form fields
        $id            = new TEntry('id');       
        $data_insert        = new TDate('data_insert');//inserida pelo sistema      
        
        if (TSession::getValue('login') != 'admin') {            
            //TSession::getValue('chave'))) é capturada em LoginForm.class.php            
               $criteria = new TCriteria;
               $criteria->add(new TFilter('id_matriz', '=', TSession::getValue('id_matriz')));                              
               $conta_debito = new TDBUniqueSearch('conta_debito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao', $criteria);
               $conta_credito = new TDBUniqueSearch('conta_credito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao', $criteria);
               $historico = new TDBUniqueSearch('historico', 'enfermeirovirtual', 'Historico', 'id', 'descricao', 'descricao', $criteria);
               $produto = new TDBUniqueSearch('produto', 'enfermeirovirtual', 'Produto', 'id', 'descricao', 'descricao', $criteria);
               $pessoa = new TDBUniqueSearch('pessoa', 'enfermeirovirtual', 'Pessoa', 'id', 'nome', 'nome', $criteria);
                          
        } else{
            $conta_debito = new TDBUniqueSearch('conta_debito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao');
            $conta_credito = new TDBUniqueSearch('conta_credito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao');
            $historico = new TDBUniqueSearch('historico', 'enfermeirovirtual', 'Historico', 'id', 'descricao', 'descricao');
            $produto = new TDBUniqueSearch('produto', 'enfermeirovirtual', 'Produto', 'id', 'descricao', 'descricao');
            $pessoa = new TDBUniqueSearch('pessoa', 'enfermeirovirtual', 'Pessoa', 'id', 'nome', 'nome');
        } 
        
        $conta_debito->setMinLength(1);//para onde foi o valor
        $conta_credito->setMinLength(1);//de onde veio o valor        
        $historico->setMinLength(1);//histórico padrão para balancetes       
        $produto->setMinLength(1);                             
        $quantidade         = new TNumeric('quantidade', '0', ',', '.' );       
        $valor         = new TNumeric('valor', '2', ',', '.' );     
        $pessoa->setMinLength(1);
        $observacao = new TEntry('observacao');                    
        
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink( _t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addActionLink( _t('Back'), new TAction(array('DistribuicaoList','onReload')), 'far:arrow-alt-circle-left blue');               
              
        $data_insert->setValue(date('d/m/Y'));//inserida pelo sistema                                
        $data_insert->setMask('dd/mm/yyyy'); 

        $change_action = new TAction(array($this, 'onChangeAction'));
        $produto->setChangeAction($change_action);
        
        // define the sizes
        $id->setSize('100%');        
        $produto->setSize('100%');
        $quantidade->setSize('100%');//inserida pelo usuário 
        $valor->setSize('100%');//inserido pelo usuário
        $pessoa->setSize('100%');
        $observacao->setSize('100%');        
        $data_insert->setSize('100%');//inserida pelo sistema        
        $conta_debito->setSize('100%');//para onde foi o valor
        $historico->setSize('100%');//histórico padrão do sistema
        $conta_credito->setSize('100%');//de onde veio o valor       
        
        // outros
        $id->setEditable(false);        
        $data_insert->setEditable(false);
        
        // validations        
        $produto->addValidation('Produto', new TRequiredValidator);
        $quantidade->addValidation('Quantidade', new TRequiredValidator);        
        $valor->addValidation('Valor', new TRequiredValidator);
        $pessoa->addValidation('Pessoa', new TRequiredValidator);
        //$observacao->addValidation('Observação', new TRequiredValidator);
        $data_insert->addValidation('Data', new TRequiredValidator);        
        $conta_debito->addValidation('Entrada Deb', new TRequiredValidator);        
        $historico->addValidation('Histórico Padrão', new TRequiredValidator);
        $conta_credito->addValidation('Saída Cred', new TRequiredValidator);        
                
        $this->form->addFields( [new TLabel('ID')], [$id], [new TLabel('Produto')], [$produto]);                        
        $this->form->addFields( [new TLabel('Quantidade')], [$quantidade], [new TLabel('Valor')], [$valor]);
        $this->form->addFields( [new TLabel('Pessoa')], [$pessoa], [new TLabel('Observação')], [$observacao]);     
        $this->form->addFields( [new TLabel('Data')], [$data_insert], [new TLabel('Entrada Deb')], [$conta_debito]);        
        $this->form->addFields( [new TLabel('Histórico')], [$historico], [new TLabel('Saída Cred')], [$conta_credito]);    
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'DistribuicaoList'));
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
            $criteria = new TCriteria();
            $criteria->add(new TFilter('produto','=',$data->produto));            
            $produtos = $repos->load($criteria);            
            $object = new Distribuicao;
            $this->form->validate();
                foreach ($produtos as $produto){
                    if($produto->quantidade_edit + (int) TSession::getValue('quantidade_old') >= $data->quantidade){
                        $object->id = $data->id;           
                        $object->produto = $data->produto;
                        $object->quantidade = $data->quantidade;
                        $object->valor = $data->valor;
                        $object->pessoa = $data->pessoa;
                        $object->observacao = $data->observacao;                    
                        $object->data_insert = TDate::date2us($data->data_insert);       
                        $object->conta_debito = $data->conta_debito;
                        $object->historico = $data->historico;
                        $object->conta_credito = $data->conta_credito;                       
                        $object->id_filial = TSession::getValue('id_filial');
                        $object->id_matriz = TSession::getValue('id_matriz');

                        //decrementa o valor e a quantidade do estoque
                        $newTab0 = new Estoque($produto->id);
                        $newTab0->quantidade_edit += (int) TSession::getValue('quantidade_old');
                        $newTab0->quantidade_edit -= (int) $data->quantidade;
                        $newTab0->valor_edit += (int) TSession::getValue('valor_old');
                        $newTab0->valor_edit -= (int) $data->valor;                        
                        $newTab0->store(); 

                        //incrementa a conte destino debito
                        $newTab1 = new PlanoConta($data->conta_debito);
                        $newTab1->valor -= (int) TSession::getValue('valor_old');
                        $newTab1->valor += (int) $data->valor;
                        $newTab1->store(); 

                        //decrementa a conta origem crédito
                        $newTab2 = new PlanoConta($data->conta_credito);
                        $newTab2->valor += (int) TSession::getValue('valor_old');
                        $newTab2->valor -= (int) $data->valor;
                        $newTab2->store();

                        TSession::setValue('valor_old', 0);                        
                        TSession::setValue('quantidade_old', 0); 
                        $object->store();
                        
                        //$data->id = $object->id;
                        //$this->form->setData($data);

                        TTransaction::close();            
                        new TMessage('info', AdiantiCoreTranslator::translate('Record saved')); 
                        $this->form->clear();                         
                        TForm::sendData('form_Distribuicao',(object)['data_insert'=>date('d/m/Y')]);                                                      
                        //return $object;
                    }else{
                        new TMessage('erro', 'Estoque insuficiente! '.($produto->quantidade_edit + (int) TSession::getValue('quantidade_old')).' unidades');         
                    }
                    
                }                
            
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
                TSession::setValue('valor_old', 0);
                TSession::setValue('quantidade_old', 0);
                // open a transaction with database 'permission'
                TTransaction::open('permission');
                
                // instantiates object Distribuicao
                $object = new Distribuicao($key);  
                //TSession::setValue('quantidade_insert_old',$object->quantidade_insert);
                TSession::setValue('valor_old',$object->valor);                
                TSession::setValue('quantidade_old',$object->quantidade);
                $object->data_insert = TDate::date2br($object->data_insert);
                
                //$object->quantidade_edit = $object->quantidade_edit - $object->quantidade_insert;
                //$object->valor_edit = $object->valor_edit - $object->valor_insert;                
                
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
      //CORRIGIR - QUANDO SE CLICA NO x PARA LIMPAR O COMBO ESSA MENSGEM MESMO ASSIM É EXIBIDA
          if(empty($estoque)){              
              new TMessage('erro', 'Estoque Inexistente! ');                  
              TForm::sendData('form_Distribuicao',(object)['produto'=>'']);
          }
          
          foreach ($estoque as $estoq){
              if($estoq->quantidade_edit == 0){
                  new TMessage('erro', 'Estoque insuficiente! '.$estoq->quantidade_edit.' unidades');         
              }
          }
         //$edit_quantidade.=$estoq->quantidade_edit;         
         //$edit_valor.=$estoq->valor_edit;         
         //$insert_quantidade.=$estoq->quantidade_insert;         
         //$insert_valor.=$estoq->valor_insert;
          
      
      TTransaction::close();
      //TForm::sendData('form_Estoque',(object)['quantidade_edit'=>$edit_quantidade]);
      //TForm::sendData('form_Estoque',(object)['valor_edit'=>$edit_valor]);
      //TForm::sendData('form_Estoque',(object)['quantidade_insert'=>'0']);
      //TForm::sendData('form_Estoque',(object)['valor_insert'=>'0']);
    }    
}
