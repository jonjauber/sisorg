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
class FilialList extends TStandardList
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $transformCallback;
    
     /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        parent::setDatabase('permission');            // defines the database
        parent::setActiveRecord('Filial');   // defines the active record
        parent::setDefaultOrder('nome', 'asc');         // defines the default order
        parent::addFilterField('id_matriz', '=', 'id_matriz'); // filterField, operator, formField
        parent::addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        
        if (TSession::getValue('login') != 'admin') { 
            $criteria3 = new TCriteria;                    
            $criteria3->add(new TFilter('id', '=', TSession::getValue('id_filial')));   
            parent::setCriteria($criteria3);           
        } 
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Filial');
        $this->form->setFormTitle( 'Filial ou Departamento' );
        
        // create the form fields
        //$id = new TEntry('id');        
        $nome = new TEntry('nome');        
        
        // add the fields
        //$this->form->addFields( [new TLabel('Id')], [$id] );        
        $this->form->addFields( [new TLabel('Nome')], [$nome] );        

        //$id->setSize('30%');        
        $nome->setSize('70%');        
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Filial_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('FilialForm', 'onEdit')), 'fa:plus green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        //$this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Id', 'center', 50);        
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');        
        //$column_cnpj = new TDataGridColumn('cnpj', 'Cnpj', 'left'); 
        $column_endereco = new TDataGridColumn('endereco', 'Endereço', 'left');        
        $column_id_matriz = new TDataGridColumn('matriz->nome', 'Matriz', 'left');  


        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);        
        $this->datagrid->addColumn($column_nome);        
        //$this->datagrid->addColumn($column_cnpj);        
        $this->datagrid->addColumn($column_endereco);        
        $this->datagrid->addColumn($column_id_matriz);   


        // creates the datagrid column actions
        //$order_id = new TAction(array($this, 'onReload'));
        //$order_id->setParameter('order', 'id');
        //$column_id->setAction($order_id);
        
        $order_nome = new TAction(array($this, 'onReload'));
        $order_nome->setParameter('order', 'nome');
        $column_nome->setAction($order_nome);
        
        $order_matriz = new TAction(array($this, 'onReload'));
        $order_matriz->setParameter('order', 'id_matriz');
        $column_id_matriz->setAction($order_matriz);
        
        // create EDIT action
        $action_edit = new TDataGridAction(array('FilialForm', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit blue');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('far:trash-alt red');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);     
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table fa-fw blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf fa-fw red' );
        $dropdown->addAction( _t('Save as XML'), new TAction([$this, 'onExportXML'], ['register_state' => 'false', 'static'=>'1']), 'fa:code fa-fw green' );
        $panel->addHeaderWidget( $dropdown );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }    
}
