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
class LancamentoList extends TStandardList
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
        parent::setActiveRecord('Lancamento');   // defines the active record
        parent::setDefaultOrder('competencia', 'asc');         // defines the default order
        parent::addFilterField('conta_debito', '=', 'conta_debito'); // filterField, operator, formField
        //parent::addFilterField('conta_credito', '=', 'conta_credito'); // filterField, operator, formField
        parent::addFilterField('numero_documento', '=', 'numero_documento'); // filterField, operator, formField
        
        /*Filtro de listagem de lançamentos - se o usuário for admin, lista todos.
         * se o usuário for diferente de admin mostra os usuários de sua competência*/
        
        if (TSession::getValue('login') != 'admin') {            
            //TSession::getValue('chave'))) é capturada em LoginForm.class.php
            if(TSession::getValue('tipo') != 'S'){
               $criteria = new TCriteria;
               $criteria->add(new TFilter('id_filial', '=', TSession::getValue('id_filial')));                              
               parent::setCriteria($criteria);   
            }            
            if(TSession::getValue('tipo') == 'S'){
               $criteria = new TCriteria;
               //$criteria->add(new TFilter('id_filial', '=', TSession::getValue('id_filial')));               
               $criteria->add(new TFilter('id_matriz', '=', TSession::getValue('id_matriz')));                
               parent::setCriteria($criteria);   
            }    
        }    
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Lancamento');
        $this->form->setFormTitle( 'Lançamentos Diários' );
        
        // create the form fields               
        if (TSession::getValue('login') != 'admin') {            
            //TSession::getValue('chave'))) é capturada em LoginForm.class.php            
               $criteria = new TCriteria;
               $criteria->add(new TFilter('id_matriz', '=', TSession::getValue('id_matriz')));                              
               $conta_debito = new TDBUniqueSearch('conta_debito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao', $criteria);
                          
        } else{
            $conta_debito = new TDBUniqueSearch('conta_debito', 'enfermeirovirtual', 'PlanoConta', 'id', 'descricao', 'descricao');
        }          
        
        // add the fields
        //$this->form->addFields( [new TLabel('Matriz ou Sede')], [$id_matriz] );        
        $this->form->addFields( [new TLabel('Plano de Contas')], [$conta_debito] );        

        $conta_debito->setMinLength(1);
        //$id_matriz->setSize('100%');        
        $conta_debito->setSize('100%');        
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Lancamento_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('LancamentoForm', 'onEdit')), 'fa:plus green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        //$this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Id', 'center', 50);        
        $column_competencia = new TDataGridColumn('competencia', 'Competência', 'left');                 
        $column_conta_debito = new TDataGridColumn('debito->descricao', 'Entrada Deb', 'left');
        $column_historico = new TDataGridColumn('historico_padrao->descricao', 'Histórico', 'left');
        $column_conta_credito = new TDataGridColumn('credito->descricao','Saída Cred', 'left');
        $column_data_documento = new TDataGridColumn('data_documento','Data Documento', 'center');       
        $column_valor = new TDataGridColumn('valor','Valor', 'center');
        
        $column_valor->setTransformer(function($value, $object, $row) {
            if (!$value) {
                $value = 0;
            }
            return "R$ " . number_format($value, 2, ",", ".");
        });
        
        $column_conta_credito->enableAutoHide(500);
        $column_conta_debito->enableAutoHide(500);
        $column_historico->enableAutoHide(500);        
        $column_historico->enableAutoHide(500); 

        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);        
        $this->datagrid->addColumn($column_competencia);                  
        $this->datagrid->addColumn($column_conta_debito); 
        $this->datagrid->addColumn($column_historico);
        $this->datagrid->addColumn($column_conta_credito);
        
        $this->datagrid->addColumn($column_data_documento);
        $column_data_documento->setTransformer(array($this, 'formatDate'));
        
        
        $this->datagrid->addColumn($column_valor);       
        
        $order_competencia = new TAction(array($this, 'onReload'));
        $order_competencia->setParameter('order', 'competencia');
        $column_competencia->setAction($order_competencia);
        
        $order_conta_debito = new TAction(array($this, 'onReload'));
        $order_conta_debito->setParameter('order', 'conta_debito');
        $column_conta_debito->setAction($order_conta_debito);
        
        $order_data_documento = new TAction(array($this, 'onReload'));
        $order_data_documento->setParameter('order', 'data_documento');
        $column_data_documento->setAction($order_data_documento);
        
        // create EDIT action
        $action_edit = new TDataGridAction(array('LancamentoForm', 'onEdit'));
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
}
