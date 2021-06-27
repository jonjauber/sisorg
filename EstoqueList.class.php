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
class EstoqueList extends TStandardList
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
        parent::setActiveRecord('Estoque');   // defines the active record
        parent::setDefaultOrder('data_insert', 'asc');         // defines the default order
        parent::addFilterField('conta_debito', '=', 'conta_debito'); // filterField, operator, formField
        parent::addFilterField('conta_credito', '=', 'conta_credito'); // filterField, operator, formField
        parent::addFilterField('produto', '=', 'produto'); // filterField, operator, formField
        
        /*Filtro de listagem de lançamentos - se o usuário for admin, lista todos.
         * se o usuário for diferente de admin mostra os usuários de sua competência*/
        
        if (TSession::getValue('login') != 'admin') {            
            //TSession::getValue('chave'))) é capturada em LoginForm.class.php
            if(TSession::getValue('tipo') != 'S'){
               $criteria = new TCriteria;
               $criteria->add(new TFilter('quantidade_edit', '>', 0));
               $criteria->add(new TFilter('id_filial', '=', TSession::getValue('id_filial')));                              
               parent::setCriteria($criteria);   
            }            
            if(TSession::getValue('tipo') == 'S'){
               $criteria = new TCriteria;
               $criteria->add(new TFilter('quantidade_edit', '>', 0));
               //$criteria->add(new TFilter('id_filial', '=', TSession::getValue('id_filial')));               
               $criteria->add(new TFilter('id_matriz', '=', TSession::getValue('id_matriz')));                
               parent::setCriteria($criteria);   
            }    
        }    
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Estoque');
        $this->form->setFormTitle( 'Controle de Estoque' );
        
        // create the form fields               
        if (TSession::getValue('login') != 'admin') {            
            //TSession::getValue('chave'))) é capturada em LoginForm.class.php            
               $criteria = new TCriteria;
               $criteria->add(new TFilter('id_matriz', '=', TSession::getValue('id_matriz')));                              
               $produto = new TDBUniqueSearch('produto', 'enfermeirovirtual', 'Produto', 'id', 'descricao', 'descricao', $criteria);
                          
        } else{
            $produto = new TDBUniqueSearch('produto', 'enfermeirovirtual', 'Produto', 'id', 'descricao', 'descricao');
        }          
        
        // add the fields
        //$this->form->addFields( [new TLabel('Matriz ou Sede')], [$id_matriz] );        
        $this->form->addFields( [new TLabel('Produto')], [$produto] );        

        $produto->setMinLength(1);
        //$id_matriz->setSize('100%');        
        $produto->setSize('100%');        
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Estoque_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('EstoqueForm', 'onEdit')), 'fa:plus green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        //$this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Id', 'center', 50);        
        $column_produto = new TDataGridColumn('produto_id->descricao', 'Produto', 'left');                 
        //$column_conta_debito = new TDataGridColumn('debito->descricao', 'Conta Débito', 'left');
        //$column_historico = new TDataGridColumn('historico_id->descricao', 'Histórico', 'left');        
        $column_data_insert = new TDataGridColumn('data_insert','Data', 'center');       
        $column_quantidade_edit = new TDataGridColumn('quantidade_edit','Qtd Total', 'left');
        $column_valor_edit = new TDataGridColumn('valor_edit','Vl Total', 'center');
        $column_quantidade_insert = new TDataGridColumn('quantidade_insert','Qtd Inserida', 'left');
        $column_valor_insert = new TDataGridColumn('valor_insert','Vl Inserido', 'center');
        
        $column_valor_edit->setTransformer(array($this, 'formatValue'));
        $column_valor_insert->setTransformer(array($this, 'formatValue'));
        
        
        $column_quantidade_insert->enableAutoHide(500);
        $column_valor_insert->enableAutoHide(500);
        $column_valor_edit->enableAutoHide(500);        
        $column_data_insert->enableAutoHide(500); 

        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);        
        $this->datagrid->addColumn($column_produto);                  
        //$this->datagrid->addColumn($column_conta_debito); 
        //$this->datagrid->addColumn($column_historico);
        $this->datagrid->addColumn($column_quantidade_insert);        
        $this->datagrid->addColumn($column_valor_insert);
        $this->datagrid->addColumn($column_quantidade_edit);        
        $this->datagrid->addColumn($column_valor_edit);
        $this->datagrid->addColumn($column_data_insert);
        $column_data_insert->setTransformer(array($this, 'formatDate'));                  
        
        $order_produto = new TAction(array($this, 'onReload'));
        $order_produto->setParameter('order', 'produto');
        $column_produto->setAction($order_produto);
        
        //$order_conta_debito = new TAction(array($this, 'onReload'));
        //$order_conta_debito->setParameter('order', 'conta_debito');
        //$column_conta_debito->setAction($order_conta_debito);
        
        $order_data_insert = new TAction(array($this, 'onReload'));
        $order_data_insert->setParameter('order', 'data_insert');
        $column_data_insert->setAction($order_data_insert);
        
        // create EDIT action
        $action_edit = new TDataGridAction(array('EstoqueForm', 'onEdit'));
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
    
     public function formatDate($date, $object)
    {
        $dt = new DateTime($date);
        return $dt->format('d/m/Y');
    }
    public function formatValue($value, $object)
    {
        if (!$value) {
                $value = 0;
            }
        return "R$ " . number_format($value, 2, ",", ".");
    }
}
