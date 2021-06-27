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
class ProdutoForm extends TStandardForm
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
        $this->form = new BootstrapFormBuilder('form_Produto');
        $this->form->setFormTitle( 'Cadastro de Produtos' );
        
        // defines the database
        parent::setDatabase('permission');
        
        // defines the active record
        parent::setActiveRecord('Produto');
        
        // create the form fields
        $id            = new TEntry('id');        
        $descricao         = new TEntry('descricao');               
        
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink( _t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addActionLink( _t('Back'), new TAction(array('ProdutoList','onReload')), 'far:arrow-alt-circle-left blue');
        
        // define the sizes
        $id->setSize('50%');        
        $descricao->setSize('100%');                
        
        // outros
        $id->setEditable(false);
        
        // validations        
        $descricao->addValidation('Descrição', new TRequiredValidator);        
        
        $this->form->addFields( [new TLabel('ID')], [$id]);        
        $this->form->addFields( [new TLabel('Descrição')], [$descricao]);              
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'ProdutoList'));
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
            
            $object = new Produto;
            $object->id = $data->id;            
            $object->descricao = $data->descricao;            
            $object->id_matriz = TSession::getValue('id_matriz');
            $object->id_filial = TSession::getValue('id_filial');
            
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
                $object = new Produto($key);
                
                unset($object->password);              
                
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
