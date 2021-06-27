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
class PessoaForm extends TStandardForm
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
        $this->form = new BootstrapFormBuilder('form_Pessoa');
        $this->form->setFormTitle( 'Gestão de Pessoas' );
        
        // defines the database
        parent::setDatabase('permission');
        
        // defines the active record
        parent::setActiveRecord('Pessoa');
        
        // create the form fields
        $id            = new TEntry('id');        
        $nome         = new TEntry('nome');               
        $cpf_cnpj         = new TEntry('cpf_cnpj');       
        $endereco         = new TEntry('endereco');               
        $telefone         = new TEntry('telefone'); 
        $telefone->setMask('(00)0.0000.0000');
        $e_mail         = new TEntry('e_mail');
        
        //$tipo         = new TEntry('tipo');
        $tipo   = new TCombo('tipo');
        $escolha_tipo = array();
        $escolha_tipo['A'] = 'Atendido';  
        $escolha_tipo['D'] = 'Doador/Patrocinador';
        $escolha_tipo['F'] = 'Funcionário';
        $escolha_tipo['R'] = 'Fornecedor';
        $escolha_tipo['V'] = 'Voluntario';        
        $tipo->addItems($escolha_tipo);
        
         if (TSession::getValue('login') != 'admin') {            
            //TSession::getValue('chave'))) é capturada em LoginForm.class.php
            if(TSession::getValue('tipo') != 'S'){
               $criteria = new TCriteria;
               $criteria->add(new TFilter('id', '=', TSession::getValue('id_filial')));                              
               $id_filial         = new TDBUniqueSearch('id_filial', 'enfermeirovirtual', 'Filial', 'id', 'nome', 'nome', $criteria);
            }            
            if(TSession::getValue('tipo') == 'S'){
               $criteria2 = new TCriteria;
               //$criteria->add(new TFilter('id_filial', '=', TSession::getValue('id_filial')));               
               $criteria2->add(new TFilter('id_matriz', '=', TSession::getValue('id_matriz')));                
               $id_filial         = new TDBUniqueSearch('id_filial', 'enfermeirovirtual', 'Filial', 'id', 'nome', 'nome', $criteria2);   
            }    
        }else{        
                $id_filial         = new TDBUniqueSearch('id_filial', 'enfermeirovirtual', 'Filial', 'id', 'nome', 'nome');   
        }
        $id_filial->setMask('{nome} - {endereco}');
        $id_filial->setMinLength(1);
        
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink( _t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addActionLink( _t('Back'), new TAction(array('PessoaList','onReload')), 'far:arrow-alt-circle-left blue');
        
        // define the sizes
        $id->setSize('100%');        
        $nome->setSize('100%');                
        $cpf_cnpj->setSize('100%');
        $endereco->setSize('100%');
        $telefone->setSize('100%');
        $e_mail->setSize('100%');
        $tipo->setSize('40%');
        //$status->setSize('100%');
        $id_filial->setSize('60%');
        
        // outros
        $id->setEditable(false);
        
        // validations        
        $nome->addValidation('Nome', new TRequiredValidator);        
        $cpf_cnpj->addValidation('Cpf ou Cnpj', new TRequiredValidator);
        $endereco->addValidation('Endereço', new TRequiredValidator);
        $telefone->addValidation('Telefone', new TRequiredValidator);
        $e_mail->addValidation('Email', new TEmailValidator());
        $tipo->addValidation('Tipo', new TRequiredValidator);
        //$status->addValidation('Status', new TRequiredValidator);
        $id_filial->addValidation('Filial', new TRequiredValidator);
        
        $this->form->addFields( [new TLabel('ID')], [$id], [new TLabel('Nome')], [$nome]);        
        //$this->form->addFields( [new TLabel('Nome')], [$nome]);              
        $this->form->addFields( [new TLabel('Cpf ou Cnpj')], [$cpf_cnpj], [new TLabel('Endereço')], [$endereco]);
        //$this->form->addFields( [new TLabel('Endereço')], [$endereco]);
        $this->form->addFields( [new TLabel('Telefone')], [$telefone], [new TLabel('Email')], [$e_mail]);
        //$this->form->addFields( [new TLabel('Email')], [$e_mail]);
        $this->form->addFields( [new TLabel('Tipo')], [$tipo]);
        //$this->form->addFields( [new TLabel('Status')], [$status]);
        $this->form->addFields( [new TLabel('Filial')], [$id_filial]);
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'PessoaList'));
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
            
            $object = new Pessoa;
            $object->id = $data->id;            
            $object->nome = $data->nome;            
            $object->cpf_cnpj = $data->cpf_cnpj;
            $object->endereco = $data->endereco;
            $object->telefone = $data->telefone;
            $object->e_mail = $data->e_mail;
            $object->tipo = $data->tipo;
            //$object->status = $data->status;
            $object->active = 'Y';
            $object->id_filial = $data->id_filial;
            $object->id_matriz = TSession::getValue('id_matriz');
            
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
                
                // instantiates object Pessoa
                $object = new Pessoa($key);                        
                
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
}
